<?php

namespace App\Http\Controllers;

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
     * Display a listing of work reports (HRM view)
     */
    public function index()
    {
        if (!Auth::user()->can('manage work report')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $reports = WorkReport::with(['employee', 'employee.user', 'reviewer'])
                    ->where('created_by', Auth::user()->creatorId())
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);

        return view('workreport.index', compact('reports'));
    }

    /**
     * Show the form for creating a new work report (Employee view)
     */
    public function create()
    {
        $employee = Auth::user()->employee;
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }
        
        $today = date('Y-m-d');
        
        // Check if already submitted today
        $existingReport = WorkReport::where('employee_id', $employee->id)
                            ->whereDate('created_at', $today)
                            ->first();
                            
        if ($existingReport) {
            return redirect()->back()->with('info', 'You have already submitted a work report today.');
        }
        
        // Check if employee is clocked in
        $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                        ->where('date', $today)
                        ->where('clock_in', '!=', '00:00:00')
                        ->where('clock_out', '00:00:00')
                        ->first();
        
        return view('workreport.create', compact('employee', 'attendance'));
    }

    /**
     * Store a newly created work report
     */
    public function store(Request $request)
    {
        try {
            // Log the incoming request for debugging
            Log::info('Work report submission started', [
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'work_description' => 'required|string|min:3|max:5000',
                'date' => 'required|date',
                'attendance_id' => 'nullable|exists:attendance_employees,id',
                'clock_in' => 'nullable',
                'clock_out' => 'nullable',
                'quick_tasks' => 'nullable|array',
                'quick_tasks.*' => 'string|max:100',
                'achievements' => 'nullable|string|max:1000',
                'challenges' => 'nullable|string|max:1000',
                'tomorrow_plan' => 'nullable|string|max:1000',
                'hours_project' => 'nullable|numeric|min:0|max:24',
                'hours_meeting' => 'nullable|numeric|min:0|max:24',
                'hours_admin' => 'nullable|numeric|min:0|max:24',
            ]);

            if ($validator->fails()) {
                Log::error('Work report validation failed', [
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 422);
            }

            // Check if already submitted today
            $existingReport = WorkReport::where('employee_id', $request->employee_id)
                                ->whereDate('date', $request->date)
                                ->first();
            
            if ($existingReport) {
                return response()->json([
                    'success' => false,
                    'message' => 'Work report already submitted for today.'
                ], 422);
            }

            // Prepare data
            $data = $request->all();
            
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
            $data['created_by'] = Auth::user()->creatorId();
            
            // Set default status
            $data['review_status'] = 'pending';

            // Create work report
            $workReport = WorkReport::create($data);

            Log::info('Work Report Submitted Successfully', [
                'work_report_id' => $workReport->id,
                'employee_id' => $data['employee_id'],
                'attendance_id' => $data['attendance_id'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Work report submitted successfully!',
                'work_report_id' => $workReport->id
            ]);

        } catch (\Exception $e) {
            Log::error('Work Report Submit Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit work report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified work report
     */
    public function show($id)
    {
        $report = WorkReport::with(['employee', 'employee.user', 'attendance', 'reviewer'])
                    ->findOrFail($id);
        
        // Check permission
        $user = Auth::user();
        $creatorId = $user->creatorId();
        
        if ($report->created_by != $creatorId && $report->employee_id != $user->employee->id) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        return view('workreport.show', compact('report'));
    }

    /**
     * Show the form for editing the specified work report
     */
    public function edit($id)
    {
        $report = WorkReport::findOrFail($id);
        
        // Check if user owns this report
        if ($report->employee_id != Auth::user()->employee->id) {
            return redirect()->back()->with('error', 'You do not own this report.');
        }
        
        // Only allow editing if not reviewed
        if ($report->review_status !== 'pending') {
            return redirect()->back()->with('error', 'This report has already been reviewed and cannot be edited.');
        }

        return view('workreport.edit', compact('report'));
    }

    /**
     * Update the specified work report
     */
    public function update(Request $request, $id)
    {
        $report = WorkReport::findOrFail($id);
        
        // Check if user owns this report
        if ($report->employee_id != Auth::user()->employee->id) {
            return redirect()->back()->with('error', 'You do not own this report.');
        }
        
        // Only allow updating if not reviewed
        if ($report->review_status !== 'pending') {
            return redirect()->back()->with('error', 'This report has already been reviewed and cannot be edited.');
        }

        $validator = Validator::make($request->all(), [
            'work_description' => 'required|string|min:10|max:5000',
            'quick_tasks' => 'nullable|array',
            'quick_tasks.*' => 'string|max:100',
            'achievements' => 'nullable|string|max:1000',
            'challenges' => 'nullable|string|max:1000',
            'tomorrow_plan' => 'nullable|string|max:1000',
            'hours_project' => 'nullable|numeric|min:0|max:24',
            'hours_meeting' => 'nullable|numeric|min:0|max:24',
            'hours_admin' => 'nullable|numeric|min:0|max:24',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->all();
            
            // Process quick tasks
            if (isset($data['quick_tasks']) && is_array($data['quick_tasks'])) {
                $data['quick_tasks'] = implode(', ', array_filter($data['quick_tasks']));
            } else {
                $data['quick_tasks'] = null;
            }

            $report->update($data);

            return redirect()->route('workreport.my')
                ->with('success', 'Work report updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update work report: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified work report
     */
    public function destroy($id)
    {
        $report = WorkReport::findOrFail($id);
        
        // Check if user owns this report
        if ($report->employee_id != Auth::user()->employee->id) {
            return redirect()->back()->with('error', 'You do not own this report.');
        }
        
        // Only allow deletion if not reviewed
        if ($report->review_status !== 'pending') {
            return redirect()->back()->with('error', 'This report has already been reviewed and cannot be deleted.');
        }

        try {
            // Delete attachment if exists
            if ($report->attachment) {
                Storage::disk('public')->delete($report->attachment);
            }
            
            $report->delete();

            return redirect()->route('workreport.my')
                ->with('success', 'Work report deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete work report: ' . $e->getMessage());
        }
    }

    /**
     * Display the employee's own reports
     */
    public function myReports()
    {
        $employee = Auth::user()->employee;
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        $reports = WorkReport::where('employee_id', $employee->id)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        return view('workreport.my', compact('reports'));
    }

    /**
     * Show the review form for a work report (HRM view)
     */
    public function review($id)
    {
        if (!Auth::user()->can('manage work report')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $report = WorkReport::with(['employee', 'employee.user'])
                    ->where('created_by', Auth::user()->creatorId())
                    ->findOrFail($id);

        return view('workreport.review', compact('report'));
    }

    /**
     * Update the review status of a work report
     */
    public function updateReview(Request $request, $id)
    {
        if (!Auth::user()->can('manage work report')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $report = WorkReport::where('created_by', Auth::user()->creatorId())
                    ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'review_status' => 'required|in:approved,rejected,pending',
            'review_notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $report->review_status = $request->review_status;
            $report->review_notes = $request->review_notes;
            $report->reviewed_by = Auth::id();
            $report->reviewed_at = now();
            $report->save();

            Log::info('Work Report Reviewed', [
                'work_report_id' => $report->id,
                'review_status' => $request->review_status,
                'reviewed_by' => Auth::id()
            ]);

            return redirect()->route('workreport.index')
                ->with('success', 'Work report reviewed successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to review work report: ' . $e->getMessage());
        }
    }

    /**
     * Export work reports to CSV (HRM view)
     */
    public function export(Request $request)
    {
        if (!Auth::user()->can('manage work report')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $reports = WorkReport::with(['employee', 'employee.user'])
                    ->where('created_by', Auth::user()->creatorId())
                    ->get();

        if ($reports->isEmpty()) {
            return redirect()->back()->with('error', 'No reports to export.');
        }

        $filename = 'work_reports_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://output', 'w');

        // CSV Headers
        fputcsv($handle, [
            'ID',
            'Employee Name',
            'Employee Email',
            'Date',
            'Clock In',
            'Clock Out',
            'Work Description',
            'Quick Tasks',
            'Achievements',
            'Challenges',
            'Tomorrow Plan',
            'Hours (Project)',
            'Hours (Meeting)',
            'Hours (Admin)',
            'Total Hours',
            'Status',
            'Review Notes',
            'Reviewed By',
            'Reviewed At',
            'Submitted At'
        ]);

        // CSV Data
        foreach ($reports as $report) {
            fputcsv($handle, [
                $report->id,
                $report->employee->name ?? 'N/A',
                $report->employee->email ?? 'N/A',
                $report->date ?? 'N/A',
                $report->clock_in ?? 'N/A',
                $report->clock_out ?? 'N/A',
                $report->work_description ?? 'N/A',
                $report->quick_tasks ?? 'N/A',
                $report->achievements ?? 'N/A',
                $report->challenges ?? 'N/A',
                $report->tomorrow_plan ?? 'N/A',
                $report->hours_project ?? 0,
                $report->hours_meeting ?? 0,
                $report->hours_admin ?? 0,
                ($report->hours_project ?? 0) + ($report->hours_meeting ?? 0) + ($report->hours_admin ?? 0),
                $report->review_status ?? 'pending',
                $report->review_notes ?? 'N/A',
                $report->reviewer->name ?? 'N/A',
                $report->reviewed_at ?? 'N/A',
                $report->created_at ?? 'N/A'
            ]);
        }

        fclose($handle);

        return response('', 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Get work report status for the current employee (AJAX)
     */
    public function getStatus(Request $request)
    {
        try {
            $employee = Auth::user()->employee;
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ]);
            }

            $today = date('Y-m-d');
            $existingReport = WorkReport::where('employee_id', $employee->id)
                                ->whereDate('created_at', $today)
                                ->exists();

            return response()->json([
                'success' => true,
                'submitted_today' => $existingReport,
                'employee_id' => $employee->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get work report data for popup (AJAX)
     */
    public function getPopupData(Request $request)
    {
        try {
            $employeeId = $request->input('employee_id');
            $attendanceId = $request->input('attendance_id');
            
            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee ID is required'
                ]);
            }
            
            $employee = Employee::find($employeeId);
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ]);
            }

            $attendance = null;
            if ($attendanceId) {
                $attendance = AttendanceEmployee::find($attendanceId);
            }
            
            // Check if already submitted today
            $today = date('Y-m-d');
            $existingReport = WorkReport::where('employee_id', $employeeId)
                                ->whereDate('date', $today)
                                ->exists();

            return response()->json([
                'success' => true,
                'employee_name' => $employee->name,
                'employee_id' => $employee->id,
                'attendance' => $attendance,
                'date' => $today,
                'already_submitted' => $existingReport,
                'clock_in' => $attendance ? $attendance->clock_in : null,
                'clock_out' => $attendance ? $attendance->clock_out : null,
                'status' => $attendance ? $attendance->status : null,
                'half_day_threshold' => $employee->half_day_threshold ?? 4.0
            ]);

        } catch (\Exception $e) {
            Log::error('Get popup data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}