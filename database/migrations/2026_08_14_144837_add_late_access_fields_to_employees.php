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
        Schema::table('employees', function (Blueprint $table) {
            // ============================================================
            // 1. Check and add half_day_threshold if it doesn't exist
            // ============================================================
            if (!Schema::hasColumn('employees', 'half_day_threshold')) {
                $table->decimal('half_day_threshold', 4, 1)->default(4.0)->after('face_descriptor');
            }

            // ============================================================
            // 2. Check and add enable_half_day if it doesn't exist
            // ============================================================
            if (!Schema::hasColumn('employees', 'enable_half_day')) {
                $table->boolean('enable_half_day')->default(true)->after('half_day_threshold');
            }

            // ============================================================
            // 3. Check and add late_grace_period if it doesn't exist
            // ============================================================
            if (!Schema::hasColumn('employees', 'late_grace_period')) {
                $table->integer('late_grace_period')->default(15)->after('enable_half_day');
            }

            // ============================================================
            // 4. Check and add enable_late_marking if it doesn't exist
            // ============================================================
            if (!Schema::hasColumn('employees', 'enable_late_marking')) {
                $table->boolean('enable_late_marking')->default(true)->after('late_grace_period');
            }

            // ============================================================
            // 5. NEW: Check and add late_access_enabled if it doesn't exist
            // ============================================================
            if (!Schema::hasColumn('employees', 'late_access_enabled')) {
                $table->boolean('late_access_enabled')->default(false)->after('enable_late_marking');
            }

            // ============================================================
            // 6. NEW: Check and add late_allowed_minutes if it doesn't exist
            // ============================================================
            if (!Schema::hasColumn('employees', 'late_allowed_minutes')) {
                $table->integer('late_allowed_minutes')->default(60)->after('late_access_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $columns = [
                'half_day_threshold',
                'enable_half_day',
                'late_grace_period',
                'enable_late_marking',
                'late_access_enabled',
                'late_allowed_minutes'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};