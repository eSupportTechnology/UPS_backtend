<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TrackPointFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'track_id' => (string) Str::uuid(),
            'lat' => $this->faker->latitude(6.9, 7.0),
            'lng' => $this->faker->longitude(79.8, 79.9),
            'accuracy' => $this->faker->randomFloat(1, 1, 10),
            'speed' => $this->faker->randomFloat(1, 0, 40),
            'heading' => $this->faker->numberBetween(0, 360),
            'battery' => $this->faker->numberBetween(50, 100),
            'recorded_at' => now(),
        ];
    }
}
