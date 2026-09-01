<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ApiFaceAttendanceController extends Controller
{
    /**
     * ✅ FIXED: Get authenticated user from session OR token
     */
    protected function getAuthenticatedUser(Request $request)
    {
        // Try session auth first (for web routes)
        $user = auth()->user();
        
        // If no session, try token auth (for API routes)
        if (!$user) {
            $user = auth('sanctum')->user();
        }
        
        // If still no user, try the request user
        if (!$user) {
            $user = $request->user();
        }
        
        return $user;
    }

    /**
     * ✅ FIXED: Get creator ID from user
     */
    protected function getCreatorId($user)
    {
        if (!$user) {
            return null;
        }
        
        // For company users, creatorId is their own ID
        if ($user->type == 'company' || $user->type == 'super admin') {
            return $user->id;
        }
        
        // For employees, creatorId is the company ID
        return $user->created_by ?? $user->id;
    }

    /**
     * Debug method - Check if controller is reachable
     */
    public function debug(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            Log::info('=== DEBUG METHOD CALLED ===');
            return response()->json([
                'success' => true,
                'message' => 'ApiFaceAttendanceController is reachable!',
                'authenticated' => $user ? true : false,
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->type,
                    'creator_id' => $this->getCreatorId($user),
                ] : null,
                'employee_id' => $user && $user->employee ? $user->employee->id : null,
                'timestamp' => now()->toDateTimeString(),
                'auth_method' => auth()->check() ? 'session' : (auth('sanctum')->check() ? 'token' : 'none'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Debug error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Status - Simple status check for Blade/API
     */
    public function getStatus(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                Log::warning('getStatus: User not authenticated');
                return $this->errorResponse('User not authenticated', 401);
            }

            Log::info('getStatus: User authenticated', ['user_id' => $user->id, 'type' => $user->type]);

            $employee = Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                $creatorId = $this->getCreatorId($user);
                $employee = Employee::where('created_by', $creatorId)
                    ->where('user_id', $user->id)
                    ->first();
            }
            
            $data = [
                'authenticated' => true,
                'user_id' => $user->id,
                'user_type' => $user->type,
                'user_email' => $user->email,
                'has_employee_record' => $employee ? true : false,
                'employee_id' => $employee ? $employee->id : null,
                'employee_name' => $employee ? $employee->name : null,
                'employee_code' => $employee ? $employee->employee_id : null,
                'is_face_enrolled' => $employee && !empty($employee->face_descriptor),
                'face_enrolled_at' => $employee ? $employee->face_enrolled_at : null,
                'has_face_photo' => $employee && !empty($employee->face_photo),
                'face_photo_url' => $employee && !empty($employee->face_photo) 
                    ? asset('uploads/face/' . $employee->face_photo) 
                    : null,
                'half_day_threshold' => $employee ? ($employee->half_day_threshold ?? 4.0) : 4.0,
                'enable_half_day' => $employee ? ($employee->enable_half_day ?? true) : true,
                'late_access_enabled' => $employee ? ($employee->late_access_enabled ?? false) : false,
                'late_allowed_minutes' => $employee ? ($employee->late_allowed_minutes ?? 60) : 60,
                'timestamp' => now()->toDateTimeString(),
                'api_version' => '1.0.0'
            ];

            if ($employee) {
                $today = date('Y-m-d');
                $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                    ->whereDate('date', $today)
                    ->first();

                if ($attendance) {
                    $data['attendance_status'] = [
                        'is_clocked_in' => $attendance->clock_in != '00:00:00',
                        'is_clocked_out' => $attendance->clock_out != '00:00:00',
                        'is_on_break' => !empty($attendance->tea_break_out) 
                            && $attendance->tea_break_out != '00:00:00'
                            && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00'),
                        'clock_in_time' => $attendance->clock_in != '00:00:00' 
                            ? date('h:i A', strtotime($attendance->clock_in)) 
                            : null,
                        'clock_out_time' => $attendance->clock_out != '00:00:00' 
                            ? date('h:i A', strtotime($attendance->clock_out)) 
                            : null,
                        'status' => $attendance->status ?? 'Not Clocked In',
                        'attendance_id' => $attendance->id,
                        'is_late' => $attendance->late != '00:00:00',
                        'late_duration' => $attendance->late != '00:00:00' ? $attendance->late : null,
                    ];
                }
            }

            return $this->successResponse($data, 'Status retrieved successfully');
            
        } catch (\Exception $e) {
            Log::error('getStatus error: ' . $e->getMessage());
            return $this->errorResponse('Error checking status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Enroll Face - For Web and Flutter App
     */
    public function enrollFace(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                Log::warning('enrollFace: User not authenticated');
                return $this->errorResponse('User not authenticated', 401);
            }

            Log::info('enrollFace: User authenticated', ['user_id' => $user->id]);

            $validator = Validator::make($request->all(), [
                'face_descriptor' => 'required|array',
                'face_photo' => 'nullable|string',
                'employee_id' => 'nullable|exists:employees,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $descriptorCheck = $this->validateDescriptor($request->face_descriptor);
            if (!$descriptorCheck['valid']) {
                return $this->errorResponse($descriptorCheck['message'], 422);
            }

            $employeeId = $request->input('employee_id');
            $creatorId = $this->getCreatorId($user);
            
            if (!$employeeId) {
                $employee = Employee::where('user_id', $user->id)->first();
                
                if (!$employee) {
                    $employee = Employee::where('created_by', $creatorId)
                        ->where('user_id', $user->id)
                        ->first();
                }
                
                if (!$employee) {
                    Log::warning('enrollFace: Employee not found for user', ['user_id' => $user->id]);
                    return $this->errorResponse('Employee not found. Please contact HR.', 404);
                }
                $employeeId = $employee->id;
            }

            $employee = Employee::where('created_by', $creatorId)
                ->where('id', $employeeId)
                ->first();

            if (!$employee) {
                Log::warning('enrollFace: Employee not found', ['employee_id' => $employeeId, 'creator_id' => $creatorId]);
                return $this->errorResponse('Employee not found', 404);
            }

            $employee->face_descriptor = json_encode($request->face_descriptor);
            $employee->face_enrolled_at = now();

            if ($request->has('face_photo') && !empty($request->face_photo)) {
                $photoName = $this->saveFacePhotoFromBase64($request->face_photo, $employee->id);
                if ($photoName) {
                    if (!empty($employee->face_photo)) {
                        $this->deleteFacePhoto($employee->face_photo);
                    }
                    $employee->face_photo = $photoName;
                }
            }

            $employee->save();

            Log::info('Face enrolled via API', [
                'employee_id' => $employee->id,
                'user_id' => $user->id,
                'platform' => $request->header('X-Platform', 'web')
            ]);

            return $this->successResponse([
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'face_enrolled_at' => $employee->face_enrolled_at,
                'has_face_photo' => !empty($employee->face_photo),
                'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                'late_access_enabled' => $employee->late_access_enabled ?? false,
                'late_allowed_minutes' => $employee->late_allowed_minutes ?? 60,
            ], 'Face enrolled successfully');

        } catch (\Exception $e) {
            Log::error('Face enrollment API error: ' . $e->getMessage());
            return $this->errorResponse('Failed to enroll face: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify Face - Compare with stored descriptors
     */
    public function verifyFace(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                Log::warning('verifyFace: User not authenticated');
                return $this->errorResponse('User not authenticated', 401);
            }

            $validator = Validator::make($request->all(), [
                'face_descriptor' => 'required|array',
                'threshold' => 'nullable|numeric|min:0|max:1',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $descriptorCheck = $this->validateDescriptor($request->face_descriptor);
            if (!$descriptorCheck['valid']) {
                return $this->errorResponse($descriptorCheck['message'], 422);
            }

            $currentDescriptor = $request->input('face_descriptor');
            $threshold = $request->input('threshold', 0.6);
            $creatorId = $this->getCreatorId($user);

            Log::info('verifyFace: Searching for matches', ['creator_id' => $creatorId]);

            $employees = Employee::where('created_by', $creatorId)
                ->whereNotNull('face_descriptor')
                ->with(['user', 'department', 'designation'])
                ->get();

            if ($employees->isEmpty()) {
                Log::info('verifyFace: No enrolled faces found', ['creator_id' => $creatorId]);
                return $this->errorResponse('No enrolled faces found', 404);
            }

            $bestMatch = null;
            $bestDistance = $threshold;
            $bestConfidence = 0;

            foreach ($employees as $employee) {
                $storedDescriptor = json_decode($employee->face_descriptor, true);
                if (empty($storedDescriptor)) {
                    continue;
                }

                $distance = $this->calculateFaceDistance($currentDescriptor, $storedDescriptor);
                $confidence = (1 - $distance) * 100;

                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestMatch = $employee;
                    $bestConfidence = $confidence;
                }
            }

            if ($bestMatch) {
                Log::info('verifyFace: Match found', [
                    'employee_id' => $bestMatch->id,
                    'confidence' => $bestConfidence,
                    'distance' => $bestDistance
                ]);

                $today = date('Y-m-d');
                $attendance = AttendanceEmployee::where('employee_id', $bestMatch->id)
                    ->whereDate('date', $today)
                    ->first();

                return $this->successResponse([
                    'employee_id' => $bestMatch->id,
                    'employee_name' => $bestMatch->name,
                    'employee_code' => $bestMatch->employee_id,
                    'email' => $bestMatch->email,
                    'department' => $bestMatch->department ? $bestMatch->department->name : null,
                    'designation' => $bestMatch->designation ? $bestMatch->designation->name : null,
                    'confidence' => round($bestConfidence, 2),
                    'distance' => round($bestDistance, 4),
                    'has_face_photo' => !empty($bestMatch->face_photo),
                    'face_photo_url' => !empty($bestMatch->face_photo) 
                        ? asset('uploads/face/' . $bestMatch->face_photo) 
                        : null,
                    'attendance_status' => [
                        'is_clocked_in' => $attendance && $attendance->clock_in != '00:00:00',
                        'is_clocked_out' => $attendance && $attendance->clock_out != '00:00:00',
                        'is_on_break' => $attendance && !empty($attendance->tea_break_out) 
                            && $attendance->tea_break_out != '00:00:00'
                            && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00'),
                        'clock_in_time' => $attendance && $attendance->clock_in != '00:00:00' 
                            ? date('h:i A', strtotime($attendance->clock_in)) 
                            : null,
                        'clock_out_time' => $attendance && $attendance->clock_out != '00:00:00' 
                            ? date('h:i A', strtotime($attendance->clock_out)) 
                            : null,
                    ],
                    'late_access_enabled' => $bestMatch->late_access_enabled ?? false,
                    'late_allowed_minutes' => $bestMatch->late_allowed_minutes ?? 60,
                    'half_day_threshold' => $bestMatch->half_day_threshold ?? 4.0,
                ], 'Face verified successfully');
            }

            Log::info('verifyFace: No match found');
            return $this->errorResponse('No matching face found', 404);

        } catch (\Exception $e) {
            Log::error('Face verification API error: ' . $e->getMessage());
            return $this->errorResponse('Verification failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete Face Enrollment
     */
    public function deleteEnrollment(Request $request, $employeeId)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                Log::warning('deleteEnrollment: User not authenticated');
                return $this->errorResponse('User not authenticated', 401);
            }

            $creatorId = $this->getCreatorId($user);

            $employee = Employee::where('created_by', $creatorId)
                ->where('id', $employeeId)
                ->first();

            if (!$employee) {
                Log::warning('deleteEnrollment: Employee not found', ['employee_id' => $employeeId]);
                return $this->errorResponse('Employee not found', 404);
            }

            if (!empty($employee->face_photo)) {
                $this->deleteFacePhoto($employee->face_photo);
            }

            $employee->face_descriptor = null;
            $employee->face_enrolled_at = null;
            $employee->face_photo = null;
            $employee->save();

            Log::info('Face enrollment deleted', [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'user_id' => $user->id,
            ]);

            return $this->successResponse(null, 'Face enrollment deleted successfully');

        } catch (\Exception $e) {
            Log::error('Delete face enrollment error: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete enrollment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark Attendance using Face ID
     */
    public function markAttendance(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                Log::warning('markAttendance: User not authenticated');
                return $this->errorResponse('User not authenticated', 401);
            }

            Log::info('markAttendance: User authenticated', ['user_id' => $user->id]);

            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'action' => 'required|in:clock_in,clock_out,tea_break_in,tea_break_out',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'face_confidence' => 'nullable|numeric|min:0|max:100',
                'punch_photo' => 'nullable|string',
                'mode' => 'nullable|in:office,remote',
                'work_report' => 'nullable|string|max:1000',
                'date' => 'nullable|date_format:Y-m-d',
                'time' => 'nullable|date_format:H:i:s',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $employeeId = $request->input('employee_id');
            $date = $request->input('date', date('Y-m-d'));
            $time = $request->input('time', date('H:i:s'));
            $action = $request->input('action');
            
            $creatorId = $this->getCreatorId($user);

            $employee = Employee::where('created_by', $creatorId)
                ->where('id', $employeeId)
                ->first();

            if (!$employee) {
                Log::warning('markAttendance: Employee not found', ['employee_id' => $employeeId]);
                return $this->errorResponse('Employee not found', 404);
            }

            if ($request->input('mode') !== 'remote') {
                $locationValid = $this->checkLocation($request);
                if (!$locationValid['success']) {
                    return $this->errorResponse($locationValid['message'], 400, [
                        'distance' => $locationValid['distance'] ?? null,
                        'required_radius' => $locationValid['radius'] ?? 300,
                    ]);
                }
            }

            $startTime = Utility::getValByName('company_start_time') ?? '09:00:00';
            $endTime = Utility::getValByName('company_end_time') ?? '18:00:00';

            $existing = AttendanceEmployee::where('employee_id', $employeeId)
                ->whereDate('date', $date)
                ->first();

            switch ($action) {
                case 'clock_in':
                    return $this->handleClockIn($request, $employee, $existing, $date, $time, $startTime);
                case 'clock_out':
                    return $this->handleClockOut($request, $employee, $existing, $date, $time, $endTime);
                case 'tea_break_in':
                    return $this->handleBreakIn($employee, $existing, $date, $time);
                case 'tea_break_out':
                    return $this->handleBreakOut($employee, $existing, $date, $time);
                default:
                    return $this->errorResponse('Invalid action', 400);
            }

        } catch (\Exception $e) {
            Log::error('Mark attendance API error: ' . $e->getMessage());
            return $this->errorResponse('Failed to mark attendance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Attendance Status for Employee
     */
    public function getAttendanceStatus(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                Log::error('getAttendanceStatus: User not authenticated');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            Log::info('getAttendanceStatus called', ['user_id' => $user->id]);

            $creatorId = $this->getCreatorId($user);

            if ($user->type == 'company' || $user->type == 'super admin') {
                $employeeId = $request->input('employee_id');
                
                if (!$employeeId) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'user_type' => $user->type,
                            'message' => 'Please provide employee_id parameter',
                            'example' => '/api/face-api/attendance-status?employee_id=10'
                        ]
                    ]);
                }
                
                $employee = Employee::where('created_by', $creatorId)
                    ->where('id', $employeeId)
                    ->first();
                    
                if (!$employee) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Employee not found in your company'
                    ], 404);
                }
                
                $today = date('Y-m-d');
                $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                    ->whereDate('date', $today)
                    ->first();
                    
                return response()->json([
                    'success' => true,
                    'data' => [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->name,
                        'has_face_enrolled' => !empty($employee->face_descriptor),
                        'is_clocked_in' => $attendance && $attendance->clock_in != '00:00:00',
                        'is_clocked_out' => $attendance && $attendance->clock_out != '00:00:00',
                        'is_on_break' => $attendance && !empty($attendance->tea_break_out) 
                            && $attendance->tea_break_out != '00:00:00'
                            && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00'),
                        'clock_in_time' => $attendance && $attendance->clock_in != '00:00:00' 
                            ? date('h:i A', strtotime($attendance->clock_in)) 
                            : null,
                        'clock_out_time' => $attendance && $attendance->clock_out != '00:00:00' 
                            ? date('h:i A', strtotime($attendance->clock_out)) 
                            : null,
                        'status' => $attendance ? $attendance->status : 'Not Clocked In',
                        'today_attendance_id' => $attendance ? $attendance->id : null,
                    ]
                ]);
            }

            $employeeId = $request->input('employee_id');
            
            if (!$employeeId) {
                $employee = Employee::where('created_by', $creatorId)
                    ->where('user_id', $user->id)
                    ->first();
                
                if (!$employee) {
                    Log::error('getAttendanceStatus: Employee not found for user', ['user_id' => $user->id]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Employee not found for this user. Please contact HR.'
                    ], 404);
                }
                $employeeId = $employee->id;
            }

            Log::info('getAttendanceStatus: Employee ID', ['employee_id' => $employeeId]);

            $employee = Employee::where('created_by', $creatorId)
                ->where('id', $employeeId)
                ->first();

            if (!$employee) {
                Log::error('getAttendanceStatus: Employee not found', ['employee_id' => $employeeId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            $today = date('Y-m-d');
            $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                ->whereDate('date', $today)
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'has_face_enrolled' => !empty($employee->face_descriptor),
                    'is_clocked_in' => $attendance && $attendance->clock_in != '00:00:00',
                    'is_clocked_out' => $attendance && $attendance->clock_out != '00:00:00',
                    'is_on_break' => $attendance && !empty($attendance->tea_break_out) 
                        && $attendance->tea_break_out != '00:00:00'
                        && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00'),
                    'clock_in_time' => $attendance && $attendance->clock_in != '00:00:00' 
                        ? date('h:i A', strtotime($attendance->clock_in)) 
                        : null,
                    'clock_out_time' => $attendance && $attendance->clock_out != '00:00:00' 
                        ? date('h:i A', strtotime($attendance->clock_out)) 
                        : null,
                    'status' => $attendance ? $attendance->status : 'Not Clocked In',
                    'today_attendance_id' => $attendance ? $attendance->id : null,
                    'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                    'late_access_enabled' => $employee->late_access_enabled ?? false,
                    'late_allowed_minutes' => $employee->late_allowed_minutes ?? 60,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('getAttendanceStatus error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Attendance Stats for Dashboard
     */
    public function getAttendanceStats(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                return $this->errorResponse('User not authenticated', 401);
            }

            $creatorId = $this->getCreatorId($user);
            $today = $request->input('date', date('Y-m-d'));

            $totalEmployees = Employee::where('created_by', $creatorId)->count();
            $enrolled = Employee::where('created_by', $creatorId)->whereNotNull('face_descriptor')->count();
            $present = AttendanceEmployee::whereDate('date', $today)->where('status', 'Present')->distinct('employee_id')->count('employee_id');
            $halfDay = AttendanceEmployee::whereDate('date', $today)->where('status', 'Half Day')->distinct('employee_id')->count('employee_id');
            $absent = max(0, $totalEmployees - $present - $halfDay);

            $recentLog = AttendanceEmployee::with('employee')
                ->whereDate('date', $today)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'employee_name' => $item->employee ? $item->employee->name : 'Unknown',
                        'employee_id' => $item->employee_id,
                        'clock_in_time' => $item->clock_in != '00:00:00' ? date('h:i A', strtotime($item->clock_in)) : null,
                        'clock_out_time' => $item->clock_out != '00:00:00' ? date('h:i A', strtotime($item->clock_out)) : null,
                        'status' => $item->status,
                        'marked_by' => $item->marked_by ?? 'manual',
                        'is_on_break' => !empty($item->tea_break_out) && $item->tea_break_out != '00:00:00' 
                            && (empty($item->tea_break_in) || $item->tea_break_in == '00:00:00'),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Attendance stats retrieved',
                'data' => [
                    'date' => $today,
                    'total_employees' => $totalEmployees,
                    'enrolled_faces' => $enrolled,
                    'present' => $present,
                    'half_day' => $halfDay,
                    'absent' => $absent,
                    'recent_log' => $recentLog,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('getAttendanceStats error: ' . $e->getMessage());
            return $this->errorResponse('Failed to get stats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Enrollment Status
     */
    public function getEnrollmentStatus(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            Log::info('getEnrollmentStatus called', ['user_id' => $user->id]);

            $creatorId = $this->getCreatorId($user);

            if ($user->type == 'company' || $user->type == 'super admin') {
                $employeeId = $request->input('employee_id');
                
                if (!$employeeId) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'user_type' => $user->type,
                            'message' => 'Please provide employee_id parameter'
                        ]
                    ]);
                }
                
                $employee = Employee::where('created_by', $creatorId)
                    ->where('id', $employeeId)
                    ->first();
                    
                if (!$employee) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Employee not found in your company'
                    ], 404);
                }
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->name,
                        'is_face_enrolled' => !empty($employee->face_descriptor),
                        'face_enrolled_at' => $employee->face_enrolled_at,
                        'has_face_photo' => !empty($employee->face_photo),
                        'face_photo_url' => !empty($employee->face_photo) 
                            ? asset('uploads/face/' . $employee->face_photo) 
                            : null,
                    ]
                ]);
            }

            $employeeId = $request->input('employee_id');
            
            if (!$employeeId) {
                $employee = Employee::where('created_by', $creatorId)
                    ->where('user_id', $user->id)
                    ->first();
                    
                if (!$employee) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Employee not found'
                    ], 404);
                }
                $employeeId = $employee->id;
            }

            Log::info('getEnrollmentStatus: Employee ID', ['employee_id' => $employeeId]);

            $employee = Employee::where('created_by', $creatorId)
                ->where('id', $employeeId)
                ->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'is_face_enrolled' => !empty($employee->face_descriptor),
                    'face_enrolled_at' => $employee->face_enrolled_at,
                    'has_face_photo' => !empty($employee->face_photo),
                    'face_photo_url' => !empty($employee->face_photo) 
                        ? asset('uploads/face/' . $employee->face_photo) 
                        : null,
                    'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                    'enable_half_day' => $employee->enable_half_day ?? true,
                    'late_access_enabled' => $employee->late_access_enabled ?? false,
                    'late_allowed_minutes' => $employee->late_allowed_minutes ?? 60,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('getEnrollmentStatus error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get enrollment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Face Photo
     */
    public function getFacePhoto(Request $request, $employeeId)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                return $this->errorResponse('User not authenticated', 401);
            }

            $creatorId = $this->getCreatorId($user);

            $employee = Employee::where('created_by', $creatorId)
                ->where('id', $employeeId)
                ->first();

            if (!$employee) {
                return $this->errorResponse('Employee not found', 404);
            }

            if (empty($employee->face_photo)) {
                return $this->errorResponse('Face photo not found', 404);
            }

            $photoPath = public_path('uploads/face/' . $employee->face_photo);
            if (!file_exists($photoPath)) {
                return $this->errorResponse('Face photo file not found', 404);
            }

            return $this->successResponse([
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'photo_url' => asset('uploads/face/' . $employee->face_photo),
                'photo_name' => $employee->face_photo,
            ]);

        } catch (\Exception $e) {
            Log::error('Get face photo error: ' . $e->getMessage());
            return $this->errorResponse('Failed to get face photo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Bulk Enroll
     */
    public function bulkEnroll(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                return $this->errorResponse('User not authenticated', 401);
            }

            $creatorId = $this->getCreatorId($user);

            $validator = Validator::make($request->all(), [
                'employees' => 'required|array',
                'employees.*.employee_id' => 'required|exists:employees,id',
                'employees.*.face_descriptor' => 'required|array',
                'employees.*.face_photo' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $results = [];
            $successCount = 0;
            $failCount = 0;

            foreach ($request->employees as $data) {
                $employee = Employee::where('created_by', $creatorId)
                    ->where('id', $data['employee_id'])
                    ->first();

                if (!$employee) {
                    $failCount++;
                    $results[] = [
                        'employee_id' => $data['employee_id'],
                        'status' => 'failed',
                        'message' => 'Employee not found',
                    ];
                    continue;
                }

                try {
                    $descriptorCheck = $this->validateDescriptor($data['face_descriptor']);
                    if (!$descriptorCheck['valid']) {
                        $failCount++;
                        $results[] = [
                            'employee_id' => $employee->id,
                            'status' => 'failed',
                            'message' => $descriptorCheck['message'],
                        ];
                        continue;
                    }

                    $employee->face_descriptor = json_encode($data['face_descriptor']);
                    $employee->face_enrolled_at = now();

                    if (isset($data['face_photo']) && !empty($data['face_photo'])) {
                        $photoName = $this->saveFacePhotoFromBase64($data['face_photo'], $employee->id);
                        if ($photoName) {
                            if (!empty($employee->face_photo)) {
                                $this->deleteFacePhoto($employee->face_photo);
                            }
                            $employee->face_photo = $photoName;
                        }
                    }

                    $employee->save();

                    $successCount++;
                    $results[] = [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->name,
                        'status' => 'success',
                        'message' => 'Face enrolled successfully',
                    ];

                } catch (\Exception $e) {
                    $failCount++;
                    $results[] = [
                        'employee_id' => $data['employee_id'],
                        'status' => 'failed',
                        'message' => $e->getMessage(),
                    ];
                }
            }

            return $this->successResponse([
                'total' => count($request->employees),
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'results' => $results,
            ], 'Bulk enrollment completed');

        } catch (\Exception $e) {
            Log::error('Bulk enroll error: ' . $e->getMessage());
            return $this->errorResponse('Failed to bulk enroll: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Statistics
     */
    public function getStats(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                return $this->errorResponse('User not authenticated', 401);
            }

            $creatorId = $this->getCreatorId($user);
            $date = $request->get('date', date('Y-m-d'));

            $todayTotal = AttendanceEmployee::whereDate('date', $date)
                ->where('marked_by', 'face_recognition')
                ->count();

            $todayPresent = AttendanceEmployee::whereDate('date', $date)
                ->where('marked_by', 'face_recognition')
                ->where('status', 'Present')
                ->count();

            $todayHalfDay = AttendanceEmployee::whereDate('date', $date)
                ->where('marked_by', 'face_recognition')
                ->where('status', 'Half Day')
                ->count();

            $totalEnrolled = Employee::where('created_by', $creatorId)
                ->whereNotNull('face_descriptor')
                ->count();

            return $this->successResponse([
                'today' => [
                    'date' => $date,
                    'total' => $todayTotal,
                    'present' => $todayPresent,
                    'half_day' => $todayHalfDay,
                    'absent' => $todayTotal - $todayPresent - $todayHalfDay,
                ],
                'enrollment' => [
                    'total_enrolled' => $totalEnrolled,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Get stats error: ' . $e->getMessage());
            return $this->errorResponse('Failed to get stats: ' . $e->getMessage(), 500);
        }
    }

    // ================================================================
    // ================ UNIFIED ATTENDANCE METHODS ====================
    // ================================================================

    /**
     * ✅ UNIFIED CLOCK IN - Supports both Web (with verification) and Flutter (without verification)
     * POST /api/face-api/clock-in-unified
     */
    public function clockInUnified(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                Log::warning('clockInUnified: User not authenticated');
                return $this->errorResponse('User not authenticated', 401);
            }

            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'mode' => 'nullable|in:office,remote',
                'punch_photo' => 'nullable|string',
                'face_descriptor' => 'nullable|array',
                'face_confidence' => 'nullable|numeric|min:0|max:100',
                'source' => 'nullable|in:web,flutter,manual',
                'is_verified' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $employeeId = $request->employee_id;
            $employee = Employee::find($employeeId);
            
            if (!$employee) {
                return $this->errorResponse('Employee not found', 404);
            }

            $today = date('Y-m-d');
            $time = date('H:i:s');
            $source = $request->input('source', 'web');

            // Check if already clocked in today
            $existing = AttendanceEmployee::where('employee_id', $employeeId)
                ->whereDate('date', $today)
                ->first();

            if ($existing && $existing->clock_in != '00:00:00' && $existing->clock_in !== null) {
                if ($existing->clock_out != '00:00:00' && $existing->clock_out !== null) {
                    return $this->errorResponse('You have already clocked out today', 400);
                }
                return $this->successResponse([
                    'attendance_id' => $existing->id,
                    'clock_in_time' => date('h:i A', strtotime($existing->clock_in)),
                    'status' => $existing->status,
                    'source' => $existing->source ?? 'manual',
                    'is_verified' => $existing->is_verified ?? false,
                    'punch_photo' => $existing->punch_photo ? asset('uploads/attendance/' . $existing->punch_photo) : null,
                ], 'Already clocked in');
            }

            // Calculate late
            $startTime = Utility::getValByName('company_start_time') ?? '09:00:00';
            $late = '00:00:00';
            $status = 'Present';

            if (strtotime($time) > strtotime($startTime)) {
                $lateSeconds = strtotime($time) - strtotime($startTime);
                $hours = floor($lateSeconds / 3600);
                $mins = floor(($lateSeconds % 3600) / 60);
                $secs = $lateSeconds % 60;
                $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                $lateAccessEnabled = $employee->late_access_enabled ?? false;
                $lateAllowedMinutes = $employee->late_allowed_minutes ?? 60;
                $halfDayEnabled = $employee->enable_half_day ?? true;
                $lateMinutes = $lateSeconds / 60;

                if ($halfDayEnabled) {
                    if ($lateAccessEnabled) {
                        if ($lateMinutes > $lateAllowedMinutes) {
                            $status = 'Half Day';
                        }
                    } else {
                        $status = 'Half Day';
                    }
                }
            }

            // Save photo
            $photoName = null;
            if ($request->has('punch_photo') && !empty($request->punch_photo)) {
                $photoName = $this->saveAttendancePhoto($request->punch_photo, $employeeId, 'clockin');
            }

            // ✅ Determine verification status based on source
            $isVerified = false;
            $faceConfidence = null;
            $markedBy = 'manual';
            $verificationMessage = null;

            if ($source === 'web' && $request->has('face_descriptor')) {
                // ✅ WEB: Face verification
                $storedDescriptor = json_decode($employee->face_descriptor, true);
                $inputDescriptor = $request->input('face_descriptor');
                
                if ($storedDescriptor && $inputDescriptor) {
                    $distance = $this->calculateFaceDistance($inputDescriptor, $storedDescriptor);
                    $threshold = 0.6;
                    $isVerified = $distance < $threshold;
                    $faceConfidence = $isVerified ? round((1 - $distance) * 100, 2) : round($distance * 100, 2);
                    $markedBy = $isVerified ? 'face_recognition' : 'face_recognition_failed';
                    $verificationMessage = $isVerified ? 'Face verified successfully' : 'Face verification failed';
                    
                    Log::info('Web face verification (unified)', [
                        'employee_id' => $employeeId,
                        'is_verified' => $isVerified,
                        'confidence' => $faceConfidence,
                        'distance' => $distance
                    ]);
                } else {
                    $markedBy = 'manual';
                    $verificationMessage = 'No face descriptor available';
                    Log::warning('No face descriptor for web verification', [
                        'employee_id' => $employeeId,
                        'has_stored' => !empty($storedDescriptor),
                        'has_input' => !empty($inputDescriptor)
                    ]);
                }
            } elseif ($source === 'flutter') {
                // ✅ FLUTTER: No verification, just mark as mobile
                $markedBy = 'mobile_app';
                $isVerified = false;
                $faceConfidence = null;
                $verificationMessage = 'Mobile app punch in (no verification)';
                
                Log::info('Flutter clock in (unified)', [
                    'employee_id' => $employeeId,
                    'has_photo' => !empty($photoName)
                ]);
            } else {
                // ✅ MANUAL: Admin marking
                $markedBy = 'manual';
                $isVerified = $request->input('is_verified', false);
                $faceConfidence = $request->input('face_confidence');
                $verificationMessage = 'Manual entry';
            }

            // Create attendance record
            $attendance = new AttendanceEmployee();
            $attendance->employee_id = $employeeId;
            $attendance->date = $today;
            $attendance->clock_in = $time;
            $attendance->clock_out = '00:00:00';
            $attendance->late = $late;
            $attendance->early_leaving = '00:00:00';
            $attendance->overtime = '00:00:00';
            $attendance->total_rest = '00:00:00';
            $attendance->status = $status;
            $attendance->marked_by = $markedBy;
            $attendance->source = $source;
            $attendance->face_confidence = $faceConfidence;
            $attendance->is_verified = $isVerified;
            $attendance->created_by = $this->getCreatorId($user);
            $attendance->latitude = $request->latitude;
            $attendance->longitude = $request->longitude;
            $attendance->location_mode = $request->mode ?? 'office';
            
            if ($photoName) {
                $attendance->punch_photo = $photoName;
            }
            
            $attendance->save();

            $responseData = [
                'attendance_id' => $attendance->id,
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'clock_in_time' => date('h:i A', strtotime($time)),
                'status' => $status,
                'is_late' => $late != '00:00:00',
                'late_duration' => $late,
                'source' => $source,
                'marked_by' => $markedBy,
                'is_verified' => $isVerified,
                'face_confidence' => $faceConfidence,
                'verification_message' => $verificationMessage,
                'punch_photo' => $photoName ? asset('uploads/attendance/' . $photoName) : null,
                'location' => [
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'mode' => $request->mode ?? 'office',
                ],
                'date' => $today,
            ];

            $message = $source === 'web' 
                ? ($isVerified ? 'Clocked in with Face Verification! ✅' : 'Clocked in (Face verification failed) ⚠️')
                : 'Clocked in successfully! ✅';

            return $this->successResponse($responseData, $message);

        } catch (\Exception $e) {
            Log::error('clockInUnified error: ' . $e->getMessage());
            return $this->errorResponse('Failed to clock in: ' . $e->getMessage(), 500);
        }
    }

    /**
     * ✅ UNIFIED CLOCK OUT - Supports both Web and Flutter
     * POST /api/face-api/clock-out-unified
     */
    public function clockOutUnified(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                Log::warning('clockOutUnified: User not authenticated');
                return $this->errorResponse('User not authenticated', 401);
            }

            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'punch_photo' => 'nullable|string',
                'source' => 'nullable|in:web,flutter,manual',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $employeeId = $request->employee_id;
            $today = date('Y-m-d');
            $time = date('H:i:s');
            $source = $request->input('source', 'web');

            $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                ->whereDate('date', $today)
                ->first();

            if (!$attendance || $attendance->clock_in == '00:00:00') {
                return $this->errorResponse('You are not clocked in', 400);
            }

            if ($attendance->clock_out != '00:00:00') {
                return $this->errorResponse('Already clocked out', 400);
            }

            // Check if on break
            if (!empty($attendance->tea_break_out) && $attendance->tea_break_out != '00:00:00'
                && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00')) {
                return $this->errorResponse('Please end your tea break first', 400);
            }

            $endTime = Utility::getValByName('company_end_time') ?? '18:00:00';

            $earlyLeaving = '00:00:00';
            if (strtotime($time) < strtotime($endTime)) {
                $earlySeconds = strtotime($endTime) - strtotime($time);
                $hours = floor($earlySeconds / 3600);
                $mins = floor(($earlySeconds % 3600) / 60);
                $secs = $earlySeconds % 60;
                $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            }

            $overtime = '00:00:00';
            if (strtotime($time) > strtotime($endTime)) {
                $overtimeSeconds = strtotime($time) - strtotime($endTime);
                $hours = floor($overtimeSeconds / 3600);
                $mins = floor(($overtimeSeconds % 3600) / 60);
                $secs = $overtimeSeconds % 60;
                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            }

            // Save punch out photo
            $photoName = null;
            if ($request->has('punch_photo') && !empty($request->punch_photo)) {
                $photoName = $this->saveAttendancePhoto($request->punch_photo, $employeeId, 'clockout');
            }

            $attendance->clock_out = $time;
            $attendance->early_leaving = $earlyLeaving;
            $attendance->overtime = $overtime;
            $attendance->source = $source;
            $attendance->latitude = $request->latitude;
            $attendance->longitude = $request->longitude;
            
            if ($photoName) {
                $attendance->punch_out_photo = $photoName;
            }
            $attendance->save();

            // Calculate worked hours
            $workedSeconds = strtotime($time) - strtotime($attendance->clock_in);
            $hours = floor($workedSeconds / 3600);
            $mins = floor(($workedSeconds % 3600) / 60);
            $workedHours = sprintf('%02d:%02d', $hours, $mins);

            $employee = Employee::find($employeeId);

            return $this->successResponse([
                'attendance_id' => $attendance->id,
                'employee_id' => $employeeId,
                'employee_name' => $employee ? $employee->name : null,
                'clock_in_time' => date('h:i A', strtotime($attendance->clock_in)),
                'clock_out_time' => date('h:i A', strtotime($time)),
                'worked_hours' => $workedHours,
                'status' => $attendance->status,
                'source' => $source,
                'punch_out_photo' => $photoName ? asset('uploads/attendance/' . $photoName) : null,
                'date' => $today,
            ], 'Clocked out successfully! ✅');

        } catch (\Exception $e) {
            Log::error('clockOutUnified error: ' . $e->getMessage());
            return $this->errorResponse('Failed to clock out: ' . $e->getMessage(), 500);
        }
    }

    /**
     * ✅ GET UNIFIED ATTENDANCE STATUS - Shows both Web and Flutter punches
     * GET /api/face-api/attendance-status-unified
     */
    public function getAttendanceStatusUnified(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                return $this->errorResponse('User not authenticated', 401);
            }

            $creatorId = $this->getCreatorId($user);
            $employeeId = $request->input('employee_id');
            
            if (!$employeeId) {
                $employee = Employee::where('created_by', $creatorId)
                    ->where('user_id', $user->id)
                    ->first();
                    
                if (!$employee) {
                    return $this->errorResponse('Employee not found', 404);
                }
                $employeeId = $employee->id;
            }

            $employee = Employee::where('created_by', $creatorId)
                ->where('id', $employeeId)
                ->first();

            if (!$employee) {
                return $this->errorResponse('Employee not found', 404);
            }

            $today = date('Y-m-d');
            $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                ->whereDate('date', $today)
                ->first();

            $sourceInfo = $attendance ? [
                'source' => $attendance->source ?? 'manual',
                'marked_by' => $attendance->marked_by ?? 'manual',
                'is_verified' => $attendance->is_verified ?? false,
                'face_confidence' => $attendance->face_confidence,
                'verification_badge' => $this->getVerificationBadgeUnified($attendance),
            ] : null;

            return $this->successResponse([
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'employee_code' => $employee->employee_id,
                'department' => $employee->department ? $employee->department->name : null,
                'designation' => $employee->designation ? $employee->designation->name : null,
                'has_face_enrolled' => !empty($employee->face_descriptor),
                'is_clocked_in' => $attendance && $attendance->clock_in != '00:00:00',
                'is_clocked_out' => $attendance && $attendance->clock_out != '00:00:00',
                'is_on_break' => $attendance && !empty($attendance->tea_break_out) 
                    && $attendance->tea_break_out != '00:00:00'
                    && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00'),
                'clock_in_time' => $attendance && $attendance->clock_in != '00:00:00' 
                    ? date('h:i A', strtotime($attendance->clock_in)) 
                    : null,
                'clock_out_time' => $attendance && $attendance->clock_out != '00:00:00' 
                    ? date('h:i A', strtotime($attendance->clock_out)) 
                    : null,
                'status' => $attendance ? $attendance->status : 'Not Clocked In',
                'attendance_id' => $attendance ? $attendance->id : null,
                'late_duration' => $attendance ? $attendance->late : null,
                'punch_photo' => $attendance && $attendance->punch_photo 
                    ? asset('uploads/attendance/' . $attendance->punch_photo) 
                    : null,
                'punch_out_photo' => $attendance && $attendance->punch_out_photo 
                    ? asset('uploads/attendance/' . $attendance->punch_out_photo) 
                    : null,
                'source_info' => $sourceInfo,
                'verification_badge' => $sourceInfo ? $sourceInfo['verification_badge'] : null,
                'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                'late_access_enabled' => $employee->late_access_enabled ?? false,
                'late_allowed_minutes' => $employee->late_allowed_minutes ?? 60,
            ], 'Attendance status retrieved');

        } catch (\Exception $e) {
            Log::error('getAttendanceStatusUnified error: ' . $e->getMessage());
            return $this->errorResponse('Failed to get status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * ✅ GET UNIFIED DASHBOARD - Shows both Web and Flutter punches
     * GET /api/face-api/dashboard-unified
     */
    public function getDashboardUnified(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            
            if (!$user) {
                return $this->errorResponse('User not authenticated', 401);
            }

            $creatorId = $this->getCreatorId($user);
            $today = $request->input('date', date('Y-m-d'));

            $totalEmployees = Employee::where('created_by', $creatorId)->count();
            
            $attendances = AttendanceEmployee::whereDate('date', $today)
                ->where('created_by', $creatorId)
                ->get();

            $sourceBreakdown = [
                'web' => [
                    'total' => 0,
                    'verified' => 0,
                    'failed' => 0,
                ],
                'flutter' => [
                    'total' => 0,
                ],
                'manual' => [
                    'total' => 0,
                ],
            ];

            foreach ($attendances as $attendance) {
                $source = $attendance->source ?? 'manual';
                
                if ($source === 'web') {
                    $sourceBreakdown['web']['total']++;
                    if ($attendance->is_verified) {
                        $sourceBreakdown['web']['verified']++;
                    } elseif ($attendance->face_confidence !== null) {
                        $sourceBreakdown['web']['failed']++;
                    }
                } elseif ($source === 'flutter') {
                    $sourceBreakdown['flutter']['total']++;
                } else {
                    $sourceBreakdown['manual']['total']++;
                }
            }

            $recentActivity = AttendanceEmployee::whereDate('date', $today)
                ->where('created_by', $creatorId)
                ->with('employee')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'employee_name' => $item->employee ? $item->employee->name : 'Unknown',
                        'clock_in_time' => $item->clock_in != '00:00:00' 
                            ? date('h:i A', strtotime($item->clock_in)) 
                            : null,
                        'clock_out_time' => $item->clock_out != '00:00:00' 
                            ? date('h:i A', strtotime($item->clock_out)) 
                            : null,
                        'status' => $item->status,
                        'source' => $item->source ?? 'manual',
                        'marked_by' => $item->marked_by,
                        'is_verified' => $item->is_verified ?? false,
                        'verification_badge' => $this->getVerificationBadgeUnified($item),
                        'has_photo' => !empty($item->punch_photo),
                    ];
                });

            return $this->successResponse([
                'date' => $today,
                'total_employees' => $totalEmployees,
                'present_count' => $attendances->where('status', 'Present')->count(),
                'half_day_count' => $attendances->where('status', 'Half Day')->count(),
                'absent_count' => $totalEmployees - $attendances->count(),
                'source_breakdown' => $sourceBreakdown,
                'recent_activity' => $recentActivity,
            ], 'Dashboard data retrieved');

        } catch (\Exception $e) {
            Log::error('getDashboardUnified error: ' . $e->getMessage());
            return $this->errorResponse('Failed to get dashboard: ' . $e->getMessage(), 500);
        }
    }

    // ================================================================
    // ================ PRIVATE HELPER METHODS ========================
    // ================================================================

    private function handleClockIn($request, $employee, $existing, $date, $time, $startTime)
    {
        if ($existing && $existing->clock_in != '00:00:00' && $existing->clock_out == '00:00:00') {
            return $this->successResponse([
                'already_clocked_in' => true,
                'clock_in_time' => date('h:i A', strtotime($existing->clock_in)),
                'status' => $existing->status,
                'attendance_id' => $existing->id,
            ], 'Already clocked in');
        }

        if ($existing && $existing->clock_in != '00:00:00' && $existing->clock_out != '00:00:00') {
            return $this->errorResponse('Already clocked out for today', 400);
        }

        $late = '00:00:00';
        $isLate = false;
        $status = 'Present';

        if (strtotime($time) > strtotime($startTime)) {
            $lateSeconds = strtotime($time) - strtotime($startTime);
            $hours = floor($lateSeconds / 3600);
            $mins = floor(($lateSeconds % 3600) / 60);
            $secs = $lateSeconds % 60;
            $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            $isLate = true;

            $lateAccessEnabled = $employee->late_access_enabled ?? false;
            $lateAllowedMinutes = $employee->late_allowed_minutes ?? 60;
            $halfDayEnabled = $employee->enable_half_day ?? true;
            $lateMinutes = $lateSeconds / 60;

            if ($halfDayEnabled && $isLate) {
                if ($lateAccessEnabled) {
                    if ($lateMinutes > $lateAllowedMinutes) {
                        $status = 'Half Day';
                    }
                } else {
                    $status = 'Half Day';
                }
            }
        }

        $photoName = null;
        if ($request->has('punch_photo') && !empty($request->punch_photo)) {
            $photoName = $this->saveAttendancePhoto($request->punch_photo, $employee->id, 'clockin');
        }

        if (!$existing) {
            $attendance = new AttendanceEmployee();
            $attendance->employee_id = $employee->id;
            $attendance->date = $date;
            $attendance->created_by = auth()->user() ? auth()->user()->creatorId() : $employee->created_by;
        } else {
            $attendance = $existing;
        }

        $attendance->clock_in = $time;
        $attendance->clock_out = '00:00:00';
        $attendance->status = $status;
        $attendance->late = $late;
        $attendance->early_leaving = '00:00:00';
        $attendance->overtime = '00:00:00';
        $attendance->total_rest = '00:00:00';
        $attendance->marked_by = 'face_recognition';
        $attendance->face_confidence = $request->face_confidence;
        $attendance->latitude = $request->latitude;
        $attendance->longitude = $request->longitude;
        
        if ($photoName) {
            $attendance->punch_photo = $photoName;
        }
        
        $attendance->save();

        return $this->successResponse([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'clock_in_time' => date('h:i A', strtotime($time)),
            'status' => $status,
            'is_late' => $isLate,
            'late_duration' => $late,
            'face_confidence' => $request->face_confidence,
            'location' => [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'mode' => $request->mode ?? 'office',
            ],
        ], 'Clock in successful');
    }

    private function handleClockOut($request, $employee, $existing, $date, $time, $endTime)
    {
        if (!$existing || $existing->clock_in == '00:00:00') {
            return $this->errorResponse('Not clocked in', 400);
        }

        if ($existing->clock_out != '00:00:00') {
            return $this->errorResponse('Already clocked out', 400);
        }

        if (!empty($existing->tea_break_out) && $existing->tea_break_out != '00:00:00'
            && (empty($existing->tea_break_in) || $existing->tea_break_in == '00:00:00')) {
            return $this->errorResponse('Please end your tea break first', 400);
        }

        $earlyLeaving = '00:00:00';
        if (strtotime($time) < strtotime($endTime)) {
            $earlySeconds = strtotime($endTime) - strtotime($time);
            $hours = floor($earlySeconds / 3600);
            $mins = floor(($earlySeconds % 3600) / 60);
            $secs = $earlySeconds % 60;
            $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        }

        $overtime = '00:00:00';
        if (strtotime($time) > strtotime($endTime)) {
            $overtimeSeconds = strtotime($time) - strtotime($endTime);
            $hours = floor($overtimeSeconds / 3600);
            $mins = floor(($overtimeSeconds % 3600) / 60);
            $secs = $overtimeSeconds % 60;
            $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        }

        $photoName = null;
        if ($request->has('punch_photo') && !empty($request->punch_photo)) {
            $photoName = $this->saveAttendancePhoto($request->punch_photo, $employee->id, 'clockout');
        }

        $existing->clock_out = $time;
        $existing->early_leaving = $earlyLeaving;
        $existing->overtime = $overtime;
        $existing->face_confidence = $request->face_confidence;
        $existing->latitude = $request->latitude;
        $existing->longitude = $request->longitude;
        
        if ($photoName) {
            $existing->punch_out_photo = $photoName;
        }
        $existing->save();

        if ($request->has('work_report') && !empty($request->work_report)) {
            $this->saveWorkReportApi($employee->id, $request->work_report, $existing->id);
        }

        $workedSeconds = strtotime($time) - strtotime($existing->clock_in);
        $hours = floor($workedSeconds / 3600);
        $mins = floor(($workedSeconds % 3600) / 60);
        $workedHours = sprintf('%02d:%02d', $hours, $mins);

        return $this->successResponse([
            'attendance_id' => $existing->id,
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'clock_in_time' => date('h:i A', strtotime($existing->clock_in)),
            'clock_out_time' => date('h:i A', strtotime($time)),
            'worked_hours' => $workedHours,
            'status' => $existing->status,
            'is_early_leave' => $earlyLeaving != '00:00:00',
            'overtime' => $overtime,
            'face_confidence' => $request->face_confidence,
            'location' => [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'mode' => $request->mode ?? 'office',
            ],
            'work_report_saved' => $request->has('work_report'),
        ], 'Clock out successful');
    }

    private function handleBreakIn($employee, $existing, $date, $time)
    {
        if (!$existing || $existing->clock_in == '00:00:00') {
            return $this->errorResponse('Not clocked in', 400);
        }

        if ($existing->clock_out != '00:00:00') {
            return $this->errorResponse('Already clocked out', 400);
        }

        if (!empty($existing->tea_break_out) && $existing->tea_break_out != '00:00:00'
            && (empty($existing->tea_break_in) || $existing->tea_break_in == '00:00:00')) {
            return $this->errorResponse('Already on break', 400);
        }

        $existing->tea_break_out = $time;
        $existing->save();

        return $this->successResponse([
            'attendance_id' => $existing->id,
            'employee_name' => $employee->name,
            'break_start_time' => date('h:i A', strtotime($time)),
        ], 'Tea break started');
    }

    private function handleBreakOut($employee, $existing, $date, $time)
    {
        if (!$existing || $existing->clock_in == '00:00:00') {
            return $this->errorResponse('Not clocked in', 400);
        }

        if ($existing->clock_out != '00:00:00') {
            return $this->errorResponse('Already clocked out', 400);
        }

        if (empty($existing->tea_break_out) || $existing->tea_break_out == '00:00:00') {
            return $this->errorResponse('Not on break', 400);
        }

        if (!empty($existing->tea_break_in) && $existing->tea_break_in != '00:00:00') {
            return $this->errorResponse('Break already ended', 400);
        }

        $existing->tea_break_in = $time;
        $existing->save();

        return $this->successResponse([
            'attendance_id' => $existing->id,
            'employee_name' => $employee->name,
            'break_end_time' => date('h:i A', strtotime($time)),
        ], 'Tea break ended');
    }

    private function saveWorkReportApi($employeeId, $report, $attendanceId)
    {
        try {
            if (class_exists('\App\Models\WorkReport')) {
                $workReport = new \App\Models\WorkReport();
                $workReport->employee_id = $employeeId;
                $workReport->attendance_id = $attendanceId;
                $workReport->report = $report;
                $workReport->date = date('Y-m-d');
                $workReport->created_by = auth()->user() ? auth()->user()->id : null;
                $workReport->save();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Save work report API helper error: ' . $e->getMessage());
            return false;
        }
    }

    private function saveFacePhotoFromBase64($base64Image, $employeeId)
    {
        try {
            if (empty($base64Image)) {
                return null;
            }

            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
            $imageInfo = getimagesizefromstring($imageData);
            
            if (!$imageInfo) {
                return null;
            }

            $extension = image_type_to_extension($imageInfo[2], false);
            $filename = 'face_' . $employeeId . '_' . time() . '.' . $extension;
            
            $path = public_path('uploads/face');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            file_put_contents($path . '/' . $filename, $imageData);
            return $filename;

        } catch (\Exception $e) {
            Log::error('Save face photo from base64 error: ' . $e->getMessage());
            return null;
        }
    }

    private function saveAttendancePhoto($photo, $employeeId, $type = 'photo')
    {
        try {
            if (is_string($photo) && strpos($photo, 'data:image') === 0) {
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photo));
                $imageInfo = getimagesizefromstring($imageData);
                
                if (!$imageInfo) {
                    return null;
                }

                $extension = image_type_to_extension($imageInfo[2], false);
                $timestamp = date('Ymd_His');
                $filename = $type . '_' . $employeeId . '_' . $timestamp . '.' . $extension;
                
                $path = public_path('uploads/attendance');
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                file_put_contents($path . '/' . $filename, $imageData);
                return $filename;
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Save attendance photo API error: ' . $e->getMessage());
            return null;
        }
    }

    private function deleteFacePhoto($filename)
    {
        try {
            if (empty($filename)) return false;
            $path = public_path('uploads/face/' . $filename);
            if (file_exists($path)) {
                unlink($path);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Delete face photo error: ' . $e->getMessage());
            return false;
        }
    }

    private function calculateFaceDistance($desc1, $desc2)
    {
        if (count($desc1) !== count($desc2)) {
            return 999;
        }
        
        $sum = 0;
        for ($i = 0; $i < count($desc1); $i++) {
            $diff = $desc1[$i] - $desc2[$i];
            $sum += $diff * $diff;
        }
        return sqrt($sum);
    }

    private function validateDescriptor($descriptor)
    {
        if (!is_array($descriptor) || count($descriptor) === 0) {
            return ['valid' => false, 'message' => 'face_descriptor must be a non-empty array'];
        }

        foreach ($descriptor as $value) {
            if (!is_numeric($value)) {
                return ['valid' => false, 'message' => 'face_descriptor must contain only numeric values'];
            }
        }

        return ['valid' => true, 'message' => 'ok'];
    }

    private function checkLocation($request)
    {
        if ($request->input('mode') == 'remote') {
            return ['success' => true, 'message' => 'Remote attendance allowed'];
        }

        if (!$request->has('latitude') || !$request->has('longitude')) {
            return ['success' => true, 'message' => 'Location not provided, skipping verification'];
        }

        $officeLocation = Utility::getOfficeLocation();
        
        if (!$officeLocation['restriction_enabled']) {
            return ['success' => true, 'message' => 'Location restriction disabled'];
        }

        if (!$officeLocation['latitude'] || !$officeLocation['longitude']) {
            return ['success' => false, 'message' => 'Office location not configured'];
        }

        $distance = Utility::calculateDistance(
            (float) $request->latitude,
            (float) $request->longitude,
            (float) $officeLocation['latitude'],
            (float) $officeLocation['longitude']
        );

        $radius = (float) ($officeLocation['radius'] ?? 300);

        if ($distance <= $radius) {
            return ['success' => true, 'message' => "Within allowed radius", 'distance' => $distance, 'radius' => $radius];
        }

        return ['success' => false, 'message' => "You are " . round($distance, 0) . " meters away. Allowed radius is {$radius}m", 'distance' => $distance, 'radius' => $radius];
    }

    /**
     * ✅ GET VERIFICATION BADGE - Helper method
     */
    private function getVerificationBadgeUnified($attendance)
    {
        if (!$attendance) {
            return 'Not Marked';
        }
        
        $source = $attendance->source ?? 'manual';
        
        if ($source === 'web') {
            if ($attendance->is_verified) {
                return "✅ Face Verified (" . round($attendance->face_confidence ?? 0) . "%)";
            } elseif ($attendance->face_confidence !== null) {
                return "❌ Face Failed (" . round($attendance->face_confidence) . "%)";
            } else {
                return "⚠️ Manual Entry (Web)";
            }
        } elseif ($source === 'flutter') {
            return "📱 Mobile App";
        } else {
            return "📝 Manual Entry";
        }
    }

    private function errorResponse($message, $code = 400, $data = [])
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    private function successResponse($data = [], $message = 'Success')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], 200);
    }
}