<?php

namespace Database\Seeders;

use App\Models\ShopInventory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShopInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ShopInventory::factory()->count(30)->create();
    }
}
