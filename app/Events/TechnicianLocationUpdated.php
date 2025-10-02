<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TechnicianLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $technicianId;
    public string $jobId;
    public array $points;

    public function __construct(string $technicianId, string $jobId, array $points)
    {
        $this->technicianId = $technicianId;
        $this->jobId = $jobId;
        $this->points = is_array($points[0] ?? null) ? $points : [$points];

        Log::info('Broadcasting location update', [
            'technician_id' => $technicianId,
            'job_id' => $jobId,
            'points_count' => count($this->points)
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('tracking.' . $this->technicianId),
            new Channel('job.' . $this->jobId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'TechnicianLocationUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'technician_id' => $this->technicianId,
            'job_id' => $this->jobId,
            'points' => $this->points,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
