<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tickets';
    public $incrementing = false;

    protected $fillable = [
        'job_type',
        'job_number',
        'parent_ticket_id',
        'customer_id',
        'title',
        'description',
        'photo_paths',
        'ups_serial_number',
        'ups_model',
        'ups_brand',
        'status',
        'priority',
        'assigned_to',
        'accepted_at',
        'completed_at',
        'district',
        'city',
        'gramsewa_division',
        'inspection_notes',
        'inspected_at',
        'inspected_by',
        'quote_data',
        'quote_total',
        'quoted_at',
        'quoted_by',
        'approval_status',
        'approval_decision_at',
        'approval_notes',
        'in_repair_at',
        'repair_notes',
        'actual_parts_used',
    ];

    protected $casts = [
        'photo_paths' => 'array',
        'quote_data' => 'array',
        'actual_parts_used' => 'array',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
        'inspected_at' => 'datetime',
        'quoted_at' => 'datetime',
        'approval_decision_at' => 'datetime',
        'in_repair_at' => 'datetime',
    ];
    public function assignedTechnician()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }

    public function track()
    {
        return $this->hasOne(Track::class, 'job_id');
    }

    // Parent-child relationships for outside/inside jobs
    public function parentTicket()
    {
        return $this->belongsTo(Ticket::class, 'parent_ticket_id');
    }

    public function insideJobs()
    {
        return $this->hasMany(Ticket::class, 'parent_ticket_id');
    }

    // Inside job specific relationships
    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspected_by', 'id');
    }

    public function quoter()
    {
        return $this->belongsTo(User::class, 'quoted_by', 'id');
    }

    public function quoteLineItems()
    {
        return $this->hasMany(QuoteLineItem::class);
    }

    public function inventoryUsages()
    {
        return $this->hasMany(InventoryItemUsage::class, 'reference_id');
    }

    // Scopes for filtering
    public function scopeOutsideJobs($query)
    {
        return $query->where('job_type', 'outside');
    }

    public function scopeInsideJobs($query)
    {
        return $query->where('job_type', 'inside');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('job_type', 'inside')
                     ->where('approval_status', 'pending');
    }

    // Helper methods
    public function isOutsideJob(): bool
    {
        return $this->job_type === 'outside';
    }

    public function isInsideJob(): bool
    {
        return $this->job_type === 'inside';
    }

    public function needsApproval(): bool
    {
        return $this->isInsideJob() && $this->approval_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }
}
