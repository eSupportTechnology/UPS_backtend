<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tickets';
    public $incrementing = false;

    protected $fillable = [
        'customer_id',
        'title',
        'description',
        'photo_paths',
        'status',
        'priority',
        'assigned_to',
        'accepted_at',
        'completed_at',
        'district',
        'city',
        'gramsewa_division',
    ];
    public function assignedTechnician()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }
    public function track()
    {
        return $this->hasOne(Track::class, 'job_id', 'id');
    }

}
