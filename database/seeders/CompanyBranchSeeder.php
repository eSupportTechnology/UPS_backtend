<?php

namespace Database\Seeders;

use App\Models\CompanyBranch;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanyBranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all company customers
        $companyCustomers = User::where('customer_type', 'company')
            ->where('is_active', true)
            ->get();

        foreach ($companyCustomers as $customer) {
            // Create 2-3 branches for each company customer
            $branches = [
                [
                    'company_id' => $customer->id,
                    'branch_name' => $customer->company_name . ' - Headquarters',
                    'is_primary' => true,
                ],
                [
                    'company_id' => $customer->id,
                    'branch_name' => $customer->company_name . ' - Branch 1',
                    'is_primary' => false,
                ],
                [
                    'company_id' => $customer->id,
                    'branch_name' => $customer->company_name . ' - Branch 2',
                    'is_primary' => false,
                ],
            ];

            foreach ($branches as $branch) {
                // Check if branch already exists
                $exists = CompanyBranch::where('company_id', $branch['company_id'])
                    ->where('branch_name', $branch['branch_name'])
                    ->exists();

                if (!$exists) {
                    CompanyBranch::create($branch);
                }
            }
        }
    }
}
