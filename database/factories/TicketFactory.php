<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'customer_id' => (string) Str::uuid(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'photo_paths' => json_encode([]),
            'status' => $this->faker->randomElement(['open', 'in_progress', 'completed']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'assigned_to' => (string) Str::uuid(),
            'accepted_at' => now(),
            'completed_at' => null,
            'district' => $this->faker->city,
            'city' => $this->faker->city,
            'gramsewa_division' => $this->faker->streetName,
        ];
    }
}
