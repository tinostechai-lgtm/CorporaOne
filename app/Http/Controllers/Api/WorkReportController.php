<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkReport;
use App\Models\Employee;
use App\Models\AttendanceEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class WorkReportController extends Controller
{
    /**
     * Get the authenticated user or return error.
     */
    private function getAuthenticatedUser(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(response()->json(['success' => false, 'message' => 'Unauthenticated'], 401));
        }
        return $user;
    }

    /**
     * Get current authenticated user's employee record
     */
    private function getEmployee(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        return Employee::where('user_id', $user->id)->first();
    }

    /**
     * GET /api/work-reports
     * List all work reports (HRM/Admin view)
     */
    public function index(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);

            // Check permission - using can() method
            if (!$user->can('manage work report')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied.'
                ], 403);
            }

            $perPage = $request->input('per_page', 15);
            $status = $request->input('status');
            $employeeId = $request->input('employee_id');
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');

            $query = WorkReport::with(['employee', 'employee.user', 'reviewer'])
                ->where('created_by', $user->creatorId());

            // Apply filters
            if ($status) {
                $query->where('review_status', $status);
            }

            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            if ($fromDate) {
                $query->whereDate('date', '>=', $fromDate);
            }

            if ($toDate) {
                $query->whereDate('date', '<=', $toDate);
            }

            $reports = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $reports,
                'message' => 'Work reports retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('API: Work Report Index Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve work reports: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/work-reports/my
     * Get current employee's own reports
     */
    public function myReports(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            $employee = $this->getEmployee($request);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found.'
                ], 404);
            }

            $perPage = $request->input('per_page', 10);
            $status = $request->input('status');

            $query = WorkReport::where('employee_id', $employee->id);

            if ($status) {
                $query->where('review_status', $status);
            }

            $reports = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $reports,
                'message' => 'Your work reports retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('API: My Reports Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve your reports: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/work-reports/{id}
     * Get specific work report details
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            $employee = $this->getEmployee($request);

            $report = WorkReport::with(['employee', 'employee.user', 'attendance', 'reviewer'])
                ->findOrFail($id);

            // Check permission: user must be creator OR the employee who owns the report
            $creatorId = $user->creatorId();
            if ($report->created_by != $creatorId && ($employee && $report->employee_id != $employee->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'Work report retrieved successfully.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Work report not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('API: Show Report Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve work report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/work-reports
     * Create a new work report
     */
    public function store(Request $request)
    {
        try {
            Log::info('API: Work report submission started', [
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            $user = $this->getAuthenticatedUser($request);
            $employee = $this->getEmployee($request);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'work_description' => 'required|string|min:3|max:5000',
                'date' => 'required|date|date_format:Y-m-d',
                'attendance_id' => 'nullable|exists:attendance_employees,id',
                'clock_in' => 'nullable|date_format:H:i:s',
                'clock_out' => 'nullable|date_format:H:i:s',
                'quick_tasks' => 'nullable|array',
                'quick_tasks.*' => 'string|max:100',
                'achievements' => 'nullable|string|max:1000',
                'challenges' => 'nullable|string|max:1000',
                'tomorrow_plan' => 'nullable|string|max:1000',
                'hours_project' => 'nullable|numeric|min:0|max:24',
                'hours_meeting' => 'nullable|numeric|min:0|max:24',
                'hours_admin' => 'nullable|numeric|min:0|max:24',
                'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png'
            ]);

            if ($validator->fails()) {
                Log::error('API: Work report validation failed', [
                    'errors' => $validator->errors()->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 422);
            }

            // Check if already submitted today
            $existingReport = WorkReport::where('employee_id', $employee->id)
                ->whereDate('date', $request->date)
                ->first();

            if ($existingReport) {
                return response()->json([
                    'success' => false,
                    'message' => 'Work report already submitted for this date.'
                ], 422);
            }

            // Prepare data
            $data = $request->all();
            $data['employee_id'] = $employee->id;

            // Process quick tasks
            if (isset($data['quick_tasks']) && is_array($data['quick_tasks'])) {
                $data['quick_tasks'] = implode(', ', array_filter($data['quick_tasks']));
            } else {
                $data['quick_tasks'] = null;
            }

            // Set default values for hours
            $data['hours_project'] = $data['hours_project'] ?? 0;
            $data['hours_meeting'] = $data['hours_meeting'] ?? 0;
            $data['hours_admin'] = $data['hours_admin'] ?? 0;

            // Set created_by
            $data['created_by'] = $user->creatorId();

            // Set default status
            $data['review_status'] = 'pending';

            // Handle file attachment
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = 'work_report_' . $employee->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('work_reports', $filename, 'public');
                $data['attachment'] = $path;
            }

            // Create work report
            $workReport = WorkReport::create($data);

            Log::info('API: Work Report Submitted Successfully', [
                'work_report_id' => $workReport->id,
                'employee_id' => $employee->id,
                'attendance_id' => $data['attendance_id'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Work report submitted successfully!',
                'data' => $workReport->load(['employee', 'attendance'])
            ], 201);

        } catch (\Exception $e) {
            Log::error('API: Work Report Submit Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit work report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/work-reports/{id}
     * Update a work report (only if pending)
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            $employee = $this->getEmployee($request);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found.'
                ], 404);
            }

            $report = WorkReport::findOrFail($id);

            // Check if user owns this report
            if ($report->employee_id != $employee->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not own this report.'
                ], 403);
            }

            // Only allow updating if not reviewed
            if ($report->review_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This report has already been reviewed and cannot be edited.'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'work_description' => 'sometimes|required|string|min:3|max:5000',
                'quick_tasks' => 'nullable|array',
                'quick_tasks.*' => 'string|max:100',
                'achievements' => 'nullable|string|max:1000',
                'challenges' => 'nullable|string|max:1000',
                'tomorrow_plan' => 'nullable|string|max:1000',
                'hours_project' => 'nullable|numeric|min:0|max:24',
                'hours_meeting' => 'nullable|numeric|min:0|max:24',
                'hours_admin' => 'nullable|numeric|min:0|max:24',
                'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 422);
            }

            $data = $request->all();

            // Process quick tasks
            if (isset($data['quick_tasks']) && is_array($data['quick_tasks'])) {
                $data['quick_tasks'] = implode(', ', array_filter($data['quick_tasks']));
            }

            // Handle file attachment
            if ($request->hasFile('attachment')) {
                // Delete old attachment if exists
                if ($report->attachment) {
                    Storage::disk('public')->delete($report->attachment);
                }

                $file = $request->file('attachment');
                $filename = 'work_report_' . $employee->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('work_reports', $filename, 'public');
                $data['attachment'] = $path;
            }

            $report->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Work report updated successfully!',
                'data' => $report->fresh()->load(['employee', 'attendance'])
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Work report not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('API: Update Report Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update work report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/work-reports/{id}
     * Delete a work report (only if pending)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            $employee = $this->getEmployee($request);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found.'
                ], 404);
            }

            $report = WorkReport::findOrFail($id);

            // Check if user owns this report
            if ($report->employee_id != $employee->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not own this report.'
                ], 403);
            }

            // Only allow deletion if not reviewed
            if ($report->review_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This report has already been reviewed and cannot be deleted.'
                ], 422);
            }

            // Delete attachment if exists
            if ($report->attachment) {
                Storage::disk('public')->delete($report->attachment);
            }

            $report->delete();

            return response()->json([
                'success' => true,
                'message' => 'Work report deleted successfully!'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Work report not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('API: Delete Report Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete work report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/work-reports/{id}/review
     * Review a work report (HRM/Admin only)
     */
    public function review(Request $request, $id)
    {
        try {
            $user = $this->getAuthenticatedUser($request);

            // Check permission
            if (!$user->can('manage work report')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied.'
                ], 403);
            }

            $report = WorkReport::where('created_by', $user->creatorId())
                ->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'review_status' => 'required|in:approved,rejected,pending',
                'review_notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 422);
            }

            $report->review_status = $request->review_status;
            $report->review_notes = $request->review_notes;
            $report->reviewed_by = $user->id;
            $report->reviewed_at = now();
            $report->save();

            Log::info('API: Work Report Reviewed', [
                'work_report_id' => $report->id,
                'review_status' => $request->review_status,
                'reviewed_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Work report reviewed successfully!',
                'data' => $report->load(['employee', 'reviewer'])
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Work report not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('API: Review Report Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to review work report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/work-reports/status
     * Get current employee's report status for today
     */
    public function getStatus(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            $employee = $this->getEmployee($request);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found.'
                ], 404);
            }

            $date = $request->input('date', date('Y-m-d'));

            $report = WorkReport::where('employee_id', $employee->id)
                ->whereDate('date', $date)
                ->first();

            $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                ->whereDate('date', $date)
                ->where('clock_in', '!=', '00:00:00')
                ->where('clock_out', '00:00:00')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employee->id,
                    'date' => $date,
                    'submitted' => $report ? true : false,
                    'report' => $report,
                    'attendance' => $attendance,
                    'clocked_in' => $attendance ? true : false,
                    'can_submit' => $attendance ? true : false,
                    'review_status' => $report ? $report->review_status : null,
                    'status_message' => $report ? $this->getStatusMessage($report->review_status) : 'No report submitted'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API: Get Status Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/work-reports/popup-data
     * Get data for work report popup
     */
    public function getPopupData(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            $employeeId = $request->input('employee_id');
            $attendanceId = $request->input('attendance_id');

            if (!$employeeId) {
                // Try to get current employee
                $employee = $this->getEmployee($request);
                if (!$employee) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Employee ID is required'
                    ], 422);
                }
                $employeeId = $employee->id;
            }

            $employee = Employee::find($employeeId);
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            $attendance = null;
            if ($attendanceId) {
                $attendance = AttendanceEmployee::find($attendanceId);
            } else {
                // Get today's attendance
                $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                    ->whereDate('date', date('Y-m-d'))
                    ->first();
            }

            // Check if already submitted today
            $today = date('Y-m-d');
            $existingReport = WorkReport::where('employee_id', $employeeId)
                ->whereDate('date', $today)
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'employee_email' => $employee->email,
                    'date' => $today,
                    'attendance' => $attendance,
                    'clock_in' => $attendance ? $attendance->clock_in : null,
                    'clock_out' => $attendance ? $attendance->clock_out : null,
                    'already_submitted' => $existingReport ? true : false,
                    'existing_report' => $existingReport,
                    'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                    'status' => $attendance ? $attendance->status : null,
                    'can_submit' => !$existingReport && $attendance && $attendance->clock_in != '00:00:00'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API: Get Popup Data Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get popup data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/work-reports/stats
     * Get report statistics (HRM/Admin only)
     */
    public function getStats(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);

            if (!$user->can('manage work report')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied.'
                ], 403);
            }

            $creatorId = $user->creatorId();
            $date = $request->input('date', date('Y-m-d'));

            $stats = [
                'total' => WorkReport::where('created_by', $creatorId)->count(),
                'pending' => WorkReport::where('created_by', $creatorId)->where('review_status', 'pending')->count(),
                'approved' => WorkReport::where('created_by', $creatorId)->where('review_status', 'approved')->count(),
                'rejected' => WorkReport::where('created_by', $creatorId)->where('review_status', 'rejected')->count(),
                'today' => WorkReport::where('created_by', $creatorId)->whereDate('date', $date)->count(),
                'this_week' => WorkReport::where('created_by', $creatorId)
                    ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'this_month' => WorkReport::where('created_by', $creatorId)
                    ->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistics retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('API: Get Stats Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/work-reports/export
     * Export work reports (HRM/Admin only)
     */
    public function export(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);

            if (!$user->can('manage work report')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied.'
                ], 403);
            }

            $status = $request->input('status');
            $employeeId = $request->input('employee_id');
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');

            $query = WorkReport::with(['employee', 'employee.user', 'reviewer'])
                ->where('created_by', $user->creatorId());

            if ($status) {
                $query->where('review_status', $status);
            }

            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            if ($fromDate) {
                $query->whereDate('date', '>=', $fromDate);
            }

            if ($toDate) {
                $query->whereDate('date', '<=', $toDate);
            }

            $reports = $query->get();

            if ($reports->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No reports to export.'
                ], 404);
            }

            $exportData = $reports->map(function ($report) {
                return [
                    'id' => $report->id,
                    'employee_name' => $report->employee->name ?? 'N/A',
                    'employee_email' => $report->employee->email ?? 'N/A',
                    'date' => $report->date ?? 'N/A',
                    'clock_in' => $report->clock_in ?? 'N/A',
                    'clock_out' => $report->clock_out ?? 'N/A',
                    'work_description' => $report->work_description ?? 'N/A',
                    'quick_tasks' => $report->quick_tasks ?? 'N/A',
                    'achievements' => $report->achievements ?? 'N/A',
                    'challenges' => $report->challenges ?? 'N/A',
                    'tomorrow_plan' => $report->tomorrow_plan ?? 'N/A',
                    'hours_project' => $report->hours_project ?? 0,
                    'hours_meeting' => $report->hours_meeting ?? 0,
                    'hours_admin' => $report->hours_admin ?? 0,
                    'total_hours' => ($report->hours_project ?? 0) + ($report->hours_meeting ?? 0) + ($report->hours_admin ?? 0),
                    'review_status' => $report->review_status ?? 'pending',
                    'review_notes' => $report->review_notes ?? 'N/A',
                    'reviewed_by' => $report->reviewer->name ?? 'N/A',
                    'reviewed_at' => $report->reviewed_at ?? 'N/A',
                    'submitted_at' => $report->created_at ?? 'N/A'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $exportData,
                'count' => $exportData->count(),
                'message' => 'Export data retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('API: Export Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export reports: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Get status message
     */
    private function getStatusMessage($status)
    {
        $messages = [
            'pending' => 'Pending review',
            'approved' => 'Approved',
            'rejected' => 'Rejected'
        ];
        return $messages[$status] ?? 'Unknown status';
    }
}