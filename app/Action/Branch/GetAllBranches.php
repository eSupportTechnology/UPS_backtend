<?php

namespace App\Action\Branch;

use App\Models\Branch;
use App\Response\CommonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class GetAllBranches
{
    public function __invoke(array $filters = []): array
    {
        try {
            $query = Branch::select([
                'id', 'name', 'branch_code', 'type', 'country', 'state', 'city',
                'address_line1', 'address_line2', 'postal_code',
                'contact_person', 'contact_number', 'email', 'operating_hours',
                'is_active'
            ]);

            $this->applyFilters($query, $filters);
            $this->applySorting($query, $filters);

            $perPage = $filters['per_page'] ?? 10;
            $branches = $query->paginate($perPage);

            return CommonResponse::sendSuccessResponseWithData('branches', $branches);
        } catch (\Exception $e) {
            Log::error('Failed to fetch branches: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving branch list');
        }
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('branch_code', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%")
                    ->orWhere('contact_person', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['country'])) {
            $query->where('country', 'LIKE', "%{$filters['country']}%");
        }

        if (!empty($filters['state'])) {
            $query->where('state', 'LIKE', "%{$filters['state']}%");
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'LIKE', "%{$filters['city']}%");
        }

        if (isset($filters['is_active'])) {
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
