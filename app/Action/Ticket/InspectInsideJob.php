<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InspectInsideJob
{
    /**
     * Record inspection findings for inside job
     */
    public function __invoke(array $data): array
    {
        DB::beginTransaction();

        try {
            $ticket = Ticket::findOrFail($data['ticket_id']);

            if ($ticket->job_type !== 'inside') {
                throw new \Exception('Can only inspect inside jobs');
            }

            $ticket->update([
                'inspection_notes' => $data['inspection_notes'],
                'inspected_at' => Carbon::now(),
                'inspected_by' => $data['inspected_by'],
                'status' => 'inspected',
            ]);

            DB::commit();

            return CommonResponse::sendSuccessResponse('Inspection recorded successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record inspection: ' . $e->getMessage(), ['data' => $data]);
            return CommonResponse::sendBadResponseWithMessage('Failed to record inspection');
        }
    }
}
