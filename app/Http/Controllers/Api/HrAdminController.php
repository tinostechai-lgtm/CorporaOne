<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementEmployee;
use App\Models\Notification;
use App\Models\Warning;
use App\Models\Termination;
use App\Models\TerminationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HrAdminController extends Controller
{
    /**
     * Display a listing of announcements
     */
    public function indexAnnouncements(Request $request)
    {
        $announcements = Announcement::where('created_by', $request->user()->creatorId())
            ->with('announcementEmployees')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $announcements
        ], 200);
    }

    /**
     * Store a newly created announcement
     */
    public function storeAnnouncement(Request $request)
{
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'branch_id' => 'nullable|integer',
        'department_id' => 'nullable|array',
        'employee_id' => 'nullable|array',
        'description' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    // Create the announcement
    $announcement = new Announcement();
    $announcement->title = $request->title;
    $announcement->start_date = $request->start_date;
    $announcement->end_date = $request->end_date;
    $announcement->branch_id = $request->branch_id ?? 0;
    
    // 🔥 IMPORTANT: Convert arrays to JSON strings
    $announcement->department_id = json_encode($request->department_id ?? []);
    $announcement->employee_id = json_encode($request->employee_id ?? []);
    
    $announcement->description = $request->description;
    $announcement->created_by = $request->user()->creatorId();
    $announcement->save();

    // Now create pivot entries for employees
    $employeeIds = $request->employee_id ?? [];
    if (!empty($employeeIds)) {
        // If the array contains 0 (meaning "All"), fetch all employees
        if (in_array(0, $employeeIds)) {
            $employeeIds = \App\Models\Employee::where('created_by', $request->user()->creatorId())
                ->pluck('id')
                ->toArray();
        }
        
        // Assign each employee to the announcement via pivot
        foreach ($employeeIds as $empId) {
            AnnouncementEmployee::create([
                'announcement_id' => $announcement->id,
                'employee_id' => $empId,
            ]);
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Announcement created successfully',
        'data' => $announcement->load('announcementEmployees')
    ], 201);
}

    /**
     * Display the specified announcement
     */
    public function showAnnouncement(Request $request, $id)
    {
        $announcement = Announcement::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->with('announcementEmployees')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $announcement
        ]);
    }

    /**
     * Update the specified announcement
     */
    public function updateAnnouncement(Request $request, $id)
    {
        $announcement = Announcement::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'employee' => 'nullable|array',
            'employee.*' => 'exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $announcement->fill($request->except('employee'));
        $announcement->save();

        if ($request->has('employee')) {
            AnnouncementEmployee::where('announcement_id', $announcement->id)->delete();
            foreach ($request->employee as $employeeId) {
                AnnouncementEmployee::create([
                    'announcement_id' => $announcement->id,
                    'employee_id' => $employeeId,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Announcement updated successfully',
            'data' => $announcement->load('announcementEmployees')
        ]);
    }

    /**
     * Remove the specified announcement
     */
    public function destroyAnnouncement(Request $request, $id)
    {
        $announcement = Announcement::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Announcement deleted successfully'
        ]);
    }

    /**
     * Display a listing of warnings
     */
    public function indexWarnings(Request $request)
    {
        $warnings = Warning::where('created_by', $request->user()->creatorId())
            ->with('employee')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $warnings
        ], 200);
    }

    /**
     * Store a newly created warning
     */
    public function storeWarning(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'warning_date' => 'required|date',
            'warning_type' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $warning = new Warning();
        $warning->fill($request->all());
        $warning->created_by = $request->user()->creatorId();
        $warning->save();

        return response()->json([
            'success' => true,
            'message' => 'Warning created successfully',
            'data' => $warning->load('employee')
        ], 201);
    }

    /**
     * Display the specified warning
     */
    public function showWarning(Request $request, $id)
    {
        $warning = Warning::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->with('employee')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $warning
        ]);
    }

    /**
     * Update the specified warning
     */
    public function updateWarning(Request $request, $id)
    {
        $warning = Warning::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'warning_date' => 'sometimes|date',
            'warning_type' => 'nullable|string',
            'subject' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $warning->fill($request->only(['warning_date', 'warning_type', 'subject', 'description']));
        $warning->save();

        return response()->json([
            'success' => true,
            'message' => 'Warning updated successfully',
            'data' => $warning->load('employee')
        ]);
    }

    /**
     * Remove the specified warning
     */
    public function destroyWarning(Request $request, $id)
    {
        $warning = Warning::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $warning->delete();

        return response()->json([
            'success' => true,
            'message' => 'Warning deleted successfully'
        ]);
    }

    /**
     * Display a listing of terminations
     */
    public function indexTerminations(Request $request)
    {
        $terminations = Termination::where('created_by', $request->user()->creatorId())
            ->with(['employee', 'terminationType'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $terminations
        ], 200);
    }

    /**
     * Store a newly created termination
     */
    public function storeTermination(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'termination_type' => 'required|exists:termination_types,id',
            'termination_date' => 'required|date',
            'notice_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $termination = new Termination();
        $termination->fill($request->all());
        $termination->created_by = $request->user()->creatorId();
        $termination->save();

        return response()->json([
            'success' => true,
            'message' => 'Termination created successfully',
            'data' => $termination->load(['employee', 'terminationType'])
        ], 201);
    }

    /**
     * Display the specified termination
     */
    public function showTermination(Request $request, $id)
    {
        $termination = Termination::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->with(['employee', 'terminationType'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $termination
        ]);
    }

    /**
     * Update the specified termination
     */
    public function updateTermination(Request $request, $id)
    {
        $termination = Termination::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'termination_type' => 'sometimes|exists:termination_types,id',
            'termination_date' => 'sometimes|date',
            'notice_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $termination->fill($request->only(['termination_type', 'termination_date', 'notice_date', 'description']));
        $termination->save();

        return response()->json([
            'success' => true,
            'message' => 'Termination updated successfully',
            'data' => $termination->load(['employee', 'terminationType'])
        ]);
    }

    /**
     * Remove the specified termination
     */
    public function destroyTermination(Request $request, $id)
    {
        $termination = Termination::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $termination->delete();

        return response()->json([
            'success' => true,
            'message' => 'Termination deleted successfully'
        ]);
    }
}
