<?php

namespace App\Action\User;

use App\Models\User;
use App\Response\CommonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class GetAllUsers
{
    public function __invoke(array $filters = []): array
    {
        try {
            $query = User::select('id', 'name', 'email', 'role_as', 'phone', 'address', 'customer_type', 'company_name', 'is_active', 'created_at');
            $this->applyFilters($query, $filters);
            $this->applySorting($query, $filters);

            $perPage = $filters['per_page'] ?? 10;

            $users = $query->paginate($perPage);

            return CommonResponse::sendSuccessResponseWithData('users', $users);
        } catch (\Exception $e) {
            Log::error('Failed to fetch users: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving user list');
        }
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['role']) && $filters['role'] !== '' && $filters['role'] !== null) {
            $query->where('role_as', $filters['role']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $query->where('is_active', $filters['is_active']);
        }
    }

    private function applySorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $query->orderBy($sortBy, $sortDirection);
        if ($sortBy !== 'id') {
            $query->orderBy('id', 'desc');
        }
    }
}
