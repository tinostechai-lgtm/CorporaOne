<?php

namespace App\Http\Controllers;

use App\Imports\AttendanceImport;
use App\Models\AttendanceEmployee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\IpRestrict;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AttendanceEmployeeController extends Controller
{
    // =================================================================
    // INDEX - List Attendance
    // =================================================================
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage attendance')) {

            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('Select Department', '');

            if (\Auth::user()->type != 'client' && \Auth::user()->type != 'company') {

                $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

                $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year = date('Y', strtotime($request->month));

                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween('date', [$start_date, $end_date]);
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {
                    $month = date('m');
                    $year = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween('date', [$start_date, $end_date]);
                }
                $attendanceEmployee = $attendanceEmployee->get();

            } else {

                $employee = Employee::select('id')->where('created_by', \Auth::user()->creatorId());
                if (!empty($request->branch)) {
                    $employee->where('branch_id', $request->branch);
                }

                if (!empty($request->department)) {
                    $employee->where('department_id', $request->department);
                }

                $employee = $employee->get()->pluck('id');

                $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee);
                if ($request->type == 'monthly' && !empty($request->month)) {

                    $month = date('m', strtotime($request->month));
                    $year = date('Y', strtotime($request->month));
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    $attendanceEmployee->whereBetween('date', [$start_date, $end_date]);
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {

                    $month = date('m');
                    $year = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    $attendanceEmployee->whereBetween('date', [$start_date, $end_date]);
                }

                $attendanceEmployee = $attendanceEmployee->get();
            }

            return view('attendance.index', compact('attendanceEmployee', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // =================================================================
    // CREATE - Show Create Form
    // =================================================================
    public function create()
    {
        if (\Auth::user()->can('create attendance')) {
            $employees = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '=', "employee")->get()->pluck('name', 'id');

            return view('attendance.create', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // =================================================================
    // STORE - Save Attendance
    // =================================================================
    public function store(Request $request)
    {
        if (\Auth::user()->can('create attendance')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'date' => 'required',
                    'clock_in' => 'required',
                    'clock_out' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $startTime = Utility::getValByName('company_start_time');
            $endTime = Utility::getValByName('company_end_time');
            $attendance = AttendanceEmployee::where('employee_id', '=', $request->employee_id)->where('date', '=', $request->date)->where('clock_out', '=', '00:00:00')->get()->toArray();
            if ($attendance) {
                return redirect()->route('attendanceemployee.index')->with('error', __('Employee Attendance Already Created.'));
            } else {
                $date = date("Y-m-d");

                $employee = Employee::find($request->employee_id);
                $lateAccessEnabled = $employee ? ($employee->late_access_enabled ?? false) : false;
                $lateAllowedMinutes = $employee ? ($employee->late_allowed_minutes ?? 60) : 60;
                $halfDayEnabled = $employee ? ($employee->enable_half_day ?? true) : true;

                $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . $startTime);
                $hours = floor($totalLateSeconds / 3600);
                $mins = floor($totalLateSeconds / 60 % 60);
                $secs = floor($totalLateSeconds % 60);
                $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($request->clock_out);
                $hours = floor($totalEarlyLeavingSeconds / 3600);
                $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                if (strtotime($request->clock_out) > strtotime($date . $endTime)) {
                    $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . $endTime);
                    $hours = floor($totalOvertimeSeconds / 3600);
                    $mins = floor($totalOvertimeSeconds / 60 % 60);
                    $secs = floor($totalOvertimeSeconds % 60);
                    $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                } else {
                    $overtime = '00:00:00';
                }

                $status = 'Present';
                if ($halfDayEnabled) {
                    $lateMinutes = $totalLateSeconds / 60;
                    if ($totalLateSeconds > 0) {
                        if ($lateAccessEnabled) {
                            if ($lateMinutes > $lateAllowedMinutes) {
                                $status = 'Half Day';
                            }
                        } else {
                            $status = 'Half Day';
                        }
                    }
                }

                $employeeAttendance = new AttendanceEmployee();
                $employeeAttendance->employee_id = $request->employee_id;
                $employeeAttendance->date = $request->date;
                $employeeAttendance->status = $status;
                $employeeAttendance->clock_in = $request->clock_in . ':00';
                $employeeAttendance->clock_out = $request->clock_out . ':00';
                $employeeAttendance->late = $late;
                $employeeAttendance->early_leaving = $earlyLeaving;
                $employeeAttendance->overtime = $overtime;
                $employeeAttendance->total_rest = '00:00:00';
                $employeeAttendance->created_by = \Auth::user()->creatorId();
                $employeeAttendance->save();

                return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully created.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // =================================================================
    // SHOW - Redirect to Index
    // =================================================================
    public function show()
    {
        return redirect()->route('attendanceemployee.index');
    }

    // =================================================================
    // EDIT - Show Edit Form
    // =================================================================
    public function edit($id)
    {
        if (\Auth::user()->can('edit attendance')) {
            $attendanceEmployee = AttendanceEmployee::where('id', $id)->first();
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('attendance.edit', compact('attendanceEmployee', 'employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // =================================================================
    // UPDATE - Update Attendance
    // =================================================================
    public function update(Request $request, $id)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'HR') {
            $employeeId = AttendanceEmployee::where('employee_id', $request->employee_id)->first();
            $check = AttendanceEmployee::where('id', $id)->where('employee_id', '=', $request->employee_id)->where('date', $request->date)->first();

            $startTime = Utility::getValByName('company_start_time');
            $endTime = Utility::getValByName('company_end_time');

            $clockIn = $request->clock_in;
            $clockOut = $request->clock_out;

            if ($clockIn) {
                $status = "present";
            } else {
                $status = "leave";
            }

            $employee = Employee::find($request->employee_id);
            $lateAccessEnabled = $employee ? ($employee->late_access_enabled ?? false) : false;
            $lateAllowedMinutes = $employee ? ($employee->late_allowed_minutes ?? 60) : 60;
            $halfDayEnabled = $employee ? ($employee->enable_half_day ?? true) : true;

            $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);
            $hours = floor($totalLateSeconds / 3600);
            $mins = floor($totalLateSeconds / 60 % 60);
            $secs = floor($totalLateSeconds % 60);
            $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
            $hours = floor($totalEarlyLeavingSeconds / 3600);
            $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            if (strtotime($clockOut) > strtotime($endTime)) {
                $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
                $hours = floor($totalOvertimeSeconds / 3600);
                $mins = floor($totalOvertimeSeconds / 60 % 60);
                $secs = floor($totalOvertimeSeconds % 60);
                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            $attendanceStatus = 'Present';
            if ($halfDayEnabled) {
                $lateMinutes = $totalLateSeconds / 60;
                if ($totalLateSeconds > 0) {
                    if ($lateAccessEnabled) {
                        if ($lateMinutes > $lateAllowedMinutes) {
                            $attendanceStatus = 'Half Day';
                        }
                    } else {
                        $attendanceStatus = 'Half Day';
                    }
                }
            }

            if ($check->date == date('Y-m-d')) {
                $check->update([
                    'late' => $late,
                    'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                    'overtime' => $overtime,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'status' => $attendanceStatus,
                ]);

                return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
            } else {
                return redirect()->route('attendanceemployee.index')->with('error', __('you can only update current day attendance.'));
            }
        }

        $employeeId = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();

        $startTime = Utility::getValByName('company_start_time');
        $endTime = Utility::getValByName('company_end_time');

        if (Auth::user()->type == 'Employee') {

            $date = date("Y-m-d");
            $time = date("H:i:s");
            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
            $hours = floor($totalEarlyLeavingSeconds / 3600);
            $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            if (time() > strtotime($date . $endTime)) {
                $totalOvertimeSeconds = time() - strtotime($date . $endTime);
                $hours = floor($totalOvertimeSeconds / 3600);
                $mins = floor($totalOvertimeSeconds / 60 % 60);
                $secs = floor($totalOvertimeSeconds % 60);
                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            $attendanceEmployee['clock_out'] = $time;
            $attendanceEmployee['early_leaving'] = $earlyLeaving;
            $attendanceEmployee['overtime'] = $overtime;

            if (!empty($request->date)) {
                $attendanceEmployee['date'] = $request->date;
            }
            AttendanceEmployee::where('id', $id)->update($attendanceEmployee);

            return redirect()->route('hrm.dashboard')->with('success', __('Employee successfully clock Out.'));
        } else {
            $date = date("Y-m-d");
            $clockout_time = date("H:i:s");
            $totalLateSeconds = strtotime($clockout_time) - strtotime($date . $startTime);

            $hours = abs(floor($totalLateSeconds / 3600));
            $mins = abs(floor($totalLateSeconds / 60 % 60));
            $secs = abs(floor($totalLateSeconds % 60));

            $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($clockout_time);
            $hours = floor($totalEarlyLeavingSeconds / 3600);
            $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            if (strtotime($clockout_time) > strtotime($date . $endTime)) {
                $totalOvertimeSeconds = strtotime($clockout_time) - strtotime($date . $endTime);
                $hours = floor($totalOvertimeSeconds / 3600);
                $mins = floor($totalOvertimeSeconds / 60 % 60);
                $secs = floor($totalOvertimeSeconds % 60);
                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            $attendanceEmployee = AttendanceEmployee::find($id);
            $attendanceEmployee->clock_out = $clockout_time;
            $attendanceEmployee->late = $late;
            $attendanceEmployee->early_leaving = $earlyLeaving;
            $attendanceEmployee->overtime = $overtime;
            $attendanceEmployee->total_rest = '00:00:00';

            $attendanceEmployee->save();

            return redirect()->back()->with('success', __('Employee attendance successfully updated.'));
        }
    }

    // =================================================================
    // DESTROY - Delete Attendance
    // =================================================================
    public function destroy($id)
    {
        if (\Auth::user()->can('delete attendance')) {
            $attendance = AttendanceEmployee::where('id', $id)->first();
            $attendance->delete();

            return redirect()->route('attendanceemployee.index')->with('success', __('Attendance successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // =================================================================
    // ATTENDANCE - Clock In/Out with Location Validation & Late Access (FIXED)
    // =================================================================
    public function attendance(Request $request)
    {
        try {
            $settings = Utility::settings();

            if ($settings['ip_restrict'] == 'on') {
                $userIp = request()->ip();
                $ip = IpRestrict::where('created_by', \Auth::user()->creatorId())->whereIn('ip', [$userIp])->first();
                if (!empty($ip)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This IP is not allowed to clock in & clock out.'
                    ]);
                }
            }
            
            $employeeId = $request->input('employee_id', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0);
            
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
            $action = $request->input('action', 'clock_in');

            $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                            ->where('date', $today)
                            ->first();

            $startTime = Utility::getValByName('company_start_time') ?? '09:00:00';
            $endTime = Utility::getValByName('company_end_time') ?? '18:00:00';

            // ============================================================
            // ✅ FIXED: LOCATION VALIDATION - Using checkLocation() helper
            // ============================================================
            $locationValid = $this->checkLocation($request);
            
            // If location validation fails, return error immediately
            if (!$locationValid['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $locationValid['message'],
                    'distance' => $locationValid['distance'] ?? null,
                    'required_radius' => $locationValid['radius'] ?? 300,
                ], 400);
            }

            // ============================================================
            // ACTION: CLOCK IN - WITH LATE ACCESS LOGIC
            // ============================================================
            if ($action == 'clock_in') {
                if ($attendance && $attendance->clock_in != '00:00:00' && $attendance->clock_out == '00:00:00') {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are already clocked in.'
                    ]);
                }

                // Calculate late duration
                $late = '00:00:00';
                $isLate = false;
                $status = 'Present';
                $lateSeconds = 0;
                $lateMinutes = 0;
                
                if (strtotime($now) > strtotime($startTime)) {
                    $lateSeconds = strtotime($now) - strtotime($startTime);
                    $lateMinutes = $lateSeconds / 60;
                    $hours = floor($lateSeconds / 3600);
                    $mins = floor(($lateSeconds % 3600) / 60);
                    $secs = $lateSeconds % 60;
                    $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    $isLate = true;
                }

                // Get employee late access settings
                $lateAccessEnabled = $employee->late_access_enabled ?? false;
                $lateAllowedMinutes = $employee->late_allowed_minutes ?? 60;
                $halfDayEnabled = $employee->enable_half_day ?? true;

                // Determine status based on late access
                if ($isLate && $halfDayEnabled) {
                    if ($lateAccessEnabled) {
                        if ($lateMinutes > $lateAllowedMinutes) {
                            $status = 'Half Day';
                        } else {
                            $status = 'Present';
                        }
                    } else {
                        $status = 'Half Day';
                    }
                } else {
                    $status = 'Present';
                }

                $photoName = null;
                if ($request->hasFile('punch_photo')) {
                    $photoName = $this->saveAttendancePhoto($request->file('punch_photo'), $employeeId, 'clockin');
                }

                if (!$attendance) {
                    $attendance = new AttendanceEmployee();
                    $attendance->employee_id = $employeeId;
                    $attendance->date = $today;
                    $attendance->created_by = \Auth::user()->creatorId();
                }

                $attendance->clock_in = $now;
                $attendance->clock_out = '00:00:00';
                $attendance->late = $late;
                $attendance->early_leaving = '00:00:00';
                $attendance->overtime = '00:00:00';
                $attendance->total_rest = '00:00:00';
                $attendance->status = $status;
                
                // Save location data
                $attendance->latitude = $request->input('latitude');
                $attendance->longitude = $request->input('longitude');
                
                if (Schema::hasColumn('attendance_employees', 'marked_by')) {
                    $attendance->marked_by = 'face_recognition';
                }
                
                if ($photoName) {
                    $attendance->punch_photo = $photoName;
                }
                
                $attendance->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Employee Successfully Clocked In. ✅',
                    'clock_in_time' => $now,
                    'employee_name' => $employee->name,
                    'status' => $status,
                    'is_late' => $isLate,
                    'late_duration' => $late,
                    'location' => [
                        'latitude' => $request->input('latitude'),
                        'longitude' => $request->input('longitude'),
                        'mode' => $request->input('mode', 'office'),
                        'distance_from_office' => $locationValid['distance'] ?? null,
                    ],
                    'late_access_enabled' => $lateAccessEnabled,
                    'late_allowed_minutes' => $lateAllowedMinutes,
                ]);
            }

            // ============================================================
            // ACTION: TEA BREAK IN (Start Break)
            // ============================================================
            if ($action == 'tea_break_in' || $action == 'tea_break') {
                if (!$attendance || $attendance->clock_in == '00:00:00') {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not clocked in. Please clock in first.'
                    ]);
                }

                if ($attendance->clock_out != '00:00:00') {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are already clocked out.'
                    ]);
                }

                if (!empty($attendance->tea_break_out) && $attendance->tea_break_out != '00:00:00' 
                    && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are already on break. Please end your break first.'
                    ]);
                }

                $photoName = null;
                if ($request->hasFile('break_photo')) {
                    $photoName = $this->saveAttendancePhoto($request->file('break_photo'), $employeeId, 'breakin');
                }

                $attendance->tea_break_out = $now;
                if ($photoName) {
                    $attendance->break_in_photo = $photoName;
                }
                $attendance->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Tea Break Started ☕',
                    'break_time' => $now,
                    'employee_name' => $employee->name
                ]);
            }

            // ============================================================
            // ACTION: TEA BREAK OUT (End Break)
            // ============================================================
            if ($action == 'tea_break_out') {
                if (!$attendance || $attendance->clock_in == '00:00:00') {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not clocked in.'
                    ]);
                }

                if ($attendance->clock_out != '00:00:00') {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are already clocked out.'
                    ]);
                }

                if (empty($attendance->tea_break_out) || $attendance->tea_break_out == '00:00:00') {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not on break.'
                    ]);
                }

                $photoName = null;
                if ($request->hasFile('break_photo')) {
                    $photoName = $this->saveAttendancePhoto($request->file('break_photo'), $employeeId, 'breakout');
                }

                $attendance->tea_break_in = $now;
                if ($photoName) {
                    $attendance->break_out_photo = $photoName;
                }
                $attendance->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Tea Break Ended 🍵',
                    'break_end_time' => $now,
                    'employee_name' => $employee->name
                ]);
            }

            // ============================================================
            // ACTION: PUNCH OUT with Work Report Popup
            // ============================================================
            if ($action == 'punch_out') {
                if (!$attendance || $attendance->clock_in == '00:00:00') {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not clocked in.'
                    ]);
                }

                if ($attendance->clock_out != '00:00:00') {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are already clocked out.'
                    ]);
                }

                if (!empty($attendance->tea_break_out) && $attendance->tea_break_out != '00:00:00' 
                    && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please end your tea break first before punching out.'
                    ]);
                }

                $status = $attendance->status ?? 'Present';

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

                // Save Punch Out Photo
                $photoName = null;
                if ($request->hasFile('punch_photo')) {
                    $photoName = $this->saveAttendancePhoto($request->file('punch_photo'), $employeeId, 'punchout');
                }

                // Update attendance record
                $attendance->clock_out = $now;
                $attendance->early_leaving = $earlyLeaving;
                $attendance->overtime = $overtime;
                if ($photoName) {
                    $attendance->punch_out_photo = $photoName;
                }
                $attendance->save();

                // Check if work report already submitted today
                $workReportExists = \App\Models\WorkReport::where('employee_id', $employeeId)
                                    ->whereDate('created_at', $today)
                                    ->exists();

                return response()->json([
                    'success' => true,
                    'message' => 'Employee Successfully Clocked Out. ✅',
                    'clock_out_time' => $now,
                    'status' => $status,
                    'employee_name' => $employee->name,
                    'employee_id' => $employee->id,
                    'attendance_id' => $attendance->id,
                    'date' => $today,
                    'clock_in' => $attendance->clock_in,
                    'clock_out' => $now,
                    'show_work_report' => !$workReportExists,
                    'work_report_already_submitted' => $workReportExists,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid action.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Attendance action error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // =================================================================
    // SAVE ATTENDANCE PHOTO - Centralized (UPDATED with base64 support)
    // =================================================================
    private function saveAttendancePhoto($photo, $employeeId, $type = 'photo')
    {
        try {
            // Handle base64 image string
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
                Log::info('✅ Attendance photo saved (base64): ' . $filename);
                return $filename;
            }

            // Handle uploaded file
            if ($photo instanceof \Illuminate\Http\UploadedFile) {
                $timestamp = time();
                $extension = $photo->getClientOriginalExtension();
                $filename = $timestamp . '_' . $employeeId . '_' . $type . '.' . $extension;
                $path = public_path('uploads/attendance');
                
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                
                $photo->move($path, $filename);
                Log::info('✅ Attendance photo saved (upload): ' . $filename);
                return $filename;
            }

            return null;
            
        } catch (\Exception $e) {
            Log::error('Save attendance photo error: ' . $e->getMessage());
            return null;
        }
    }

    // =================================================================
    // SAVE FACE PHOTO - For enrollment
    // =================================================================
    private function saveFacePhoto($photo, $employeeId)
    {
        try {
            // Handle base64 image
            if (is_string($photo) && strpos($photo, 'data:image') === 0) {
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photo));
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
            }
            
            // Handle uploaded file
            if ($photo instanceof \Illuminate\Http\UploadedFile) {
                $filename = 'face_' . $employeeId . '_' . time() . '.' . $photo->getClientOriginalExtension();
                $path = public_path('uploads/face');
                
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                
                $photo->move($path, $filename);
                return $filename;
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Save face photo error: ' . $e->getMessage());
            return null;
        }
    }

    // =================================================================
    // DELETE FACE PHOTO
    // =================================================================
    private function deleteFacePhoto($filename)
    {
        try {
            if (empty($filename)) {
                return false;
            }
            
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

    // =================================================================
    // DAILY - Daily Attendance View
    // =================================================================
    public function daily(Request $request)
    {
        if (!\Auth::user()->can('manage attendance')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $currentUser = \Auth::user();
        $companyId = $currentUser->creatorId();
        $date = $request->date ?? date('Y-m-d');
        $startTime = Utility::getValByName('company_start_time') ?? '09:00:00';
        $endTime = Utility::getValByName('company_end_time') ?? '18:00:00';

        $branches = Branch::where('created_by', $companyId)->get();
        $departments = Department::where('created_by', $companyId)->get();

        $employeesQuery = Employee::where('created_by', $companyId);
        
        if ($request->filled('branch')) {
            $employeesQuery->where('branch_id', $request->branch);
        }
        if ($request->filled('department')) {
            $employeesQuery->where('department_id', $request->department);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $employeesQuery->where('name', 'like', '%' . $search . '%');
        }
        
        $employees = $employeesQuery->get();
        $employeeIds = $employees->pluck('id')->toArray();

        $attendances = AttendanceEmployee::whereIn('employee_id', $employeeIds)
                        ->whereDate('date', $date)
                        ->get()
                        ->keyBy('employee_id');

        $employeeStatuses = collect();
        $statusCounts = [
            'all' => 0,
            'in' => 0,
            'out' => 0,
            'not_punched' => 0,
            'break' => 0,
            'late' => 0,
            'early_leave' => 0,
            'half_day' => 0,
        ];

        foreach ($employees as $employee) {
            $attendance = $attendances->get($employee->id);
            $status = $this->getAttendanceStatus($attendance, $startTime, $endTime);
            
            $statusCounts['all']++;
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
            
            $isHalfDay = false;
            if ($attendance && $attendance->status == 'Half Day') {
                $isHalfDay = true;
                $statusCounts['half_day']++;
            }

            $user = $employee->user;
            $employeeStatuses->push((object) [
                'employee' => $employee,
                'user' => $user,
                'attendance' => $attendance,
                'status' => $status,
                'isClockedIn' => $status === 'in' || $status === 'late' || $status === 'break',
                'isClockedOut' => $status === 'out' || $status === 'early_leave',
                'isLive' => $status === 'in' || $status === 'late' || $status === 'break',
                'isLate' => $status === 'late',
                'isEarlyLeave' => $status === 'early_leave',
                'isOnBreak' => $status === 'break',
                'isHalfDay' => $isHalfDay,
                'clock_in' => $attendance ? $attendance->clock_in : '00:00:00',
                'clock_out' => $attendance ? $attendance->clock_out : '00:00:00',
                'worked_hours' => $this->calculateWorkedHours($attendance),
                'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                'late_access_enabled' => $employee->late_access_enabled ?? false,
                'late_allowed_minutes' => $employee->late_allowed_minutes ?? 60,
            ]);
        }

        if ($request->status && $request->status !== 'all') {
            $employeeStatuses = $employeeStatuses->filter(function($item) use ($request) {
                return $item->status === $request->status;
            });
        }

        return view('attendance.daily', compact(
            'employeeStatuses', 
            'statusCounts', 
            'branches', 
            'departments', 
            'date'
        ));
    }

    // =================================================================
    // BULK ATTENDANCE
    // =================================================================
    public function bulkAttendance(Request $request)
    {
        if (\Auth::user()->can('create attendance')) {

            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('Select Department', '');

            $employees = [];
            if (!empty($request->branch) && !empty($request->department)) {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())->where('branch_id', $request->branch)->where('department_id', $request->department)->get();

            } else {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())->where('branch_id', 1)->where('department_id', 1)->get();
            }

            return view('attendance.bulk', compact('employees', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // =================================================================
    // BULK ATTENDANCE DATA - With Late Access Support
    // =================================================================
    public function bulkAttendanceData(Request $request)
    {
        if (\Auth::user()->can('create attendance')) {
            if (!empty($request->branch) && !empty($request->department)) {
                $startTime = Utility::getValByName('company_start_time');
                $endTime = Utility::getValByName('company_end_time');
                $date = $request->date;

                $employees = $request->employee_id;

                if (!empty($employees)) {
                    foreach ($employees as $employee) {
                        $present = 'present-' . $employee;
                        $in = 'in-' . $employee;
                        $out = 'out-' . $employee;
                        
                        if ($request->$present == 'on') {

                            $in = date("H:i:s", strtotime($request->$in));
                            $out = date("H:i:s", strtotime($request->$out));

                            $emp = Employee::find($employee);
                            $lateAccessEnabled = $emp ? ($emp->late_access_enabled ?? false) : false;
                            $lateAllowedMinutes = $emp ? ($emp->late_allowed_minutes ?? 60) : 60;
                            $halfDayEnabled = $emp ? ($emp->enable_half_day ?? true) : true;

                            $totalLateSeconds = strtotime($in) - strtotime($startTime);
                            $hours = floor($totalLateSeconds / 3600);
                            $mins = floor($totalLateSeconds / 60 % 60);
                            $secs = floor($totalLateSeconds % 60);
                            $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                            $status = 'Present';
                            if ($halfDayEnabled) {
                                $lateMinutes = $totalLateSeconds / 60;
                                if ($totalLateSeconds > 0) {
                                    if ($lateAccessEnabled) {
                                        if ($lateMinutes > $lateAllowedMinutes) {
                                            $status = 'Half Day';
                                        }
                                    } else {
                                        $status = 'Half Day';
                                    }
                                }
                            }

                            $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($out);
                            $hours = floor($totalEarlyLeavingSeconds / 3600);
                            $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
                            $secs = floor($totalEarlyLeavingSeconds % 60);
                            $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                            if (strtotime($out) > strtotime($endTime)) {
                                $totalOvertimeSeconds = strtotime($out) - strtotime($endTime);
                                $hours = floor($totalOvertimeSeconds / 3600);
                                $mins = floor($totalOvertimeSeconds / 60 % 60);
                                $secs = floor($totalOvertimeSeconds % 60);
                                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                            } else {
                                $overtime = '00:00:00';
                            }

                            $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                            if (!empty($attendance)) {
                                $employeeAttendance = $attendance;
                            } else {
                                $employeeAttendance = new AttendanceEmployee();
                                $employeeAttendance->employee_id = $employee;
                                $employeeAttendance->created_by = \Auth::user()->creatorId();
                            }
                            $employeeAttendance->date = $request->date;
                            $employeeAttendance->status = $status;
                            $employeeAttendance->clock_in = $in;
                            $employeeAttendance->clock_out = $out;
                            $employeeAttendance->late = $late;
                            $employeeAttendance->early_leaving = ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00';
                            $employeeAttendance->overtime = $overtime;
                            $employeeAttendance->total_rest = '00:00:00';
                            $employeeAttendance->save();

                        } else {
                            $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                            if (!empty($attendance)) {
                                $employeeAttendance = $attendance;
                            } else {
                                $employeeAttendance = new AttendanceEmployee();
                                $employeeAttendance->employee_id = $employee;
                                $employeeAttendance->created_by = \Auth::user()->creatorId();
                            }

                            $employeeAttendance->status = 'Leave';
                            $employeeAttendance->date = $request->date;
                            $employeeAttendance->clock_in = '00:00:00';
                            $employeeAttendance->clock_out = '00:00:00';
                            $employeeAttendance->late = '00:00:00';
                            $employeeAttendance->early_leaving = '00:00:00';
                            $employeeAttendance->overtime = '00:00:00';
                            $employeeAttendance->total_rest = '00:00:00';
                            $employeeAttendance->save();
                        }
                    }
                } else {
                    return redirect()->back()->with('error', __('Employee not found.'));
                }

                return redirect()->back()->with('success', __('Employee attendance successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Branch & department field required.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // =================================================================
    // IMPORT FILE
    // =================================================================
    public function importFile()
    {
        return view('attendance.import');
    }

    // =================================================================
    // DASHBOARD
    // =================================================================
    public function dashboard(Request $request)
    {
        $user = \Auth::user();
        $creatorId = $user->creatorId();
        $date = $request->get('date', date('Y-m-d'));

        $startTime = Utility::getValByName('company_start_time') ?? '09:00:00';
        $endTime   = Utility::getValByName('company_end_time') ?? '18:00:00';

        $branches = Branch::where('created_by', $creatorId)->get();
        $departments = Department::where('created_by', $creatorId)->get();

        $employees = Employee::where('created_by', $creatorId)
                    ->with(['user', 'branch', 'department', 'designation'])
                    ->get();

        $employeeIds = $employees->pluck('id')->toArray();

        $attendances = AttendanceEmployee::whereIn('employee_id', $employeeIds)
                        ->whereDate('date', $date)
                        ->get()
                        ->keyBy('employee_id');

        $leaves = \App\Models\Leave::where('created_by', $creatorId)
                    ->where('status', 'Approved')
                    ->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date)
                    ->get()
                    ->keyBy('employee_id');

        $holidays = \App\Models\Holiday::where('created_by', $creatorId)
                    ->where('type', 'holiday')
                    ->whereDate('date', '<=', $date)
                    ->whereDate('end_date', '>=', $date)
                    ->get();

        $isCompanyHoliday = $holidays->isNotEmpty();

        $weekOffs = \App\Models\Holiday::where('created_by', $creatorId)
                    ->where('type', 'week_off')
                    ->get();

        $dayOfWeek = date('N', strtotime($date));
        
        $weekOffEmployeeIds = [];
        foreach ($weekOffs as $weekOff) {
            $weekOffDays = $weekOff->week_off_days;
            if (is_string($weekOffDays)) {
                $weekOffDays = json_decode($weekOffDays, true);
            }
            
            if (is_array($weekOffDays) && in_array($dayOfWeek, $weekOffDays)) {
                if ($weekOff->applicable_to == 'all') {
                    $weekOffEmployeeIds = array_merge($weekOffEmployeeIds, $employeeIds);
                } else {
                    $departments = $weekOff->departments;
                    if (is_string($departments)) {
                        $departments = json_decode($departments, true);
                    }
                    if (is_array($departments)) {
                        $deptEmployees = Employee::whereIn('department_id', $departments)
                                            ->where('created_by', $creatorId)
                                            ->pluck('id')
                                            ->toArray();
                        $weekOffEmployeeIds = array_merge($weekOffEmployeeIds, $deptEmployees);
                    }
                }
            }
        }
        $weekOffEmployeeIds = array_unique($weekOffEmployeeIds);

        $counts = [
            'present' => 0, 'absent' => 0, 'half_day' => 0,
            'week_off' => 0, 'holiday' => 0, 'paid_leave' => 0,
            'unpaid_leave' => 0, 'overtime_working_day' => 0,
            'overtime_week_off' => 0, 'overtime_holiday' => 0,
            'late_coming' => 0, 'early_leaving' => 0,
        ];

        $employeeDetails = [];

        foreach ($employees as $employee) {
            $attendance = $attendances->get($employee->id);
            $leave = $leaves->get($employee->id);
            
            $isWeekOff = in_array($employee->id, $weekOffEmployeeIds);
            $isHoliday = $isCompanyHoliday;

            $status = 'Absent';
            $clockIn = '00:00:00';
            $clockOut = '00:00:00';
            $workedSeconds = 0;
            $isPresent = false;
            $isHalfDay = false;
            $isLate = false;
            $isEarlyLeave = false;
            $overtimeSeconds = 0;
            $overtimeType = null;
            $leaveType = null;
            $halfDayThreshold = $employee->half_day_threshold ?? 4.0;

            if ($attendance && $attendance->clock_in != '00:00:00') {
                $clockIn = $attendance->clock_in;
                $clockOut = $attendance->clock_out ?? '00:00:00';
                $isPresent = true;
                $status = $attendance->status ?? 'Present';
                
                $workedSeconds = strtotime($clockOut) - strtotime($clockIn);
                if ($clockOut == '00:00:00') {
                    $workedSeconds = time() - strtotime($clockIn);
                }
                
                if ($attendance->status == 'Half Day') {
                    $isHalfDay = true;
                    $status = 'Half Day';
                    $counts['half_day']++;
                } elseif ($workedSeconds > 0 && ($workedSeconds / 3600) < $halfDayThreshold) {
                    $isHalfDay = true;
                    $status = 'Half Day';
                    $counts['half_day']++;
                }
                
                if (strtotime($clockIn) > strtotime($startTime)) {
                    $isLate = true;
                    $counts['late_coming']++;
                }
                
                if ($clockOut != '00:00:00' && strtotime($clockOut) < strtotime($endTime)) {
                    $isEarlyLeave = true;
                    $counts['early_leaving']++;
                }
                
                if ($clockOut != '00:00:00' && strtotime($clockOut) > strtotime($endTime)) {
                    $overtimeSeconds = strtotime($clockOut) - strtotime($endTime);
                    
                    if ($isHoliday) {
                        $overtimeType = 'holiday';
                        $counts['overtime_holiday']++;
                    } elseif ($isWeekOff) {
                        $overtimeType = 'week_off';
                        $counts['overtime_week_off']++;
                    } else {
                        $overtimeType = 'working_day';
                        $counts['overtime_working_day']++;
                    }
                }
            }

            if (!$isPresent && $leave) {
                $leaveType = $leave->leave_type;
                if ($leave->leave_type == 'Paid') {
                    $counts['paid_leave']++;
                    $status = 'Paid Leave';
                } else {
                    $counts['unpaid_leave']++;
                    $status = 'Unpaid Leave';
                }
            }

            if (!$isPresent && !$leave && $isWeekOff) {
                $counts['week_off']++;
                $status = 'Week Off';
            }

            if (!$isPresent && !$leave && !$isWeekOff && $isHoliday) {
                $counts['holiday']++;
                $status = 'Holiday';
            }

            if ($isPresent && !$leave) {
                $counts['present']++;
            }

            if (!$isPresent && !$leave && !$isWeekOff && !$isHoliday) {
                $counts['absent']++;
            }

            $employeeDetails[] = (object) [
                'employee'      => $employee,
                'user'          => $employee->user,
                'attendance'    => $attendance,
                'status'        => $status,
                'clock_in'      => $clockIn,
                'clock_out'     => $clockOut,
                'worked_hours'  => $workedSeconds > 0 ? gmdate('H:i:s', $workedSeconds) : '00:00:00',
                'late'          => $isLate,
                'early_leave'   => $isEarlyLeave,
                'overtime'      => $overtimeSeconds > 0 ? gmdate('H:i:s', $overtimeSeconds) : '00:00:00',
                'overtime_type' => $overtimeType,
                'leave_type'    => $leaveType,
                'half_day'      => $isHalfDay,
                'half_day_threshold' => $halfDayThreshold,
                'is_week_off'   => $isWeekOff,
                'is_holiday'    => $isHoliday,
            ];
        }

        $totals = $counts;

        return view('attendance.dashboard', compact(
            'employeeDetails', 
            'totals', 
            'date',
            'branches',
            'departments'
        ));
    }

    /**
     * Check if a date is a holiday
     */
    public function isDateHoliday($date, $creatorId)
    {
        $holiday = \App\Models\Holiday::where('created_by', $creatorId)
                    ->where('type', 'holiday')
                    ->whereDate('date', '<=', $date)
                    ->whereDate('end_date', '>=', $date)
                    ->first();
                    
        return !is_null($holiday);
    }

    /**
     * Check if an employee has week off on a date
     */
    public function isEmployeeWeekOff($employeeId, $date, $creatorId)
    {
        $dayOfWeek = date('N', strtotime($date));
        
        $weekOffs = \App\Models\Holiday::where('created_by', $creatorId)
                    ->where('type', 'week_off')
                    ->get();
                    
        foreach ($weekOffs as $weekOff) {
            $weekOffDays = is_string($weekOff->week_off_days) 
                ? json_decode($weekOff->week_off_days, true) 
                : $weekOff->week_off_days;
                
            if (!is_array($weekOffDays) || !in_array($dayOfWeek, $weekOffDays)) {
                continue;
            }
            
            if ($weekOff->applicable_to == 'all') {
                return true;
            }
            
            $departments = is_string($weekOff->departments) 
                ? json_decode($weekOff->departments, true) 
                : $weekOff->departments;
                
            $employee = Employee::find($employeeId);
            if ($employee && in_array($employee->department_id, $departments ?? [])) {
                return true;
            }
        }
        
        return false;
    }

    // =================================================================
    // LIVE ATTENDANCE
    // =================================================================
    public function live(Request $request)
    {
        $currentUser = \Auth::user();
        $companyId   = $currentUser->creatorId();
        $today       = date('Y-m-d');
        $startTime   = \App\Models\Utility::getValByName('company_start_time') ?? '09:00:00';
        $endTime     = \App\Models\Utility::getValByName('company_end_time') ?? '18:00:00';

        $employeesQuery = Employee::where('created_by', $companyId);
        if ($request->filled('branch')) {
            $employeesQuery->where('branch_id', $request->branch);
        }
        if ($request->filled('department')) {
            $employeesQuery->where('department_id', $request->department);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $employeesQuery->where('name', 'like', '%' . $search . '%');
        }
        $employees = $employeesQuery->get();

        $employeeIds = $employees->pluck('id')->toArray();

        $attendances = AttendanceEmployee::whereIn('employee_id', $employeeIds)
                        ->whereDate('date', $today)
                        ->get()
                        ->keyBy('employee_id');

        $allEmployeeStatuses = collect();

        foreach ($employees as $employee) {
            $attendance = $attendances->get($employee->id);
            $user = $employee->user;
            
            $status = 'not_punched';
            $isClockedIn = false;
            $isClockedOut = false;
            $isLate = false;
            $isEarlyLeave = false;
            $isOnBreak = false;
            $isLive = false;
            $isHalfDay = false;
            
            if ($attendance && $attendance->clock_in != '00:00:00') {
                $isClockedIn = true;
                $status = 'in';
                
                if ($attendance->clock_out == '00:00:00') {
                    $isLive = true;
                } else {
                    $isClockedOut = true;
                    $status = 'out';
                }
                
                if (strtotime($attendance->clock_in) > strtotime($startTime)) {
                    $isLate = true;
                    
                    $lateSeconds = strtotime($attendance->clock_in) - strtotime($startTime);
                    $lateMinutes = $lateSeconds / 60;
                    $lateAccessEnabled = $employee->late_access_enabled ?? false;
                    $lateAllowedMinutes = $employee->late_allowed_minutes ?? 60;
                    $halfDayEnabled = $employee->enable_half_day ?? true;
                    
                    if ($halfDayEnabled && $attendance->status == 'Half Day') {
                        $isHalfDay = true;
                    }
                    
                    if ($isLive) {
                        $status = 'late';
                    }
                }
                
                if ($isClockedOut && strtotime($attendance->clock_out) < strtotime($endTime)) {
                    $isEarlyLeave = true;
                    $status = 'early_leave';
                }
                
                if ($isLive && !empty($attendance->tea_break_out) && $attendance->tea_break_out != '00:00:00' 
                    && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00')) {
                    $isOnBreak = true;
                    $status = 'break';
                }
                
                if ($attendance->status == 'Half Day') {
                    $isHalfDay = true;
                    if ($isClockedOut) {
                        $status = 'half_day';
                    }
                }
            }
            
            $hasPunchPhoto = false;
            $punchPhotoUrl = null;
            $photoType = null;
            
            if ($attendance) {
                $photoPaths = [
                    'punch_photo' => 'Clock In',
                    'break_in_photo' => 'Break In',
                    'break_out_photo' => 'Break Out',
                    'punch_out_photo' => 'Punch Out',
                ];
                
                foreach ($photoPaths as $field => $type) {
                    if (!$hasPunchPhoto && !empty($attendance->$field)) {
                        $filePath = public_path('uploads/attendance/' . $attendance->$field);
                        if (file_exists($filePath)) {
                            $hasPunchPhoto = true;
                            $punchPhotoUrl = asset('uploads/attendance/' . $attendance->$field);
                            $photoType = $type;
                            break;
                        }
                    }
                }
            }

            $allEmployeeStatuses->push((object) [
                'employee'   => $employee,
                'user'       => $user,
                'attendance' => $attendance,
                'status'     => $status,
                'isClockedIn' => $isClockedIn,
                'isClockedOut' => $isClockedOut,
                'isLive' => $isLive,
                'isLate' => $isLate,
                'isEarlyLeave' => $isEarlyLeave,
                'isOnBreak' => $isOnBreak,
                'isHalfDay' => $isHalfDay,
                'hasPunchPhoto' => $hasPunchPhoto,
                'punchPhotoUrl' => $punchPhotoUrl,
                'photoType' => $photoType,
                'clock_in'   => $attendance ? $attendance->clock_in : '00:00:00',
                'clock_out'  => $attendance ? $attendance->clock_out : '00:00:00',
                'tea_break_out' => $attendance ? $attendance->tea_break_out : null,
                'tea_break_in' => $attendance ? $attendance->tea_break_in : null,
                'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                'late_access_enabled' => $employee->late_access_enabled ?? false,
                'late_allowed_minutes' => $employee->late_allowed_minutes ?? 60,
            ]);
        }

        $statusCounts = [
            'all'          => $allEmployeeStatuses->count(),
            'in'           => $allEmployeeStatuses->filter(fn($item) => $item->isClockedIn && !$item->isClockedOut)->count(),
            'out'          => $allEmployeeStatuses->filter(fn($item) => $item->isClockedOut && !$item->isEarlyLeave && !$item->isHalfDay)->count(),
            'not_punched'  => $allEmployeeStatuses->filter(fn($item) => !$item->isClockedIn)->count(),
            'break'        => $allEmployeeStatuses->filter(fn($item) => $item->isOnBreak)->count(),
            'late'         => $allEmployeeStatuses->filter(fn($item) => $item->isLate && !$item->isClockedOut)->count(),
            'early_leave'  => $allEmployeeStatuses->filter(fn($item) => $item->isEarlyLeave)->count(),
            'half_day'     => $allEmployeeStatuses->filter(fn($item) => $item->isHalfDay && $item->isClockedOut)->count(),
        ];

        $statusParam = $request->status ?? 'all';
        
        if ($statusParam === 'all') {
            $employeeStatuses = $allEmployeeStatuses;
        } elseif ($statusParam === 'in') {
            $employeeStatuses = $allEmployeeStatuses->filter(fn($item) => $item->isClockedIn && !$item->isClockedOut);
        } elseif ($statusParam === 'late') {
            $employeeStatuses = $allEmployeeStatuses->filter(fn($item) => $item->isLate && !$item->isClockedOut);
        } elseif ($statusParam === 'break') {
            $employeeStatuses = $allEmployeeStatuses->filter(fn($item) => $item->isOnBreak);
        } elseif ($statusParam === 'out') {
            $employeeStatuses = $allEmployeeStatuses->filter(fn($item) => $item->isClockedOut && !$item->isEarlyLeave && !$item->isHalfDay);
        } elseif ($statusParam === 'early_leave') {
            $employeeStatuses = $allEmployeeStatuses->filter(fn($item) => $item->isEarlyLeave);
        } elseif ($statusParam === 'half_day') {
            $employeeStatuses = $allEmployeeStatuses->filter(fn($item) => $item->isHalfDay);
        } elseif ($statusParam === 'not_punched') {
            $employeeStatuses = $allEmployeeStatuses->filter(fn($item) => !$item->isClockedIn);
        } else {
            $employeeStatuses = $allEmployeeStatuses;
        }

        $branches = Branch::where('created_by', $companyId)->get();
        $departments = Department::where('created_by', $companyId)->get();
        $shifts = [];

        return view('attendance.live', compact(
            'employeeStatuses', 'statusCounts', 'branches', 'departments', 'shifts'
        ));
    }

    // =================================================================
    // GET ATTENDANCE DETAILS
    // =================================================================
    public function getAttendanceDetails(Request $request)
    {
        $employeeId = $request->employee_id;
        $date = $request->date ?? date('Y-m-d');
        
        $employee = Employee::with(['user', 'designation', 'branch', 'department'])
                    ->where('id', $employeeId)
                    ->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
        
        $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                        ->whereDate('date', $date)
                        ->first();
        
        $photos = [
            'punch_in' => null,
            'break_in' => null,
            'break_out' => null,
            'punch_out' => null,
        ];
        
        if ($attendance) {
            $findPhoto = function($filename) {
                if (empty($filename)) return null;
                
                $publicPath = public_path('uploads/attendance/' . $filename);
                if (file_exists($publicPath)) {
                    return asset('uploads/attendance/' . $filename);
                }
                
                $storagePath = public_path('storage/uploads/attendance/' . $filename);
                if (file_exists($storagePath)) {
                    return asset('storage/uploads/attendance/' . $filename);
                }
                
                return null;
            };
            
            if (!empty($attendance->punch_photo)) {
                $photos['punch_in'] = $findPhoto($attendance->punch_photo);
            }
            
            if (!empty($attendance->break_in_photo)) {
                $photos['break_in'] = $findPhoto($attendance->break_in_photo);
            }
            
            if (!empty($attendance->break_out_photo)) {
                $photos['break_out'] = $findPhoto($attendance->break_out_photo);
            }
            
            if (!empty($attendance->punch_out_photo)) {
                $photos['punch_out'] = $findPhoto($attendance->punch_out_photo);
            }
        }
        
        $workedHours = '--:--';
        if ($attendance && $attendance->clock_in != '00:00:00' && $attendance->clock_out != '00:00:00') {
            $start = \Carbon\Carbon::parse($attendance->clock_in);
            $end = \Carbon\Carbon::parse($attendance->clock_out);
            $diff = $start->diff($end);
            $workedHours = $diff->format('%H:%I');
        } elseif ($attendance && $attendance->clock_in != '00:00:00' && $attendance->clock_out == '00:00:00') {
            $start = \Carbon\Carbon::parse($attendance->clock_in);
            $now = \Carbon\Carbon::now();
            $diff = $start->diff($now);
            $workedHours = $diff->format('%H:%I');
        }
        
        $data = [
            'employee' => $employee,
            'attendance' => $attendance,
            'photos' => $photos,
            'worked_hours' => $workedHours,
            'date' => $date,
            'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
            'late_access_enabled' => $employee->late_access_enabled ?? false,
            'late_allowed_minutes' => $employee->late_allowed_minutes ?? 60,
        ];
        
        return view('attendance._details_sidebar', compact('data'))->render();
    }

    // =================================================================
    // ROSTER
    // =================================================================
    public function roster(Request $request)
    {
        $user = \Auth::user();
        $creatorId = $user->creatorId();
        $startOfWeek = $request->get('week', date('Y-m-d', strtotime('monday this week')));
        $endOfWeek = date('Y-m-d', strtotime($startOfWeek . ' +6 days'));

        $settings = \App\Models\Utility::settings();
        $weekOffDays = isset($settings['week_off_days']) ? explode(',', $settings['week_off_days']) : [];
        $employeeWeekOffs = isset($settings['employee_week_offs']) ? json_decode($settings['employee_week_offs'], true) : [];

        $employees = Employee::where('created_by', $creatorId)->get();

        $weekAttendance = AttendanceEmployee::whereIn('employee_id', $employees->pluck('id'))
                            ->whereBetween('date', [$startOfWeek, $endOfWeek])
                            ->get()
                            ->groupBy('employee_id');

        $daysOfWeek = [];
        for ($i = 0; $i < 7; $i++) {
            $daysOfWeek[] = date('Y-m-d', strtotime($startOfWeek . " +$i days"));
        }

        $employeeStatuses = $employees->map(function ($employee) use ($weekAttendance, $daysOfWeek, $weekOffDays, $employeeWeekOffs) {
            $attendanceRecords = $weekAttendance->get($employee->id) ?? collect();
            
            $dailyStatus = [];
            foreach ($daysOfWeek as $day) {
                $attendance = $attendanceRecords->firstWhere('date', $day);
                
                $dayOfWeekNumber = date('N', strtotime($day));
                $isWeekOff = in_array($dayOfWeekNumber, $weekOffDays);
                
                if (isset($employeeWeekOffs[$employee->id]) && in_array($dayOfWeekNumber, $employeeWeekOffs[$employee->id])) {
                    $isWeekOff = true;
                }
                
                if ($attendance && $attendance->clock_in != '00:00:00') {
                    $status = $this->getAttendanceStatus($attendance, '09:00:00', '18:00:00');
                    $statusDisplay = ucfirst(str_replace('_', ' ', $status));
                    
                    if ($attendance->status == 'Half Day') {
                        $statusDisplay = 'Half Day';
                        $status = 'half_day';
                    }
                } elseif ($isWeekOff) {
                    $status = 'week_off';
                    $statusDisplay = 'Week Off';
                } else {
                    $status = 'absent';
                    $statusDisplay = 'Absent';
                }

                $dailyStatus[$day] = (object) [
                    'attendance' => $attendance,
                    'status' => $status,
                    'status_display' => $statusDisplay,
                    'is_week_off' => $isWeekOff,
                    'clock_in' => $attendance ? $attendance->clock_in : null,
                    'clock_out' => $attendance ? $attendance->clock_out : null,
                    'punch_photo' => $attendance ? $attendance->punch_photo : null,
                    'break_in_photo' => $attendance ? $attendance->break_in_photo : null,
                    'break_out_photo' => $attendance ? $attendance->break_out_photo : null,
                    'punch_out_photo' => $attendance ? $attendance->punch_out_photo : null,
                ];
            }
            
            return (object) [
                'employee' => $employee,
                'dailyStatus' => $dailyStatus,
                'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                'late_access_enabled' => $employee->late_access_enabled ?? false,
                'late_allowed_minutes' => $employee->late_allowed_minutes ?? 60,
            ];
        });

        return view('attendance.roster', compact('employeeStatuses', 'startOfWeek', 'endOfWeek', 'daysOfWeek'));
    }

    /**
 * FACE RECOGNITION - Get Attendance Status (for AJAX calls)
 */
public function getFaceAttendanceStatus(Request $request)
{
    try {
        $employeeId = $request->input('employee_id');
        
        if (!$employeeId) {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'clocked_in' => false,
                    'message' => 'User not authenticated'
                ]);
            }
            
            $employee = $user->employee;
            if (!$employee) {
                return response()->json([
                    'clocked_in' => false,
                    'message' => 'Employee record not found for this user'
                ]);
            }
            $employeeId = $employee->id;
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return response()->json([
                'clocked_in' => false,
                'message' => 'Employee not found'
            ]);
        }

        $today = date('Y-m-d');
        $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                        ->whereDate('date', $today)
                        ->first();

        $clocked_in = $attendance && $attendance->clock_in != '00:00:00' && $attendance->clock_in !== null;
        $clocked_out = $attendance && $attendance->clock_out != '00:00:00' && $attendance->clock_out !== null;
        $on_break = $attendance && !empty($attendance->tea_break_out) 
                    && $attendance->tea_break_out != '00:00:00'
                    && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00');

        return response()->json([
            'clocked_in' => $clocked_in,
            'clocked_out' => $clocked_out,
            'on_break' => $on_break,
            'status' => $attendance ? $attendance->status : null,
            'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
            'clock_in_time' => $attendance && $attendance->clock_in != '00:00:00' 
                ? \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') 
                : null,
            'clock_out_time' => $attendance && $attendance->clock_out != '00:00:00' 
                ? \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') 
                : null,
            'attendance_id' => $attendance ? $attendance->id : null,
        ]);

    } catch (\Exception $e) {
        Log::error('Face attendance status error: ' . $e->getMessage());
        return response()->json([
            'clocked_in' => false,
            'error' => $e->getMessage()
        ]);
    }
}

    // =================================================================
    // GET ATTENDANCE STATUS - Helper with Late Access
    // =================================================================
    private function getAttendanceStatus($attendance, $startTime, $endTime)
    {
        if (!$attendance || $attendance->clock_in == '00:00:00') {
            return 'not_punched';
        }

        $employee = Employee::find($attendance->employee_id);
        if ($employee) {
            $halfDayEnabled = $employee->enable_half_day ?? true;
            $lateAccessEnabled = $employee->late_access_enabled ?? false;
            $lateAllowedMinutes = $employee->late_allowed_minutes ?? 60;
            
            if ($halfDayEnabled) {
                $clockInTime = strtotime($attendance->clock_in);
                $startTimeStamp = strtotime($startTime);
                
                if ($clockInTime > $startTimeStamp) {
                    $lateSeconds = $clockInTime - $startTimeStamp;
                    $lateMinutes = $lateSeconds / 60;
                    
                    if ($lateAccessEnabled) {
                        if ($lateMinutes > $lateAllowedMinutes) {
                            return 'half_day';
                        }
                    } else {
                        return 'half_day';
                    }
                }
            }
        }

        if ($attendance->clock_out != '00:00:00') {
            if (strtotime($attendance->clock_out) < strtotime($endTime)) {
                return 'early_leave';
            }
            return 'out';
        }

        if (!empty($attendance->tea_break_out) && $attendance->tea_break_out != '00:00:00' 
            && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00')) {
            return 'break';
        }

        if (strtotime($attendance->clock_in) > strtotime($startTime)) {
            return 'late';
        }

        return 'in';
    }

    // =================================================================
    // CALCULATE WORKED HOURS - Helper
    // =================================================================
    private function calculateWorkedHours($attendance)
    {
        if (!$attendance || $attendance->clock_in == '00:00:00') {
            return '00:00:00';
        }
        
        if ($attendance->clock_out != '00:00:00') {
            $start = \Carbon\Carbon::parse($attendance->clock_in);
            $end = \Carbon\Carbon::parse($attendance->clock_out);
            $diff = $start->diff($end);
            return $diff->format('%H:%I:%S');
        }
        
        $start = \Carbon\Carbon::parse($attendance->clock_in);
        $now = \Carbon\Carbon::now();
        $diff = $start->diff($now);
        return $diff->format('%H:%I:%S');
    }

    // =================================================================
    // REFRESH LIVE - With Late Access
    // =================================================================
    public function refreshLive(Request $request)
    {
        $currentUser = \Auth::user();
        $companyId   = $currentUser->creatorId();
        $today       = date('Y-m-d');

        $startTime = \App\Models\Utility::getValByName('company_start_time') ?? '09:00:00';
        $endTime   = \App\Models\Utility::getValByName('company_end_time') ?? '18:00:00';

        $employeesQuery = Employee::where('created_by', $companyId);
        if ($request->filled('branch')) {
            $employeesQuery->where('branch_id', $request->branch);
        }
        if ($request->filled('department')) {
            $employeesQuery->where('department_id', $request->department);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $employeesQuery->where('name', 'like', '%' . $search . '%');
        }
        $employees = $employeesQuery->get();

        $employeeIds = $employees->pluck('id')->toArray();
        $attendances = AttendanceEmployee::whereIn('employee_id', $employeeIds)
                        ->whereDate('date', $today)
                        ->get()
                        ->keyBy('employee_id');

        $employeeStatuses = $employees->map(function ($employee) use ($attendances, $startTime, $endTime) {
            $attendance = $attendances->get($employee->id);
            
            $halfDayThreshold = floatval($employee->half_day_threshold ?? 4.0);
            $halfDayEnabled = $employee->enable_half_day ?? true;
            $lateAccessEnabled = $employee->late_access_enabled ?? false;
            $lateAllowedMinutes = $employee->late_allowed_minutes ?? 60;
            
            $isClockedIn = false;
            $isClockedOut = false;
            $isLive = false;
            $isLate = false;
            $isEarlyLeave = false;
            $isOnBreak = false;
            $isHalfDay = false;
            $status = 'not_punched';
            $workedHours = '00:00:00';
            $workedHoursFloat = 0;
            
            if ($attendance && $attendance->clock_in != '00:00:00') {
                $isClockedIn = true;
                
                $clockInTime = strtotime($attendance->clock_in);
                
                if ($attendance->clock_out != '00:00:00') {
                    $clockOutTime = strtotime($attendance->clock_out);
                    $isClockedOut = true;
                    $workedSeconds = $clockOutTime - $clockInTime;
                } else {
                    $clockOutTime = time();
                    $isLive = true;
                    $workedSeconds = $clockOutTime - $clockInTime;
                }
                
                $workedHoursFloat = $workedSeconds / 3600;
                $hours = floor($workedSeconds / 3600);
                $mins = floor(($workedSeconds % 3600) / 60);
                $workedHours = sprintf('%02d:%02d', $hours, $mins);
                
                if (strtotime($attendance->clock_in) > strtotime($startTime)) {
                    $isLate = true;
                    $lateSeconds = strtotime($attendance->clock_in) - strtotime($startTime);
                    $lateMinutes = $lateSeconds / 60;
                    
                    if ($halfDayEnabled && $isLate) {
                        if ($lateAccessEnabled) {
                            if ($lateMinutes > $lateAllowedMinutes) {
                                $status = 'half_day';
                                $isHalfDay = true;
                            }
                        } else {
                            $status = 'half_day';
                            $isHalfDay = true;
                        }
                    }
                }
                
                if ($isClockedOut && strtotime($attendance->clock_out) < strtotime($endTime)) {
                    $isEarlyLeave = true;
                }
                
                if ($isLive && !empty($attendance->tea_break_out) && $attendance->tea_break_out != '00:00:00' 
                    && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00')) {
                    $isOnBreak = true;
                }
                
                if ($status === 'half_day') {
                    // Half Day takes precedence
                } elseif ($isOnBreak) {
                    $status = 'break';
                } elseif ($isLate && $isLive) {
                    $status = 'late';
                } elseif ($isLive) {
                    $status = 'in';
                } elseif ($isClockedOut && $isEarlyLeave) {
                    $status = 'early_leave';
                } elseif ($isClockedOut) {
                    $status = 'out';
                }
            }
            
            $hasPunchPhoto = false;
            $punchPhotoUrl = null;
            $photoType = null;
            
            if ($attendance) {
                $photoPaths = [
                    'punch_photo' => 'Clock In',
                    'break_in_photo' => 'Break In',
                    'break_out_photo' => 'Break Out',
                    'punch_out_photo' => 'Punch Out',
                ];
                
                foreach ($photoPaths as $field => $type) {
                    if (!$hasPunchPhoto && !empty($attendance->$field)) {
                        $filePath = public_path('uploads/attendance/' . $attendance->$field);
                        if (file_exists($filePath)) {
                            $hasPunchPhoto = true;
                            $punchPhotoUrl = asset('uploads/attendance/' . $attendance->$field);
                            $photoType = $type;
                            break;
                        }
                    }
                }
            }
            
            return (object) [
                'employee'      => $employee,
                'user'          => $employee->user,
                'attendance'    => $attendance,
                'status'        => $status,
                'isClockedIn'   => $isClockedIn,
                'isClockedOut'  => $isClockedOut,
                'isLive'        => $isLive,
                'isLate'        => $isLate,
                'isEarlyLeave'  => $isEarlyLeave,
                'isOnBreak'     => $isOnBreak,
                'isHalfDay'     => $isHalfDay,
                'hasPunchPhoto' => $hasPunchPhoto,
                'punchPhotoUrl' => $punchPhotoUrl,
                'photoType'     => $photoType,
                'clock_in'      => $attendance ? $attendance->clock_in : '00:00:00',
                'clock_out'     => $attendance ? $attendance->clock_out : '00:00:00',
                'tea_break_out' => $attendance ? $attendance->tea_break_out : null,
                'tea_break_in'  => $attendance ? $attendance->tea_break_in : null,
                'half_day_threshold' => $halfDayThreshold,
                'half_day_enabled' => $halfDayEnabled,
                'late_access_enabled' => $lateAccessEnabled,
                'late_allowed_minutes' => $lateAllowedMinutes,
                'worked_hours'  => $workedHours,
                'worked_hours_float' => round($workedHoursFloat, 2),
            ];
        });

        if ($request->filled('branch')) {
            $employeeStatuses = $employeeStatuses->filter(fn($item) => $item->employee && $item->employee->branch_id == $request->branch);
        }
        if ($request->filled('department')) {
            $employeeStatuses = $employeeStatuses->filter(fn($item) => $item->employee && $item->employee->department_id == $request->department);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $employeeStatuses = $employeeStatuses->filter(fn($item) => $item->user && stripos($item->user->name, $search) !== false);
        }
        if ($request->status && $request->status !== 'all') {
            $employeeStatuses = $employeeStatuses->filter(fn($item) => $item->status === $request->status);
        }

        $statusCounts = [
            'all'          => $employeeStatuses->count(),
            'in'           => $employeeStatuses->filter(fn($item) => $item->status === 'in')->count(),
            'out'          => $employeeStatuses->filter(fn($item) => $item->status === 'out')->count(),
            'not_punched'  => $employeeStatuses->filter(fn($item) => $item->status === 'not_punched')->count(),
            'break'        => $employeeStatuses->filter(fn($item) => $item->status === 'break')->count(),
            'late'         => $employeeStatuses->filter(fn($item) => $item->status === 'late')->count(),
            'early_leave'  => $employeeStatuses->filter(fn($item) => $item->status === 'early_leave')->count(),
            'half_day'     => $employeeStatuses->filter(fn($item) => $item->isHalfDay && $item->isClockedOut)->count(),
            'half_day_live' => $employeeStatuses->filter(fn($item) => $item->isHalfDay && $item->isLive)->count(),
        ];

        $html = view('attendance._live_staff_list', compact('employeeStatuses'))->render();

        return response()->json([
            'html' => $html,
            'statusCounts' => $statusCounts,
        ]);
    }

    // =================================================================
    // GET EMPLOYEE PHOTO - Helper
    // =================================================================
    private function getEmployeePhoto($employee, $attendance)
    {
        if ($attendance) {
            $photoFields = ['punch_photo', 'break_in_photo', 'break_out_photo', 'punch_out_photo'];
            foreach ($photoFields as $field) {
                if (!empty($attendance->$field)) {
                    $path = 'uploads/attendance/' . $attendance->$field;
                    $fullPath = public_path($path);
                    if (file_exists($fullPath)) {
                        return asset($path) . '?v=' . filemtime($fullPath);
                    }
                }
            }
        }
        
        if ($employee && $employee->user) {
            $avatarFile = $employee->user->avatar ?? 'avatar.png';
            if ($avatarFile && $avatarFile !== 'avatar.png' && $avatarFile !== 'user-avatar.png') {
                $avatarPath = public_path('uploads/avatar/' . $avatarFile);
                if (file_exists($avatarPath)) {
                    return asset('uploads/avatar/' . $avatarFile) . '?v=' . filemtime($avatarPath);
                }
            }
        }
        
        return asset('assets/img/user-avatar.png') . '?v=1';
    }

    // =================================================================
    // RECALCULATE ATTENDANCE
    // =================================================================
    public function recalculateAttendance($id)
    {
        if (!\Auth::user()->can('manage attendance')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
        $result = $this->updateAttendanceStatus($id);
        
        if ($result) {
            return redirect()->back()->with('success', __('Attendance status recalculated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Failed to recalculate attendance status.'));
        }
    }

    // =================================================================
    // UPDATE ATTENDANCE STATUS - Recalculate Half Day
    // =================================================================
    public function updateAttendanceStatus($attendanceId)
    {
        try {
            $attendance = AttendanceEmployee::find($attendanceId);
            if (!$attendance) {
                return false;
            }
            
            if ($attendance->clock_in == '00:00:00') {
                return false;
            }
            
            $employee = Employee::find($attendance->employee_id);
            if (!$employee) {
                return false;
            }
            
            $halfDayThreshold = $employee->half_day_threshold ?? 4.0;
            $halfDayEnabled = $employee->enable_half_day ?? true;
            $lateAccessEnabled = $employee->late_access_enabled ?? false;
            $lateAllowedMinutes = $employee->late_allowed_minutes ?? 60;
            
            if (!$halfDayEnabled) {
                if ($attendance->clock_in != '00:00:00') {
                    $attendance->status = 'Present';
                    $attendance->save();
                }
                return true;
            }
            
            $startTime = Utility::getValByName('company_start_time') ?? '09:00:00';
            $clockInTime = strtotime($attendance->clock_in);
            $startTimeStamp = strtotime($startTime);
            
            $status = 'Present';
            
            if ($clockInTime > $startTimeStamp) {
                $lateSeconds = $clockInTime - $startTimeStamp;
                $lateMinutes = $lateSeconds / 60;
                
                if ($lateAccessEnabled) {
                    if ($lateMinutes > $lateAllowedMinutes) {
                        $status = 'Half Day';
                    }
                } else {
                    $status = 'Half Day';
                }
            }
            
            if ($attendance->status != $status) {
                $attendance->status = $status;
                $attendance->save();
                \Log::info('Attendance status updated', [
                    'attendance_id' => $attendance->id,
                    'employee_id' => $employee->id,
                    'old_status' => $attendance->getOriginal('status'),
                    'new_status' => $status,
                    'late_minutes' => $lateMinutes ?? 0,
                    'late_access_enabled' => $lateAccessEnabled,
                    'late_allowed_minutes' => $lateAllowedMinutes,
                ]);
            }
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Update attendance status error: ' . $e->getMessage());
            return false;
        }
    }

    // =================================================================
    // QUICK CLOCK IN/OUT
    // =================================================================
    public function quickClockIn(Request $request)
    {
        $employeeId = \Auth::user()->employee->id ?? 0;
        if (!$employeeId) {
            return response()->json(['error' => 'Employee record not found.'], 404);
        }

        $today = date('Y-m-d');
        $now = date('H:i:s');

        $existing = AttendanceEmployee::where('employee_id', $employeeId)
                        ->where('date', $today)
                        ->first();

        if ($existing && $existing->clock_in != '00:00:00' && $existing->clock_out == '00:00:00') {
            return response()->json(['error' => 'You are already clocked in.'], 400);
        }

        $startTime = Utility::getValByName('company_start_time');
        $totalLateSeconds = strtotime($now) - strtotime($startTime);
        $hours = max(0, floor($totalLateSeconds / 3600));
        $mins = max(0, floor($totalLateSeconds / 60 % 60));
        $secs = max(0, floor($totalLateSeconds % 60));
        $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

        if ($existing) {
            $existing->clock_in = $now;
            $existing->late = $late;
            $existing->status = 'Present';
            $existing->save();
        } else {
            $attendance = new AttendanceEmployee();
            $attendance->employee_id = $employeeId;
            $attendance->date = $today;
            $attendance->clock_in = $now;
            $attendance->clock_out = '00:00:00';
            $attendance->late = $late;
            $attendance->early_leaving = '00:00:00';
            $attendance->overtime = '00:00:00';
            $attendance->total_rest = '00:00:00';
            $attendance->status = 'Present';
            $attendance->created_by = \Auth::user()->creatorId();
            $attendance->save();
        }

        return response()->json(['success' => 'Clocked in successfully at ' . $now]);
    }

    public function quickClockOut(Request $request)
    {
        $employeeId = \Auth::user()->employee->id ?? 0;
        if (!$employeeId) {
            return response()->json(['error' => 'Employee record not found.'], 404);
        }

        $today = date('Y-m-d');
        $now = date('H:i:s');

        $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                        ->where('date', $today)
                        ->where('clock_out', '00:00:00')
                        ->first();

        if (!$attendance) {
            return response()->json(['error' => 'You are not clocked in.'], 400);
        }

        $employee = Employee::find($employeeId);
        $halfDayThreshold = $employee ? $employee->half_day_threshold ?? 4.0 : 4.0;
        $halfDayEnabled = $employee ? ($employee->enable_half_day ?? true) : true;

        $status = 'Present';
        if ($halfDayEnabled) {
            $startTime = Utility::getValByName('company_start_time');
            $clockInTime = strtotime($attendance->clock_in);
            $startTimeStamp = strtotime($startTime);
            $lateAccessEnabled = $employee->late_access_enabled ?? false;
            $lateAllowedMinutes = $employee->late_allowed_minutes ?? 60;
            
            if ($clockInTime > $startTimeStamp) {
                $lateSeconds = $clockInTime - $startTimeStamp;
                $lateMinutes = $lateSeconds / 60;
                
                if ($lateAccessEnabled) {
                    if ($lateMinutes > $lateAllowedMinutes) {
                        $status = 'Half Day';
                    }
                } else {
                    $status = 'Half Day';
                }
            }
        }

        $endTime = Utility::getValByName('company_end_time');
        $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($now);
        $hours = max(0, floor($totalEarlyLeavingSeconds / 3600));
        $mins = max(0, floor($totalEarlyLeavingSeconds / 60 % 60));
        $secs = max(0, floor($totalEarlyLeavingSeconds % 60));
        $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

        if (strtotime($now) > strtotime($endTime)) {
            $totalOvertimeSeconds = strtotime($now) - strtotime($endTime);
            $hours = floor($totalOvertimeSeconds / 3600);
            $mins = floor($totalOvertimeSeconds / 60 % 60);
            $secs = floor($totalOvertimeSeconds % 60);
            $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        } else {
            $overtime = '00:00:00';
        }

        $attendance->clock_out = $now;
        $attendance->early_leaving = ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00';
        $attendance->overtime = $overtime;
        $attendance->status = $status;
        $attendance->save();

        return response()->json([
            'success' => 'Clocked out successfully at ' . $now,
            'status' => $status,
        ]);
    }

    // =================================================================
    // FACE RECOGNITION - Admin Dashboard
    // =================================================================
    public function faceMarkAttendance(Request $request)
    {
        if (!\Auth::user()->can('manage face id attendance')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $today = date('Y-m-d');
        $companyId = \Auth::user()->creatorId();
        $date = $request->get('date', $today);
        
        $totalEnrolled = Employee::where('created_by', $companyId)
                            ->whereNotNull('face_descriptor')
                            ->count();
        
        $totalEmployees = Employee::where('created_by', $companyId)->count();
        $pendingEnrollment = $totalEmployees - $totalEnrolled;
        
        $presentToday = AttendanceEmployee::whereDate('date', $date)
                            ->where('status', 'Present')
                            ->distinct('employee_id')
                            ->count('employee_id');
        
        $todayLog = AttendanceEmployee::with('employee')
                        ->whereDate('date', $date)
                        ->orderBy('clock_in', 'desc')
                        ->limit(50)
                        ->get();

        $employees = Employee::where('created_by', $companyId)
            ->with(['user', 'department', 'designation'])
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department ? $employee->department->name : null,
                    'designation' => $employee->designation ? $employee->designation->name : null,
                    'is_face_enrolled' => !empty($employee->face_descriptor),
                    'face_enrolled_at' => $employee->face_enrolled_at,
                    'has_face_photo' => !empty($employee->face_photo),
                    'face_photo' => $employee->face_photo,
                ];
            });

        return view('attendance.face_mark', compact(
            'totalEnrolled', 
            'totalEmployees',
            'pendingEnrollment',
            'presentToday', 
            'todayLog',
            'employees',
            'date'
        ));
    }

    // =================================================================
    // FACE RECOGNITION - Verify Face
    // =================================================================
   /**
 * FACE RECOGNITION - Verify Face (for AJAX calls from face.clockin blade)
 */
public function verifyFace(Request $request)
{
    try {
        // ✅ Accept 'descriptor' (from frontend) OR 'face_descriptor' (fallback)
        $descriptor = $request->input('descriptor') ?? $request->input('face_descriptor');

        if (empty($descriptor) || !is_array($descriptor)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid face descriptor provided. Please try again.'
            ]);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $companyId = $user->creatorId();
        $threshold = 0.6;

        // Get all employees with face descriptors
        $employees = Employee::where('created_by', $companyId)
            ->whereNotNull('face_descriptor')
            ->with(['user', 'department', 'designation'])
            ->get();

        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No enrolled faces found in the system. Please enroll your face first.'
            ]);
        }

        $bestMatch = null;
        $bestDistance = $threshold;
        $bestConfidence = 0;

        foreach ($employees as $employee) {
            $storedDescriptor = json_decode($employee->face_descriptor, true);
            
            if (empty($storedDescriptor) || !is_array($storedDescriptor)) {
                continue;
            }
            
            $distance = $this->calculateFaceDistance($descriptor, $storedDescriptor);
            $confidence = (1 - $distance) * 100;
            
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMatch = $employee;
                $bestConfidence = $confidence;
            }
        }

        if ($bestMatch) {
            // Check today's attendance status
            $today = date('Y-m-d');
            $attendance = AttendanceEmployee::where('employee_id', $bestMatch->id)
                            ->whereDate('date', $today)
                            ->first();

            $isClockedIn = $attendance && $attendance->clock_in != '00:00:00' && $attendance->clock_in !== null;
            $isClockedOut = $attendance && $attendance->clock_out != '00:00:00' && $attendance->clock_out !== null;
            $isOnBreak = $attendance && !empty($attendance->tea_break_out) 
                        && $attendance->tea_break_out != '00:00:00'
                        && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00');

            return response()->json([
                'success' => true,
                'message' => 'Face verified successfully!',
                'user_id' => $bestMatch->user_id ?? null,
                'employee_id' => $bestMatch->id,
                'user_name' => $bestMatch->name,
                'employee_name' => $bestMatch->name,
                'confidence' => round($bestConfidence, 2),
                'distance' => round($bestDistance, 4),
                'has_face_photo' => !empty($bestMatch->face_photo),
                'face_photo_url' => !empty($bestMatch->face_photo) ? asset('uploads/face/' . $bestMatch->face_photo) : null,
                'attendance_status' => [
                    'is_clocked_in' => $isClockedIn,
                    'is_clocked_out' => $isClockedOut,
                    'is_on_break' => $isOnBreak,
                    'clock_in_time' => $attendance && $attendance->clock_in != '00:00:00' 
                        ? \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') 
                        : null,
                    'clock_out_time' => $attendance && $attendance->clock_out != '00:00:00' 
                        ? \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') 
                        : null,
                    'status' => $attendance ? $attendance->status : null,
                ],
                'late_access_enabled' => $bestMatch->late_access_enabled ?? false,
                'late_allowed_minutes' => $bestMatch->late_allowed_minutes ?? 60,
                'half_day_threshold' => $bestMatch->half_day_threshold ?? 4.0,
                'is_half_day' => $attendance && $attendance->status == 'Half Day',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Face not recognized. Please enroll your face first or try again.'
        ]);

    } catch (\Exception $e) {
        Log::error('Face verification error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Verification failed: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Calculate Euclidean distance between two face descriptors
 */
  // =================================================================
    // FACE RECOGNITION - Mark Attendance (FIXED)
    // =================================================================
    /**
 * FACE RECOGNITION - Mark Attendance
 * Handles both clock in and clock out via face recognition
 */
public function markFaceAttendance(Request $request)
{
    try {
        // ✅ Updated validation - only employee_id is required now
        $validator = \Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'user_id' => 'nullable|exists:users,id',
            'action' => 'nullable|in:clock_in,clock_out',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i:s',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'face_confidence' => 'nullable|numeric|min:0|max:100',
            'punch_photo' => 'nullable|string',
            'mode' => 'nullable|in:office,remote',
            'work_report' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $employeeId = $request->input('employee_id');
        $date = $request->input('date', date('Y-m-d'));
        $time = $request->input('time', date('H:i:s'));
        // ✅ Default action to clock_in if not provided
        $action = $request->input('action', 'clock_in');
        
        Log::info('Face attendance marking started', [
            'employee_id' => $employeeId,
            'date' => $date,
            'time' => $time,
            'action' => $action
        ]);

        if (empty($employeeId)) {
            return response()->json([
                'success' => false,
                'message' => 'Employee ID missing'
            ]);
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ]);
        }

        // ✅ Check if face is enrolled (but allow if not - will still mark attendance)
        if (empty($employee->face_descriptor)) {
            Log::warning('Face not enrolled for employee, but marking attendance anyway', [
                'employee_id' => $employeeId
            ]);
            // Continue anyway - we still mark attendance
        }

        // ✅ Location validation (skip if mode is remote)
        if ($request->input('mode') !== 'remote') {
            $locationValid = $this->checkLocation($request);
            if (!$locationValid['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $locationValid['message'],
                    'distance' => $locationValid['distance'] ?? null,
                    'required_radius' => $locationValid['radius'] ?? 300,
                ], 400);
            }
        }

        $startTime = Utility::getValByName('company_start_time') ?? '09:00:00';
        $endTime = Utility::getValByName('company_end_time') ?? '18:00:00';

        $existing = AttendanceEmployee::where('employee_id', $employeeId)
                        ->whereDate('date', $date)
                        ->first();

        // ✅ CLOCK IN
        if ($action == 'clock_in') {
            // If already clocked in today
            if ($existing && $existing->clock_in != '00:00:00' && $existing->clock_in !== null) {
                if ($existing->clock_out != '00:00:00' && $existing->clock_out !== null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You have already clocked out for today',
                        'already_marked' => true,
                    ], 400);
                }
                
                // Update existing record with face recognition data
                if (empty($existing->marked_by)) {
                    $existing->marked_by = 'face_recognition';
                }
                $existing->face_confidence = $request->face_confidence;
                if ($request->has('latitude')) {
                    $existing->latitude = $request->latitude;
                }
                if ($request->has('longitude')) {
                    $existing->longitude = $request->longitude;
                }
                $existing->save();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Already clocked in. Status updated.',
                    'data' => [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->name,
                        'clock_in_time' => $existing->clock_in,
                        'status' => $existing->status ?? 'Present',
                        'attendance_id' => $existing->id,
                        'type' => 'update',
                    ]
                ]);
            }

            // Calculate late
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

            // Save punch photo if provided
            $photoName = null;
            if ($request->has('punch_photo') && !empty($request->punch_photo)) {
                $photoName = $this->saveAttendancePhoto($request->punch_photo, $employeeId, 'clockin');
            }

            if (!$existing) {
                $attendance = new AttendanceEmployee();
                $attendance->employee_id = $employeeId;
                $attendance->date = $date;
                $attendance->created_by = \Auth::user()->creatorId();
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
            
            if ($request->has('face_confidence')) {
                $attendance->face_confidence = $request->face_confidence;
            }
            if ($request->has('latitude')) {
                $attendance->latitude = $request->latitude;
            }
            if ($request->has('longitude')) {
                $attendance->longitude = $request->longitude;
            }
            
            if ($photoName) {
                $attendance->punch_photo = $photoName;
            }
            
            $attendance->save();

            Log::info('Face recognition clock in successful', [
                'employee_id' => $employeeId,
                'employee_name' => $employee->name,
                'time' => $time,
                'attendance_id' => $attendance->id,
                'status' => $status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clock in successful via Face ID ✅',
                'data' => [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'clock_in_time' => $time,
                    'status' => $status,
                    'is_late' => $isLate,
                    'late_duration' => $late,
                    'face_confidence' => $request->face_confidence,
                    'attendance_id' => $attendance->id,
                    'type' => 'new',
                ]
            ]);
        }

        // ✅ CLOCK OUT
        if ($action == 'clock_out') {
            if (!$existing || $existing->clock_in == '00:00:00' || $existing->clock_in === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not clocked in'
                ], 400);
            }

            if ($existing->clock_out != '00:00:00' && $existing->clock_out !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already clocked out'
                ], 400);
            }

            if (!empty($existing->tea_break_out) && $existing->tea_break_out != '00:00:00'
                && (empty($existing->tea_break_in) || $existing->tea_break_in == '00:00:00')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please end your tea break first before clocking out'
                ], 400);
            }

            // Calculate early leaving
            $earlyLeaving = '00:00:00';
            if (strtotime($time) < strtotime($endTime)) {
                $earlySeconds = strtotime($endTime) - strtotime($time);
                $hours = floor($earlySeconds / 3600);
                $mins = floor(($earlySeconds % 3600) / 60);
                $secs = $earlySeconds % 60;
                $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            }

            // Calculate overtime
            $overtime = '00:00:00';
            if (strtotime($time) > strtotime($endTime)) {
                $overtimeSeconds = strtotime($time) - strtotime($endTime);
                $hours = floor($overtimeSeconds / 3600);
                $mins = floor(($overtimeSeconds % 3600) / 60);
                $secs = $overtimeSeconds % 60;
                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            }

            // Save punch out photo if provided
            $photoName = null;
            if ($request->has('punch_photo') && !empty($request->punch_photo)) {
                $photoName = $this->saveAttendancePhoto($request->punch_photo, $employeeId, 'clockout');
            }

            $status = $existing->status ?? 'Present';

            $existing->clock_out = $time;
            $existing->early_leaving = $earlyLeaving;
            $existing->overtime = $overtime;
            $existing->punch_state = 'clock_out';
            
            if ($request->has('face_confidence')) {
                $existing->face_confidence = $request->face_confidence;
            }
            if ($request->has('latitude')) {
                $existing->latitude = $request->latitude;
            }
            if ($request->has('longitude')) {
                $existing->longitude = $request->longitude;
            }
            
            if ($photoName) {
                $existing->punch_out_photo = $photoName;
            }
            $existing->save();

            // Save work report if provided
            if ($request->has('work_report') && !empty($request->work_report)) {
                $this->saveWorkReport($employee->id, $request->work_report, $existing->id);
            }

            // Calculate worked hours
            $clockInTime = strtotime($existing->clock_in);
            $clockOutTime = strtotime($time);
            $workedSeconds = $clockOutTime - $clockInTime;
            $hours = floor($workedSeconds / 3600);
            $mins = floor(($workedSeconds % 3600) / 60);
            $workedHours = sprintf('%02d:%02d', $hours, $mins);

            Log::info('Face recognition clock out successful', [
                'employee_id' => $employeeId,
                'employee_name' => $employee->name,
                'time' => $time,
                'attendance_id' => $existing->id,
                'status' => $status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clock out successful via Face ID ✅',
                'data' => [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'clock_in_time' => $existing->clock_in,
                    'clock_out_time' => $time,
                    'worked_hours' => $workedHours,
                    'status' => $status,
                    'is_early_leave' => $earlyLeaving != '00:00:00',
                    'overtime' => $overtime,
                    'face_confidence' => $request->face_confidence,
                    'attendance_id' => $existing->id,
                    'work_report_saved' => $request->has('work_report'),
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid action. Use clock_in or clock_out.'
        ], 400);

    } catch (\Exception $e) {
        Log::error('Mark face attendance error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to mark attendance: ' . $e->getMessage()
        ], 500);
    }
}

    // =================================================================
    // CHECK LOCATION - Internal Helper (returns array) - FIXED
    // =================================================================
    private function checkLocation($request)
    {
        // If remote mode, skip location verification
        if ($request->mode == 'remote') {
            return [
                'success' => true,
                'message' => 'Remote attendance allowed',
                'distance' => null,
                'radius' => null,
            ];
        }

        // Skip if no location data provided
        if (!$request->has('latitude') || !$request->has('longitude')) {
            return [
                'success' => true,
                'message' => 'Location not provided, skipping verification',
                'distance' => null,
                'radius' => null,
            ];
        }

        $officeLocation = Utility::getOfficeLocation();
        
        // Debug log
        \Log::info('Office Location Check:', [
            'restriction_enabled' => $officeLocation['restriction_enabled'] ?? false,
            'latitude' => $officeLocation['latitude'] ?? null,
            'longitude' => $officeLocation['longitude'] ?? null,
            'radius' => $officeLocation['radius'] ?? 300,
        ]);
        
        // Check if location restriction is enabled
        if (!$officeLocation['restriction_enabled']) {
            return [
                'success' => true,
                'message' => 'Location restriction disabled',
                'distance' => null,
                'radius' => null,
            ];
        }

        // Check if office location is configured
        if (!$officeLocation['latitude'] || !$officeLocation['longitude']) {
            return [
                'success' => false,
                'message' => 'Office location not configured. Please contact administrator.',
                'distance' => null,
                'radius' => null,
            ];
        }

        // Calculate distance
        $distance = Utility::calculateDistance(
            (float) $request->latitude,
            (float) $request->longitude,
            (float) $officeLocation['latitude'],
            (float) $officeLocation['longitude']
        );

        $radius = (float) ($officeLocation['radius'] ?? 300);

        // Debug log distance
        \Log::info('Location Distance Check:', [
            'distance' => $distance,
            'radius' => $radius,
            'is_within_radius' => $distance <= $radius,
            'user_latitude' => $request->latitude,
            'user_longitude' => $request->longitude,
            'office_latitude' => $officeLocation['latitude'],
            'office_longitude' => $officeLocation['longitude'],
        ]);

        if ($distance <= $radius) {
            return [
                'success' => true,
                'message' => "Within allowed radius ({$radius}m)",
                'distance' => $distance,
                'radius' => $radius,
            ];
        }

        return [
            'success' => false,
            'message' => "You are " . round($distance, 0) . " meters away. Allowed radius is {$radius}m",
            'distance' => $distance,
            'radius' => $radius,
        ];
    }

    // =================================================================
    // SAVE WORK REPORT - Helper method
    // =================================================================
    private function saveWorkReport($employeeId, $report, $attendanceId)
    {
        try {
            if (class_exists('\App\Models\WorkReport')) {
                $workReport = new \App\Models\WorkReport();
                $workReport->employee_id = $employeeId;
                $workReport->attendance_id = $attendanceId;
                $workReport->report = $report;
                $workReport->date = date('Y-m-d');
                $workReport->created_by = $employeeId;
                $workReport->save();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Save work report error: ' . $e->getMessage());
            return false;
        }
    }

    // =================================================================
    // FACE RECOGNITION - Get Stats
    // =================================================================
    public function getFaceAttendanceStats(Request $request)
    {
        try {
            $today = $request->input('date', date('Y-m-d'));
            $companyId = \Auth::user()->creatorId();

            $totalEmployees = Employee::where('created_by', $companyId)->count();
            
            $enrolled = Employee::where('created_by', $companyId)
                            ->whereNotNull('face_descriptor')
                            ->count();
            
            $present = AttendanceEmployee::whereDate('date', $today)
                            ->where('status', 'Present')
                            ->distinct('employee_id')
                            ->count('employee_id');
            
            $halfDay = AttendanceEmployee::whereDate('date', $today)
                            ->where('status', 'Half Day')
                            ->distinct('employee_id')
                            ->count('employee_id');
            
            $log = AttendanceEmployee::with('employee')
                        ->whereDate('date', $today)
                        ->orderBy('clock_in', 'desc')
                        ->get()
                        ->map(function($item) {
                            return [
                                'id' => $item->id,
                                'employee_name' => $item->employee ? $item->employee->name : 'Unknown',
                                'employee_id' => $item->employee_id,
                                'time' => $item->clock_in,
                                'status' => $item->status,
                                'clock_out' => $item->clock_out,
                                'late' => $item->late,
                                'marked_by' => $item->marked_by ?? 'manual',
                                'on_break' => $item->tea_break_out != '00:00:00' && $item->tea_break_in == '00:00:00'
                            ];
                        });
            
            $recent = AttendanceEmployee::with('employee')
                        ->whereDate('date', $today)
                        ->where('marked_by', 'face_recognition')
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get()
                        ->map(function($item) {
                            return [
                                'employee_name' => $item->employee ? $item->employee->name : 'Unknown',
                                'time' => $item->clock_in,
                                'status' => $item->status
                            ];
                        });

            return response()->json([
                'success' => true,
                'total_employees' => $totalEmployees,
                'enrolled' => $enrolled,
                'present' => $present,
                'half_day' => $halfDay,
                'absent' => max(0, $totalEmployees - $present - $halfDay),
                'log' => $log,
                'recent' => $recent,
                'date' => $today
            ]);

        } catch (\Exception $e) {
            Log::error('Face attendance stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stats'
            ]);
        }
    }

    

    // =================================================================
    // FACE RECOGNITION - Get Attendance Status
    // =================================================================
   /**
 * FACE RECOGNITION - Get Attendance Status (for AJAX calls)
 */
