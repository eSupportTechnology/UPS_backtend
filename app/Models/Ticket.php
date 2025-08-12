<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'tickets';

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
    ];

    protected $casts = [
        'photo_paths' => 'array',
    ];
}
