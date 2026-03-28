<?php

use App\Events\FireAlertEvent;
use App\Events\HighHumidityEvent;
use App\Models\Device;
use App\Models\House;
use App\Models\Measurement;
use App\Models\Room;
use App\Models\Sensor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class, \Tests\TestCase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->house = House::factory()->create([
        "user_id" => $this->user->id
    ]);

    $this->room = Room::factory()->create([
        "house_id" => $this->house->id
    ]);

    $this->device = Device::factory()->create([
        "room_id" => $this->room->id,
        "api_key" => 'secret-api-key',
        "is_active" => true,
    ]);

    Event::fake();

    $this->room->sensors()->createMany([
        ["type" => "temperature"],
        ["type" => "humidity"]
    ]);
});

it("allows a logged in user to see their room measurements", function () {
    $sensor = Sensor::factory()->create([
        "room_id" => $this->room->id,
        "type" => "temperature",
    ]);

    Measurement::factory()->count(3)->create([
        "sensor_id" => $sensor->id,
        "value" => 24.5,
    ]);

    $response = actingAs($this->user)
        ->get(route('room.measurements', $this->room));

    $response->assertStatus(200);

    $response->assertJsonStructure([
        "temperature" => [
            "labels",
            "values",
        ]
    ]);
});

it("prevents an user from seeing someone else's room measurements", function () {
    $otherUser = User::factory()->create();

    $response = actingAs($otherUser)
        ->get(route('room.measurements', $this->room));

    $response->assertStatus(403);
});

it("rejects measurements if data types are invalid", function () {
    $response = $this->withHeaders([
        "X-API-KEY" => "secret-api-key",
    ])->postJson(('/api/measurements'), [
        "temperature" => "a hundred",
        "humidity" => ["test"],
        "light" => true,
    ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrors([
        "temperature",
        "humidity",
        "light",
    ]);
});

it("dispatches FireAlertEvent and HighHumidityEvent when values exceed thresholds", function () {
    $response = $this->withHeaders([
        "X-API-KEY" => "secret-api-key",
    ])->postJson(('/api/measurements'), [
        "temperature" => config("smarthome.thresholds.fire_temperature") + 1,
        "humidity" => config("smarthome.thresholds.humidity") + 1,
    ]);

    $response->assertStatus(200);

    Event::assertDispatched(FireAlertEvent::class);
    Event::assertDispatched(HighHumidityEvent::class);
});

it("does not dispatch alerts when measurement values are normal", function () {
    $response = $this->withHeaders([
        "X-API-KEY" => "secret-api-key",
    ])->postJson(('/api/measurements'), [
        "temperature" => config("smarthome.thresholds.fire_temperature") - 1,
        "humidity" => config("smarthome.thresholds.humidity") - 1,
    ]);

    $response->assertStatus(200);

    Event::assertNotDispatched(FireAlertEvent::class);
    Event::assertNotDispatched(HighHumidityEvent::class);
});

it("prevents events from spamming emails to user", function () {
    $this->withHeaders([
        "X-API-KEY" => "secret-api-key",
    ])->postJson(('/api/measurements'), [
        "temperature" => config("smarthome.thresholds.fire_temperature") + 1,
        "humidity" => config("smarthome.thresholds.humidity") + 1,
    ]);

    $this->withHeaders([
        "X-API-KEY" => "secret-api-key",
    ])->postJson(('/api/measurements'), [
        "temperature" => config("smarthome.thresholds.fire_temperature") + 1,
        "humidity" => config("smarthome.thresholds.humidity") + 1,
    ]);

    Event::assertDispatchedTimes(FireAlertEvent::class, 1);
    Event::assertDispatchedTimes(HighHumidityEvent::class, 1);
});
