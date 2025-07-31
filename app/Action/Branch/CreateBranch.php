<?php

namespace App\Action\Branch;

use App\Models\Branch;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class CreateBranch
{
    public function __invoke(array $data): array
    {
        try {
            Branch::create($data);
            return CommonResponse::sendSuccessResponse('Branch created successfully');
        } catch (\Exception $e) {
            Log::error('Create branch error: ' . $e->getMessage(), ['data' => $data]);
            return CommonResponse::sendBadResponseWithMessage('Failed to create branch');
        }
    }
}
