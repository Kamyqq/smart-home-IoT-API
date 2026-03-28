<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name" => $this->faker->word(),
            "room_id" => Room::factory(),
            "api_key" => $this->faker->uuid(),
            "is_active" => $this->faker->boolean(),
        ];
    }
}
