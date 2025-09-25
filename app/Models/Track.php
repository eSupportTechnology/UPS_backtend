<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Track extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'technician_id',
        'job_id',
        'started_at',
        'ended_at'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function points()
    {
        return $this->hasMany(TrackPoint::class);
    }
    public function job()
    {
        return $this->belongsTo(Ticket::class, 'job_id', 'id');
    }
}
