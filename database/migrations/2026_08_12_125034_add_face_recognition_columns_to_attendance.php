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

        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'face_descriptor')) {
                $table->text('face_descriptor')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('employees', 'face_enrolled_at')) {
                $table->timestamp('face_enrolled_at')->nullable()->after('face_descriptor');
            }
        });
    }

    public function down()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->dropColumn('marked_by');
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('face_descriptor');
            $table->dropColumn('face_enrolled_at');
        });
    }
};