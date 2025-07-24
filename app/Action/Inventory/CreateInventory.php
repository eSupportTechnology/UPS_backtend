<?php

namespace App\Action\Inventory;

use App\Models\ShopInventory;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class CreateInventory
{
    public function __invoke(array $data): array
    {
        try {
            ShopInventory::create($data);
            return CommonResponse::sendSuccessResponse('Inventory created successfully');
        } catch (\Exception $e) {
            Log::error('Create inventory error: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage('Failed to create inventory');
        }
    }
}

