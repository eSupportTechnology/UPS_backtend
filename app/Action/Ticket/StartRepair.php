<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StartRepair
{
    /**
     * Start repair work on approved inside job
     */
    public function __invoke(array $data): array
    {
        DB::beginTransaction();

        try {
            $ticket = Ticket::findOrFail($data['ticket_id']);

            // Update to in_repair status - allow from any status
            $ticket->update([
                'in_repair_at' => now(),
                'status' => 'in_repair',
            ]);

            DB::commit();

            return CommonResponse::sendSuccessResponse('Repair started successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to start repair: ' . $e->getMessage(), ['data' => $data]);
            return CommonResponse::sendBadResponseWithMessage('Failed to start repair');
        }
    }
}
