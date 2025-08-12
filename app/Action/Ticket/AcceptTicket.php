<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AcceptTicket
{
    public function __invoke(array $data): array
    {
        DB::beginTransaction();

        try {
            $ticket = Ticket::findOrFail($data['ticket_id']);

            $ticket->accepted_at = Carbon::now();

            $ticket->status = 'accepted';

            $ticket->save();

            DB::commit();

            return CommonResponse::sendSuccessResponse('Ticket marked as accepted successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to mark ticket as accepted: ' . $e->getMessage(), ['data' => $data]);

            return CommonResponse::sendBadResponseWithMessage('Failed to accept ticket');
        }
    }
}
