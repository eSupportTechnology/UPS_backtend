<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class TechnicianLocationUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public string $technicianId;
    public array $point;

    public function __construct(string $technicianId, array $point)
    {
        $this->technicianId = $technicianId;
        $this->point = $point;
    }

    public function broadcastOn()
    {
        return new Channel('tech.' . $this->technicianId);
    }

    public function broadcastAs()
    {
        return 'TechnicianLocationUpdated';
    }
}
