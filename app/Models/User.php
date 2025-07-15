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
}
