<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Leave;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Attendance Dashboard
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();
        $date = $request->get('date', date('Y-m-d'));

        $startTime = Utility::getValByName('company_start_time') ?? '09:00:00';
        $endTime = Utility::getValByName('company_end_time') ?? '18:00:00';

        // Get employees with filters
        $employeesQuery = Employee::where('created_by', $creatorId)
            ->with(['user', 'branch', 'department', 'designation']);

        if ($request->has('branch_id')) {
            $employeesQuery->where('branch_id', $request->branch_id);
        }
        if ($request->has('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }
        if ($request->has('search')) {
            $employeesQuery->where('name', 'like', '%' . $request->search . '%');
        }

        $employees = $employeesQuery->get();
        $employeeIds = $employees->pluck('id')->toArray();

        // Get attendance for the date
        $attendances = AttendanceEmployee::whereIn('employee_id', $employeeIds)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('employee_id');

        // Get approved leaves
        $leaves = Leave::where('created_by', $creatorId)
            ->where('status', 'Approved')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->get()
            ->keyBy('employee_id');

        // Check holidays
        $holidays = Holiday::where('created_by', $creatorId)
            ->where('type', 'holiday')
            ->whereDate('date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->get();

        $isCompanyHoliday = $holidays->isNotEmpty();

        // Check week offs
        $weekOffs = Holiday::where('created_by', $creatorId)
            ->where('type', 'week_off')
            ->get();

        $dayOfWeek = date('N', strtotime($date));

        $weekOffEmployeeIds = [];
        foreach ($weekOffs as $weekOff) {
            $weekOffDays = $this->getWeekOffDaysArray($weekOff);
            if (is_array($weekOffDays) && in_array($dayOfWeek, $weekOffDays)) {
                if ($weekOff->applicable_to == 'all') {
                    $weekOffEmployeeIds = array_merge($weekOffEmployeeIds, $employeeIds);
                } else {
                    $departments = $this->getDepartmentsArray($weekOff);
                    if (is_array($departments) && !empty($departments)) {
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

        // Build response
        $counts = [
            'present' => 0,
            'absent' => 0,
            'half_day' => 0,
            'week_off' => 0,
            'holiday' => 0,
            'paid_leave' => 0,
            'unpaid_leave' => 0,
            'late_coming' => 0,
            'early_leaving' => 0,
            'overtime_working_day' => 0,
            'overtime_week_off' => 0,
            'overtime_holiday' => 0,
            'total_employees' => $employees->count(),
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

            $employeeDetails[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'employee_code' => $employee->employee_id,
                'email' => $employee->email,
                'branch' => $employee->branch ? $employee->branch->name : null,
                'department' => $employee->department ? $employee->department->name : null,
                'designation' => $employee->designation ? $employee->designation->name : null,
                'avatar' => $employee->user ? $employee->user->avatar : null,
                'status' => $status,
                'clock_in' => $clockIn != '00:00:00' ? Carbon::parse($clockIn)->format('h:i A') : null,
                'clock_out' => $clockOut != '00:00:00' ? Carbon::parse($clockOut)->format('h:i A') : null,
                'worked_hours' => $workedSeconds > 0 ? gmdate('H:i:s', $workedSeconds) : '00:00:00',
                'is_late' => $isLate,
                'is_early_leave' => $isEarlyLeave,
                'is_half_day' => $isHalfDay,
                'overtime' => $overtimeSeconds > 0 ? gmdate('H:i:s', $overtimeSeconds) : '00:00:00',
                'overtime_type' => $overtimeType,
                'leave_type' => $leaveType,
                'is_week_off' => $isWeekOff,
                'is_holiday' => $isHoliday,
                'half_day_threshold' => $halfDayThreshold,
            ];
        }

        $branches = Branch::where('created_by', $creatorId)->get(['id', 'name']);
        $departments = Department::where('created_by', $creatorId)->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'summary' => $counts,
                'employees' => $employeeDetails,
                'filters' => [
                    'branches' => $branches,
                    'departments' => $departments,
                ],
                'holiday_info' => [
                    'is_holiday' => $isCompanyHoliday,
                    'holiday_name' => $isCompanyHoliday ? $holidays->first()->occasion : null,
                ],
                'week_off_info' => [
                    'is_week_off_for_all' => in_array('all', $weekOffEmployeeIds),
                    'employee_count_on_week_off' => count($weekOffEmployeeIds),
                ],
            ]
        ]);
    }

    /**
     * Live Attendance
     */
    public function live(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();
        $today = date('Y-m-d');

        $employeesQuery = Employee::where('created_by', $creatorId)
            ->with(['user', 'branch', 'department', 'designation']);

        if ($request->has('branch_id')) {
            $employeesQuery->where('branch_id', $request->branch_id);
        }
        if ($request->has('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }

        $employees = $employeesQuery->get();
        $employeeIds = $employees->pluck('id')->toArray();

        $attendances = AttendanceEmployee::whereIn('employee_id', $employeeIds)
            ->whereDate('date', $today)
            ->get()
            ->keyBy('employee_id');

        $employeeStatuses = [];

        foreach ($employees as $employee) {
            $attendance = $attendances->get($employee->id);
            
            $status = 'not_punched';
            $isClockedIn = false;
            $isClockedOut = false;
            $isLate = false;
            $isLive = false;
            $clockIn = '00:00:00';
            $clockOut = '00:00:00';
            $workedHours = '00:00:00';

            if ($attendance && $attendance->clock_in != '00:00:00') {
                $isClockedIn = true;
                $clockIn = $attendance->clock_in;
                $status = 'in';

                if ($attendance->clock_out == '00:00:00') {
                    $isLive = true;
                } else {
                    $isClockedOut = true;
                    $clockOut = $attendance->clock_out;
                    $status = 'out';
                }

                if ($isLive && !empty($attendance->tea_break_out) && $attendance->tea_break_out != '00:00:00'
                    && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00')) {
                    $status = 'break';
                }
            }

            $employeeStatuses[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'employee_code' => $employee->employee_id,
                'branch' => $employee->branch ? $employee->branch->name : null,
                'department' => $employee->department ? $employee->department->name : null,
                'avatar' => $employee->user ? $employee->user->avatar : null,
                'status' => $status,
                'status_label' => ucfirst(str_replace('_', ' ', $status)),
                'is_clocked_in' => $isClockedIn,
                'is_clocked_out' => $isClockedOut,
                'is_live' => $isLive,
                'clock_in' => $clockIn != '00:00:00' ? Carbon::parse($clockIn)->format('h:i A') : null,
                'clock_out' => $clockOut != '00:00:00' ? Carbon::parse($clockOut)->format('h:i A') : null,
                'worked_hours' => $workedHours,
            ];
        }

        $statusCounts = [
            'all' => count($employeeStatuses),
            'in' => collect($employeeStatuses)->filter(fn($item) => $item['status'] === 'in')->count(),
            'out' => collect($employeeStatuses)->filter(fn($item) => $item['status'] === 'out')->count(),
            'not_punched' => collect($employeeStatuses)->filter(fn($item) => $item['status'] === 'not_punched')->count(),
            'break' => collect($employeeStatuses)->filter(fn($item) => $item['status'] === 'break')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $today,
                'status_counts' => $statusCounts,
                'employees' => $employeeStatuses,
            ]
        ]);
    }

    /**
     * Daily Attendance
     */
    public function daily(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();
        $date = $request->get('date', date('Y-m-d'));

        $employeesQuery = Employee::where('created_by', $creatorId)
            ->with(['user', 'branch', 'department', 'designation']);

        if ($request->has('branch_id')) {
            $employeesQuery->where('branch_id', $request->branch_id);
        }
        if ($request->has('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }

        $employees = $employeesQuery->get();
        $employeeIds = $employees->pluck('id')->toArray();

        $attendances = AttendanceEmployee::whereIn('employee_id', $employeeIds)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('employee_id');

        $employeeStatuses = [];
        $statusCounts = [
            'all' => 0,
            'present' => 0,
            'absent' => 0,
            'half_day' => 0,
        ];

        foreach ($employees as $employee) {
            $attendance = $attendances->get($employee->id);
            $statusCounts['all']++;

            $status = 'Absent';
            $clockIn = '00:00:00';
            $clockOut = '00:00:00';
            $workedSeconds = 0;
            $isPresent = false;

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
                    $status = 'Half Day';
                    $statusCounts['half_day']++;
                }
            }

            if ($isPresent) {
                $statusCounts['present']++;
            } else {
                $statusCounts['absent']++;
            }

            $employeeStatuses[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'employee_code' => $employee->employee_id,
                'branch' => $employee->branch ? $employee->branch->name : null,
                'department' => $employee->department ? $employee->department->name : null,
                'status' => $status,
                'clock_in' => $clockIn != '00:00:00' ? Carbon::parse($clockIn)->format('h:i A') : null,
                'clock_out' => $clockOut != '00:00:00' ? Carbon::parse($clockOut)->format('h:i A') : null,
                'worked_hours' => $workedSeconds > 0 ? gmdate('H:i:s', $workedSeconds) : '00:00:00',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'summary' => $statusCounts,
                'employees' => $employeeStatuses,
            ]
        ]);
    }

    /**
     * Company Roster
     */
    public function roster(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $startOfWeek = $request->get('week', date('Y-m-d', strtotime('monday this week')));
        $endOfWeek = date('Y-m-d', strtotime($startOfWeek . ' +6 days'));

        $employeesQuery = Employee::where('created_by', $creatorId)
            ->with(['user', 'branch', 'department', 'designation']);

        if ($request->has('branch_id')) {
            $employeesQuery->where('branch_id', $request->branch_id);
        }
        if ($request->has('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }

        $employees = $employeesQuery->get();
        $employeeIds = $employees->pluck('id')->toArray();

        $attendances = AttendanceEmployee::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->get()
            ->groupBy('employee_id');

        $daysOfWeek = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime($startOfWeek . " +$i days"));
            $daysOfWeek[] = [
                'date' => $date,
                'day_name' => date('l', strtotime($date)),
                'day_short' => date('D', strtotime($date)),
            ];
        }

        $roster = [];

        foreach ($employees as $employee) {
            $attendanceRecords = $attendances->get($employee->id) ?? collect();

            $dailyStatus = [];
            $totalPresent = 0;
            $totalAbsent = 0;

            foreach ($daysOfWeek as $day) {
                $date = $day['date'];
                $attendance = $attendanceRecords->firstWhere('date', $date);

                $status = 'Absent';
                $clockIn = null;
                $clockOut = null;
                $workedHours = null;

                if ($attendance && $attendance->clock_in != '00:00:00') {
                    $clockIn = $attendance->clock_in;
                    $clockOut = $attendance->clock_out;
                    $status = $attendance->status ?? 'Present';

                    $start = Carbon::parse($clockIn);
                    if ($clockOut != '00:00:00') {
                        $end = Carbon::parse($clockOut);
                        $workedHours = $start->diff($end)->format('%H:%I:%S');
                    }
                    $totalPresent++;
                } else {
                    $totalAbsent++;
                }

                $dailyStatus[$date] = [
                    'date' => $date,
                    'day_name' => $day['day_name'],
                    'status' => $status,
                    'clock_in' => $clockIn ? Carbon::parse($clockIn)->format('h:i A') : null,
                    'clock_out' => $clockOut ? Carbon::parse($clockOut)->format('h:i A') : null,
                    'worked_hours' => $workedHours,
                ];
            }

            $roster[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'employee_code' => $employee->employee_id,
                'branch' => $employee->branch ? $employee->branch->name : null,
                'department' => $employee->department ? $employee->department->name : null,
                'designation' => $employee->designation ? $employee->designation->name : null,
                'daily_status' => $dailyStatus,
                'summary' => [
                    'present' => $totalPresent,
                    'absent' => $totalAbsent,
                ],
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'week_start' => $startOfWeek,
                'week_end' => $endOfWeek,
                'days' => $daysOfWeek,
                'roster' => $roster,
            ]
        ]);
    }

    /**
     * Employee Monthly Attendance
     */
    public function employeeAttendance(Request $request, $employeeId)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $employee = Employee::where('created_by', $creatorId)
            ->where('id', $employeeId)
            ->with(['user', 'branch', 'department', 'designation'])
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $startDate = date($year . '-' . $month . '-01');
        $endDate = date($year . '-' . $month . '-t');

        $attendances = AttendanceEmployee::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy('date');

        $calendar = [];
        $currentDate = Carbon::parse($startDate);

        $summary = [
            'present' => 0,
            'absent' => 0,
            'half_day' => 0,
            'total_worked_hours' => 0,
        ];

        while ($currentDate <= Carbon::parse($endDate)) {
            $date = $currentDate->format('Y-m-d');
            $attendance = $attendances->get($date);

            $status = 'Absent';
            $clockIn = null;
            $clockOut = null;
            $workedHours = '00:00:00';

            if ($attendance && $attendance->clock_in != '00:00:00') {
                $clockIn = $attendance->clock_in;
                $clockOut = $attendance->clock_out;
                $status = $attendance->status ?? 'Present';

                if ($attendance->status == 'Half Day') {
                    $status = 'Half Day';
                    $summary['half_day']++;
                }

                $start = Carbon::parse($clockIn);
                if ($clockOut != '00:00:00') {
                    $end = Carbon::parse($clockOut);
                    $workedHours = $start->diff($end)->format('%H:%I:%S');
                    $summary['total_worked_hours'] += $start->diffInSeconds($end);
                } else {
                    $workedHours = 'In Progress';
                }

                $summary['present']++;
            } else {
                $summary['absent']++;
            }

            $calendar[] = [
                'date' => $date,
                'day_name' => $currentDate->format('l'),
                'day_of_month' => $currentDate->format('d'),
                'status' => $status,
                'clock_in' => $clockIn ? Carbon::parse($clockIn)->format('h:i A') : null,
                'clock_out' => $clockOut ? Carbon::parse($clockOut)->format('h:i A') : null,
                'worked_hours' => $workedHours,
            ];

            $currentDate->addDay();
        }

        $totalWorkedHoursFormatted = $summary['total_worked_hours'] > 0 
            ? gmdate('H:i:s', $summary['total_worked_hours']) 
            : '00:00:00';

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'employee_code' => $employee->employee_id,
                    'branch' => $employee->branch ? $employee->branch->name : null,
                    'department' => $employee->department ? $employee->department->name : null,
                ],
                'month' => $month,
                'year' => $year,
                'month_name' => Carbon::parse($startDate)->format('F Y'),
                'calendar' => $calendar,
                'summary' => [
                    'present' => $summary['present'],
                    'absent' => $summary['absent'],
                    'half_day' => $summary['half_day'],
                    'total_days' => count($calendar),
                    'total_worked_hours' => $totalWorkedHoursFormatted,
                ],
            ]
        ]);
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    private function getWeekOffDaysArray($holiday)
    {
        $days = $holiday->week_off_days;
        if (is_string($days)) {
            $days = json_decode($days, true);
        }
        return is_array($days) ? $days : [];
    }

    private function getDepartmentsArray($holiday)
    {
        $departments = $holiday->departments;
        if (is_string($departments)) {
            $departments = json_decode($departments, true);
        }
        return is_array($departments) ? $departments : [];
    }

        // ============================================================
    // CLOCK IN / CLOCK OUT METHODS
    // ============================================================

    /**
     * Clock In an employee
     */
    public function clockIn(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $employee = Employee::where('created_by', $creatorId)
            ->where('id', $request->employee_id)
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $today = date('Y-m-d');
        $now = date('H:i:s');
        $startTime = Utility::getValByName('company_start_time') ?? '09:00:00';

        // Check if already clocked in today
        $existingAttendance = AttendanceEmployee::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->clock_in != '00:00:00' && $existingAttendance->clock_out == '00:00:00') {
            return response()->json([
                'success' => false,
                'message' => 'Employee already clocked in today',
                'data' => [
                    'clock_in_time' => $existingAttendance->clock_in,
                    'attendance_id' => $existingAttendance->id,
                ]
            ], 422);
        }

        if ($existingAttendance && $existingAttendance->clock_in != '00:00:00' && $existingAttendance->clock_out != '00:00:00') {
            return response()->json([
                'success' => false,
                'message' => 'Employee already clocked out for today',
            ], 422);
        }

        // Calculate late
        $late = '00:00:00';
        $isLate = false;
        $status = 'Present';

        if (strtotime($now) > strtotime($startTime)) {
            $lateSeconds = strtotime($now) - strtotime($startTime);
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

        if (!$existingAttendance) {
            $attendance = new AttendanceEmployee();
            $attendance->employee_id = $employee->id;
            $attendance->date = $today;
            $attendance->created_by = $creatorId;
        } else {
            $attendance = $existingAttendance;
        }

        $attendance->clock_in = $now;
        $attendance->clock_out = '00:00:00';
        $attendance->status = $status;
        $attendance->late = $late;
        $attendance->early_leaving = '00:00:00';
        $attendance->overtime = '00:00:00';
        $attendance->total_rest = '00:00:00';
        $attendance->marked_by = 'manual';
        
        if ($request->has('latitude')) {
            $attendance->latitude = $request->latitude;
        }
        if ($request->has('longitude')) {
            $attendance->longitude = $request->longitude;
        }
        
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Clock in successful',
            'data' => [
                'attendance_id' => $attendance->id,
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'date' => $today,
                'clock_in' => $now,
                'status' => $status,
                'is_late' => $isLate,
                'late_duration' => $late,
            ]
        ]);
    }

    /**
     * Clock Out an employee
     */
    public function clockOut(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $employee = Employee::where('created_by', $creatorId)
            ->where('id', $request->employee_id)
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $today = date('Y-m-d');
        $now = date('H:i:s');
        $endTime = Utility::getValByName('company_end_time') ?? '18:00:00';

        $attendance = AttendanceEmployee::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || $attendance->clock_in == '00:00:00') {
            return response()->json([
                'success' => false,
                'message' => 'Employee is not clocked in'
            ], 422);
        }

        if ($attendance->clock_out != '00:00:00') {
            return response()->json([
                'success' => false,
                'message' => 'Employee already clocked out'
            ], 422);
        }

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

        $attendance->clock_out = $now;
        $attendance->early_leaving = $earlyLeaving;
        $attendance->overtime = $overtime;
        $attendance->marked_by = 'manual';
        
        if ($request->has('latitude')) {
            $attendance->latitude = $request->latitude;
        }
        if ($request->has('longitude')) {
            $attendance->longitude = $request->longitude;
        }
        
        $attendance->save();

        // Calculate worked hours
        $workedSeconds = strtotime($now) - strtotime($attendance->clock_in);
        $hours = floor($workedSeconds / 3600);
        $mins = floor(($workedSeconds % 3600) / 60);
        $workedHours = sprintf('%02d:%02d', $hours, $mins);

        return response()->json([
            'success' => true,
            'message' => 'Clock out successful',
            'data' => [
                'attendance_id' => $attendance->id,
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'date' => $today,
                'clock_in' => $attendance->clock_in,
                'clock_out' => $now,
                'worked_hours' => $workedHours,
                'is_early_leave' => $earlyLeaving != '00:00:00',
                'early_leaving' => $earlyLeaving,
                'overtime' => $overtime,
            ]
        ]);
    }

    /**
     * Get today's attendance
     */
    public function todayAttendance(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();
        $today = date('Y-m-d');

        $attendance = AttendanceEmployee::with('employee')
            ->whereDate('date', $today)
            ->where('created_by', $creatorId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Get all attendance records
     */
    public function indexAttendance(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $query = AttendanceEmployee::with('employee')
            ->where('created_by', $creatorId)
            ->orderBy('date', 'desc');

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $attendance = $query->paginate($request->limit ?? 50);

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Show specific attendance record
     */
    public function showAttendance($id)
    {
        $attendance = AttendanceEmployee::with('employee')->find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Update attendance record
     */
    public function updateAttendance(Request $request, $id)
    {
        $attendance = AttendanceEmployee::find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found'
            ], 404);
        }

        $attendance->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully',
            'data' => $attendance
        ]);
    }

    /**
     * Delete attendance record
     */
    public function deleteAttendance($id)
    {
        $attendance = AttendanceEmployee::find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found'
            ], 404);
        }

        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance deleted successfully'
        ]);
    }

    /**
     * Store attendance (for manual entry)
     */
    public function storeAttendance(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'clock_in' => 'required',
            'clock_out' => 'nullable',
            'status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $attendance = AttendanceEmployee::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out ?? '00:00:00',
            'status' => $request->status ?? 'Present',
            'created_by' => $creatorId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance created successfully',
            'data' => $attendance
        ]);
    }
    
}