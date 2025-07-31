<?php

namespace App\Action\Branch;

use App\Models\Branch;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class DeleteBranch
{
    public function __invoke(string $id): array
    {
        try {
            $branch = Branch::find($id);

            if (! $branch) {
                return CommonResponse::sendBadResponseWithMessage('Branch not found');
            }

            $branch->delete();

            return CommonResponse::sendSuccessResponse('Branch deleted successfully');
        } catch (\Exception $e) {
            Log::error('Delete branch error: ' . $e->getMessage(), ['id' => $id]);
            return CommonResponse::sendBadResponseWithMessage('Failed to delete branch');
        }
    }
}
