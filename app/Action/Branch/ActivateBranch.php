<?php

namespace App\Action\Branch;

use App\Models\Branch;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class ActivateBranch
{
    public function __invoke(string $id): array
    {
        try {
            $branch = Branch::find($id);

            if (! $branch) {
                return CommonResponse::sendBadResponseWithMessage('Branch not found');
            }

            $branch->is_active = true;
            $branch->save();

            return CommonResponse::sendSuccessResponse('Branch activated successfully');
        } catch (\Exception $e) {
            Log::error('Activate branch error: ' . $e->getMessage(), ['id' => $id]);
            return CommonResponse::sendBadResponseWithMessage('Failed to activate branch');
        }
    }
}
