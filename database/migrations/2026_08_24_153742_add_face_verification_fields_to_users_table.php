<?php
// database/migrations/2024_01_xx_xxxxxx_add_face_verification_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'verifinow_face_id')) {
                $table->string('verifinow_face_id')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'face_registered')) {
                $table->boolean('face_registered')->default(false)->after('verifinow_face_id');
            }
            if (!Schema::hasColumn('users', 'face_registered_at')) {
                $table->timestamp('face_registered_at')->nullable()->after('face_registered');
            }
            if (!Schema::hasColumn('users', 'face_photo')) {
                $table->string('face_photo')->nullable()->after('face_registered_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'verifinow_face_id',
                'face_registered',
                'face_registered_at',
                'face_photo'
            ]);
        });
    }
};