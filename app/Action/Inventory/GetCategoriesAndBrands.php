<?php

namespace App\Action\Inventory;

use App\Models\ShopInventory;
use Illuminate\Support\Facades\Log;

class GetCategoriesAndBrands
{
    public function __invoke(): array
    {
        try {
            $categories = ShopInventory::whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->toArray();

            $brands = ShopInventory::whereNotNull('brand')
                ->where('brand', '!=', '')
                ->distinct()
                ->orderBy('brand')
                ->pluck('brand')
                ->toArray();

            return [
                'success' => true,
                'data' => [
                    'categories' => $categories,
                    'brands' => $brands,
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch categories and brands: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error retrieving categories and brands'
            ];
        }
    }
}
