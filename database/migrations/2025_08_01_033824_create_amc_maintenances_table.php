<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('amc_maintenances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('amc_contract_id');
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'completed', 'missed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amc_maintenances');
    }
};
