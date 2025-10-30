<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackPoint extends Model
{
    use HasUuids;

    protected $fillable = [
        'track_id',
        'lat',
        'lng',
        'accuracy',
        'speed',
        'heading',
        'battery',
        'recorded_at',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'accuracy' => 'float',
        'speed' => 'float',
        'heading' => 'float',
        'battery' => 'integer',
        'recorded_at' => 'datetime',
    ];


    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function scopeWithinTimeRange($query, $startTime, $endTime)
    {
        return $query->whereBetween('recorded_at', [$startTime, $endTime]);
    }

    public function scopeOrderByRecorded($query, $direction = 'asc')
    {
        return $query->orderBy('recorded_at', $direction);
    }
}
