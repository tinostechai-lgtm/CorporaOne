<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceEmployee;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    /**
     * Get office location settings (API)
     */
    public function getOfficeLocation(Request $request)
    {
        try {
            $officeLocation = Utility::getOfficeLocation();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'latitude' => $officeLocation['latitude'] ?? null,
                    'longitude' => $officeLocation['longitude'] ?? null,
                    'radius' => $officeLocation['radius'] ?? 300,
                    'restriction_enabled' => $officeLocation['restriction_enabled'] ?? false,
                    'address' => $officeLocation['address'] ?? null,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Get office location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get office location: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate user location against office location (API)
     */
    public function validateLocation(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'mode' => 'nullable|in:office,remote',
                'employee_id' => 'nullable|exists:employees,id',
            ]);

            $mode = $request->input('mode', 'office');
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');

            // If remote mode, always valid
            if ($mode === 'remote') {
                return response()->json([
                    'success' => true,
                    'valid' => true,
                    'message' => 'Remote attendance allowed',
                    'mode' => 'remote',
                ]);
            }

            $officeLocation = Utility::getOfficeLocation();
            
            // Check if restriction is enabled
            if (!$officeLocation['restriction_enabled']) {
                return response()->json([
                    'success' => true,
                    'valid' => true,
                    'message' => 'Location restriction is disabled',
                    'mode' => 'office',
                ]);
            }

            // Check if office location is configured
            if (!$officeLocation['latitude'] || !$officeLocation['longitude']) {
                return response()->json([
                    'success' => false,
                    'valid' => false,
                    'message' => 'Office location not configured. Please contact administrator.',
                    'mode' => 'office',
                ]);
            }

            // Calculate distance
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
                'mode' => 'office',
                'data' => [
                    'distance' => round($distance, 2),
                    'radius' => $radius,
                    'is_within_radius' => $isWithinRadius,
                    'user_location' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ],
                    'office_location' => [
                        'latitude' => $officeLocation['latitude'],
                        'longitude' => $officeLocation['longitude'],
                    ],
                    'message' => $isWithinRadius 
                        ? "You are within {$radius} meters of the office." 
                        : "You are " . round($distance, 2) . " meters away. Allowed radius is {$radius} meters.",
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Validate location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Failed to validate location: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the location of the user (IP-based or GPS)
     */
    public function getUserLocation(Request $request)
    {
        try {
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $ip = $request->ip();

            // If GPS coordinates provided, use them
            if ($latitude && $longitude) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'latitude' => (float) $latitude,
                        'longitude' => (float) $longitude,
                        'source' => 'gps',
                        'accuracy' => $request->input('accuracy', null),
                        'timestamp' => now()->toISOString(),
                    ]
                ]);
            }

            // Fallback: Get location from IP
            $locationData = $this->getLocationFromIP($ip);

            return response()->json([
                'success' => true,
                'data' => [
                    'latitude' => $locationData['latitude'] ?? null,
                    'longitude' => $locationData['longitude'] ?? null,
                    'city' => $locationData['city'] ?? null,
                    'country' => $locationData['country'] ?? null,
                    'source' => 'ip',
                    'ip' => $ip,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get user location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get location: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance location history for an employee
     */
    public function getLocationHistory(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'nullable|exists:employees,id',
                'date' => 'nullable|date',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            $employeeId = $request->input('employee_id');
            $date = $request->input('date', date('Y-m-d'));
            $limit = $request->input('limit', 50);

            if (!$employeeId) {
                $user = Auth::user();
                $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();
                if ($employee) {
                    $employeeId = $employee->id;
                }
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

            $data = $attendanceRecords->map(function($record) {
                return [
                    'id' => $record->id,
                    'date' => $record->date,
                    'clock_in' => $record->clock_in,
                    'clock_out' => $record->clock_out,
                    'latitude' => $record->latitude,
                    'longitude' => $record->longitude,
                    'address' => $record->address ?? null,
                    'location_mode' => $record->location_mode ?? 'office',
                    'status' => $record->status,
                    'punch_state' => $record->punch_state ?? null,
                    'created_at' => $record->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employeeId,
                    'date' => $date,
                    'count' => $data->count(),
                    'records' => $data,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get location history error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get location history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save location for an attendance record
     */
    public function saveLocation(Request $request)
    {
        try {
            $request->validate([
                'attendance_id' => 'required|exists:attendance_employees,id',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'address' => 'nullable|string|max:500',
                'mode' => 'nullable|in:office,remote',
            ]);

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
            Log::error('Save location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save location: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get nearby employees (within a radius)
     */
    public function getNearbyEmployees(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|numeric|min:1|max:10000',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $radius = $request->input('radius', 1000);
            $limit = $request->input('limit', 20);

            $today = date('Y-m-d');
            $attendanceRecords = AttendanceEmployee::whereDate('date', $today)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->with('employee')
                ->get();

            $nearbyEmployees = [];
            foreach ($attendanceRecords as $record) {
                $distance = $this->calculateDistance(
                    $latitude,
                    $longitude,
                    (float) $record->latitude,
                    (float) $record->longitude
                );

                if ($distance <= $radius) {
                    $nearbyEmployees[] = [
                        'employee_id' => $record->employee_id,
                        'employee_name' => $record->employee ? $record->employee->name : 'Unknown',
                        'clock_in' => $record->clock_in,
                        'clock_out' => $record->clock_out,
                        'status' => $record->status,
                        'latitude' => $record->latitude,
                        'longitude' => $record->longitude,
                        'distance' => round($distance, 2),
                        'distance_unit' => 'meters',
                    ];
                }
            }

            usort($nearbyEmployees, function($a, $b) {
                return $a['distance'] <=> $b['distance'];
            });

            $nearbyEmployees = array_slice($nearbyEmployees, 0, $limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'user_location' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ],
                    'radius' => $radius,
                    'total_found' => count($nearbyEmployees),
                    'employees' => $nearbyEmployees,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get nearby employees error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get nearby employees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
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

    /**
     * Get location from IP address (fallback)
     */
    private function getLocationFromIP($ip)
    {
        try {
            $url = "http://ip-api.com/json/{$ip}?fields=status,message,country,city,lat,lon";
            $response = file_get_contents($url);
            $data = json_decode($response, true);

            if ($data && $data['status'] === 'success') {
                return [
                    'latitude' => $data['lat'] ?? null,
                    'longitude' => $data['lon'] ?? null,
                    'city' => $data['city'] ?? null,
                    'country' => $data['country'] ?? null,
                ];
            }

            return [
                'latitude' => null,
                'longitude' => null,
                'city' => null,
                'country' => null,
            ];
        } catch (\Exception $e) {
            Log::error('IP location error: ' . $e->getMessage());
            return [
                'latitude' => null,
                'longitude' => null,
                'city' => null,
                'country' => null,
            ];
        }
    }
}