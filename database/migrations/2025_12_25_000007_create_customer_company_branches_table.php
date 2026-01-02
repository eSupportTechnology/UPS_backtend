<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_company_branches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('customer_id')->index(); // Company customer user ID (references users.id which is bigInteger)
            $table->uuid('branch_id')->index();   // Branch ID
            $table->boolean('is_primary')->default(false); // Mark primary/headquarters branch
            $table->timestamps();

            // Foreign keys
            $table->foreign('customer_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('cascade');

            // Unique constraint - a customer can't have the same branch twice
            $table->unique(['customer_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_company_branches');
    }
};
