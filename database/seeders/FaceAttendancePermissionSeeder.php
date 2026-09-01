<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class FaceAttendancePermissionSeeder extends Seeder
{
    public function run()
    {
        // Create Face ID Attendance permissions if they don't exist
        Permission::firstOrCreate(['name' => 'face_attendance']);
        Permission::firstOrCreate(['name' => 'face_enroll']);
    }
}
