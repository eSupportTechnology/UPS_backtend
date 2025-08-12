<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CompleteTicket
{
    public function __invoke(array $data): array
    {
        DB::beginTransaction();

        try {
            $ticket = Ticket::findOrFail($data['ticket_id']);

            $ticket->completed_at = Carbon::now();
            $ticket->status = 'completed';
            $ticket->save();

            DB::commit();

            return CommonResponse::sendSuccessResponse('Ticket marked as completed successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to complete ticket: ' . $e->getMessage(), ['data' => $data]);

            return CommonResponse::sendBadResponseWithMessage('Failed to complete ticket');
        }
    }
}
