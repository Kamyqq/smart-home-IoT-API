<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\House;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IoTSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
        ]);

        $token = $admin->createToken('user-token')->plainTextToken;

        $this->command->info("Admin token: " . $token);

        $house = House::create([
            'name' => 'House',
            'user_id' => $admin->id,
        ]);

        $room = Room::create([
            'name' => 'Bedroom',
            'house_id' => $house->id,
        ]);

        $room->sensors()->create(['type' => 'temperature']);
        $room->sensors()->create(['type' => 'humidity']);
        $room->sensors()->create(['type' => 'light']);

        $bedroomArduino = Device::create([
            'name' => 'Bedroom arduino',
            'room_id' => 1,
            'api_key' => Str::random(64),
            'is_active' => true,
        ]);

        $this->command->info("Bedroom arduino api key: " . $bedroomArduino->api_key);
    }
}
