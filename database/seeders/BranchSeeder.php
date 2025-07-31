<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::create([
            'name' => 'Main Branch Colombo',
            'branch_code' => 'CMB001',
            'type' => 'head',
            'city' => 'Colombo',
            'contact_person' => 'Mr. Silva',
            'contact_number' => '+94-77-1234567',
            'email' => 'colombo@yourcompany.lk',
        ]);

        Branch::create([
            'name' => 'Sub Branch - Galle',
            'branch_code' => 'GAL001',
            'type' => 'sub',
            'city' => 'Galle',
            'contact_person' => 'Ms. Perera',
            'contact_number' => '+94-77-7654321',
            'email' => 'galle@yourcompany.lk',
        ]);
    }
}
