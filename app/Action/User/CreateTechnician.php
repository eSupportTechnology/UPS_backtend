<?php

namespace App\Action\User;

use App\Models\User;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CreateTechnician
{
    public function __invoke(array $data): array
    {
        try {
            // Handle profile image upload
            $profileImagePath = null;
            if (isset($data['profile_image']) && $data['profile_image']) {
                $file = $data['profile_image'];
                $filename = 'technician_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $profileImagePath = $file->storeAs('technicians', $filename, 'public');
            }

            // Generate a temporary password
            $temporaryPassword = $this->generateTemporaryPassword();

            // Create technician user
            $technician = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($temporaryPassword),
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'role_as' => User::ROLE_TECHNICIAN,
                'is_active' => true,
                'technician_type' => $data['technician_type'], // 'inside' or 'outside'
                'employment_type' => $data['employment_type'] ?? null, // 'full_time' or 'part_time' for inside technicians
                'profile_image' => $profileImagePath,
                'specialization' => $data['specialization'] ?? null,
            ]);

            return CommonResponse::sendSuccessResponse(
                "Technician created successfully. Temporary password: {$temporaryPassword}",
                [
                    'technician' => $technician->load([]),
                    'temporary_password' => $temporaryPassword,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to create technician: ' . $e->getMessage(), ['data' => $data]);
            return CommonResponse::sendBadResponseWithMessage($e->getMessage());
        }
    }

    private function generateTemporaryPassword(): string
    {
        return 'Tech' . date('Y') . rand(10000, 99999);
    }
}
