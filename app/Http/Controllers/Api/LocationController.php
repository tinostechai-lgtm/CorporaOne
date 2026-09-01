<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\AttendanceEmployee;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Get office location settings
     */
    public function getOfficeLocation(Request $request)
    {
        try {
            $officeLocation = Utility::getOfficeLocation();
            
            return response()->json([
                'success' => true,
                'message' => 'Office location retrieved successfully',
                'data' => [
                    'latitude' => $officeLocation['latitude'] ?? null,
                    'longitude' => $officeLocation['longitude'] ?? null,
                    'radius' => $officeLocation['radius'] ?? 300,
                    'restriction_enabled' => $officeLocation['restriction_enabled'] ?? false,
                    'address' => $officeLocation['address'] ?? null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get office location: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate user location
     */
    public function validateLocation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'mode' => 'nullable|in:office,remote',
                'employee_id' => 'nullable|exists:employees,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $mode = $request->input('mode', 'office');
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');

            // Remote mode always valid
            if ($mode === 'remote') {
                return response()->json([
                    'success' => true,
                    'valid' => true,
                    'message' => 'Remote attendance allowed',
                    'mode' => 'remote',
                ]);
            }

            $officeLocation = Utility::getOfficeLocation();
            
            if (!$officeLocation['restriction_enabled']) {
                return response()->json([
                    'success' => true,
                    'valid' => true,
                    'message' => 'Location restriction is disabled',
                ]);
            }

            if (!$officeLocation['latitude'] || !$officeLocation['longitude']) {
                return response()->json([
                    'success' => false,
                    'valid' => false,
                    'message' => 'Office location not configured',
                ]);
            }

            $distance = $this->calculateDistance(
                (float) $latitude,
                (float) $longitude,
                (float) $officeLocation['latitude'],
                (float) $officeLocation['longitude']
            );

            $radius = (float) ($officeLocation['radius'] ?? 300);
            $isWithinRadius = $distance <= $radius;

            return response()->json([
                'success' => true,
                'valid' => $isWithinRadius,
                'data' => [
                    'distance' => round($distance, 2),
                    'radius' => $radius,
                    'is_within_radius' => $isWithinRadius,
                    'message' => $isWithinRadius 
                        ? "You are within {$radius} meters of the office" 
                        : "You are " . round($distance, 2) . " meters away",
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate location: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save location for attendance
     */
    public function saveLocation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'attendance_id' => 'required|exists:attendance_employees,id',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'address' => 'nullable|string|max:500',
                'mode' => 'nullable|in:office,remote',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $attendance = AttendanceEmployee::find($request->attendance_id);
            
            // Check permission
            if ($attendance->employee_id != Auth::user()->employee->id && !Auth::user()->can('manage attendance')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied'
                ], 403);
            }

            $attendance->latitude = $request->latitude;
            $attendance->longitude = $request->longitude;
            $attendance->address = $request->address;
            $attendance->location_mode = $request->mode ?? 'office';
            $attendance->save();

            return response()->json([
                'success' => true,
                'message' => 'Location saved successfully',
                'data' => [
                    'attendance_id' => $attendance->id,
                    'latitude' => $attendance->latitude,
                    'longitude' => $attendance->longitude,
                    'address' => $attendance->address,
                    'mode' => $attendance->location_mode,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save location: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get location history
     */
    public function getLocationHistory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'nullable|exists:employees,id',
                'date' => 'nullable|date',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $employeeId = $request->input('employee_id');
            $date = $request->input('date', date('Y-m-d'));
            $limit = $request->input('limit', 50);

            if (!$employeeId) {
                $user = Auth::user();
                $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();
                $employeeId = $employee->id ?? null;
            }

            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            $attendanceRecords = AttendanceEmployee::where('employee_id', $employeeId)
                ->whereDate('date', $date)
                ->where(function($query) {
                    $query->whereNotNull('latitude')
                        ->orWhereNotNull('longitude');
                })
                ->orderBy('clock_in', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employeeId,
                    'date' => $date,
                    'count' => $attendanceRecords->count(),
                    'records' => $attendanceRecords->map(function($record) {
                        return [
                            'id' => $record->id,
                            'date' => $record->date,
                            'clock_in' => $record->clock_in,
                            'clock_out' => $record->clock_out,
                            'latitude' => $record->latitude,
                            'longitude' => $record->longitude,
                            'address' => $record->address,
                            'status' => $record->status,
                        ];
                    }),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get location history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate distance between two coordinates
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}