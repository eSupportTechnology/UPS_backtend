<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApproveQuote
{
    /**
     * Customer/Admin approves or rejects quote
     */
    public function __invoke(array $data): array
    {
        DB::beginTransaction();

        try {
            $ticket = Ticket::findOrFail($data['ticket_id']);

            if ($ticket->approval_status !== 'pending') {
                throw new \Exception('Quote has already been decided');
            }

            $approved = $data['approved'];

            $ticket->update([
                'approval_status' => $approved ? 'approved' : 'rejected',
                'approval_decision_at' => Carbon::now(),
                'approval_notes' => $data['notes'] ?? null,
                'status' => $approved ? 'approved_for_repair' : 'quote_rejected',
            ]);

            DB::commit();

            $message = $approved
                ? 'Quote approved successfully'
                : 'Quote rejected';

            return CommonResponse::sendSuccessResponse($message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process quote approval: ' . $e->getMessage(), ['data' => $data]);
            return CommonResponse::sendBadResponseWithMessage('Failed to process approval');
        }
    }
}
