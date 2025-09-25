<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TrackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'technician_id' => (string) Str::uuid(),
            'job_id' => (string) Str::uuid(),
            'started_at' => now(),
            'ended_at' => null,
        ];
    }
}
