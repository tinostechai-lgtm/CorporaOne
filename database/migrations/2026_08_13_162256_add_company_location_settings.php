<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            // Add company location settings
            if (!Schema::hasColumn('settings', 'office_latitude')) {
                $table->string('office_latitude')->nullable()->after('value');
                $table->string('office_longitude')->nullable()->after('office_latitude');
                $table->string('office_radius')->default('300')->after('office_longitude');
                $table->boolean('location_restriction')->default(false)->after('office_radius');
                $table->string('office_address')->nullable()->after('location_restriction');
            }
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'office_latitude',
                'office_longitude',
                'office_radius',
                'location_restriction',
                'office_address'
            ]);
        });
    }
};