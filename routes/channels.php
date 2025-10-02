<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('tracking.{technicianId}', function ($user, $technicianId) {
    return true;
});

Broadcast::channel('job.{jobId}', function ($user, $jobId) {
    return true;
});
