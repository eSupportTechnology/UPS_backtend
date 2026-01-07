<?php

namespace App\Action\Ticket;

use App\Models\JobPlannedMaterial;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ExportMaterialsPdf
{
    public function __invoke(array $filters = []): Response
    {
        try {
            $materials = $this->getMaterials($filters);

            // Group materials by category for summary
            $categoryTotals = $materials->groupBy('category')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'total_quantity' => $items->sum('quantity'),
                ];
            });

            // Group materials by brand for summary
            $brandTotals = $materials->groupBy('brand')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'total_quantity' => $items->sum('quantity'),
                ];
            });

            $data = [
                'materials' => $materials,
                'filters' => $filters,
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'total_materials' => $materials->count(),
                'total_quantity' => $materials->sum('quantity'),
                'unique_products' => $materials->unique('product_name')->count(),
                'category_totals' => $categoryTotals,
                'brand_totals' => $brandTotals,
                'jobs_count' => $materials->unique('ticket_id')->count(),
            ];

            $pdf = Pdf::loadView('reports.materials-usage', $data)
                ->setPaper('a4', 'landscape')
                ->setOption('margin-top', 10)
                ->setOption('margin-bottom', 10)
                ->setOption('margin-left', 10)
                ->setOption('margin-right', 10);

            $fileName = 'materials_usage_' . now()->format('Y_m_d_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Failed to export materials to PDF: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    private function getMaterials(array $filters)
    {
        $query = JobPlannedMaterial::with(['ticket', 'inventory']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->get();
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('product_name', 'LIKE', "%{$search}%")
                    ->orWhere('brand', 'LIKE', "%{$search}%")
                    ->orWhere('category', 'LIKE', "%{$search}%")
                    ->orWhereHas('ticket', function($tq) use ($search) {
                        $tq->where('job_number', 'LIKE', "%{$search}%")
                            ->orWhere('customer_name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        if (!empty($filters['job_id'])) {
            $query->where('ticket_id', $filters['job_id']);
        }

        if (!empty($filters['status'])) {
            $query->whereHas('ticket', function($q) use ($filters) {
                if (is_array($filters['status'])) {
                    $q->whereIn('status', $filters['status']);
                } else {
                    $statuses = explode(',', $filters['status']);
                    $q->whereIn('status', $statuses);
                }
            });
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['today']) && $filters['today'] === 'true') {
            $query->whereDate('created_at', now()->toDateString());
        }
    }
}
