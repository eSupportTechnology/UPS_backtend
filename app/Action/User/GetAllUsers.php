<?php

namespace App\Action\User;

use App\Models\User;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class GetAllUsers
{
    public function __invoke(): array
    {
        try {
            $users = User::select('id', 'name', 'email', 'role_as', 'phone', 'address', 'is_active')
                ->paginate(10);

            return CommonResponse::sendSuccessResponseWithData('users', $users);
        } catch (\Exception $e) {
            Log::error('Failed to fetch users: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage('Error retrieving user list');
        }
    }
}
