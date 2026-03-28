<?php

namespace Database\Factories;

use App\Models\Measurement;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Measurement>
 */
class MeasurementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "sensor_id" => Sensor::factory(),
            "value" => $this->faker->numberBetween(1, 100),
        ];
    }
}
