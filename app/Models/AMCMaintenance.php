<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AMCMaintenance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'amc_maintenances';
    public $incrementing = false;

    protected $fillable = [
        'amc_contract_id',
        'scheduled_date',
        'completed_date',
        'assigned_to',
        'note',
        'status',
    ];

    public function amcContract()
    {
        return $this->belongsTo(AMCContract::class, 'amc_contract_id', 'id');
    }

}
