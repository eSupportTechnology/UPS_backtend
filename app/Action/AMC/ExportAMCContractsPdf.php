<?php

namespace App\Action\AMC;

use App\Models\AMCContract;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ExportAMCContractsPdf
{
    public function __invoke(array $filters = []): Response
    {
        try {
            $contracts = $this->getContracts($filters);

            $data = [
                'contracts' => $contracts,
                'filters' => $filters,
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'total_amount' => $contracts->sum('contract_amount'),
                'active_count' => $contracts->where('is_active', true)->count(),
                'inactive_count' => $contracts->where('is_active', false)->count(),
            ];

            $pdf = Pdf::loadView('reports.amc-contracts', $data)
                ->setPaper('a4', 'landscape')
                ->setOption('margin-top', 10)
                ->setOption('margin-bottom', 10)
                ->setOption('margin-left', 10)
                ->setOption('margin-right', 10);

            $fileName = 'amc_contracts_' . now()->format('Y_m_d_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Failed to export AMC contracts to PDF: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    private function getContracts(array $filters)
    {
        $query = AMCContract::with(['branch', 'customer', 'maintenances'])
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
                'created_at',
            ]);

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    private function applyFilters($query, array $filters): void
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

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }
    }
}
