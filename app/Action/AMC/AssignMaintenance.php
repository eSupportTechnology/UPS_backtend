<?php

namespace App\Action\AMC;

use App\Models\AMCMaintenance;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignMaintenance
{
    public function __invoke(array $data): array
    {
        DB::beginTransaction();

        try {
            $maintenance = AMCMaintenance::findOrFail($data['maintenance_id']);

            $maintenance->assigned_to = $data['assigned_to'];
            $maintenance->status = 'assigned';
            $maintenance->save();

            DB::commit();

            return CommonResponse::sendSuccessResponse('Maintenance assigned successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign maintenance: ' . $e->getMessage(), ['data' => $data]);

            return CommonResponse::sendBadResponseWithMessage('Failed to assign maintenance');
        }
    }
}
