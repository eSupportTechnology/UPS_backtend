<?php

namespace App\Action\AMC;

use App\Models\AMCContract;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class ActivateAMCContract
{
    public function __invoke(string $id): array
    {
        try {
            $contract = AMCContract::find($id);

            if (! $contract) {
                return CommonResponse::sendBadResponseWithMessage('Contract not found');
            }

            $contract->is_active = 1;
            $contract->save();

            return CommonResponse::sendSuccessResponse('Contract activated successfully');
        } catch (\Exception $e) {
            Log::error('Contract activation failed: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage('Contract activation failed');
        }
    }
}
