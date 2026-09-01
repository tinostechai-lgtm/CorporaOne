<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AttendanceApiController extends Controller
{
    /**
     * Dashboard - Summary with holiday/week off integration
     * GET /api/attendance/dashboard
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();
        $date = $request->get('date', date('Y-m-d'));

        $startTime = Utility::getValByName('company_start_time') ?? '09:00:00';
        $endTime = Utility::getValByName('company_end_time') ?? '18:00:00';

        // Get all employees
        $employees = Employee::where('created_by', $creatorId)->get();
        $employeeIds = $employees->pluck('id')->toArray();

        // Get attendance for today
        $attendances = AttendanceEmployee::whereIn('employee_id', $employeeIds)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('employee_id');

        // Get approved leaves for today
        $leaves = \App\Models\Leave::where('created_by', $creatorId)
            ->where('status', 'Approved')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->get()
            ->keyBy('employee_id');

        // Get holidays
        $holidays = Holiday::where('created_by', $creatorId)
            ->where('type', 'holiday')
            ->whereDate('date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->get();

        $isCompanyHoliday = $holidays->isNotEmpty();

        // Get week offs
        $weekOffs = Holiday::where('created_by', $creatorId)
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

        // Calculate statistics
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
            $leaveType = null;

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
                }

                if (strtotime($clockIn) > strtotime($startTime)) {
                    $isLate = true;
                    $counts['late_coming']++;
                }

                if ($clockOut != '00:00:00' && strtotime($clockOut) < strtotime($endTime)) {
                    $isEarlyLeave = true;
                    $counts['early_leaving']++;
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
                'employee' => $employee,
                'user' => $employee->user,
                'attendance' => $attendance,
                'status' => $status,
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'worked_hours' => $workedSeconds > 0 ? gmdate('H:i:s', $workedSeconds) : '00:00:00',
                'late' => $isLate,
                'early_leave' => $isEarlyLeave,
                'leave_type' => $leaveType,
                'half_day' => $isHalfDay,
                'is_week_off' => $isWeekOff,
                'is_holiday' => $isHoliday,
                'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                'late_access_enabled' => $employee->late_access_enabled ?? false,
                'late_allowed_minutes' => $employee->late_allowed_minutes ?? 60,
            ];
        }

        $branches = Branch::where('created_by', $creatorId)->get();
        $departments = Department::where('created_by', $creatorId)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'totals' => $counts,
                'employees' => $employeeDetails,
                'branches' => $branches,
                'departments' => $departments,
                'is_holiday' => $isCompanyHoliday,
            ]
        ]);
    }

    /**
     * Live Attendance - Real-time status
     * GET /api/attendance/live
     */
    public function live(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();
        $today = date('Y-m-d');

        $startTime = Utility::getValByName('company_start_time') ?? '09:00:00';
        $endTime = Utility::getValByName('company_end_time') ?? '18:00:00';

        $employeesQuery = Employee::where('created_by', $creatorId);

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

        $employeeStatuses = [];

        foreach ($employees as $employee) {
            $attendance = $attendances->get($employee->id);
            $status = $this->getAttendanceStatus($attendance, $startTime, $endTime);

            $isOnBreak = false;
            if ($attendance && !empty($attendance->tea_break_out) && $attendance->tea_break_out != '00:00:00'
                && (empty($attendance->tea_break_in) || $attendance->tea_break_in == '00:00:00')) {
                $isOnBreak = true;
            }

            $employeeStatuses[] = [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'employee_code' => $employee->employee_id,
                    'avatar' => $employee->user ? $employee->user->avatar : null,
                    'department' => $employee->department ? $employee->department->name : null,
                    'designation' => $employee->designation ? $employee->designation->name : null,
                ],
                'attendance' => $attendance ? [
                    'id' => $attendance->id,
                    'clock_in' => $attendance->clock_in != '00:00:00' ? Carbon::parse($attendance->clock_in)->format('h:i A') : null,
                    'clock_out' => $attendance->clock_out != '00:00:00' ? Carbon::parse($attendance->clock_out)->format('h:i A') : null,
                    'status' => $attendance->status,
                    'late' => $attendance->late,
                    'worked_hours' => $this->calculateWorkedHours($attendance),
                ] : null,
                'status' => $status,
                'is_clocked_in' => $attendance && $attendance->clock_in != '00:00:00',
                'is_clocked_out' => $attendance && $attendance->clock_out != '00:00:00',
                'is_late' => $status === 'late',
                'is_early_leave' => $status === 'early_leave',
                'is_on_break' => $isOnBreak,
                'is_half_day' => $attendance && $attendance->status === 'Half Day',
                'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                'late_access_enabled' => $employee->late_access_enabled ?? false,
                'late_allowed_minutes' => $employee->late_allowed_minutes ?? 60,
            ];
        }

        $statusCounts = [
            'all' => count($employeeStatuses),
            'in' => count(array_filter($employeeStatuses, fn($item) => $item['status'] === 'in')),
            'out' => count(array_filter($employeeStatuses, fn($item) => $item['status'] === 'out')),
            'not_punched' => count(array_filter($employeeStatuses, fn($item) => $item['status'] === 'not_punched')),
            'break' => count(array_filter($employeeStatuses, fn($item) => $item['status'] === 'break')),
            'late' => count(array_filter($employeeStatuses, fn($item) => $item['status'] === 'late')),
            'early_leave' => count(array_filter($employeeStatuses, fn($item) => $item['status'] === 'early_leave')),
            'half_day' => count(array_filter($employeeStatuses, fn($item) => $item['is_half_day'])),
        ];

        $branches = Branch::where('created_by', $creatorId)->get();
        $departments = Department::where('created_by', $creatorId)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'employees' => $employeeStatuses,
                'status_counts' => $statusCounts,
                'branches' => $branches,
                'departments' => $departments,
                'date' => $today,
            ]
        ]);
    }

    /**
     * Daily Attendance - Detailed daily view
     * GET /api/attendance/daily
     */
    public function daily(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();
        $date = $request->get('date', date('Y-m-d'));

        $startTime = Utility::getValByName('company_start_time') ?? '09:00:00';
        $endTime = Utility::getValByName('company_end_time') ?? '18:00:00';

        $employeesQuery = Employee::where('created_by', $creatorId);

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

        $employeeStatuses = [];

        foreach ($employees as $employee) {
            $attendance = $attendances->get($employee->id);
            $status = $this->getAttendanceStatus($attendance, $startTime, $endTime);

            $employeeStatuses[] = [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'employee_code' => $employee->employee_id,
                    'department' => $employee->department ? $employee->department->name : null,
                    'designation' => $employee->designation ? $employee->designation->name : null,
                ],
                'attendance' => $attendance ? [
                    'id' => $attendance->id,
                    'clock_in' => $attendance->clock_in,
                    'clock_out' => $attendance->clock_out,
                    'status' => $attendance->status,
                    'late' => $attendance->late,
                    'early_leaving' => $attendance->early_leaving,
                    'overtime' => $attendance->overtime,
                    'worked_hours' => $this->calculateWorkedHours($attendance),
                ] : null,
                'status' => $status,
                'is_clocked_in' => $attendance && $attendance->clock_in != '00:00:00',
                'is_clocked_out' => $attendance && $attendance->clock_out != '00:00:00',
                'is_late' => $status === 'late',
                'is_early_leave' => $status === 'early_leave',
                'is_half_day' => $attendance && $attendance->status === 'Half Day',
                'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                'late_access_enabled' => $employee->late_access_enabled ?? false,
                'late_allowed_minutes' => $employee->late_allowed_minutes ?? 60,
            ];
        }

        $statusCounts = [
            'all' => count($employeeStatuses),
            'in' => count(array_filter($employeeStatuses, fn($item) => $item['status'] === 'in')),
            'out' => count(array_filter($employeeStatuses, fn($item) => $item['status'] === 'out')),
            'not_punched' => count(array_filter($employeeStatuses, fn($item) => $item['status'] === 'not_punched')),
            'break' => count(array_filter($employeeStatuses, fn($item) => $item['status'] === 'break')),
            'late' => count(array_filter($employeeStatuses, fn($item) => $item['status'] === 'late')),
            'early_leave' => count(array_filter($employeeStatuses, fn($item) => $item['status'] === 'early_leave')),
            'half_day' => count(array_filter($employeeStatuses, fn($item) => $item['is_half_day'])),
        ];

        $branches = Branch::where('created_by', $creatorId)->get();
        $departments = Department::where('created_by', $creatorId)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'employees' => $employeeStatuses,
                'status_counts' => $statusCounts,
                'branches' => $branches,
                'departments' => $departments,
            ]
        ]);
    }

    /**
     * Company Roster - Weekly roster
     * GET /api/attendance/roster
     */
    public function roster(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $startOfWeek = $request->get('week', date('Y-m-d', strtotime('monday this week')));
        $endOfWeek = date('Y-m-d', strtotime($startOfWeek . ' +6 days'));

        $daysOfWeek = [];
        for ($i = 0; $i < 7; $i++) {
            $daysOfWeek[] = date('Y-m-d', strtotime($startOfWeek . " +$i days"));
        }

        $employees = Employee::where('created_by', $creatorId)->get();
        $employeeIds = $employees->pluck('id')->toArray();

        $weekAttendance = AttendanceEmployee::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->get()
            ->groupBy('employee_id');

        $employeeStatuses = [];

        foreach ($employees as $employee) {
            $attendanceRecords = $weekAttendance->get($employee->id) ?? collect();

            $dailyStatus = [];
            foreach ($daysOfWeek as $day) {
                $attendance = $attendanceRecords->firstWhere('date', $day);

                if ($attendance && $attendance->clock_in != '00:00:00') {
                    $status = 'Present';
                    if ($attendance->status === 'Half Day') {
                        $status = 'Half Day';
                    }
                } else {
                    // Check if it's a week off
                    $dayOfWeekNumber = date('N', strtotime($day));
                    $isWeekOff = $this->isEmployeeWeekOff($employee->id, $day, $creatorId);
                    $status = $isWeekOff ? 'Week Off' : 'Absent';
                }

                $dailyStatus[$day] = [
                    'status' => $status,
                    'clock_in' => $attendance ? $attendance->clock_in : null,
                    'clock_out' => $attendance ? $attendance->clock_out : null,
                    'worked_hours' => $attendance ? $this->calculateWorkedHours($attendance) : null,
                ];
            }

            $employeeStatuses[] = [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'employee_code' => $employee->employee_id,
                    'department' => $employee->department ? $employee->department->name : null,
                    'designation' => $employee->designation ? $employee->designation->name : null,
                ],
                'daily_status' => $dailyStatus,
                'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'week_start' => $startOfWeek,
                'week_end' => $endOfWeek,
                'days' => $daysOfWeek,
                'employees' => $employeeStatuses,
            ]
        ]);
    }

    /**
     * Employee Monthly Attendance
     * GET /api/attendance/employee/{employeeId}
     */
    public function employeeAttendance(Request $request, $employeeId)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $employee = Employee::where('created_by', $creatorId)
            ->where('id', $employeeId)
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        $startDate = $year . '-' . $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $attendances = AttendanceEmployee::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $summary = [
            'total_days' => 0,
            'present' => 0,
            'absent' => 0,
            'half_day' => 0,
            'late' => 0,
            'early_leave' => 0,
            'total_worked_hours' => 0,
        ];

        $dailyData = [];

        foreach ($attendances as $attendance) {
            $summary['total_days']++;

            if ($attendance->status === 'Present') {
                $summary['present']++;
            } elseif ($attendance->status === 'Half Day') {
                $summary['half_day']++;
            } else {
                $summary['absent']++;
            }

            if ($attendance->late != '00:00:00') {
                $summary['late']++;
            }

            if ($attendance->early_leaving != '00:00:00') {
                $summary['early_leave']++;
            }

            // Calculate worked hours
            if ($attendance->clock_in != '00:00:00' && $attendance->clock_out != '00:00:00') {
                $start = Carbon::parse($attendance->clock_in);
                $end = Carbon::parse($attendance->clock_out);
                $diff = $start->diff($end);
                $hours = $diff->h + ($diff->days * 24);
                $minutes = $diff->i;
                $summary['total_worked_hours'] += $hours + ($minutes / 60);
            }

            $dailyData[] = [
                'date' => $attendance->date,
                'day' => Carbon::parse($attendance->date)->format('D'),
                'clock_in' => $attendance->clock_in,
                'clock_out' => $attendance->clock_out,
                'status' => $attendance->status,
                'late' => $attendance->late,
                'early_leaving' => $attendance->early_leaving,
                'overtime' => $attendance->overtime,
                'worked_hours' => $this->calculateWorkedHours($attendance),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'employee_code' => $employee->employee_id,
                    'department' => $employee->department ? $employee->department->name : null,
                    'designation' => $employee->designation ? $employee->designation->name : null,
                ],
                'month' => $month,
                'year' => $year,
                'summary' => $summary,
                'daily' => $dailyData,
                'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                'late_access_enabled' => $employee->late_access_enabled ?? false,
                'late_allowed_minutes' => $employee->late_allowed_minutes ?? 60,
            ]
        ]);
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    /**
     * Get attendance status
     */
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
                        if ($lateMinutes > $lateAllowedMinutes && $attendance->status === 'Half Day') {
                            return 'half_day';
                        }
                    } else {
                        if ($attendance->status === 'Half Day') {
                            return 'half_day';
                        }
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

    /**
     * Calculate worked hours
     */
    private function calculateWorkedHours($attendance)
    {
        if (!$attendance || $attendance->clock_in == '00:00:00') {
            return '00:00:00';
        }

        if ($attendance->clock_out != '00:00:00') {
            $start = Carbon::parse($attendance->clock_in);
            $end = Carbon::parse($attendance->clock_out);
            $diff = $start->diff($end);
            return $diff->format('%H:%I:%S');
        }

        $start = Carbon::parse($attendance->clock_in);
        $now = Carbon::now();
        $diff = $start->diff($now);
        return $diff->format('%H:%I:%S');
    }

    /**
     * Check if employee has week off on a date
     */
    private function isEmployeeWeekOff($employeeId, $date, $creatorId)
    {
        $dayOfWeek = date('N', strtotime($date));

        $weekOffs = Holiday::where('created_by', $creatorId)
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
}