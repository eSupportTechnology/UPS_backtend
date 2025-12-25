<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_as',
        'is_active',
        'phone',
        'address',
        'technician_type',
        'employment_type',
        'profile_image',
        'specialization',
        'customer_type',
        'company_name',
        'company_headquarters_branch_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    const ROLE_SUPER_ADMIN = 1;
    const ROLE_ADMIN = 2;
    const ROLE_OPERATOR = 3;
    const ROLE_TECHNICIAN = 4;
    const ROLE_CUSTOMER = 5;

    public function isSuperAdmin(): bool
    {
        return $this->role_as === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role_as === self::ROLE_ADMIN;
    }

    public function isOperator(): bool
    {
        return $this->role_as === self::ROLE_OPERATOR;
    }

    public function isTechnician(): bool
    {
        return $this->role_as === self::ROLE_TECHNICIAN;
    }

    public function isCustomer(): bool
    {
        return $this->role_as === self::ROLE_CUSTOMER;
    }

    public function getRoleName(): string
    {
        return match($this->role_as) {
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_OPERATOR => 'Operator',
            self::ROLE_TECHNICIAN => 'Technician',
            self::ROLE_CUSTOMER => 'Customer',
            default => 'Unknown',
        };
    }

    public function hasRole(int $role): bool
    {
        return $this->role_as === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role_as, $roles);
    }

    // Technician Type Helpers
    public function isInsideTechnician(): bool
    {
        return $this->technician_type === 'inside';
    }

    public function isOutsideTechnician(): bool
    {
        return $this->technician_type === 'outside';
    }

    public function isFullTimeTechnician(): bool
    {
        return $this->employment_type === 'full_time';
    }

    public function isPartTimeTechnician(): bool
    {
        return $this->employment_type === 'part_time';
    }

    public function getTechnicianTypeLabel(): string
    {
        return match($this->technician_type) {
            'inside' => 'Inside Technician',
            'outside' => 'Outside Technician',
            default => 'Not a Technician',
        };
    }

    public function getEmploymentTypeLabel(): string
    {
        return match($this->employment_type) {
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            default => 'N/A',
        };
    }

    // Customer Type Helpers
    public function isPersonalCustomer(): bool
    {
        return $this->customer_type === 'personal';
    }

    public function isCompanyCustomer(): bool
    {
        return $this->customer_type === 'company';
    }

    public function getCustomerTypeLabel(): string
    {
        return match($this->customer_type) {
            'personal' => 'Individual Customer',
            'company' => 'Company Customer',
            default => 'N/A',
        };
    }

    // Relationships
    public function branches()
    {
        return $this->hasMany(CompanyBranch::class, 'company_id');
    }
}
