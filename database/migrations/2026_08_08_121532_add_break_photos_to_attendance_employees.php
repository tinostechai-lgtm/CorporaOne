<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->string('break_in_photo')->nullable()->after('punch_photo');
            $table->string('break_out_photo')->nullable()->after('break_in_photo');
            $table->string('punch_out_photo')->nullable()->after('break_out_photo');
            $table->time('break_start')->nullable()->after('clock_in');
            $table->time('break_end')->nullable()->after('break_start');
        });
    }

    public function down()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->dropColumn(['break_in_photo', 'break_out_photo', 'punch_out_photo', 'break_start', 'break_end']);
        });
    }
};