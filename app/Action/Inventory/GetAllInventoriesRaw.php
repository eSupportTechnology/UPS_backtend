<?php

namespace App\Action\Inventory;

use App\Models\ShopInventory;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class GetAllInventoriesRaw
{
    public function __invoke(): array
    {
        try {
            $inventories = ShopInventory::select([
                'id',
                'product_name',
                'serial_number',
                'category',
                'quantity',
                'warranty',
            ])
                ->orderBy('created_at', 'desc')
                ->get();

            return CommonResponse::sendSuccessResponseWithData('inventories', $inventories);
        } catch (\Exception $e) {
            Log::error('Failed to fetch all inventories (raw): ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving full inventory list');
        }
    }
}