// public function getFaceAttendanceStatus(Request $request)
// {
//     try {
//         $employeeId = $request->input('employee_id');
        
//         if (!$employeeId) {
//             $user = Auth::user();
//             if (!$user) {
//                 return response()->json([
//                     'clocked_in' => false,
//                     'message' => 'User not authenticated'
//                 ]);
//             }
            
//             $employee = $user->employee;
//             if (!$employee) {
//                 return response()->json([
//                     'clocked_in' => false,
//                     'message' => 'Employee record not found for this user'
//                 ]);
//             }
//             $employeeId = $employee->id;
//         }

//         $employee = Employee::find($employeeId);
//         if (!$employee) {
//             return response()->json([
//                 'clocked_in' => false,
//                 'message' => 'Employee not found'
//             ]);
//         }

//         $today = date('Y-m-d');
//         $attendance = AttendanceEmployee::where('employee_id', $employeeId)
//                         ->whereDate('date', $today)
//                         ->first();

//         $clocked_in = $attendance && $attendance->clock_in != '00:00:00' && $attendance->clock_in !== null;
//         $clocked_out = $attendance && $attendance->clock_out != '00:00:00' && $attendance->clock_out !== null;
//         $on_break = $attendance && !empty($attendance->tea_break_out) 
//                     && $attendance->tea_break_out != '00:00:00'
//                     && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00');

