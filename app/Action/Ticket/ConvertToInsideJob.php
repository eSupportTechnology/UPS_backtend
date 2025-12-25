<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use App\Services\JobNumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConvertToInsideJob
{
    /**
     * Convert an outside job to inside job (workshop repair)
     */
    public function __invoke(array $data): array
    {
        DB::beginTransaction();

        try {
            $outsideTicket = Ticket::findOrFail($data['outside_ticket_id']);

            // Validate it's an outside job
            if ($outsideTicket->job_type !== 'outside') {
                throw new \Exception('Can only convert outside jobs to inside jobs');
            }

            // Generate job number
            $jobNumber = JobNumberGenerator::generateInsideJobNumber();

            // Create inside job ticket
            $insideJob = Ticket::create([
                'job_type' => 'inside',
                'job_number' => $jobNumber,
                'parent_ticket_id' => $outsideTicket->id,
                'customer_id' => $outsideTicket->customer_id,
                'title' => $data['title'] ?? "Workshop Repair - " . $outsideTicket->title,
                'description' => $data['description'] ?? $outsideTicket->description,
                'ups_serial_number' => $data['ups_serial_number'] ?? null,
                'ups_model' => $data['ups_model'] ?? null,
                'ups_brand' => $data['ups_brand'] ?? null,
                'status' => 'pending_inspection',
                'priority' => $data['priority'] ?? $outsideTicket->priority,
                'assigned_to' => $data['assigned_to'] ?? null,
                'district' => $outsideTicket->district,
                'city' => $outsideTicket->city,
                'gramsewa_division' => $outsideTicket->gramsewa_division,
            ]);

            // Update outside job status
            $outsideTicket->update([
                'status' => 'transferred_to_workshop',
                'completed_at' => now(),
            ]);

            DB::commit();

            return CommonResponse::sendSuccessResponse(
                "Inside job created successfully with job number: {$jobNumber}",
                ['inside_job' => $insideJob]
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to convert to inside job: ' . $e->getMessage(), ['data' => $data]);
            return CommonResponse::sendBadResponseWithMessage($e->getMessage());
        }
    }
}
