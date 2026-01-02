<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompanyBranch extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'branch_name',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the company that owns this branch
     */
    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }
}
