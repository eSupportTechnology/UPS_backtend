<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Technician type: inside, outside, or null for non-technicians
            $table->enum('technician_type', ['inside', 'outside'])->nullable()->after('role_as');

            // Employment type for inside technicians: part_time or full_time
            $table->enum('employment_type', ['part_time', 'full_time'])->nullable()->after('technician_type');

            // Profile image path
            $table->string('profile_image')->nullable()->after('employment_type');

            // Specialization/expertise areas
            $table->string('specialization')->nullable()->after('profile_image');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['technician_type', 'employment_type', 'profile_image', 'specialization']);
        });
    }
};
