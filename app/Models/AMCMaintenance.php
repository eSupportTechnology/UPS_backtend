<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AMCMaintenance extends Model
{
    use HasUuids;

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

    public function assignedTechnician()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    public function maintenances()
    {
        return $this->hasMany(AMCMaintenance::class, 'amc_contract_id', 'id');
    }

}
