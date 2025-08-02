<?php

namespace App\Action\AMC;

use App\Models\AMCContract;
use App\Response\CommonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class GetAllContracts
{
    public function __invoke(array $filters = []): array
    {
        try {
            $query = AMCContract::with(['maintenances', 'branch', 'customer'])
                ->select([
                    'id',
                    'branch_id',
                    'customer_id',
                    'contract_type',
                    'purchase_date',
                    'warranty_end_date',
                    'contract_amount',
                    'notes',
                    'is_active',
                ]);

            $this->applyFilters($query, $filters);
            $this->applySorting($query, $filters);

            $perPage = $filters['per_page'] ?? 10;
            $contracts = $query->paginate($perPage);

            return CommonResponse::sendSuccessResponseWithData('contracts', $contracts);
        } catch (\Exception $e) {
            Log::error('Failed to fetch AMC contracts: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving AMC contracts list');
        }
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('contract_type', 'LIKE', "%{$search}%")
                    ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
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
