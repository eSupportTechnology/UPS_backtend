<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update inventory_item_usages table
        DB::statement("ALTER TABLE inventory_item_usages MODIFY COLUMN usage_type ENUM('maintenance', 'contract', 'inside_job', 'outside_job')");

        // Update inventory_item_returns table
        DB::statement("ALTER TABLE inventory_item_returns MODIFY COLUMN usage_type ENUM('maintenance', 'contract', 'inside_job', 'outside_job')");
    }

    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE inventory_item_usages MODIFY COLUMN usage_type ENUM('maintenance', 'contract')");
        DB::statement("ALTER TABLE inventory_item_returns MODIFY COLUMN usage_type ENUM('maintenance', 'contract')");
    }
};
