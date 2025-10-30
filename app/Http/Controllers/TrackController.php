<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\TrackPoint;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrackController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'technician_id' => 'required|exists:users,id',
            'job_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $activeTrack = Track::where('technician_id', $request->technician_id)
            ->whereNull('ended_at')
            ->first();

        if ($activeTrack) {
            return response()->json([
                'success' => false,
                'message' => 'Technician already has an active tracking session',
                'track' => $activeTrack
            ], 400);
        }

        $track = Track::create([
            'technician_id' => $request->technician_id,
            'job_id' => $request->job_id,
            'started_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tracking started successfully',
            'track' => $track
        ], 201);
    }

    public function savePoints(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'track_id' => 'required|uuid|exists:tracks,id',
            'points' => 'required|array|min:1',
            'points.*.lat' => 'required|numeric|between:-90,90',
            'points.*.lng' => 'required|numeric|between:-180,180',
            'points.*.accuracy' => 'nullable|numeric|min:0',
            'points.*.speed' => 'nullable|numeric|min:0',
            'points.*.heading' => 'nullable|numeric|between:0,360',
            'points.*.battery' => 'nullable|integer|between:0,100',
            'points.*.recorded_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $track = Track::find($request->track_id);
        if ($track->ended_at) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add points to an ended track'
            ], 400);
        }

        $pointsToInsert = [];
        $existingTimestamps = TrackPoint::where('track_id', $request->track_id)
            ->pluck('recorded_at')
            ->map(fn($dt) => $dt->timestamp)
            ->toArray();

        foreach ($request->points as $point) {
            $recordedAt = Carbon::parse($point['recorded_at']);

            if (in_array($recordedAt->timestamp, $existingTimestamps)) {
                continue;
            }

            $pointsToInsert[] = [
                'id' => Str::uuid(),
                'track_id' => $request->track_id,
                'lat' => $point['lat'],
                'lng' => $point['lng'],
                'accuracy' => $point['accuracy'] ?? null,
                'speed' => $point['speed'] ?? null,
                'heading' => $point['heading'] ?? null,
                'battery' => $point['battery'] ?? null,
                'recorded_at' => $recordedAt,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($pointsToInsert)) {
            return response()->json([
                'success' => true,
                'message' => 'All points already exist',
                'saved_count' => 0
            ], 200);
        }

        DB::table('track_points')->insert($pointsToInsert);

        return response()->json([
            'success' => true,
            'message' => 'Location points saved successfully',
            'saved_count' => count($pointsToInsert),
            'skipped_count' => count($request->points) - count($pointsToInsert)
        ], 201);
    }

    public function stop($trackId): JsonResponse
    {
        $track = Track::find($trackId);

        if (!$track) {
            return response()->json([
                'success' => false,
                'message' => 'Track not found'
            ], 404);
        }

        if ($track->ended_at) {
            return response()->json([
                'success' => false,
                'message' => 'Track already stopped'
            ], 400);
        }

        $track->update(['ended_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Tracking stopped successfully',
            'track' => $track
        ], 200);
    }

    public function show($trackId): JsonResponse
    {
        $track = Track::with(['points' => function($query) {
            $query->orderBy('recorded_at', 'asc');
        }, 'technician:id,name,email', 'job:id,title'])
            ->find($trackId);

        if (!$track) {
            return response()->json([
                'success' => false,
                'message' => 'Track not found'
            ], 404);
        }

        $statistics = [
            'total_points' => $track->points->count(),
            'duration_minutes' => $track->ended_at
                ? $track->started_at->diffInMinutes($track->ended_at)
                : $track->started_at->diffInMinutes(now()),
            'distance_km' => $this->calculateTotalDistance($track->points),
            'average_speed' => $track->points->avg('speed'),
        ];

        return response()->json([
            'success' => true,
            'track' => $track,
            'statistics' => $statistics
        ], 200);
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'technician_id' => 'required|uuid|exists:users,id',
            'status' => 'nullable|in:active,completed',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Track::where('technician_id', $request->technician_id)
            ->with(['job:id,title'])
            ->withCount('points');

        if ($request->status === 'active') {
            $query->whereNull('ended_at');
        } elseif ($request->status === 'completed') {
            $query->whereNotNull('ended_at');
        }

        if ($request->date_from) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        $tracks = $query->orderBy('started_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'tracks' => $tracks
        ], 200);
    }

    public function getActive($technicianId): JsonResponse
    {
        $track = Track::where('technician_id', $technicianId)
            ->whereNull('ended_at')
            ->with(['points' => function($query) {
                $query->orderBy('recorded_at', 'desc')->limit(1);
            }])
            ->first();

        if (!$track) {
            return response()->json([
                'success' => false,
                'message' => 'No active track found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'track' => $track
        ], 200);
    }


    private function calculateTotalDistance($points): float|int
    {
        if ($points->count() < 2) {
            return 0;
        }

        $totalDistance = 0;
        $previousPoint = null;

        foreach ($points as $point) {
            if ($previousPoint) {
                $totalDistance += $this->haversineDistance(
                    $previousPoint->lat,
                    $previousPoint->lng,
                    $point->lat,
                    $point->lng
                );
            }
            $previousPoint = $point;
        }

        return round($totalDistance, 2);
    }


    private function haversineDistance($lat1, $lon1, $lat2, $lon2): float|int
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
