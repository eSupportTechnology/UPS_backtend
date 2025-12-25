<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use App\Services\JobNumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateInsideJobDirect
{
    /**
     * Create an inside job directly (not from converting an outside job)
     */
    public function __invoke(array $data): array
    {
        DB::beginTransaction();

        try {
            // Generate job number
            $jobNumber = JobNumberGenerator::generateInsideJobNumber();

            // Create inside job ticket
            $insideJob = Ticket::create([
                'job_type' => 'inside',
                'job_number' => $jobNumber,
                'customer_id' => $data['customer_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'ups_serial_number' => $data['ups_serial_number'] ?? null,
                'ups_model' => $data['ups_model'] ?? null,
                'ups_brand' => $data['ups_brand'] ?? null,
                'status' => 'pending_inspection',
                'priority' => $data['priority'] ?? 'medium',
                'assigned_to' => $data['assigned_to'] ?? null,
                'district' => $data['district'] ?? null,
                'city' => $data['city'] ?? null,
                'gramsewa_division' => $data['gramsewa_division'] ?? null,
            ]);

            DB::commit();

            return CommonResponse::sendSuccessResponse(
                "Inside job created successfully with job number: {$jobNumber}",
                ['inside_job' => $insideJob]
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create inside job: ' . $e->getMessage(), ['data' => $data]);
            return CommonResponse::sendBadResponseWithMessage($e->getMessage());
        }
    }
}
