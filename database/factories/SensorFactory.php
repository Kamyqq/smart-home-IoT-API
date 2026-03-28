<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sensor>
 */
class SensorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "room_id" => Room::factory(),
            "type" => $this->faker->randomElement(["temperature", "humidity", 'light']),
        ];
    }
}
