<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteLineItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'quote_line_items';
    public $incrementing = false;

    protected $fillable = [
        'ticket_id',
        'item_type',
        'inventory_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'is_approved',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'is_approved' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(ShopInventory::class, 'inventory_id');
    }
}
