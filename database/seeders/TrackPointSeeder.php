<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Track;
use App\Models\TrackPoint;

class TrackPointSeeder extends Seeder
{
    public function run(): void
    {
        $track = Track::first() ?? Track::factory()->create();

        TrackPoint::factory()->count(20)->create([
            'track_id' => $track->id,
        ]);
    }
}
