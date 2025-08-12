<?php

namespace App\Action\User;

use App\Models\User;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class GetAllTechnicianUsers
{
    public function __invoke(): array
    {
        try {
            $ROLE_TECHNICIAN = 4;

            $users = User::select(
                'id',
                'name',
                'email',
                'role_as',
                'phone',
                'address',
                'is_active',
                'created_at'
            )
                ->where('is_active', 1)
                ->where('role_as', $ROLE_TECHNICIAN)
                ->orderBy('created_at', 'desc')
                ->get();
            return CommonResponse::sendSuccessResponseWithData('users', $users);
        } catch (\Exception $e) {
            Log::error('Failed to fetch technician users: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving technician list');
        }
    }
}
