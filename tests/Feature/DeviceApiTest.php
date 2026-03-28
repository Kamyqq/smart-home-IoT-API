<?php

use App\Models\Device;
use App\Models\House;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class, \Tests\TestCase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->house = House::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->room = Room::factory()->create([
        "house_id" => $this->house->id
    ]);

    $this->device = Device::factory()->create([
        "room_id" => $this->room->id,
        "api_key" => 'secret-api-key',
        "is_active" => true,
    ]);
});

it("rejects request without api key", function () {
    $response = $this->post(('/api/measurements'), [
        'temperature' => 25.5,
    ]);

    $response->assertStatus(401)
        ->assertJson(["error" => "Missing API key"]);
});

it("rejects request with invalid api key", function () {
    $response = $this->withHeaders([
        "X-API-KEY" => "invalid-api-key",
    ])->postJson(('/api/measurements'), [
        "temperature" => 25.5,
    ]);

    $response->assertStatus(401)
        ->assertJson(["error" => "Invalid API key"]);
});

it("authenticates device with valid api key and updates last seen at", function () {
    $knownDate = now()->subDays(1);
    $this->device->update(["last_seen_at" => $knownDate]);

    $response = $this->withHeaders([
        "X-API-KEY" => "secret-api-key",
    ])->postJson(('/api/measurements'), [
        "temperature" => 25.5,
    ]);

    $response->assertStatus(200);

    $this->device->refresh();
    expect($this->device->last_seen_at->gt($knownDate))->toBeTrue();
});
