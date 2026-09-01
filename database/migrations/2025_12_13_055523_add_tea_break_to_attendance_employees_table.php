<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            // Tea Break Out time (when employee leaves for break)
            $table->time('tea_break_out')->default('00:00:00')->after('clock_in');
            
            // Tea Break In time (when employee returns from break)
            $table->time('tea_break_in')->default('00:00:00')->after('tea_break_out');
            
            // Optional: Track current punch state
            $table->enum('punch_state', ['clock_in', 'tea_break_out', 'tea_break_in', 'clock_out'])
                  ->default('clock_in')
                  ->after('total_rest');
        });
    }

    public function down()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->dropColumn(['tea_break_out', 'tea_break_in', 'punch_state']);
        });
    }
};