<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('customer_id');
            $table->string('title');
            $table->text('description');
            $table->json('photo_paths')->nullable();
            $table->string('status')->default('open');
            $table->string('priority')->default('medium');
            $table->uuid('assigned_to')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
