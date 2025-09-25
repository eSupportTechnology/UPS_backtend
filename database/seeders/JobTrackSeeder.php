<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Ticket;
use App\Models\Track;
use App\Models\TrackPoint;
use Illuminate\Support\Str;

class JobTrackSeeder extends Seeder
{
    public function run(): void
    {
        $ticket = Ticket::create([
            'id' => (string) Str::uuid(),
            'customer_id' => (string) Str::uuid(),
            'title' => 'Test AC Repair Job Bandarawela',
            'description' => 'AC not cooling properly',
            'status' => 'open',
            'priority' => 'high',
            'assigned_to' => (string) Str::uuid(),
            'district' => 'Colombo',
            'city' => 'Colombo 07',
            'gramsewa_division' => 'Ward 45',
        ]);

        // Create track
        $track = Track::create([
            'id' => (string) Str::uuid(),
            'technician_id' => (string) Str::uuid(),
            'job_id' => $ticket->id,
            'started_at' => now(),
        ]);




        $origin = "6.9271,79.8612";
        $destination = "6.8359,80.9944";
        $apiKey = env('GOOGLE_MAPS_API_KEY');

        $response = Http::withoutVerifying()->get("https://maps.googleapis.com/maps/api/directions/json", [
            'origin' => $origin,
            'destination' => $destination,
            'mode' => 'driving',
            'key' => $apiKey,
        ]);


        if ($response->successful() && $response['status'] === 'OK') {
            $points = $response['routes'][0]['overview_polyline']['points'];

            // Decode polyline → lat/lng pairs
            $coords = $this->decodePolyline($points);

            foreach ($coords as $p) {
                TrackPoint::create([
                    'id' => (string) Str::uuid(),
                    'track_id' => $track->id,
                    'lat' => $p['lat'],
                    'lng' => $p['lng'],
                    'accuracy' => 5.0,
                    'speed' => 15.0,
                    'heading' => 90,
                    'battery' => 80,
                    'recorded_at' => now(),
                ]);
            }
        }
    }

    /**
     * Decode Google Maps polyline into array of lat/lng
     */
    private function decodePolyline(string $polyline): array
    {
        $points = [];
        $index = 0;
        $len = strlen($polyline);
        $lat = 0;
        $lng = 0;

        while ($index < $len) {
            $shift = 0;
            $result = 0;
            do {
                $b = ord($polyline[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lat += $dlat;

            $shift = 0;
            $result = 0;
            do {
                $b = ord($polyline[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lng += $dlng;

            $points[] = [
                'lat' => $lat * 1e-5,
                'lng' => $lng * 1e-5,
            ];
        }

        return $points;
    }
}
