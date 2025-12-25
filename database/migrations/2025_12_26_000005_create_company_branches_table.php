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
        Schema::create('company_branches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('company_id');
            $table->string('branch_name');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            // Foreign key to users table
            $table->foreign('company_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_branches');
    }
};
