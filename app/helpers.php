<?php

if (!function_exists('getAttendanceStatus')) {
    function getAttendanceStatus($attendance, $startTime = null, $endTime = null)
    {
        // No attendance record → not punched
        if (!$attendance) {
            return 'not_punched';
        }

        // Clock-in missing or zero → not punched
        if (empty($attendance->clock_in) || $attendance->clock_in == '00:00:00') {
            return 'not_punched';
        }

        // Check if currently on break (if you have break columns)
        if (!empty($attendance->tea_break_out) && $attendance->tea_break_out != '00:00:00' 
            && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00')) {
            return 'break';
        }

        // If clocked out
        if (!empty($attendance->clock_out) && $attendance->clock_out != '00:00:00') {
            // Early leave: clocked out before official end time
            if ($endTime && strtotime($attendance->clock_out) < strtotime($endTime)) {
                return 'early_leave';
            }
            return 'out';
        }

        // Still clocked in (clock_out is '00:00:00')
        // Late: clocked in after official start time
        if ($startTime && strtotime($attendance->clock_in) > strtotime($startTime)) {
            return 'late';
        }

        return 'in';
    }
}

// ============================================================
// LOCATION HELPER FUNCTIONS
// ============================================================

if (!function_exists('getLocationSettings')) {
    function getLocationSettings()
    {
        try {
            // Get settings directly from database
            $settings = DB::table('settings')
                ->where('created_by', 2)
                ->whereIn('name', [
                    'location_restriction', 
                    'office_latitude', 
                    'office_longitude', 
                    'office_radius', 
                    'office_address'
                ])
                ->pluck('value', 'name')
                ->toArray();
            
            $defaults = [
                'location_restriction' => 'off',
                'office_latitude' => null,
                'office_longitude' => null,
                'office_radius' => 300,
                'office_address' => null,
            ];
            
            return array_merge($defaults, $settings);
        } catch (\Exception $e) {
            \Log::error('Error fetching location settings: ' . $e->getMessage());
            return [
                'location_restriction' => 'off',
                'office_latitude' => null,
                'office_longitude' => null,
                'office_radius' => 300,
                'office_address' => null,
            ];
        }
    }
}

if (!function_exists('getOfficeLocation')) {
    function getOfficeLocation()
    {
        $settings = getLocationSettings();
        return [
            'latitude' => $settings['office_latitude'],
            'longitude' => $settings['office_longitude'],
            'radius' => $settings['office_radius'],
            'address' => $settings['office_address'],
            'restriction_enabled' => $settings['location_restriction'] === 'on',
        ];
    }
}

if (!function_exists('calculateDistance')) {
    function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        return $distance; // returns in meters
    }
}

if (!function_exists('isWithinOfficeRadius')) {
    function isWithinOfficeRadius($userLat, $userLng)
    {
        $office = getOfficeLocation();
        
        // If restriction is disabled, always return true
        if (!$office['restriction_enabled']) {
            return true;
        }
        
        // If office coordinates are not set, return false
        if (!$office['latitude'] || !$office['longitude']) {
            return false;
        }
        
        $distance = calculateDistance($userLat, $userLng, $office['latitude'], $office['longitude']);
        
        return $distance <= $office['radius'];
    }
}

if (!function_exists('validateLocation')) {
    function validateLocation($userLat, $userLng)
    {
        $result = [
            'valid' => false,
            'message' => '',
            'distance' => null,
            'office' => null,
        ];
        
        $office = getOfficeLocation();
        $result['office'] = $office;
        
        // Check if location restriction is enabled
        if (!$office['restriction_enabled']) {
            $result['valid'] = true;
            $result['message'] = 'Location restriction is disabled.';
            return $result;
        }
        
        // Check if office coordinates are set
        if (!$office['latitude'] || !$office['longitude']) {
            $result['message'] = 'Office location is not configured.';
            return $result;
        }
        
        // Calculate distance
        $distance = calculateDistance($userLat, $userLng, $office['latitude'], $office['longitude']);
        $result['distance'] = round($distance, 2);
        
        // Check if within radius
        if ($distance <= $office['radius']) {
            $result['valid'] = true;
            $result['message'] = 'You are within the office radius (' . $office['radius'] . ' meters).';
        } else {
            $result['message'] = 'You are outside the office radius. Distance: ' . round($distance, 2) . ' meters (Limit: ' . $office['radius'] . ' meters)';
        }
        
        return $result;
    }
}

// ============================================================
// ATTENDANCE WITH LOCATION CHECK
// ============================================================

if (!function_exists('validateAttendanceLocation')) {
    function validateAttendanceLocation($userLat, $userLng, $attendance = null, $startTime = null, $endTime = null)
    {
        // First validate location
        $locationResult = validateLocation($userLat, $userLng);
        
        // If location is not valid, return location error
        if (!$locationResult['valid']) {
            return [
                'success' => false,
                'message' => $locationResult['message'],
                'location' => $locationResult,
                'attendance_status' => null,
            ];
        }
        
        // Get attendance status
        $status = $attendance ? getAttendanceStatus($attendance, $startTime, $endTime) : 'not_punched';
        
        // Build status message
        $statusMessages = [
            'not_punched' => 'Please clock in to start your shift.',
            'break' => 'You are currently on a break.',
            'out' => 'You have clocked out.',
            'early_leave' => 'You have clocked out early.',
            'late' => 'You have clocked in late.',
            'in' => 'You are clocked in and active.',
        ];
        
        return [
            'success' => true,
            'message' => $statusMessages[$status] ?? 'Attendance status retrieved.',
            'location' => $locationResult,
            'attendance_status' => $status,
            'within_radius' => $locationResult['valid'],
        ];
    }
}

// ============================================================
// DASHBOARD / VIEW HELPERS
// ============================================================

if (!function_exists('getAttendanceBadgeClass')) {
    function getAttendanceBadgeClass($status)
    {
        $classes = [
            'not_punched' => 'badge-secondary',
            'break' => 'badge-warning',
            'out' => 'badge-info',
            'early_leave' => 'badge-danger',
            'late' => 'badge-warning',
            'in' => 'badge-success',
        ];
        return $classes[$status] ?? 'badge-secondary';
    }
}

if (!function_exists('getAttendanceStatusLabel')) {
    function getAttendanceStatusLabel($status)
    {
        $labels = [
            'not_punched' => 'Not Punched',
            'break' => 'On Break',
            'out' => 'Clocked Out',
            'early_leave' => 'Early Leave',
            'late' => 'Late',
            'in' => 'Active',
        ];
        return $labels[$status] ?? 'Unknown';
    }
}