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
}
