<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@maintenance.com',
                'password' => Hash::make('password123'),
                'role_as' => User::ROLE_SUPER_ADMIN,
                'phone' => '+1234567890',
                'address' => '123 Admin Street, Admin City',
                'is_active' => true,
            ],
            [
                'name' => 'System Admin',
                'email' => 'admin@maintenance.com',
                'password' => Hash::make('password123'),
                'role_as' => User::ROLE_ADMIN,
                'phone' => '+1234567891',
                'address' => '456 Admin Avenue, Admin City',
                'is_active' => true,
            ],
            [
                'name' => 'Operations Manager',
                'email' => 'operator@maintenance.com',
                'password' => Hash::make('password123'),
                'role_as' => User::ROLE_OPERATOR,
                'phone' => '+1234567892',
                'address' => '789 Operations Blvd, Operations City',
                'is_active' => true,
            ],
            [
                'name' => 'Senior Technician',
                'email' => 'technician@maintenance.com',
                'password' => Hash::make('password123'),
                'role_as' => User::ROLE_TECHNICIAN,
                'phone' => '+1234567893',
                'address' => '321 Tech Lane, Tech City',
                'is_active' => true,
            ],
            [
                'name' => 'John Customer',
                'email' => 'customer@maintenance.com',
                'password' => Hash::make('password123'),
                'role_as' => User::ROLE_CUSTOMER,
                'phone' => '+1234567894',
                'address' => '654 Customer Road, Customer City',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        User::factory(5)->create([
            'role_as' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        User::factory(3)->create([
            'role_as' => User::ROLE_TECHNICIAN,
            'is_active' => true,
        ]);
    }
}
