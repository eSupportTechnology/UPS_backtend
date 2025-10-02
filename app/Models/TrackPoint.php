<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackPoint extends Model
{
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
}
