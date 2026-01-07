<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPlannedMaterial extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'job_planned_materials';

    public $incrementing = false;

    protected $fillable = [
        'ticket_id',
        'inventory_id',
        'product_name',
        'brand',
        'category',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function inventory()
    {
        return $this->belongsTo(ShopInventory::class, 'inventory_id');
    }
}
