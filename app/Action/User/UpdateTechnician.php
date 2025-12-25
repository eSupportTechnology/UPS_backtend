<?php

namespace App\Action\User;

use App\Models\User;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UpdateTechnician
{
    public function __invoke(string $technicianId, array $data): array
    {
        try {
            $technician = User::findOrFail($technicianId);

            // Handle profile image upload if provided
            $profileImagePath = $technician->profile_image;
            if (isset($data['profile_image']) && $data['profile_image']) {
                // Delete old image if exists
                if ($technician->profile_image) {
                    Storage::disk('public')->delete($technician->profile_image);
                }

                $file = $data['profile_image'];
                $filename = 'technician_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $profileImagePath = $file->storeAs('technicians', $filename, 'public');
            }

            // Prepare update data
            $updateData = [
                'name' => $data['name'] ?? $technician->name,
                'email' => $data['email'] ?? $technician->email,
                'phone' => $data['phone'] ?? $technician->phone,
                'address' => $data['address'] ?? $technician->address,
                'technician_type' => $data['technician_type'] ?? $technician->technician_type,
                'employment_type' => $data['employment_type'] ?? $technician->employment_type,
                'profile_image' => $profileImagePath,
                'specialization' => $data['specialization'] ?? $technician->specialization,
            ];

            // Only update password if provided
            if (isset($data['password']) && !empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $technician->update($updateData);

            return CommonResponse::sendSuccessResponse(
                'Technician updated successfully',
                ['technician' => $technician]
            );
        } catch (\Exception $e) {
            Log::error('Failed to update technician: ' . $e->getMessage(), ['id' => $technicianId]);
            return CommonResponse::sendBadResponseWithMessage($e->getMessage());
        }
    }
}
