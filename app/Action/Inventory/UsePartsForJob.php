<?php

namespace App\Action\Inventory;

use App\Models\InventoryItemUsage;
use App\Models\ShopInventory;
use App\Models\Ticket;
use App\Response\CommonResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsePartsForJob
{
    /**
     * Record parts usage for inside/outside jobs
     * For inside jobs, happens after approval
     * For outside jobs, happens on accept (existing flow)
     */
    public function __invoke(array $data): array
    {
        try {
            DB::transaction(function () use ($data) {
                $ticket = Ticket::findOrFail($data['ticket_id']);
                $usageType = $ticket->isInsideJob() ? 'inside_job' : 'outside_job';

                // For inside jobs, check if approved
                if ($ticket->isInsideJob() && !$ticket->isApproved()) {
                    throw new Exception('Cannot use parts for inside job until quote is approved');
                }

                foreach ($data['parts'] as $part) {
                    $inventory = ShopInventory::lockForUpdate()->find($part['inventory_id']);

                    if (!$inventory) {
                        throw new Exception("Inventory item not found: {$part['inventory_id']}");
                    }

                    if ($inventory->quantity < $part['quantity']) {
                        throw new Exception("Not enough stock for {$inventory->product_name}");
                    }

                    $inventory->quantity -= $part['quantity'];
                    $inventory->save();

                    InventoryItemUsage::create([
                        'inventory_id' => $part['inventory_id'],
                        'reference_id' => $ticket->id,
                        'usage_type'   => $usageType,
                        'quantity'     => $part['quantity'],
                        'usage_date'   => $data['usage_date'] ?? now(),
                        'notes'        => $data['notes'] ?? "Used for {$ticket->job_number}",
                    ]);
                }
            });

            return CommonResponse::sendSuccessResponse('Parts allocated successfully');
        } catch (\Exception $e) {
            Log::error('Parts usage error: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage($e->getMessage());
        }
    }
}
