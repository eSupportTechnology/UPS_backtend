<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('role_as')->default(5)->comment('1=SuperAdmin, 2=Admin, 3=Operator, 4=Technician, 5=Customer');
            $table->boolean('is_active')->default(true);
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role_as', 'is_active', 'phone', 'address']);
        });
    }
};
