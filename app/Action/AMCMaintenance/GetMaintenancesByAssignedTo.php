<?php

namespace App\Action\AMCMaintenance;

use App\Models\AMCMaintenance;
use App\Response\CommonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class GetMaintenancesByAssignedTo
{
    public function __invoke(string $assignedTo, array $filters = []): array
    {
        try {
            $query = AMCMaintenance::with([
                'assignedTechnician:id,name,email,phone',
                'amcContract:id,contract_number,customer_id,start_date,end_date,status',
                'amcContract.customer:id,name,email,phone,address'
            ])
                ->select([
                    'amc_maintenances.id',
                    'amc_maintenances.amc_contract_id',
                    'amc_maintenances.scheduled_date',
                    'amc_maintenances.completed_date',
                    'amc_maintenances.assigned_to',
                    'amc_maintenances.note',
                    'amc_maintenances.status',
                    'amc_maintenances.created_at',
                    'amc_maintenances.updated_at',
                ])
                ->where('amc_maintenances.assigned_to', $assignedTo);

            $this->applyFilters($query, $filters);
            $this->applySorting($query, $filters);

            $perPage = $filters['per_page'] ?? 10;
            $maintenances = $query->paginate($perPage);

            return CommonResponse::sendSuccessResponseWithData('maintenances', $maintenances);
        } catch (\Exception $e) {
            Log::error('Failed to fetch AMC maintenances by assigned technician: ' . $e->getMessage(), [
                'assigned_to' => $assignedTo,
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving assigned AMC maintenances');
        }
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('amc_maintenances.status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('amc_maintenances.note', 'LIKE', "%{$search}%")
                    ->orWhereHas('amcContract', function($subQuery) use ($search) {
                        $subQuery->where('contract_number', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('amcContract.customer', function($subQuery) use ($search) {
                        $subQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['scheduled_from'])) {
            $query->where('amc_maintenances.scheduled_date', '>=', $filters['scheduled_from']);
        }

        if (!empty($filters['scheduled_to'])) {
            $query->where('amc_maintenances.scheduled_date', '<=', $filters['scheduled_to']);
        }
    }

    private function applySorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'scheduled_date';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $sortableColumns = [
            'scheduled_date' => 'amc_maintenances.scheduled_date',
            'completed_date' => 'amc_maintenances.completed_date',
            'status' => 'amc_maintenances.status',
            'created_at' => 'amc_maintenances.created_at',
        ];

        $qualifiedSortBy = $sortableColumns[$sortBy] ?? 'amc_maintenances.scheduled_date';

        $query->orderBy($qualifiedSortBy, $sortDirection);

        if ($sortBy !== 'id') {
            $query->orderBy('amc_maintenances.id', 'desc');
        }
    }
}
