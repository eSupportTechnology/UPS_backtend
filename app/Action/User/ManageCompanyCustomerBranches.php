<?php

namespace App\Action\User;

use App\Models\User;
use App\Models\CompanyBranch;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class ManageCompanyCustomerBranches
{
    /**
     * Add branches to a company customer
     */
    public function addBranches(string $customerId, array $branchNames): array
    {
        try {
            $customer = User::findOrFail($customerId);

            // Verify customer is a company customer
            if ($customer->customer_type !== 'company') {
                return CommonResponse::sendBadResponseWithMessage('Customer must be a company customer');
            }

            $addedBranches = [];

            foreach ($branchNames as $branchName) {
                // Check if branch already exists for this company
                $exists = CompanyBranch::where('company_id', $customerId)
                    ->where('branch_name', $branchName)
                    ->exists();

                if (!$exists) {
                    $branch = CompanyBranch::create([
                        'company_id' => $customerId,
                        'branch_name' => $branchName,
                        'is_primary' => false,
                    ]);

                    $addedBranches[] = $branch;
                }
            }

            // Reload branches relationship
            $customer->load('branches');

            return CommonResponse::sendSuccessResponse(
                'Branches added successfully',
                [
                    'customer' => $customer,
                    'branches' => $customer->branches,
                    'added_branches' => count($addedBranches),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to add branches to company customer: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage($e->getMessage());
        }
    }

    /**
     * Update branch name for a company customer's branch
     */
    public function updateBranch(string $customerId, string $branchId, string $branchName): array
    {
        try {
            $customer = User::findOrFail($customerId);

            // Verify customer is a company customer
            if ($customer->customer_type !== 'company') {
                return CommonResponse::sendBadResponseWithMessage('Customer must be a company customer');
            }

            // Verify the branch belongs to this customer
            $branch = CompanyBranch::where('id', $branchId)
                ->where('company_id', $customerId)
                ->firstOrFail();

            // Update the branch name
            $branch->update(['branch_name' => $branchName]);

            // Reload branches relationship
            $customer->load('branches');

            return CommonResponse::sendSuccessResponse(
                'Branch updated successfully',
                [
                    'customer' => $customer,
                    'branches' => $customer->branches,
                    'updated_branch' => $branch,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to update branch: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage($e->getMessage());
        }
    }

    /**
     * Remove branch from company customer
     */
    public function removeBranch(string $customerId, string $branchId): array
    {
        try {
            $customer = User::findOrFail($customerId);

            // Verify customer is a company customer
            if ($customer->customer_type !== 'company') {
                return CommonResponse::sendBadResponseWithMessage('Customer must be a company customer');
            }

            CompanyBranch::where('id', $branchId)
                ->where('company_id', $customerId)
                ->delete();

            // Reload branches relationship
            $customer->load('branches');

            return CommonResponse::sendSuccessResponse(
                'Branch removed successfully',
                [
                    'customer' => $customer,
                    'branches' => $customer->branches,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to remove branch from company customer: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage($e->getMessage());
        }
    }

    /**
     * Get all branches for a company customer
     */
    public function getCustomerBranches(string $customerId): array
    {
        try {
            $customer = User::where('id', $customerId)
                ->where('customer_type', 'company')
                ->firstOrFail();

            $branches = CompanyBranch::where('company_id', $customerId)->get();

            return CommonResponse::sendSuccessResponse(
                'Branches retrieved successfully',
                [
                    'data' => [
                        'customer_id' => $customerId,
                        'branches' => $branches,
                    ]
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to get customer branches: ' . $e->getMessage());
            return CommonResponse::sendBadResponseWithMessage($e->getMessage());
        }
    }
}
