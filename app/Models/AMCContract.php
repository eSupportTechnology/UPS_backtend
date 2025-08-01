<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AMCContract extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'amc_contracts';
    public $incrementing = false;

    protected $fillable = [
        'branch_id',
        'customer_id',
        'contract_type',
        'purchase_date',
        'warranty_end_date',
        'contract_amount',
        'notes',
        'is_active',
    ];
}
