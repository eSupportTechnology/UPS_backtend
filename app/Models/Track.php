<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Track extends Model
{
    use HasUuids;

    protected $fillable = [
        'technician_id',
        'job_id',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function points(): HasMany
    {
        return $this->hasMany(TrackPoint::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'job_id');
    }
}