//         return response()->json([
//             'clocked_in' => $clocked_in,
//             'clocked_out' => $clocked_out,
//             'on_break' => $on_break,
//             'status' => $attendance ? $attendance->status : null,
//             'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
//             'clock_in_time' => $attendance && $attendance->clock_in != '00:00:00' 
//                 ? \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') 
//                 : null,
//             'clock_out_time' => $attendance && $attendance->clock_out != '00:00:00' 
//                 ? \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') 
//                 : null,
//         ]);

//     } catch (\Exception $e) {
//         Log::error('Face attendance status error: ' . $e->getMessage());
//         return response()->json([
//             'clocked_in' => false,
//             'error' => $e->getMessage()
//         ]);
//     }
// }
    // =================================================================
    // FACE RECOGNITION - Enroll Face
    // =================================================================
   // =================================================================
// FACE RECOGNITION - Enroll Face (AUTO-DETECT EMPLOYEE)
// =================================================================
public function enrollFace(Request $request)
{
    try {
        $validator = \Validator::make($request->all(), [
            'face_descriptor' => 'required|array',
            'face_photo' => 'nullable|string',
            'face_photo_file' => 'nullable|file|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // 🟢 AUTO-DETECT EMPLOYEE FROM AUTHENTICATED USER
        $user = \Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee record found for your account. Please contact HR.'
            ]);
        }

        // Check if user has permission to enroll
        if (!\Auth::user()->can('create face id attendance') && !\Auth::user()->can('manage face id attendance')) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied. You cannot enroll faces.'
            ]);
        }

        // 🟢 Check if employee belongs to the same company
        if ($employee->created_by != \Auth::user()->creatorId()) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found in your company.'
            ]);
        }

        // 🟢 Save the face descriptor
        $employee->face_descriptor = json_encode($request->face_descriptor);
        $employee->face_enrolled_at = now();

        // Handle face photo (if provided)
        if ($request->has('face_photo') && !empty($request->face_photo)) {
            $photoName = $this->saveFacePhoto($request->face_photo, $employee->id);
            if ($photoName) {
                if (!empty($employee->face_photo)) {
                    $this->deleteFacePhoto($employee->face_photo);
                }
                $employee->face_photo = $photoName;
            }
        }

        if ($request->hasFile('face_photo_file')) {
            $photoName = $this->saveFacePhoto($request->file('face_photo_file'), $employee->id);
            if ($photoName) {
                if (!empty($employee->face_photo)) {
                    $this->deleteFacePhoto($employee->face_photo);
                }
                $employee->face_photo = $photoName;
            }
        }

        $employee->save();

        Log::info('Face enrolled successfully (auto-detected)', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'user_id' => \Auth::user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Face enrolled successfully',
            'data' => [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'face_enrolled_at' => $employee->face_enrolled_at,
                'has_face_photo' => !empty($employee->face_photo),
                'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                'late_access_enabled' => $employee->late_access_enabled ?? false,
                'late_allowed_minutes' => $employee->late_allowed_minutes ?? 60,
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Face enrollment error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to enroll face: ' . $e->getMessage()
        ]);
    }
}

    // =================================================================
    // FACE RECOGNITION - Calculate Distance
    // =================================================================
   private function calculateFaceDistance($desc1, $desc2)
{
    if (!is_array($desc1) || !is_array($desc2)) {
        return 999;
    }
    
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
  
    // =================================================================
    // FACE RECOGNITION - Enrollment Page
    // =================================================================
    public function faceEnrollmentPage()
    {
        if (!\Auth::user()->can('create face id attendance') && !\Auth::user()->can('manage face id attendance')) {
            if (\Auth::user()->can('create face id attendance') || \Auth::user()->can('manage face id attendance')) {
                $employeeId = null;
                $alreadyEnrolled = false;
                
                $employee = \Auth::user()->employee;
                if ($employee) {
                    $employeeId = $employee->id;
                    $alreadyEnrolled = !empty($employee->face_descriptor);
                }
                
                $employees = Employee::where('created_by', \Auth::user()->creatorId())
                    ->get()
                    ->pluck('name', 'id');
                
                return view('face.enroll', compact('employeeId', 'alreadyEnrolled', 'employees'));
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $employeeId = null;
        $alreadyEnrolled = false;
        
        $employee = \Auth::user()->employee;
        if ($employee) {
            $employeeId = $employee->id;
            $alreadyEnrolled = !empty($employee->face_descriptor);
        }

        $employees = Employee::where('created_by', \Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'id');
        
        return view('face.enroll', compact('employeeId', 'alreadyEnrolled', 'employees'));
    }

    // =================================================================
    // FACE RECOGNITION - Get Enrollment Status
    // =================================================================
    public function getFaceEnrollmentStatus(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'employee_id' => 'nullable|exists:employees,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $companyId = \Auth::user()->creatorId();
            $employeeId = $request->input('employee_id');

            if (!$employeeId) {
                $employee = \Auth::user()->employee;
                if (!$employee) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Employee not found for this user'
                    ]);
                }
                $employeeId = $employee->id;
            }

            $employee = Employee::where('created_by', $companyId)
                ->where('id', $employeeId)
                ->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ]);
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
            Log::error('Get face enrollment status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get enrollment status: ' . $e->getMessage()
            ]);
        }
    }

    // =================================================================
    // DELETE FACE ENROLLMENT
    // =================================================================
    public function deleteFaceEnrollment(Request $request, $employeeId)
    {
        try {
            if (!\Auth::user()->can('delete face id attendance')) {
                return response()->json([
                    'success' => false,
                    'message' => __('Permission denied.')
                ], 403);
            }

            $creatorId = \Auth::user()->creatorId();
            $employee = Employee::where('created_by', $creatorId)
                ->where('id', $employeeId)
                ->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => __('Employee not found')
                ], 404);
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
                'admin_id' => \Auth::user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Face enrollment deleted successfully')
            ]);

        } catch (\Exception $e) {
            Log::error('Delete face enrollment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('Failed to delete face enrollment: ') . $e->getMessage()
            ], 500);
        }
    }

    // =================================================================
    // GET FACE PHOTO
    // =================================================================
    public function getFacePhoto(Request $request, $employeeId)
    {
        try {
            if (!\Auth::user()->can('manage face id attendance')) {
                return response()->json([
                    'success' => false,
                    'message' => __('Permission denied.')
                ], 403);
            }

            $creatorId = \Auth::user()->creatorId();
            $employee = Employee::where('created_by', $creatorId)
                ->where('id', $employeeId)
                ->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => __('Employee not found')
                ], 404);
            }

            if (empty($employee->face_photo)) {
                return response()->json([
                    'success' => false,
                    'message' => __('Face photo not found')
                ], 404);
            }

            $photoPath = public_path('uploads/face/' . $employee->face_photo);
            if (!file_exists($photoPath)) {
                return response()->json([
                    'success' => false,
                    'message' => __('Face photo file not found')
                ], 404);
            }

            $photoUrl = asset('uploads/face/' . $employee->face_photo);

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'photo_url' => $photoUrl,
                    'photo_name' => $employee->face_photo,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get face photo error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('Failed to get face photo')
            ], 500);
        }
    }

    // =================================================================
    // GET USER ATTENDANCE STATUS
    // =================================================================
    public function getUserAttendanceStatus(Request $request)
    {
        try {
            $employee = \Auth::user()->employee;
            if (!$employee) {
                return response()->json([
                    'status' => 'not_clocked'
                ]);
            }

            $today = date('Y-m-d');
            $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                            ->whereDate('date', $today)
                            ->first();

            if ($attendance && $attendance->clock_in != '00:00:00' && $attendance->clock_out == '00:00:00') {
                return response()->json([
                    'status' => 'clocked_in',
                    'time' => $attendance->clock_in,
                    'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                    'late_access_enabled' => $employee->late_access_enabled ?? false,
                ]);
            } elseif ($attendance && $attendance->clock_in != '00:00:00' && $attendance->clock_out != '00:00:00') {
                return response()->json([
                    'status' => 'clocked_out',
                    'time' => $attendance->clock_out,
                    'attendance_status' => $attendance->status,
                    'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                ]);
            }

            return response()->json([
                'status' => 'not_clocked'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error'
            ]);
        }
    }

    // =================================================================
    // VALIDATE LOCATION - Public method
    // =================================================================
    public function validateLocation(Request $request)
    {
        try {
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $mode = $request->input('mode', 'office');

            if ($mode === 'remote') {
                return response()->json([
                    'valid' => true,
                    'message' => 'Remote attendance allowed.'
                ]);
            }

            $officeLocation = Utility::getOfficeLocation();
            
            if (!$officeLocation['restriction_enabled']) {
                return response()->json([
                    'valid' => true,
                    'message' => 'Location restriction is disabled.'
                ]);
            }

            if (!$officeLocation['latitude'] || !$officeLocation['longitude']) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Office location not set. Please contact administrator.'
                ]);
            }

            $distance = Utility::calculateDistance(
                $latitude,
                $longitude,
                $officeLocation['latitude'],
                $officeLocation['longitude']
            );

            $radius = $officeLocation['radius'] ?? 300;

            if ($distance <= $radius) {
                return response()->json([
                    'valid' => true,
                    'message' => "You are within {$radius} meters of the office.",
                    'distance' => round($distance, 0),
                    'radius' => $radius
                ]);
            } else {
                return response()->json([
                    'valid' => false,
                    'message' => "You are " . round($distance, 0) . " meters away. You must be within {$radius} meters of the office.",
                    'distance' => round($distance, 0),
                    'radius' => $radius
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Error validating location: ' . $e->getMessage()
            ]);
        }
    }

    // =================================================================
    // ATTENDANCE IMPORT DATA
    // =================================================================
    public function attendanceImportdata(Request $request)
    {
        session_start();
        $html = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
        $flag = 0;
        $html .= '<table class="table table-bordered"><tr>';
        try {
            $request = $request->data;
            $file_data = $_SESSION['file_data'];

            unset($_SESSION['file_data']);
        } catch (\Throwable $th) {
            $html = '<h3 class="text-danger text-center">Something went wrong, Please try again</h3></br>';
            return response()->json([
                'html' => true,
                'response' => $html,
            ]);
        }
        $user = Auth::user();

        $startTime = Utility::getValByName('company_start_time');
        $endTime = Utility::getValByName('company_end_time');

        foreach ($file_data as $key => $row) {
            $employeeData = Employee::Where('email', 'like', $row[$request['employee_email']])->where('created_by', \Auth::user()->creatorId())->first();

            if (!empty($employeeData)) {
                try {

                    $employeeId = $employeeData->id;

                    $clockIn = $row[$request['clock_in']];
                    $clockOut = $row[$request['clock_out']];

                    $emp = Employee::find($employeeId);
                    $lateAccessEnabled = $emp ? ($emp->late_access_enabled ?? false) : false;
                    $lateAllowedMinutes = $emp ? ($emp->late_allowed_minutes ?? 60) : 60;
                    $halfDayEnabled = $emp ? ($emp->enable_half_day ?? true) : true;

                    $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);
                    $hours = floor($totalLateSeconds / 3600);
                    $mins = floor($totalLateSeconds / 60 % 60);
                    $secs = floor($totalLateSeconds % 60);
                    $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                    $status = 'Present';
                    if ($halfDayEnabled) {
                        $lateMinutes = $totalLateSeconds / 60;
                        if ($totalLateSeconds > 0) {
                            if ($lateAccessEnabled) {
                                if ($lateMinutes > $lateAllowedMinutes) {
                                    $status = 'Half Day';
                                }
                            } else {
                                $status = 'Half Day';
                            }
                        }
                    }

                    $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
                    $hours = floor($totalEarlyLeavingSeconds / 3600);
                    $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
                    $secs = floor($totalEarlyLeavingSeconds % 60);
                    $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                    if (strtotime($clockOut) > strtotime($endTime)) {
                        $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
                        $hours = floor($totalOvertimeSeconds / 3600);
                        $mins = floor($totalOvertimeSeconds / 60 % 60);
                        $secs = floor($totalOvertimeSeconds % 60);
                        $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    } else {
                        $overtime = '00:00:00';
                    }

                    $check = AttendanceEmployee::where('employee_id', $employeeId)->where('date', $row[$request['date']])->first();
                    if ($check) {
                        $check->update([
                            'late' => $late,
                            'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                            'overtime' => $overtime,
                            'clock_in' => $row[$request['clock_in']],
                            'clock_out' => $row[$request['clock_out']],
                            'status' => $status,
                        ]);
                    } else {
                        $time_sheet = AttendanceEmployee::create([
                            'employee_id' => $employeeId,
                            'date' => $row[$request['date']],
                            'status' => $status,
                            'late' => $late,
                            'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                            'overtime' => $overtime,
                            'clock_in' => $row[$request['clock_in']],
                            'clock_out' => $row[$request['clock_out']],
                            'created_by' => \Auth::user()->id,
                        ]);
                    }

                } catch (\Exception $e) {
                    $flag = 1;
                    $html .= '<tr>';
                    $html .= '<td>' . (isset($row[$request['employee_email']]) ? $row[$request['employee_email']] : '-') . '</td>';
                    $html .= '<td>' . (isset($row[$request['date']]) ? $row[$request['date']] : '-') . '</td>';
                    $html .= '<td>' . (isset($row[$request['clock_in']]) ? $row[$request['clock_in']] : '-') . '</td>';
                    $html .= '<td>' . (isset($row[$request['clock_out']]) ? $row[$request['clock_out']] : '-') . '</td>';
                    $html .= '</tr>';
                }
            } else {
                $flag = 1;
                $html .= '<tr>';
                $html .= '<td>' . (isset($row[$request['employee_email']]) ? $row[$request['employee_email']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['date']]) ? $row[$request['date']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['clock_in']]) ? $row[$request['clock_in']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['clock_out']]) ? $row[$request['clock_out']] : '-') . '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</table><br />';
        if ($flag == 1) {
            return response()->json([
                'html' => true,
                'response' => $html,
            ]);
        } else {
            return response()->json([
                'html' => false,
                'response' => 'Data Imported Successfully',
            ]);
        }
    }
}