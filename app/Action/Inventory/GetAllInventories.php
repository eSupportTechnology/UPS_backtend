<?php

namespace App\Action\Inventory;

use App\Models\ShopInventory;
use App\Response\CommonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class GetAllInventories
{
    public function __invoke(array $filters = []): array
    {
        try {
            $query = ShopInventory::select([
                'id',
                'created_by',
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
                'updated_at'
            ]);

            $this->applyFilters($query, $filters);

            $this->applySorting($query, $filters);

            $perPage = $filters['per_page'] ?? 10;

            $inventories = $query->paginate($perPage);

            return CommonResponse::sendSuccessResponseWithData('inventories', $inventories);
        } catch (\Exception $e) {
            Log::error('Failed to fetch inventories: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving inventory list');
        }
    }


    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'LIKE', "%{$search}%")
                    ->orWhere('brand', 'LIKE', "%{$search}%")
                    ->orWhere('model', 'LIKE', "%{$search}%")
                    ->orWhere('serial_number', 'LIKE', "%{$search}%")
                    ->orWhere('category', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category', 'LIKE', "%{$filters['category']}%");
        }

        if (!empty($filters['brand'])) {
            $query->where('brand', 'LIKE', "%{$filters['brand']}%");
        }

        if (!empty($filters['created_by'])) {
            $query->where('created_by', 'LIKE', "%{$filters['created_by']}%");
        }

        if (isset($filters['min_quantity']) && $filters['min_quantity'] !== null) {
            $query->where('quantity', '>=', $filters['min_quantity']);
        }

        if (isset($filters['max_quantity']) && $filters['max_quantity'] !== null) {
            $query->where('quantity', '<=', $filters['max_quantity']);
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== null) {
            $query->where('unit_price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== null) {
            $query->where('unit_price', '<=', $filters['max_price']);
        }

        if (!empty($filters['purchase_date_from'])) {
            $query->whereDate('purchase_date', '>=', $filters['purchase_date_from']);
        }

        if (!empty($filters['purchase_date_to'])) {
            $query->whereDate('purchase_date', '<=', $filters['purchase_date_to']);
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
