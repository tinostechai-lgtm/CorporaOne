<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('holidays', function (Blueprint $table) {
            // ============================================================
            // ADD NEW COLUMNS
            // ============================================================
            
            // Type of holiday/leave
            if (!Schema::hasColumn('holidays', 'type')) {
                $table->string('type')->default('holiday')->after('id');
            }
            
            // End date for date range
            if (!Schema::hasColumn('holidays', 'end_date')) {
                $table->date('end_date')->nullable()->after('date');
            }
            
            // Holiday type (public, company, etc.)
            if (!Schema::hasColumn('holidays', 'holiday_type')) {
                $table->string('holiday_type')->default('public')->after('end_date');
            }
            
            // Week off days (JSON array)
            if (!Schema::hasColumn('holidays', 'week_off_days')) {
                $table->json('week_off_days')->nullable()->after('holiday_type');
            }
            
            // Week off applicable to
            if (!Schema::hasColumn('holidays', 'week_off_applicable')) {
                $table->string('week_off_applicable')->default('all')->after('week_off_days');
            }
            
            // Leave type ID
            if (!Schema::hasColumn('holidays', 'leave_type_id')) {
                $table->unsignedBigInteger('leave_type_id')->nullable()->after('week_off_applicable');
            }
            
            // Leave duration
            if (!Schema::hasColumn('holidays', 'leave_duration')) {
                $table->string('leave_duration')->default('full_day')->after('leave_type_id');
            }
            
            // Leave date from
            if (!Schema::hasColumn('holidays', 'leave_date_from')) {
                $table->date('leave_date_from')->nullable()->after('leave_duration');
            }
            
            // Leave date to
            if (!Schema::hasColumn('holidays', 'leave_date_to')) {
                $table->date('leave_date_to')->nullable()->after('leave_date_from');
            }
            
            // Leave reason
            if (!Schema::hasColumn('holidays', 'leave_reason')) {
                $table->text('leave_reason')->nullable()->after('leave_date_to');
            }
            
            // Is paid
            if (!Schema::hasColumn('holidays', 'is_paid')) {
                $table->boolean('is_paid')->default(true)->after('leave_reason');
            }
            
            // Applicable to (all or specific)
            if (!Schema::hasColumn('holidays', 'applicable_to')) {
                $table->string('applicable_to')->default('all')->after('is_paid');
            }
            
            // Departments (JSON array)
            if (!Schema::hasColumn('holidays', 'departments')) {
                $table->json('departments')->nullable()->after('applicable_to');
            }
            
            // Description
            if (!Schema::hasColumn('holidays', 'description')) {
                $table->text('description')->nullable()->after('departments');
            }
            
            // Synchronize type
            if (!Schema::hasColumn('holidays', 'synchronize_type')) {
                $table->boolean('synchronize_type')->default(false)->after('description');
            }
            
            // ============================================================
            // ADD FOREIGN KEY
            // ============================================================
            if (!Schema::hasColumn('holidays', 'leave_type_id')) {
                $table->foreign('leave_type_id')
                      ->references('id')
                      ->on('leave_types')
                      ->onDelete('set null');
            }
            
            // ============================================================
            // ADD INDEXES
            // ============================================================
            $table->index(['type', 'date']);
            $table->index('is_paid');
            $table->index('created_by');
        });
    }

    public function down()
    {
        Schema::table('holidays', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['leave_type_id']);
            
            // Drop columns
            $columns = [
                'type',
                'end_date',
                'holiday_type',
                'week_off_days',
                'week_off_applicable',
                'leave_type_id',
                'leave_duration',
                'leave_date_from',
                'leave_date_to',
                'leave_reason',
                'is_paid',
                'applicable_to',
                'departments',
                'description',
                'synchronize_type'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('holidays', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            // Drop indexes
            $table->dropIndex(['type', 'date']);
            $table->dropIndex(['is_paid']);
            $table->dropIndex(['created_by']);
        });
    }
};