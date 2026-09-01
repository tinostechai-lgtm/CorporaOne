<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('half_day_threshold', 5, 2)->default(4.0)->after('face_descriptor');
            // 4.0 = 4 hours (50% of 8-hour workday)
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('half_day_threshold');
        });
    }
};