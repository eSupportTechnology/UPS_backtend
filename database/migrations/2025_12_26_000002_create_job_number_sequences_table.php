<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_number_sequences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('year');
            $table->integer('sequence')->default(0);
            $table->string('prefix')->default('INJ'); // Inside Job
            $table->timestamps();

            $table->unique(['year', 'prefix']);
            $table->index('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_number_sequences');
    }
};
