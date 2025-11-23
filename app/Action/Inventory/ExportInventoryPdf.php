<?php

namespace App\Action\Inventory;

use App\Models\ShopInventory;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ExportInventoryPdf
{
    public function __invoke(array $filters = []): Response
    {
        try {
            $inventories = $this->getInventories($filters);

            $data = [
                'inventories' => $inventories,
                'filters' => $filters,
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'total_items' => $inventories->count(),
                'total_quantity' => $inventories->sum('quantity'),
                'total_value' => $inventories->sum(function($item) {
                    return $item->quantity * $item->unit_price;
                }),
                'low_stock_count' => $inventories->where('quantity', '<=', 10)->count(),
                'out_of_stock_count' => $inventories->where('quantity', 0)->count(),
            ];

            $pdf = Pdf::loadView('reports.shop-inventories', $data)
                ->setPaper('a4', 'landscape')
                ->setOption('margin-top', 10)
                ->setOption('margin-bottom', 10)
                ->setOption('margin-left', 10)
                ->setOption('margin-right', 10);

            $fileName = 'shop_inventories_' . now()->format('Y_m_d_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Failed to export shop inventories to PDF: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    private function getInventories(array $filters)
    {
        $query = ShopInventory::query()
            ->select([
                'id',
                'product_name',
                'brand',
                'model',
                'serial_number',
                'category',
                'description',
                'quantity',
                'unit_price',
                'purchase_date',
                'warranty',
                'created_at',
            ]);

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
                    ->orWhere('model', 'LIKE', "%{$search}%")
                    ->orWhere('serial_number', 'LIKE', "%{$search}%")
                    ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('purchase_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('purchase_date', '<=', $filters['end_date']);
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $threshold = $filters['stock_threshold'] ?? 10;
            $query->where('quantity', '<=', $threshold);
        }
    }
}
