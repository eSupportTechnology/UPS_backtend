<?php

namespace App\Console\Commands;

use App\Models\CompanyBranch;
use App\Models\User;
use Illuminate\Console\Command;

class SeedCompanyBranches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-company-branches {--force : Force the operation to run when in production.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test branches for all company customers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->laravel->environment() === 'production' && !$this->option('force')) {
            $this->error('This command should not be run in production. Use --force to override.');
            return 1;
        }

        $this->info('Seeding company branches...');

        // Get all company customers
        $companyCustomers = User::where('customer_type', 'company')
            ->where('is_active', true)
            ->get();

        if ($companyCustomers->isEmpty()) {
            $this->warn('No active company customers found.');
            return 0;
        }

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($companyCustomers as $customer) {
            $this->line("Processing: {$customer->name} ({$customer->company_name})");

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
                    $createdCount++;
                    $this->line("  ✓ Created: {$branch['branch_name']}");
                } else {
                    $skippedCount++;
                    $this->line("  ⊘ Already exists: {$branch['branch_name']}");
                }
            }
        }

        $this->info("\n=== Summary ===");
        $this->line("Created: {$createdCount} branches");
        $this->line("Skipped: {$skippedCount} branches (already exist)");

        return 0;
    }
}
