<?php

namespace App\Action\Inventory;

use App\Models\ShopInventory;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class DeleteInventory
{
    public function __invoke(string $id): array
    {
        try {
            $inventory = ShopInventory::find($id);
            if (! $inventory) {
                return CommonResponse::sendBadResponseWithMessage('Inventory not found');
            }

            $inventory->delete();
            return CommonResponse::sendSuccessResponse('Inventory deleted successfully');
        } catch (\Exception $e) {
            Log::error('Delete inventory error: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage('Failed to delete inventory');
        }
    }
}
