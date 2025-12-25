<?php

namespace App\Action\User;

use App\Models\User;
use App\Models\CompanyBranch;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CreateCustomer
{
    public function __invoke(array $data): array
    {
        try {
            // Generate a temporary password
            $temporaryPassword = $this->generateTemporaryPassword();

            // Create customer user
            $customer = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($temporaryPassword),
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'role_as' => User::ROLE_CUSTOMER,
                'is_active' => true,
                'customer_type' => $data['customer_type'], // 'personal' or 'company'
                'company_name' => $data['company_name'] ?? null, // Optional
            ]);

            // If company customer and branches provided, create them (optional)
            if ($data['customer_type'] === 'company' && isset($data['branches']) && is_array($data['branches']) && count($data['branches']) > 0) {
                foreach ($data['branches'] as $index => $branchData) {
                    // Create company branch with only name
                    CompanyBranch::create([
                        'company_id' => $customer->id,
                        'branch_name' => $branchData['name'],
                        'is_primary' => $index === 0, // First branch is primary
                    ]);
                }
            }

            return CommonResponse::sendSuccessResponse(
                "Customer created successfully. Temporary password: {$temporaryPassword}",
                [
                    'customer' => $customer->load(['branches']),
                    'temporary_password' => $temporaryPassword,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to create customer: ' . $e->getMessage(), ['data' => $data]);
            return CommonResponse::sendBadResponseWithMessage($e->getMessage());
        }
    }

    private function generateTemporaryPassword(): string
    {
        return 'Cust' . date('Y') . rand(10000, 99999);
    }
}
