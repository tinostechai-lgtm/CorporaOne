<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceAndVerificationToAttendanceEmployees extends Migration
{
    public function up()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_employees', 'source')) {
                $table->string('source')->nullable()->after('marked_by')
                    ->comment('web, flutter, manual');
            }
            if (!Schema::hasColumn('attendance_employees', 'face_confidence')) {
                $table->float('face_confidence')->nullable()->after('source')
                    ->comment('Face verification confidence score (0-100)');
            }
            if (!Schema::hasColumn('attendance_employees', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('face_confidence')
                    ->comment('Whether face was successfully verified');
            }
        });
    }

    public function down()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->dropColumn(['source', 'face_confidence', 'is_verified']);
        });
    }
}