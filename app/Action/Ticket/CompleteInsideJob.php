<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompleteInsideJob
{
    /**
     * Complete inside job repair
     */
    public function __invoke(array $data): array
    {
        DB::beginTransaction();

        try {
            $ticket = Ticket::findOrFail($data['ticket_id']);

            if ($ticket->job_type !== 'inside') {
                throw new \Exception('Can only complete inside jobs with this action');
            }

            $ticket->update([
                'repair_notes' => $data['repair_notes'] ?? null,
                'actual_parts_used' => $data['actual_parts_used'] ?? null,
                'completed_at' => now(),
                'status' => 'completed',
                'planned_materials' => null,
            ]);

            // Delete planned materials from the new table
            \App\Models\JobPlannedMaterial::where('ticket_id', $ticket->id)->delete();

            DB::commit();

            return CommonResponse::sendSuccessResponse('Inside job completed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete inside job: ' . $e->getMessage(), ['data' => $data]);
            return CommonResponse::sendBadResponseWithMessage('Failed to complete inside job');
        }
    }
}
