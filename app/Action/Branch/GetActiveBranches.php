<?php

namespace App\Action\Branch;

use App\Models\Branch;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class GetActiveBranches
{
    public function __invoke(): array
    {
        try {
            $branches = Branch::select([
                'id', 'name', 'branch_code'
            ])
                ->where('is_active', true)
                ->get();

            return CommonResponse::sendSuccessResponseWithData('branches', $branches);
        } catch (\Exception $e) {
            Log::error('Failed to fetch active branches: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving active branches list');
        }
    }
}
