<?php

namespace App\Action\User;

use App\Models\User;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class UpdateUser
{
    public function __invoke(string $id, array $data): array
    {
        try {
            $user = User::find($id);

            if (! $user) {
                return CommonResponse::sendBadResponseWithMessage('User not found');
            }

            $user->update([
                'name'    => $data['name'],
                'email'   => $data['email'],
                'role_as' => $data['role_as'],
                'phone'   => $data['phone'] ,
                'address' => $data['address'] ,
            ]);

            return CommonResponse::sendSuccessResponse('User updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update user: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage('User update failed');
        }
    }
}
