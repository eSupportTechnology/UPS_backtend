<?php

namespace App\Action\Inventory;

use App\Models\ShopInventory;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class GetAllInventories
{
    public function __invoke(): array
    {
        try {
            $inventories = ShopInventory::paginate(10);
            return CommonResponse::sendSuccessResponseWithData('inventories', $inventories);
        } catch (\Exception $e) {
            Log::error('Get inventories error: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage('Failed to fetch inventories');
        }
    }
}
