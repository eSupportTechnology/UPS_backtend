<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopInventory extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'shop_inventories';

    public $incrementing = false;

    protected $fillable = [
        'created_by',
        'product_name',
        'brand',
        'model',
        'serial_number',
        'category',
        'description',
        'quantity',
        'unit_price',
        'purchase_date',
        'warranty',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get all usage records for this inventory item
     */
    public function usages()
    {
        return $this->hasMany(InventoryItemUsage::class, 'inventory_id');
    }

    /**
     * Get all return records for this inventory item
     */
    public function returns()
    {
        return $this->hasMany(InventoryItemReturn::class, 'inventory_id');
    }

    /**
     * Get the user who created this inventory item
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get total quantity used across all usages
     */
    public function getTotalUsedAttribute()
    {
        return $this->usages()->sum('quantity') ?? 0;
    }

    /**
     * Get total quantity returned across all returns
     */
    public function getTotalReturnedAttribute()
    {
        return $this->returns()->sum('quantity') ?? 0;
    }

    /**
     * Get available quantity (quantity - total_used + total_returned)
     */
    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->getTotalUsedAttribute() + $this->getTotalReturnedAttribute();
    }

    /**
     * Check if inventory is low stock
     */
    public function isLowStock(int $threshold = 5): bool
    {
        return $this->getAvailableQuantityAttribute() <= $threshold;
    }
}
