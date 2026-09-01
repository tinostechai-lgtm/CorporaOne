<?php

if (!function_exists('getAttendanceStatus')) {
    function getAttendanceStatus($attendance) {
        // 1. Not clocked in yet
        if (empty($attendance->clock_in) || $attendance->clock_in == '00:00:00') {
            return 'not_punched';
        }

        // 2. On break – tea_break_out is set, tea_break_in not yet
        if (!empty($attendance->tea_break_out) && $attendance->tea_break_out != '00:00:00' 
            && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00')) {
            return 'break';
        }

        // 3. Clocked out
        if (!empty($attendance->clock_out) && $attendance->clock_out != '00:00:00') {
            return 'out';
        }

        // 4. Otherwise, still clocked in
        return 'in';
    }
}