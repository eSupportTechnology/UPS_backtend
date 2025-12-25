<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_line_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ticket_id');
            $table->enum('item_type', ['part', 'labor', 'other']);
            $table->uuid('inventory_id')->nullable();
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('inventory_id')->references('id')->on('shop_inventories')->onDelete('set null');
            $table->index('ticket_id');
            $table->index('item_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_line_items');
    }
};
