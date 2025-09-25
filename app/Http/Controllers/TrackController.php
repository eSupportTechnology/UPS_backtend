<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Track;
use App\Events\TechnicianLocationUpdated;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function start(Request $request)
    {
        $track = Track::create([
            'technician_id' => $request->user()->id,
            'job_id' => $request->input('job_id'),
        ]);

        return response()->json($track, 201);
    }

    public function storePoints(Request $request, Track $track)
    {
        $validated = $request->validate([
            'points' => 'required|array|min:1',
            'points.*.lat' => 'required|numeric',
            'points.*.lng' => 'required|numeric',
            'points.*.accuracy' => 'numeric|nullable',
            'points.*.speed' => 'numeric|nullable',
            'points.*.heading' => 'numeric|nullable',
            'points.*.battery' => 'integer|nullable',
            'points.*.recorded_at' => 'date|nullable',
        ]);

        foreach ($validated['points'] as $point) {
            $saved = $track->points()->create($point);
            broadcast(new TechnicianLocationUpdated($track->technician_id, $saved->toArray()))->toOthers();
        }

        return response()->json(['status' => 'ok']);
    }

    public function end(Track $track)
    {
        $track->update(['ended_at' => now()]);
        return response()->json($track);
    }


    public function show(Track $track)
    {
        $points = $track->points()->orderBy('recorded_at')->get();

        return response()->json([
            'id' => $track->id,
            'technician_id' => $track->technician_id,
            'job_id' => $track->job_id,
            'points' => $points->map(fn($p) => [
                'lat' => (float) $p->lat,
                'lng' => (float) $p->lng,
                'recorded_at' => $p->recorded_at,
            ]),
        ]);
    }

    public function showByJob($jobId)
    {
        $track = Track::where('job_id', $jobId)->with('points')->first();

        if (!$track) {
            return response()->json(['message' => 'No track found for this job'], 404);
        }

        return response()->json($track);
    }

    public function index()
    {
        $jobs = Ticket::with([
            'assignedTechnician:id,name,email',
            'track.points' => function ($q) {
                $q->orderBy('recorded_at', 'asc');
            }
        ])->get();

        return response()->json($jobs);
    }
}
