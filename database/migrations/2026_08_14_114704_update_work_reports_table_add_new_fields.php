<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_reports', function (Blueprint $table) {
            // =============================================
            // 1. CHECK AND DROP EXISTING FOREIGN KEYS
            // =============================================
            
            // Get all foreign keys for the table
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'work_reports' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            foreach ($foreignKeys as $fk) {
                try {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                } catch (\Exception $e) {
                    // If dropping fails, continue
                }
            }
            
            // =============================================
            // 2. RENAME/MODIFY EXISTING COLUMNS
            // =============================================
            
            // Rename report_date to date
            if (Schema::hasColumn('work_reports', 'report_date') && !Schema::hasColumn('work_reports', 'date')) {
                $table->renameColumn('report_date', 'date');
            }
            
            // Rename tasks_completed to work_description
            if (Schema::hasColumn('work_reports', 'tasks_completed') && !Schema::hasColumn('work_reports', 'work_description')) {
                $table->renameColumn('tasks_completed', 'work_description');
            }
            
            // Rename plan_for_tomorrow to tomorrow_plan
            if (Schema::hasColumn('work_reports', 'plan_for_tomorrow') && !Schema::hasColumn('work_reports', 'tomorrow_plan')) {
                $table->renameColumn('plan_for_tomorrow', 'tomorrow_plan');
            }
            
            // Rename status to review_status
            if (Schema::hasColumn('work_reports', 'status') && !Schema::hasColumn('work_reports', 'review_status')) {
                $table->renameColumn('status', 'review_status');
            }
            
            // Rename hrm_comment to review_notes
            if (Schema::hasColumn('work_reports', 'hrm_comment') && !Schema::hasColumn('work_reports', 'review_notes')) {
                $table->renameColumn('hrm_comment', 'review_notes');
            }
            
            // =============================================
            // 3. ADD NEW COLUMNS
            // =============================================
            
            // Add attendance_id
            if (!Schema::hasColumn('work_reports', 'attendance_id')) {
                $table->unsignedBigInteger('attendance_id')->nullable()->after('employee_id');
            }
            
            // Add clock_in
            if (!Schema::hasColumn('work_reports', 'clock_in')) {
                $table->time('clock_in')->nullable()->after('date');
            }
            
            // Add clock_out
            if (!Schema::hasColumn('work_reports', 'clock_out')) {
                $table->time('clock_out')->nullable()->after('clock_in');
            }
            
            // Add quick_tasks
            if (!Schema::hasColumn('work_reports', 'quick_tasks')) {
                $table->string('quick_tasks')->nullable()->after('work_description');
            }
            
            // Add achievements
            if (!Schema::hasColumn('work_reports', 'achievements')) {
                $table->text('achievements')->nullable()->after('quick_tasks');
            }
            
            // Add hourly breakdown fields
            if (!Schema::hasColumn('work_reports', 'hours_project')) {
                $table->decimal('hours_project', 4, 1)->default(0)->after('tomorrow_plan');
            }
            
            if (!Schema::hasColumn('work_reports', 'hours_meeting')) {
                $table->decimal('hours_meeting', 4, 1)->default(0)->after('hours_project');
            }
            
            if (!Schema::hasColumn('work_reports', 'hours_admin')) {
                $table->decimal('hours_admin', 4, 1)->default(0)->after('hours_meeting');
            }
            
            // Add created_by
            if (!Schema::hasColumn('work_reports', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('reviewed_at');
            }
        });

        // =============================================
        // 4. HANDLE ORPHANED RECORDS (if any)
        // =============================================
        
        // Check if there are any records with invalid employee_id
        $invalidCount = DB::table('work_reports')
            ->whereNotExists(function ($query) {
                $query->select('id')
                    ->from('employees')
                    ->whereColumn('employees.id', 'work_reports.employee_id');
            })
            ->count();
        
        if ($invalidCount > 0) {
            // Get the first valid employee ID
            $validEmployee = DB::table('employees')->first();
            
            if ($validEmployee) {
                // Update invalid records to use the first valid employee
                DB::table('work_reports')
                    ->whereNotExists(function ($query) {
                        $query->select('id')
                            ->from('employees')
                            ->whereColumn('employees.id', 'work_reports.employee_id');
                    })
                    ->update(['employee_id' => $validEmployee->id]);
            } else {
                // If no employees exist, delete orphaned records
                DB::table('work_reports')
                    ->whereNotExists(function ($query) {
                        $query->select('id')
                            ->from('employees')
                            ->whereColumn('employees.id', 'work_reports.employee_id');
                    })
                    ->delete();
            }
        }

        // =============================================
        // 5. ADD FOREIGN KEYS
        // =============================================
        
        Schema::table('work_reports', function (Blueprint $table) {
            // Add foreign key to employees
            try {
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            } catch (\Exception $e) {
                // Foreign key might already exist
            }
            
            // Add foreign key to attendance_employees
            if (Schema::hasColumn('work_reports', 'attendance_id')) {
                try {
                    $table->foreign('attendance_id')->references('id')->on('attendance_employees')->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key might already exist
                }
            }
            
            // Add foreign key to users (reviewer)
            if (Schema::hasColumn('work_reports', 'reviewed_by')) {
                try {
                    $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key might already exist
                }
            }
            
            // =============================================
            // 6. ADD INDEXES
            // =============================================
            
            try {
                $table->index(['employee_id', 'date']);
            } catch (\Exception $e) {
                // Index might already exist
            }
            
            try {
                $table->index('review_status');
            } catch (\Exception $e) {
                // Index might already exist
            }
            
            try {
                $table->index('created_by');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_reports', function (Blueprint $table) {
            // Drop all foreign keys
            try {
                $table->dropForeign(['employee_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist
            }
            
            try {
                $table->dropForeign(['attendance_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist
            }
            
            try {
                $table->dropForeign(['reviewed_by']);
            } catch (\Exception $e) {
                // Foreign key might not exist
            }
            
            // Drop indexes
            try {
                $table->dropIndex(['employee_id', 'date']);
            } catch (\Exception $e) {
                // Index might not exist
            }
            
            try {
                $table->dropIndex(['review_status']);
            } catch (\Exception $e) {
                // Index might not exist
            }
            
            try {
                $table->dropIndex(['created_by']);
            } catch (\Exception $e) {
                // Index might not exist
            }
            
            // Drop new columns
            $columnsToDrop = [];
            
            if (Schema::hasColumn('work_reports', 'attendance_id')) {
                $columnsToDrop[] = 'attendance_id';
            }
            
            if (Schema::hasColumn('work_reports', 'clock_in')) {
                $columnsToDrop[] = 'clock_in';
            }
            
            if (Schema::hasColumn('work_reports', 'clock_out')) {
                $columnsToDrop[] = 'clock_out';
            }
            
            if (Schema::hasColumn('work_reports', 'quick_tasks')) {
                $columnsToDrop[] = 'quick_tasks';
            }
            
            if (Schema::hasColumn('work_reports', 'achievements')) {
                $columnsToDrop[] = 'achievements';
            }
            
            if (Schema::hasColumn('work_reports', 'hours_project')) {
                $columnsToDrop[] = 'hours_project';
            }
            
            if (Schema::hasColumn('work_reports', 'hours_meeting')) {
                $columnsToDrop[] = 'hours_meeting';
            }
            
            if (Schema::hasColumn('work_reports', 'hours_admin')) {
                $columnsToDrop[] = 'hours_admin';
            }
            
            if (Schema::hasColumn('work_reports', 'created_by')) {
                $columnsToDrop[] = 'created_by';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
            
            // Rename back to original names
            if (Schema::hasColumn('work_reports', 'date') && !Schema::hasColumn('work_reports', 'report_date')) {
                $table->renameColumn('date', 'report_date');
            }
            
            if (Schema::hasColumn('work_reports', 'work_description') && !Schema::hasColumn('work_reports', 'tasks_completed')) {
                $table->renameColumn('work_description', 'tasks_completed');
            }
            
            if (Schema::hasColumn('work_reports', 'tomorrow_plan') && !Schema::hasColumn('work_reports', 'plan_for_tomorrow')) {
                $table->renameColumn('tomorrow_plan', 'plan_for_tomorrow');
            }
            
            if (Schema::hasColumn('work_reports', 'review_status') && !Schema::hasColumn('work_reports', 'status')) {
                $table->renameColumn('review_status', 'status');
            }
            
            if (Schema::hasColumn('work_reports', 'review_notes') && !Schema::hasColumn('work_reports', 'hrm_comment')) {
                $table->renameColumn('review_notes', 'hrm_comment');
            }
        });
    }
};