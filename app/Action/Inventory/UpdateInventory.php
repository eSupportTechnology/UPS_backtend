<?php
namespace App\Action\Inventory;

use App\Models\ShopInventory;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class UpdateInventory
{
    public function __invoke(string $id, array $data): array
    {
        try {
            $inventory = ShopInventory::find($id);
            if (! $inventory) {
                return CommonResponse::sendBadResponseWithMessage('Inventory not found');
            }

            $inventory->update($data);
            return CommonResponse::sendSuccessResponse('Inventory updated successfully');
        } catch (\Exception $e) {
            Log::error('Update inventory error: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage('Failed to update inventory');
        }
    }
}

