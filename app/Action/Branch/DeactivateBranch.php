<?php

namespace App\Action\Branch;

use App\Models\Branch;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class DeactivateBranch
{
    public function __invoke(string $id): array
    {
        try {
            $branch = Branch::find($id);

            if (! $branch) {
                return CommonResponse::sendBadResponseWithMessage('Branch not found');
            }

            $branch->is_active = false;
            $branch->save();

            return CommonResponse::sendSuccessResponse('Branch deactivated successfully');
        } catch (\Exception $e) {
            Log::error('Deactivate branch error: ' . $e->getMessage(), ['id' => $id]);
            return CommonResponse::sendBadResponseWithMessage('Failed to deactivate branch');
        }
    }
}
