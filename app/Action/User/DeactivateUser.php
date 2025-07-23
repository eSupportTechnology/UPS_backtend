<?php

namespace App\Action\User;

use App\Models\User;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class DeactivateUser
{
    public function __invoke(string $id): array
    {
        try {
            $user = User::find($id);

            if (! $user) {
                return CommonResponse::sendBadResponseWithMessage('User not found');
            }

            $user->is_active = 0;
            $user->save();

            return CommonResponse::sendSuccessResponse('User deactivated successfully');
        } catch (\Exception $e) {
            Log::error('Deactivation failed: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage('Deactivation failed');
        }
    }
}
