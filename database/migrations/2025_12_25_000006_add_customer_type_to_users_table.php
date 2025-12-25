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
        Schema::table('users', function (Blueprint $table) {
            // Add customer_type for distinguishing personal and company customers
            // personal = Individual customer (person)
            // company = Company customer (has multiple branches)
            $table->enum('customer_type', ['personal', 'company'])->nullable()->after('role_as');

            // For company customers, store the company name separately if needed
            $table->string('company_name')->nullable()->after('customer_type');

            // For company customers, track the main branch/headquarters
            $table->uuid('company_headquarters_branch_id')->nullable()->after('company_name');

            // Add foreign key for company headquarters branch
            $table->foreign('company_headquarters_branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['company_headquarters_branch_id']);
            $table->dropColumn(['customer_type', 'company_name', 'company_headquarters_branch_id']);
        });
    }
};
