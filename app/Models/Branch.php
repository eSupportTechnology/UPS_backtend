<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'branches';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'branch_code',
        'type',
        'country',
        'state',
        'city',
        'address_line1',
        'address_line2',
        'postal_code',
        'contact_person',
        'contact_number',
        'email',
        'operating_hours',
        'is_active',
    ];
}
