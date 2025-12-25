<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItemReturn extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'inventory_item_returns';

    public $incrementing = false;

    protected $fillable = [
        'inventory_id',
        'reference_id',
        'usage_type',
        'quantity',
        'return_date',
        'notes'
    ];

    protected $casts = [
        'return_date' => 'date',
        'quantity' => 'integer',
    ];

    /**
     * Get the inventory item that was returned
     */
    public function inventory()
    {
        return $this->belongsTo(ShopInventory::class, 'inventory_id');
    }

    /**
     * Get the related maintenance record if usage_type is 'maintenance'
     */
    public function maintenance()
    {
        return $this->belongsTo(AmcMaintenance::class, 'reference_id');
    }

    /**
     * Get the related contract record if usage_type is 'contract'
     */
    public function contract()
    {
        return $this->belongsTo(AmcContract::class, 'reference_id');
    }

    /**
     * Get the related ticket if usage_type is 'inside_job' or 'outside_job'
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'reference_id');
    }

    /**
     * Get the reference entity based on usage_type
     * Returns the actual related model instance
     */
    public function getRelatedEntity()
    {
        return match($this->usage_type) {
            'maintenance' => $this->maintenance,
            'contract' => $this->contract,
            'inside_job', 'outside_job' => $this->ticket,
            default => null,
        };
    }

    /**
     * Check if this is a job-related return
     */
    public function isJobReturn(): bool
    {
        return in_array($this->usage_type, ['inside_job', 'outside_job']);
    }
}
