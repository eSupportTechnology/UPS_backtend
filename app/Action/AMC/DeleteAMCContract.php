<?php

namespace App\Action\AMC;

use App\Models\AMCContract;
use App\Models\AMCMaintenance;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteAMCContract
{
    public function __invoke(string $id): array
    {
        DB::beginTransaction();

        try {
            $contract = AMCContract::find($id);
            if (! $contract) {
                return CommonResponse::sendBadResponseWithMessage('AMC Contract not found');
            }

            AMCMaintenance::where('amc_contract_id', $id)->delete();

            $contract->delete();

            DB::commit();
            return CommonResponse::sendSuccessResponse('AMC Contract deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete AMC Contract error: ' . $e->getMessage(), ['id' => $id]);
            return CommonResponse::sendBadResponseWithMessage('Failed to delete AMC Contract');
        }
    }
}
