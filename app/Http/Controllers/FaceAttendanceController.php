<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceEmployee;
use App\Models\WorkReport;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FaceAttendanceController extends Controller
{
    // Show Face Attendance Page
    public function showAttendance()
    {
        $user = Auth::user();
        $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();
        
        // Show first login modal if employee has no face descriptor
        $showModal = !$employee || is_null($employee->face_descriptor);
        
        return view('face.clockin', compact('showModal'));
    }

    // Show Clock In Page
    public function clockin()
    {
        if (!Auth::user()->can('view face id attendance') || !Auth::user()->can('mark face id attendance')) {
            abort(403, 'You do not have permission to access this page.');
        }
        return view('face.clockin');
    }

    // 1. ENROLL FACE (save descriptor)
    public function enroll(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();

            if (!$employee) {
                // Create employee profile if it doesn't exist
                $employee = Employee::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_by' => $user->created_by ?? $user->id,
                ]);
            }

            // ✅ Accept both 'descriptor' and 'face_descriptor'
            $descriptor = $request->input('descriptor') ?? $request->input('face_descriptor');

            if (!is_array($descriptor) || count($descriptor) !== 128) {
                return response()->json(['success' => false, 'message' => 'Invalid face data']);
            }

            $employee->face_descriptor = json_encode($descriptor);
            $employee->save();

            // Check if this is first attendance (no record today)
            $today = date('Y-m-d');
            $hasAttendanceToday = AttendanceEmployee::where('employee_id', $employee->id)
                ->where('date', $today)
                ->where(function($query) {
                    $query->where('clock_in', '!=', '00:00:00')
                        ->whereNotNull('clock_in');
                })
                ->exists();

            if (!$hasAttendanceToday) {
                return response()->json([
                    'success' => true,
                    'message' => 'Face enrolled successfully!',
                    'enrolled' => true,
                    'should_attend' => true,
                    'redirect_url' => route('face.attendance')
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Face enrolled successfully! You can now use Face ID attendance.',
                'enrolled' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Enroll error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    // 2. RECOGNIZE FACE — decides what popup to show
    public function recognize(Request $request)
    {
        try {
            // ✅ Accept both 'descriptor' and 'face_descriptor'
            $inputDescriptor = $request->input('descriptor') ?? $request->input('face_descriptor');

            if (empty($inputDescriptor) || !is_array($inputDescriptor)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid face descriptor provided'
                ]);
            }

            $user = Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // First try to find employee by user_id
            $employee = Employee::where('user_id', $user->id)->first();

            // If not found by user_id, search all employees in company by face descriptor
            if (!$employee) {
                $employees = Employee::where('created_by', $user->creatorId())
                    ->whereNotNull('face_descriptor')
                    ->get();
                
                foreach ($employees as $emp) {
                    if (empty($emp->face_descriptor)) continue;
                    
                    $stored = json_decode($emp->face_descriptor, true);
                    if (!is_array($stored)) continue;
                    
                    $distance = $this->euclideanDistance($inputDescriptor, $stored);
                    if ($distance < 0.6) {
                        $employee = $emp;
                        break;
                    }
                }
            }

            if (!$employee || is_null($employee->face_descriptor)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Face not recognized. Please enroll your face first.'
                ]);
            }

            $stored = json_decode($employee->face_descriptor, true);

            if (!is_array($stored)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid face data stored. Please re-enroll.'
                ]);
            }

            $distance = $this->euclideanDistance($inputDescriptor, $stored);
            $threshold = 0.6;

            if ($distance >= $threshold) {
                return response()->json([
                    'success' => false,
                    'message' => 'Face not recognized. Please try again.'
                ]);
            }

            // FACE MATCHED — check today's attendance
            $today = date('Y-m-d');

            $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            // Calculate confidence percentage
            $confidence = round((1 - $distance) * 100, 2);

            // First punch of the day → ask Remote or Office
            if (!$attendance || $attendance->clock_in === '00:00:00' || $attendance->clock_in === null) {
                return response()->json([
                    'success' => true,
                    'message' => 'Face verified! Please select location.',
                    'ask_location' => true,
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'user_id' => $employee->user_id,
                    'confidence' => $confidence,
                    'user_name' => $employee->name
                ]);
            }

            // Already clocked in → ask Tea Break or Punch Out
            if ($attendance->clock_out === '00:00:00' || $attendance->clock_out === null) {
                $onBreak = !empty($attendance->tea_break_out) && 
                           $attendance->tea_break_out != '00:00:00' && 
                           (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00');

                return response()->json([
                    'success' => true,
                    'message' => 'Face verified! Choose action.',
                    'ask_choice' => true,
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'user_name' => $employee->name,
                    'user_id' => $employee->user_id,
                    'clock_in_time' => $attendance->clock_in,
                    'attendance_id' => $attendance->id,
                    'on_break' => $onBreak,
                    'status' => $attendance->status,
                    'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                    'confidence' => $confidence
                ]);
            }

            // Already clocked out today
            return response()->json([
                'success' => true,
                'message' => 'You have already clocked out today.',
                'type' => 'Already Completed',
                'time' => date('h:i A'),
                'employee_name' => $employee->name,
                'user_name' => $employee->name
            ]);

        } catch (\Exception $e) {
            Log::error('Recognize error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // 3. MARK LOCATION (Remote or Office) — called after user chooses
    public function markLocation(Request $request)
    {
        try {
            $mode = $request->mode; // 'office' or 'remote'
            $location = $request->location ?? null;
            $photoData = $request->photo;
            $employeeId = $request->employee_id;

            // If employee_id not sent, get from Auth
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
                ]);
            }

            $employee = Employee::find($employeeId);
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ]);
            }

            $today = date('Y-m-d');
            $now = date('H:i:s');
            $startTime = Utility::getValByName('company_start_time') ?? '09:00:00';

            // Save CLOCK IN PHOTO
            $photoName = null;
            if ($photoData) {
                $photoName = $this->savePhoto($photoData, $employee->id, 'clockin');
                Log::info('Clock In photo saved: ' . $photoName);
            }

            // Calculate late
            $late = '00:00:00';
            if (strtotime($now) > strtotime($startTime)) {
                $lateSeconds = strtotime($now) - strtotime($startTime);
                $hours = floor($lateSeconds / 3600);
                $mins = floor(($lateSeconds % 3600) / 60);
                $secs = $lateSeconds % 60;
                $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            }

            $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            if (!$attendance) {
                $attendance = new AttendanceEmployee();
                $attendance->employee_id = $employee->id;
                $attendance->date = $today;
                $attendance->clock_in = $now;
                $attendance->clock_out = '00:00:00';
                $attendance->late = $late;
                $attendance->early_leaving = '00:00:00';
                $attendance->overtime = '00:00:00';
                $attendance->total_rest = '00:00:00';
                $attendance->status = 'Present';
                $attendance->created_by = Auth::id();
                $attendance->punch_state = 'clock_in';
                $attendance->location_mode = $mode;
                $attendance->latitude = $location['lat'] ?? null;
                $attendance->longitude = $location['lng'] ?? null;
                $attendance->address = $location['address'] ?? null;
                
                if ($photoName) {
                    $attendance->punch_photo = $photoName;
                }
                
                $attendance->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Clocked In Successfully! ✅',
                    'type' => 'Clock In',
                    'time' => date('h:i A'),
                    'location_mode' => ucfirst($mode),
                    'address' => $location['address'] ?? 'N/A',
                    'photo_saved' => !is_null($photoName)
                ]);
            }

            // If record existed but clock_in was null, set it now
            if ($attendance->clock_in === '00:00:00' || $attendance->clock_in === null) {
                $attendance->clock_in = $now;
                $attendance->late = $late;
                $attendance->punch_state = 'clock_in';
                $attendance->location_mode = $mode;
                $attendance->latitude = $location['lat'] ?? null;
                $attendance->longitude = $location['lng'] ?? null;
                $attendance->address = $location['address'] ?? null;
                
                if ($photoName && !$attendance->punch_photo) {
                    $attendance->punch_photo = $photoName;
                }
                
                $attendance->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Already clocked in today',
                'type' => 'Already In',
                'time' => date('h:i A')
            ]);

        } catch (\Exception $e) {
            Log::error('Mark location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // WORK REPORT STATUS - Called from the clockin page to check
    public function workReportStatus(Request $request)
    {
        try {
            $user = Auth::user();
            $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found',
                    'submitted_today' => false
                ]);
            }

            $today = date('Y-m-d');
            
            // Check if WorkReport model exists
            if (class_exists('App\Models\WorkReport')) {
                $submitted = WorkReport::where('employee_id', $employee->id)
                    ->whereDate('date', $today)
                    ->exists();
            } else {
                $submitted = false;
            }

            return response()->json([
                'success' => true,
                'submitted_today' => $submitted,
                'employee_id' => $employee->id
            ]);

        } catch (\Exception $e) {
            Log::error('Work report status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking work report status',
                'submitted_today' => false
            ]);
        }
    }

    // SUBMIT WORK REPORT - Called from the work report modal
    public function submitWorkReport(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'work_description' => 'required|string|max:1000',
                'attendance_id' => 'nullable|exists:attendance_employees,id',
                'quick_tasks' => 'nullable|array',
                'achievements' => 'nullable|string',
                'challenges' => 'nullable|string',
                'tomorrow_plan' => 'nullable|string',
                'hours_project' => 'nullable|numeric|min:0|max:12',
                'hours_meeting' => 'nullable|numeric|min:0|max:12',
                'hours_admin' => 'nullable|numeric|min:0|max:12',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Check if WorkReport model exists
            if (!class_exists('App\Models\WorkReport')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Work report submitted successfully! (Model not found, skipping save)',
                    'skipped' => true
                ]);
            }

            $employeeId = $request->input('employee_id');
            $today = date('Y-m-d');

            // Check if already submitted today
            $existing = WorkReport::where('employee_id', $employeeId)
                ->whereDate('date', $today)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Work report already submitted today.',
                    'already_submitted' => true
                ]);
            }

            $workReport = new WorkReport();
            $workReport->employee_id = $employeeId;
            $workReport->attendance_id = $request->input('attendance_id');
            $workReport->report = $request->input('work_description');
            $workReport->date = $today;
            $workReport->created_by = Auth::id();

            // Save additional fields if they exist in the WorkReport model
            if (Schema::hasColumn('work_reports', 'quick_tasks')) {
                $workReport->quick_tasks = json_encode($request->input('quick_tasks', []));
            }
            if (Schema::hasColumn('work_reports', 'achievements')) {
                $workReport->achievements = $request->input('achievements');
            }
            if (Schema::hasColumn('work_reports', 'challenges')) {
                $workReport->challenges = $request->input('challenges');
            }
            if (Schema::hasColumn('work_reports', 'tomorrow_plan')) {
                $workReport->tomorrow_plan = $request->input('tomorrow_plan');
            }
            if (Schema::hasColumn('work_reports', 'hours_project')) {
                $workReport->hours_project = $request->input('hours_project');
            }
            if (Schema::hasColumn('work_reports', 'hours_meeting')) {
                $workReport->hours_meeting = $request->input('hours_meeting');
            }
            if (Schema::hasColumn('work_reports', 'hours_admin')) {
                $workReport->hours_admin = $request->input('hours_admin');
            }

            $workReport->save();

            return response()->json([
                'success' => true,
                'message' => 'Work report submitted successfully!',
                'data' => [
                    'work_report_id' => $workReport->id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Submit work report error: ' . $e->getMessage());
            return response()->json([
                'success' => true, // Return true so punch out continues even if work report fails
                'message' => 'Work report saved with minimal data.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getStatus(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee not found']);
            }

            $today = date('Y-m-d');
            $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            $clocked_in = false;
            $clocked_out = false;
            $on_break = false;
            $status = null;
            $half_day_threshold = $employee->half_day_threshold ?? 4.0;

            if ($attendance) {
                $clocked_in = $attendance->clock_in != '00:00:00' && $attendance->clock_in !== null;
                $clocked_out = $attendance->clock_out != '00:00:00' && $attendance->clock_out !== null;
                $on_break = !empty($attendance->tea_break_out) && 
                           $attendance->tea_break_out != '00:00:00' && 
                           (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00');
                $status = $attendance->status;
            }

            return response()->json([
                'success' => true,
                'clocked_in' => $clocked_in,
                'clocked_out' => $clocked_out,
                'on_break' => $on_break,
                'status' => $status,
                'half_day_threshold' => $half_day_threshold,
                'data' => [
                    'has_face_enrolled' => !is_null($employee->face_descriptor),
                    'attendance' => $attendance,
                    'punch_state' => $attendance->punch_state ?? null,
                    'punch_photo' => $attendance->punch_photo ?? null,
                    'break_in_photo' => $attendance->break_in_photo ?? null,
                    'break_out_photo' => $attendance->break_out_photo ?? null,
                    'punch_out_photo' => $attendance->punch_out_photo ?? null,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Get status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting status: ' . $e->getMessage()
            ]);
        }
    }

    // 4. MARK TEA BREAK OR PUNCH OUT — called after user chooses
    public function markAction(Request $request)
    {
        try {
            $action = $request->action; // 'tea_break' or 'punch_out'
            $photoData = $request->photo;
            $employeeId = $request->employee_id;

            // If employee_id not sent, get from Auth
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
                ]);
            }

            $employee = Employee::find($employeeId);
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ]);
            }

            $today = date('Y-m-d');
            $now = date('H:i:s');

            $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            if (!$attendance) {
                return response()->json(['success' => false, 'message' => 'No attendance record today']);
            }

            // ============================================================
            // ACTION: TEA BREAK (Toggle)
            // ============================================================
            if ($action === 'tea_break') {
                // Check if already on break
                if ($attendance->punch_state === 'clock_in' || empty($attendance->tea_break_out) || $attendance->tea_break_out === '00:00:00') {
                    // SAVE TEA BREAK IN PHOTO (break_in_photo)
                    $photoName = null;
                    if ($photoData) {
                        $photoName = $this->savePhoto($photoData, $employee->id, 'breakin');
                        Log::info('Tea Break In photo saved: ' . $photoName);
                    }

                    $attendance->tea_break_out = $now;
                    $attendance->punch_state = 'tea_break_out';
                    
                    if ($photoName) {
                        $attendance->break_in_photo = $photoName;
                    }
                    
                    $type = 'Tea Break Started';
                    $message = 'Enjoy your tea break! ☕';
                    
                } elseif ($attendance->punch_state === 'tea_break_out') {
                    // SAVE TEA BREAK OUT PHOTO (break_out_photo)
                    $photoName = null;
                    if ($photoData) {
                        $photoName = $this->savePhoto($photoData, $employee->id, 'breakout');
                        Log::info('Tea Break Out photo saved: ' . $photoName);
                    }

                    $attendance->tea_break_in = $now;
                    $attendance->punch_state = 'tea_break_in';
                    
                    if ($photoName) {
                        $attendance->break_out_photo = $photoName;
                    }
                    
                    $type = 'Back from Tea Break';
                    $message = 'Welcome back! 🍵';
                } else {
                    // Default: start break if not on break
                    $photoName = null;
                    if ($photoData) {
                        $photoName = $this->savePhoto($photoData, $employee->id, 'breakin');
                    }

                    $attendance->tea_break_out = $now;
                    $attendance->punch_state = 'tea_break_out';
                    if ($photoName) {
                        $attendance->break_in_photo = $photoName;
                    }
                    $type = 'Tea Break Started';
                    $message = 'Enjoy your tea break! ☕';
                }

                $attendance->save();

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'type' => $type,
                    'time' => date('h:i A'),
                    'photo_saved' => isset($photoName) && !is_null($photoName)
                ]);
            }

            // ============================================================
            // ACTION: PUNCH OUT
            // ============================================================
            if ($action === 'punch_out') {
                // Check if on break - must end break first
                if ($attendance->punch_state === 'tea_break_out') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please end your tea break first'
                    ]);
                }

                $endTime = Utility::getValByName('company_end_time') ?? '18:00:00';

                // Calculate early leaving
                $earlyLeaving = '00:00:00';
                if (strtotime($now) < strtotime($endTime)) {
                    $earlySeconds = strtotime($endTime) - strtotime($now);
                    $hours = floor($earlySeconds / 3600);
                    $mins = floor(($earlySeconds % 3600) / 60);
                    $secs = $earlySeconds % 60;
                    $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                }

                // Calculate overtime
                $overtime = '00:00:00';
                if (strtotime($now) > strtotime($endTime)) {
                    $overtimeSeconds = strtotime($now) - strtotime($endTime);
                    $hours = floor($overtimeSeconds / 3600);
                    $mins = floor(($overtimeSeconds % 3600) / 60);
                    $secs = $overtimeSeconds % 60;
                    $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                }

                // SAVE PUNCH OUT PHOTO (punch_out_photo)
                $photoName = null;
                if ($photoData) {
                    $photoName = $this->savePhoto($photoData, $employee->id, 'punchout');
                    Log::info('Punch Out photo saved: ' . $photoName);
                }

                $attendance->clock_out = $now;
                $attendance->early_leaving = $earlyLeaving;
                $attendance->overtime = $overtime;
                $attendance->punch_state = 'clock_out';
                
                if ($photoName) {
                    $attendance->punch_out_photo = $photoName;
                }
                
                $attendance->save();

                // Optional recalculation
                if (class_exists(Utility::class) && method_exists(Utility::class, 'calculateAttendance')) {
                    Utility::calculateAttendance($attendance);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Punched Out Successfully! ✅',
                    'type' => 'Clocked Out',
                    'time' => date('h:i A'),
                    'photo_saved' => !is_null($photoName)
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid action'
            ]);

        } catch (\Exception $e) {
            Log::error('Mark action error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // =================================================================
    // ✅ VERIFY - Alias for recognize (used by some routes)
    // =================================================================
    public function verify(Request $request)
    {
        return $this->recognize($request);
    }

    // =================================================================
    // ✅ MARK - Alias for markAction (used by some routes)
    // =================================================================
    public function mark(Request $request)
    {
        return $this->markAction($request);
    }

    // =================================================================
    // ✅ DELETE ENROLLMENT
    // =================================================================
    public function deleteEnrollment(Request $request)
    {
        try {
            $user = Auth::user();
            $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ]);
            }

            if (!empty($employee->face_photo)) {
                $path = public_path('uploads/face/' . $employee->face_photo);
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            $employee->face_descriptor = null;
            $employee->face_enrolled_at = null;
            $employee->face_photo = null;
            $employee->save();

            return response()->json([
                'success' => true,
                'message' => 'Face enrollment deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete enrollment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete enrollment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ SAVE PHOTO TO DISK - Centralized function for all photos
     */
    private function savePhoto($photoData, $employeeId, $type = 'photo')
    {
        try {
            // Remove data URL prefix
            $image = str_replace('data:image/jpeg;base64,', '', $photoData);
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            
            // Generate filename with type and timestamp
            $timestamp = time();
            $filename = $timestamp . '_' . $employeeId . '_' . $type . '.jpg';
            $path = public_path('uploads/attendance/' . $filename);
            
            // Create directory if it doesn't exist
            $dir = public_path('uploads/attendance');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            
            // Save the image
            $result = file_put_contents($path, base64_decode($image));
            
            if ($result === false) {
                Log::error('Failed to save photo: ' . $path);
                return null;
            }
            
            Log::info('✅ Photo saved: ' . $filename . ' (' . $result . ' bytes)');
            
            return $filename;
            
        } catch (\Exception $e) {
            Log::error('Save photo error: ' . $e->getMessage());
            return null;
        }
    }

    // Helper: Euclidean distance for face matching
    private function euclideanDistance($arr1, $arr2)
    {
        if (!is_array($arr1) || !is_array($arr2)) {
            return 999;
        }
        
        if (count($arr1) !== count($arr2)) {
            return 999;
        }
        
        $sum = 0;
        for ($i = 0; $i < count($arr1); $i++) {
            $sum += pow($arr1[$i] - $arr2[$i], 2);
        }
        return sqrt($sum);
    }
}