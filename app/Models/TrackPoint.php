<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TrackPoint extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'track_id',
        'lat',
        'lng',
        'accuracy',
        'speed',
        'heading',
        'battery',
        'recorded_at'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function track()
    {
        return $this->belongsTo(Track::class);
    }
}
