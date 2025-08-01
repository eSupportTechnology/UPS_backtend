<?php

namespace App\Action\AMC;

use App\Models\AMCContract;
use App\Models\AMCMaintenance;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class GetAllAMCContracts
{
    public function __invoke(): array
    {
        try {
            $contracts = AMCContract::select([
                'amc_contracts.id',
                'amc_contracts.branch_id',
                'branches.name as branch_name',
                'amc_contracts.customer_id',
                'users.name as customer_name',
                'amc_contracts.contract_type',
                'amc_contracts.purchase_date',
                'amc_contracts.warranty_end_date',
                'amc_contracts.contract_amount',
                'amc_contracts.notes',
                'amc_contracts.is_active',
            ])
                ->join('branches', 'amc_contracts.branch_id', '=', 'branches.id')
                ->join('users', 'amc_contracts.customer_id', '=', 'users.id')
                ->where('amc_contracts.is_active', true)
                ->get()
                ->keyBy('id');

            if ($contracts->isEmpty()) {
                return CommonResponse::sendSuccessResponseWithData('AMCContract', $contracts);
            }

            $maintenances = AMCMaintenance::select([
                'amc_contract_id',
                'scheduled_date',
                'completed_date',
                'note',
                'status'
            ])
                ->whereIn('amc_contract_id', $contracts->keys())
                ->get()
                ->groupBy('amc_contract_id');


            foreach ($contracts as $id => $contract) {
                $contract->maintenances = $maintenances->get($id, collect())->values();
            }

            return CommonResponse::sendSuccessResponseWithData('AMCContract', $contracts->values());
        } catch (\Exception $e) {
            Log::error('Failed to fetch AMC contracts with maintenance: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving AMC contract list');
        }
    }
}
