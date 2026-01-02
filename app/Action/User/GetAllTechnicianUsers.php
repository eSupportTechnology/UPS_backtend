<?php

namespace App\Action\User;

use App\Models\User;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class GetAllTechnicianUsers
{
    public function __invoke(array $filters = []): array
    {
        try {
            $ROLE_TECHNICIAN = 4;

            $query = User::select(
                'id',
                'name',
                'email',
                'role_as',
                'phone',
                'address',
                'is_active',
                'technician_type',
                'employment_type',
                'profile_image',
                'specialization',
                'created_at'
            )
                ->where('is_active', 1)
                ->where('role_as', $ROLE_TECHNICIAN);

            // Filter by technician type if provided
            if (!empty($filters['technician_type'])) {
                $query->where('technician_type', $filters['technician_type']);
            }

            $users = $query->orderBy('created_at', 'desc')->get();
            return CommonResponse::sendSuccessResponseWithData('users', $users);
        } catch (\Exception $e) {
            Log::error('Failed to fetch technician users: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving technician list');
        }
    }
}
