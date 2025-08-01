<?php

namespace App\Action\User;

use App\Models\User;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class GetActiveCustomers
{
    public function __invoke(): array
    {
        try {
            $customers = User::select('id', 'name', 'email')
                ->where('role_as', User::ROLE_CUSTOMER)
                ->where('is_active', true)
                ->orderBy('name', 'asc')
                ->get();

            return CommonResponse::sendSuccessResponseWithData('customers', $customers);
        } catch (\Exception $e) {
            Log::error('Failed to fetch active customers: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving active customers list');
        }
    }
}
