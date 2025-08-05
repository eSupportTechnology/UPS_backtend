<?php

namespace App\Action\AMC;

use App\Models\AMCContract;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class DeactivateAMCContract
{
    public function __invoke(string $id): array
    {
        try {
            $contract = AMCContract::find($id);

            if (! $contract) {
                return CommonResponse::sendBadResponseWithMessage('Contract not found');
            }

            $contract->is_active = 0;
            $contract->save();

            return CommonResponse::sendSuccessResponse('Contract deactivated successfully');
        } catch (\Exception $e) {
            Log::error('Contract deactivation failed: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage('Contract deactivation failed');
        }
    }
}
