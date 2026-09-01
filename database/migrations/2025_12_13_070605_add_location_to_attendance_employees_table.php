<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->string('location_mode')->default('office')->after('punch_state'); // remote or office
            $table->string('latitude')->nullable()->after('location_mode');
            $table->string('longitude')->nullable()->after('latitude');
            $table->text('address')->nullable()->after('longitude');
        });
    }

    public function down()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->dropColumn(['location_mode', 'latitude', 'longitude', 'address']);
        });
    }
};