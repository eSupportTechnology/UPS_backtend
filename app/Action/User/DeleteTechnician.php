<?php

namespace App\Action\User;

use App\Models\User;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteTechnician
{
    public function __invoke(string $technicianId): array
    {
        try {
            $technician = User::findOrFail($technicianId);

            // Delete profile image if exists
            if ($technician->profile_image) {
                Storage::disk('public')->delete($technician->profile_image);
            }

            // Delete the technician
            $technician->delete();

            return CommonResponse::sendSuccessResponse('Technician deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete technician: ' . $e->getMessage(), ['id' => $technicianId]);
            return CommonResponse::sendBadResponseWithMessage($e->getMessage());
        }
    }
}
