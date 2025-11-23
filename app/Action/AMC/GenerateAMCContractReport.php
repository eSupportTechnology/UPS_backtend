<?php

namespace App\Action\AMC;

use App\Models\AMCContract;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GenerateAMCContractReport
{
    public function __invoke(array $filters = []): array
    {
        try {
            $contracts = $this->getContracts($filters);

            $statistics = [
                'total_contracts' => $contracts->count(),
                'active_contracts' => $contracts->where('is_active', true)->count(),
                'inactive_contracts' => $contracts->where('is_active', false)->count(),
                'total_contract_amount' => $contracts->sum('contract_amount'),
                'average_contract_amount' => $contracts->avg('contract_amount'),
                'total_maintenances' => $contracts->sum(fn($c) => $c->maintenances->count()),
            ];

            $contractsByType = $contracts->groupBy('contract_type')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum('contract_amount'),
                    'average_amount' => $group->avg('contract_amount'),
                ];
            });

            $contractsByBranch = $contracts->groupBy('branch_id')->map(function($group) {
                return [
                    'branch_name' => $group->first()->branch->branch_name ?? 'N/A',
                    'count' => $group->count(),
                    'total_amount' => $group->sum('contract_amount'),
                ];
            });

            $monthlyTrend = $this->getMonthlyTrend($filters);

            $report = [
                'statistics' => $statistics,
                'contracts_by_type' => $contractsByType,
                'contracts_by_branch' => $contractsByBranch,
                'monthly_trend' => $monthlyTrend,
                'contracts' => $contracts->map(function($contract) {
                    return [
                        'id' => $contract->id,
                        'branch' => $contract->branch->branch_name ?? 'N/A',
                        'customer' => $contract->customer->name ?? 'N/A',
                        'contract_type' => $contract->contract_type,
                        'purchase_date' => $contract->purchase_date,
                        'warranty_end_date' => $contract->warranty_end_date,
                        'contract_amount' => $contract->contract_amount,
                        'maintenance_count' => $contract->maintenances->count(),
                        'is_active' => $contract->is_active,
                    ];
                }),
                'generated_at' => now()->toDateTimeString(),
            ];

            return CommonResponse::sendSuccessResponseWithData('report', $report);
        } catch (\Exception $e) {
            Log::error('Failed to generate AMC contract report: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error generating report');
        }
    }

    private function getContracts(array $filters)
    {
        $query = AMCContract::with(['branch', 'customer', 'maintenances']);

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['contract_type'])) {
            $query->where('contract_type', $filters['contract_type']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }
    }

    private function getMonthlyTrend(array $filters): array
    {
        $query = AMCContract::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(contract_amount) as total_amount')
        )->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12);

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query->get()->toArray();
    }
}
