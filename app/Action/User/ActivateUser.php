<?php

namespace App\Action\User;

use App\Models\User;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class ActivateUser
{
    public function __invoke(string $id): array
    {
        try {
            $user = User::find($id);

            if (! $user) {
                return CommonResponse::sendBadResponseWithMessage('User not found');
            }

            $user->is_active = 1;
            $user->save();

            return CommonResponse::sendSuccessResponse('User activated successfully');
        } catch (\Exception $e) {
            Log::error('Activation failed: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage('Activation failed');
        }
    }
}
