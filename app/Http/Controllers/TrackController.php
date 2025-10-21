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

            $track = DB::transaction(function () use ($validated, $request) {
                $existingTrack = Track::where('job_id', $validated['job_id'])
                    ->whereNull('ended_at')
                    ->lockForUpdate()
                    ->first();

                if ($existingTrack) {
                    return $existingTrack;
                }

                return Track::create([
                    'technician_id' => $request->user()->id,
                    'job_id' => $validated['job_id'],
                    'started_at' => now(),
                ]);
            });

            Log::info('Track started', [
                'track_id' => $track->id,
                'job_id' => $track->job_id,
                'technician_id' => $track->technician_id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Track started successfully',
                'track' => $track->load('points')
            ], $track->wasRecentlyCreated ? 201 : 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to start track', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to start tracking'
            ], 500);
        }
    }

    public function storePoints(Request $request, Track $track): JsonResponse
    {
        try {
            if ($track->technician_id !== $request->user()->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($track->ended_at) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Track has already ended'
                ], 400);
            }

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
                $pointsArray = array_map(function ($p) {
                    return [
                        'id' => $p->id,
                        'lat' => (float) $p->lat,
                        'lng' => (float) $p->lng,
                        'accuracy' => $p->accuracy,
                        'speed' => $p->speed,
                        'heading' => $p->heading,
                        'battery' => $p->battery,
                        'recorded_at' => $p->recorded_at->toIso8601String(),
                    ];
                }, $savedPoints);

                broadcast(new TechnicianLocationUpdated(
                    $track->technician_id,
                    $track->job_id,
                    $pointsArray
                ));

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
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to store points', [
                'track_id' => $track->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save tracking points'
            ], 500);
        }
    }

    public function end(Request $request, Track $track): JsonResponse
    {
        try {
            if ($track->technician_id !== $request->user()->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($track->ended_at) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Track has already ended',
                    'track' => $track->load('points')
                ], 400);
            }

            $track->update(['ended_at' => now()]);

            Log::info('Track ended', [
                'track_id' => $track->id,
                'duration' => $track->started_at->diffInSeconds($track->ended_at)
            ]);

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
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to end tracking'
            ], 500);
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
                'status' => 'success',
                'data' => [
                    'id' => $track->id,
                    'technician_id' => $track->technician_id,
                    'job_id' => $track->job_id,
                    'started_at' => $track->started_at,
                    'ended_at' => $track->ended_at,
                    'points' => $points,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch track', [
                'track_id' => $track->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch track'
            ], 500);
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
                    'status' => 'error',
                    'message' => 'No track found for this job'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $track
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch track by job', [
                'job_id' => $jobId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch track'
            ], 500);
        }
    }

    public function allJobs(): JsonResponse
    {
        try {
            $jobs = Ticket::whereHas('track', function ($q) {
                $q->whereNotNull('started_at')
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

            return response()->json([
                'status' => 'success',
                'data' => $jobs
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch all jobs', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch jobs'
            ], 500);
        }
    }
}
