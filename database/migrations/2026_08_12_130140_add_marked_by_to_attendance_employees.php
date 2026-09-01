<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_employees', 'marked_by')) {
                $table->string('marked_by')->default('manual')->after('total_rest');
            }
        });
    }

    public function down()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->dropColumn('marked_by');
        });
    }
};