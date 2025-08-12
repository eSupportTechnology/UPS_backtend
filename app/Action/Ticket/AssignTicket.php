<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AssignTicket
{
    public function __invoke(array $data): array
    {
        DB::beginTransaction();

        try {
            $ticket = Ticket::findOrFail($data['ticket_id']);

            $ticket->assigned_to = $data['assigned_to'];
            if (!empty($data['priority'])) {
                $ticket->priority = $data['priority'];
            }
            $ticket->status = 'assigned';
            $ticket->save();

            DB::commit();

            return CommonResponse::sendSuccessResponse('Ticket assigned successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to assign ticket: ' . $e->getMessage(), ['data' => $data]);

            return CommonResponse::sendBadResponseWithMessage('Failed to assign ticket');
        }
    }
}
