<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
   protected function schedule(Schedule $schedule)
{
    // Update attendance status every 15 minutes
    $schedule->call(function () {
        $attendances = AttendanceEmployee::where('clock_in', '!=', '00:00:00')
                        ->where('date', date('Y-m-d'))
                        ->get();
        
        foreach ($attendances as $attendance) {
            $controller = new \App\Http\Controllers\AttendanceEmployeeController();
            $controller->updateAttendanceStatus($attendance->id);
        }
    })->everyFifteenMinutes();
}

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    
}
