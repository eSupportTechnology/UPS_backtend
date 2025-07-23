<?php

namespace App\Action\User;

use App\Models\User;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class DeleteUser
{
    public function __invoke(string $id): array
    {
        try {
            $user = User::find($id);

            if (! $user) {
                return CommonResponse::sendBadResponseWithMessage('User not found');
            }

            $user->delete();

            return CommonResponse::sendSuccessResponse('User deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete user: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage('Failed to delete user');
        }
    }
}
