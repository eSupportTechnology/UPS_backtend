<?php

namespace App\Action\AMC;

use App\Models\AMCContract;
use App\Models\AMCMaintenance;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateAMCContract
{
    public function __invoke(string $id, array $data): array
    {
        DB::beginTransaction();

        try {
            $contract = AMCContract::find($id);
            if (! $contract) {
                return CommonResponse::sendBadResponseWithMessage('AMC Contract not found');
            }

            $maintenances = $data['maintenances'] ?? [];
            unset($data['maintenances']);

            $contract->update($data);

            AMCMaintenance::where('amc_contract_id', $id)->delete();

            foreach ($maintenances as $item) {
                $item['amc_contract_id'] = $contract->id;
                AMCMaintenance::create($item);
            }

            DB::commit();
            return CommonResponse::sendSuccessResponseWithData('AMC Contract updated successfully', $contract);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update AMC Contract error: ' . $e->getMessage(), ['id' => $id, 'data' => $data]);
            return CommonResponse::sendBadResponseWithMessage('Failed to update AMC Contract');
        }
    }
}
