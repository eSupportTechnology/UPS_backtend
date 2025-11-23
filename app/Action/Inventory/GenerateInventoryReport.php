<?php

namespace App\Action\Inventory;

use App\Models\ShopInventory;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GenerateInventoryReport
{
    public function __invoke(array $filters = []): array
    {
        try {
            $inventories = $this->getInventories($filters);

            $statistics = [
                'total_items' => $inventories->count(),
                'total_quantity' => $inventories->sum('quantity'),
                'total_value' => $inventories->sum(function($item) {
                    return $item->quantity * $item->unit_price;
                }),
                'average_unit_price' => $inventories->avg('unit_price'),
                'low_stock_items' => $inventories->where('quantity', '<=', 10)->count(),
                'out_of_stock_items' => $inventories->where('quantity', 0)->count(),
            ];

            $inventoriesByCategory = $inventories->groupBy('category')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_quantity' => $group->sum('quantity'),
                    'total_value' => $group->sum(function($item) {
                        return $item->quantity * $item->unit_price;
                    }),
                ];
            });

            $inventoriesByBrand = $inventories->groupBy('brand')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_quantity' => $group->sum('quantity'),
                    'total_value' => $group->sum(function($item) {
                        return $item->quantity * $item->unit_price;
                    }),
                ];
            });

            $monthlyPurchases = $this->getMonthlyPurchases($filters);

            $report = [
                'statistics' => $statistics,
                'inventories_by_category' => $inventoriesByCategory,
                'inventories_by_brand' => $inventoriesByBrand,
                'monthly_purchases' => $monthlyPurchases,
                'inventories' => $inventories->map(function($inventory) {
                    $totalValue = $inventory->quantity * $inventory->unit_price;
                    $status = 'In Stock';

                    if ($inventory->quantity <= 0) {
                        $status = 'Out of Stock';
                    } elseif ($inventory->quantity <= 10) {
                        $status = 'Low Stock';
                    }

                    return [
                        'id' => $inventory->id,
                        'product_name' => $inventory->product_name,
                        'brand' => $inventory->brand,
                        'model' => $inventory->model,
                        'serial_number' => $inventory->serial_number,
                        'category' => $inventory->category,
                        'quantity' => $inventory->quantity,
                        'unit_price' => $inventory->unit_price,
                        'total_value' => $totalValue,
                        'purchase_date' => $inventory->purchase_date,
                        'warranty' => $inventory->warranty,
                        'status' => $status,
                    ];
                }),
                'generated_at' => now()->toDateTimeString(),
            ];

            return CommonResponse::sendSuccessResponseWithData('report', $report);
        } catch (\Exception $e) {
            Log::error('Failed to generate inventory report: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error generating report');
        }
    }

    private function getInventories(array $filters)
    {
        $query = ShopInventory::query();
        $this->applyFilters($query, $filters);
        return $query->get();
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

    private function getMonthlyPurchases(array $filters): array
    {
        $query = ShopInventory::select(
            DB::raw('DATE_FORMAT(purchase_date, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(quantity * unit_price) as total_value')
        )->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12);

        if (!empty($filters['start_date'])) {
            $query->whereDate('purchase_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('purchase_date', '<=', $filters['end_date']);
        }

        return $query->get()->toArray();
    }
}
