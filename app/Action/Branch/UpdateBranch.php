<?php

namespace App\Action\Branch;

use App\Models\Branch;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class UpdateBranch
{
    public function __invoke(string $id, array $data): array
    {
        try {
            $branch = Branch::find($id);
            if (! $branch) {
                return CommonResponse::sendBadResponseWithMessage('Branch not found');
            }

            $branch->update($data);
            return CommonResponse::sendSuccessResponse('Branch updated successfully');
        } catch (\Exception $e) {
            Log::error('Update branch error: ' . $e->getMessage(), ['id' => $id, 'data' => $data]);
            return CommonResponse::sendBadResponseWithMessage('Failed to update branch');
        }
    }
}
