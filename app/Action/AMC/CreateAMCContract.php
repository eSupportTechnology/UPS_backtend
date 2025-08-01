<?php

namespace App\Action\AMC;

use App\Models\AMCContract;
use App\Models\AMCMaintenance;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateAMCContract
{
    public function __invoke(array $data): array
    {
        DB::beginTransaction();

        try {
            $maintenances = $data['maintenances'] ?? [];
            unset($data['maintenances']);

            $contract = AMCContract::create($data);

            foreach ($maintenances as $item) {
                $item['amc_contract_id'] = $contract->id;
                AMCMaintenance::create($item);
            }

            DB::commit();
            return CommonResponse::sendSuccessResponseWithData('AMC Contract created successfully', $contract);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create AMC Contract error: ' . $e->getMessage(), ['data' => $data]);
            return CommonResponse::sendBadResponseWithMessage('Failed to create AMC Contract');
        }
    }
}
