<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TechnicianLocationUpdated
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

}
