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

    public function inventory()
    {
        return $this->belongsTo(ShopInventory::class, 'inventory_id');
    }
    public function maintenance()
    {
        return $this->belongsTo(AmcMaintenance::class, 'reference_id')
            ->when($this->usage_type === 'maintenance');
    }
    public function contract()
    {
        return $this->belongsTo(AmcContract::class, 'reference_id')
            ->when($this->usage_type === 'contract');
    }
}
