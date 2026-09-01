<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->string('punch_photo')->nullable()->after('total_rest');
        });
    }

    public function down()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->dropColumn('punch_photo');
        });
    }
};