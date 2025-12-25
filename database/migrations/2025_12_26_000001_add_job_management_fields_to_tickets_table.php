<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Job Type & Numbering
            $table->enum('job_type', ['outside', 'inside'])->default('outside')->after('id');
            $table->string('job_number')->nullable()->unique()->after('job_type');
            $table->uuid('parent_ticket_id')->nullable()->after('job_number');

            // UPS Details
            $table->string('ups_serial_number')->nullable()->after('customer_id');
            $table->string('ups_model')->nullable()->after('ups_serial_number');
            $table->string('ups_brand')->nullable()->after('ups_model');

            // Inside Job Inspection
            $table->text('inspection_notes')->nullable()->after('description');
            $table->timestamp('inspected_at')->nullable()->after('accepted_at');
            $table->unsignedBigInteger('inspected_by')->nullable()->after('inspected_at');

            // Quote & Approval
            $table->json('quote_data')->nullable()->after('inspection_notes');
            $table->decimal('quote_total', 10, 2)->nullable()->after('quote_data');
            $table->timestamp('quoted_at')->nullable()->after('quote_total');
            $table->unsignedBigInteger('quoted_by')->nullable()->after('quoted_at');

            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable()->after('quoted_by');
            $table->timestamp('approval_decision_at')->nullable()->after('approval_status');
            $table->text('approval_notes')->nullable()->after('approval_decision_at');

            // Workshop Repair
            $table->timestamp('in_repair_at')->nullable()->after('approval_notes');
            $table->text('repair_notes')->nullable()->after('in_repair_at');
            $table->json('actual_parts_used')->nullable()->after('repair_notes');

            // Indexes
            $table->index('job_type');
            $table->index('job_number');
            $table->index('parent_ticket_id');
            $table->index('approval_status');

            // Foreign Keys
            $table->foreign('parent_ticket_id')->references('id')->on('tickets')->onDelete('set null');
            $table->foreign('inspected_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('quoted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['parent_ticket_id']);
            $table->dropForeign(['inspected_by']);
            $table->dropForeign(['quoted_by']);

            $table->dropIndex(['job_type']);
            $table->dropIndex(['job_number']);
            $table->dropIndex(['parent_ticket_id']);
            $table->dropIndex(['approval_status']);

            $table->dropColumn([
                'job_type',
                'job_number',
                'parent_ticket_id',
                'ups_serial_number',
                'ups_model',
                'ups_brand',
                'inspection_notes',
                'inspected_at',
                'inspected_by',
                'quote_data',
                'quote_total',
                'quoted_at',
                'quoted_by',
                'approval_status',
                'approval_decision_at',
                'approval_notes',
                'in_repair_at',
                'repair_notes',
                'actual_parts_used',
            ]);
        });
    }
};
