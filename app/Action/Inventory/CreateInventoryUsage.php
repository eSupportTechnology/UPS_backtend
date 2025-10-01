<?php

namespace App\Action\Inventory;

use App\Models\InventoryItemUsage;
use App\Models\ShopInventory;
use App\Response\CommonResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateInventoryUsage
{
    public function __invoke(array $data): array
    {
        try {
            DB::transaction(function () use ($data) {
                foreach ($data['usages'] as $usage) {
                    $inventory = ShopInventory::lockForUpdate()->find($usage['inventory_id']);

                    if (!$inventory) {
                        throw new Exception("Inventory item not found: {$usage['inventory_id']}");
                    }

                    if ($inventory->quantity < $usage['quantity']) {
                        throw new Exception("Not enough stock for inventory ID: {$usage['inventory_id']}");
                    }

                    $inventory->quantity -= $usage['quantity'];
                    $inventory->save();

                    InventoryItemUsage::create([
                        'inventory_id' => $usage['inventory_id'],
                        'reference_id' => $data['reference_id'],
                        'usage_type'   => $data['usage_type'],
                        'quantity'     => $usage['quantity'],
                        'usage_date'   => $data['usage_date'],
                        'notes'        => $data['notes'] ?? null,
                    ]);
                }
            });

            return CommonResponse::sendSuccessResponse('Inventory usage recorded and stock updated successfully');
        } catch (\Exception $e) {
            Log::error('Create inventory usage error: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage($e->getMessage());
        }
    }
}
