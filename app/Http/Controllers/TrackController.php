<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Track;
use App\Events\TechnicianLocationUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrackController extends Controller
{

    public function start(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'job_id' => 'required|exists:tickets,id',
            ]);

            $existingTrack = Track::where('job_id', $validated['job_id'])
                ->whereNull('ended_at')
                ->first();

            if ($existingTrack) {
                return response()->json([
                    'message' => 'Track already exists for this job',
                    'track' => $existingTrack->load('points')
                ], 200);
            }

            $track = Track::create([
                'technician_id' => $request->user()->id,
                'job_id' => $validated['job_id'],
                'started_at' => now(),
            ]);

            Log::info('Track started', ['track_id' => $track->id, 'job_id' => $track->job_id]);

            return response()->json($track, 201);
        } catch (\Exception $e) {
            Log::error('Failed to start track', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to start tracking'], 500);
        }
    }

    public function storePoints(Request $request, Track $track): JsonResponse
    {
        try {
            $validated = $request->validate([
                'points' => 'required|array|min:1',
                'points.*.lat' => 'required|numeric|between:-90,90',
                'points.*.lng' => 'required|numeric|between:-180,180',
                'points.*.accuracy' => 'nullable|numeric|min:0',
                'points.*.speed' => 'nullable|numeric|min:0',
                'points.*.heading' => 'nullable|numeric|between:0,360',
                'points.*.battery' => 'nullable|integer|between:0,100',
                'points.*.recorded_at' => 'nullable|date',
            ]);

            $savedPoints = [];

            DB::transaction(function () use ($track, $validated, &$savedPoints) {
                foreach ($validated['points'] as $pointData) {
                    $pointData['recorded_at'] = $pointData['recorded_at'] ?? now();
                    $saved = $track->points()->create($pointData);
                    $savedPoints[] = $saved;
                }
            });

            if (!empty($savedPoints)) {
                $pointsArray = array_map(fn($p) => $p->toArray(), $savedPoints);

                broadcast(new TechnicianLocationUpdated(
                    $track->technician_id,
                    $track->job_id,
                    $pointsArray
                ))->toOthers();

                Log::info('Location broadcast sent', [
                    'track_id' => $track->id,
                    'points_count' => count($savedPoints),
                    'technician_id' => $track->technician_id,
                    'job_id' => $track->job_id
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Points saved successfully',
                'count' => count($savedPoints),
                'points' => $savedPoints
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to store points', [
                'track_id' => $track->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to save tracking points'], 500);
        }
    }

    public function end(Track $track): JsonResponse
    {
        try {
            $track->update(['ended_at' => now()]);

            Log::info('Track ended', ['track_id' => $track->id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Track ended successfully',
                'track' => $track->load('points')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to end track', [
                'track_id' => $track->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to end tracking'], 500);
        }
    }

    public function show(Track $track): JsonResponse
    {
        try {
            $points = $track->points()
                ->orderBy('recorded_at')
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'lat' => (float) $p->lat,
                    'lng' => (float) $p->lng,
                    'accuracy' => $p->accuracy,
                    'speed' => $p->speed,
                    'heading' => $p->heading,
                    'battery' => $p->battery,
                    'recorded_at' => $p->recorded_at,
                ]);

            return response()->json([
                'id' => $track->id,
                'technician_id' => $track->technician_id,
                'job_id' => $track->job_id,
                'started_at' => $track->started_at,
                'ended_at' => $track->ended_at,
                'points' => $points,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch track', [
                'track_id' => $track->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to fetch track'], 500);
        }
    }

    public function showByJob($jobId): JsonResponse
    {
        try {
            $track = Track::where('job_id', $jobId)
                ->with(['points' => function ($query) {
                    $query->orderBy('recorded_at', 'asc');
                }])
                ->first();

            if (!$track) {
                return response()->json([
                    'message' => 'No track found for this job'
                ], 404);
            }

            return response()->json($track);
        } catch (\Exception $e) {
            Log::error('Failed to fetch track by job', [
                'job_id' => $jobId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to fetch track'], 500);
        }
    }

    public function allJobs(): JsonResponse
    {
        try {
            $jobs = Ticket::whereHas('track', function ($q) {
                $q->whereNotNull('created_at')
                    ->whereNull('ended_at');
            })
                ->with([
                    'assignedTechnician:id,name,email',
                    'track.points' => function ($q) {
                        $q->orderBy('recorded_at', 'asc');
                    }
                ])
                ->get()
                ->map(function ($job) {
                    return [
                        'id' => $job->id,
                        'title' => $job->title,
                        'status' => $job->status,
                        'assigned_technician' => $job->assignedTechnician,
                        'track' => $job->track,
                    ];
                });

            return response()->json($jobs);
        } catch (\Exception $e) {
            Log::error('Failed to fetch all jobs', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch jobs'], 500);
        }
    }

    public function testBroadcast(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'technician_id' => 'required|string',
                'job_id' => 'required|string',
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
            ]);

            $point = [
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'recorded_at' => now()->toIso8601String(),
                'speed' => rand(0, 60),
                'battery' => rand(50, 100),
                'accuracy' => rand(5, 20),
            ];

            broadcast(new TechnicianLocationUpdated(
                $validated['technician_id'],
                $validated['job_id'],
                [$point]
            ));

            return response()->json([
                'status' => 'success',
                'message' => 'Test broadcast sent',
                'point' => $point
            ]);
        } catch (\Exception $e) {
            Log::error('Test broadcast failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Test broadcast failed'], 500);
        }
    }
}
