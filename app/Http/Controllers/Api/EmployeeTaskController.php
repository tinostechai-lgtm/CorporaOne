<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaffWorkTask;
use App\Models\User;
use App\Models\Department;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EmployeeTaskController extends Controller
{
    /**
     * List tasks with filters and stats (dashboard data)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();
        $userType = $user->type;

        // Build query
        $query = StaffWorkTask::where('created_by', $creatorId)
            ->with(['employee', 'department', 'project', 'assignedBy']);

        // Employee sees only own tasks
        if ($userType == 'Employee') {
            $query->where('employee_id', $user->id);
        }

        // Filters (only for company/accountant)
        if (in_array($userType, ['company', 'accountant'])) {
            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }
            if ($request->has('department_id')) {
                $query->where('department_id', $request->department_id);
            }
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Date filters
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('due_date', '<=', $request->end_date);
        }

        $tasks = $query->orderBy('due_date', 'asc')->get();

        // Stats (if requested)
        $stats = null;
        if ($request->has('include_stats') && $request->include_stats) {
            $stats = $this->getStats($user, $creatorId);
        }

        return response()->json([
            'success' => true,
            'data' => $tasks,
            'stats' => $stats,
            'filters' => $request->all()
        ]);
    }

    /**
     * Create a new task
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'employee_id' => 'required|exists:users,id',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'description' => 'nullable|string',
            'remarks' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'project_id' => 'nullable|exists:projects,id',
            'start_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            $task = new StaffWorkTask();
            $task->task_id = StaffWorkTask::generateTaskNumber();
            $task->title = $request->title;
            $task->description = $request->description;
            $task->employee_id = $request->employee_id;
            $task->assigned_by = $user->id;
            $task->department_id = $request->department_id;
            $task->project_id = $request->project_id;
            $task->start_date = $request->start_date ?? now();
            $task->due_date = $request->due_date;
            $task->priority = $request->priority;
            $task->status = StaffWorkTask::STATUS_PENDING;
            $task->progress = 0;
            $task->remarks = $request->remarks;
            $task->created_by = $creatorId;
            $task->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => $task->load(['employee', 'department', 'project', 'assignedBy'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to create task: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show a single task
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $task = StaffWorkTask::where('created_by', $creatorId)
            ->with(['employee', 'department', 'project', 'assignedBy', 'createdBy'])
            ->find($id);

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Employee can only view own tasks
        if ($user->type == 'Employee' && $task->employee_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Permission denied'], 403);
        }

        return response()->json(['success' => true, 'data' => $task]);
    }

    /**
     * Update a task
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $task = StaffWorkTask::where('created_by', $creatorId)->find($id);
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'employee_id' => 'sometimes|exists:users,id',
            'due_date' => 'sometimes|date',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled,overdue,on_hold',
            'progress' => 'sometimes|integer|min:0|max:100',
            'description' => 'nullable|string',
            'remarks' => 'nullable|string',
            'completion_notes' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'project_id' => 'nullable|exists:projects,id',
            'start_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            $task->fill($request->only([
                'title', 'description', 'employee_id', 'department_id', 'project_id',
                'start_date', 'due_date', 'priority', 'status', 'progress', 'remarks'
            ]));

            if ($request->has('status') && $request->status == StaffWorkTask::STATUS_COMPLETED && empty($task->completed_at)) {
                $task->completed_at = now();
                $task->completion_notes = $request->completion_notes;
            }

            $task->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'data' => $task->fresh(['employee', 'department', 'project', 'assignedBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a task
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $task = StaffWorkTask::where('created_by', $creatorId)->find($id);
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json(['success' => true, 'message' => 'Task deleted successfully']);
    }

    /**
     * Update only the status (AJAX-style)
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $task = StaffWorkTask::where('created_by', $creatorId)->find($id);
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,completed,cancelled,overdue,on_hold',
            'progress' => 'nullable|integer|min:0|max:100',
            'completion_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $task->status = $request->status;
        if ($request->has('progress')) {
            $task->progress = $request->progress;
        }
        if ($request->status == StaffWorkTask::STATUS_COMPLETED && empty($task->completed_at)) {
            $task->completed_at = now();
            $task->completion_notes = $request->completion_notes;
        }
        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated',
            'data' => $task
        ]);
    }

    /**
     * Get tasks for a specific employee
     */
    public function getEmployeeTasks(Request $request, $employeeId)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $tasks = StaffWorkTask::where('employee_id', $employeeId)
            ->where('created_by', $creatorId)
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json(['success' => true, 'data' => $tasks]);
    }

    /**
     * Bulk assign tasks to an employee
     */
    public function bulkAssign(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $validator = Validator::make($request->all(), [
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:staff_work_tasks,id',
            'employee_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $taskIds = $request->task_ids;
        $employeeId = $request->employee_id;

        $updated = StaffWorkTask::whereIn('id', $taskIds)
            ->where('created_by', $creatorId)
            ->update(['employee_id' => $employeeId]);

        return response()->json([
            'success' => true,
            'message' => $updated . ' tasks assigned successfully',
            'updated' => $updated
        ]);
    }

    /**
     * Get tasks for calendar view
     */
    public function calendarTasks(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $tasks = StaffWorkTask::where('created_by', $creatorId)->get();

        $events = [];
        foreach ($tasks as $task) {
            $events[] = [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->due_date,
                'url' => route('employee.task.tracker.show', Crypt::encrypt($task->id)), // optional, can be omitted
                'className' => 'bg-' . ($task->status == StaffWorkTask::STATUS_COMPLETED ? 'success' :
                                        ($task->status == StaffWorkTask::STATUS_IN_PROGRESS ? 'info' :
                                        ($task->priority == StaffWorkTask::PRIORITY_URGENT ? 'danger' : 'warning'))),
                'extendedProps' => [
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'employee' => $task->employee ? $task->employee->name : '',
                    'progress' => $task->progress
                ]
            ];
        }

        return response()->json($events);
    }

    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $stats = $this->getStats($user, $creatorId);

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /**
     * Internal helper to compute stats
     */
    private function getStats($user, $creatorId)
    {
        $total = StaffWorkTask::where('created_by', $creatorId)->count();
        $completed = StaffWorkTask::where('created_by', $creatorId)
            ->where('status', StaffWorkTask::STATUS_COMPLETED)->count();
        $inProgress = StaffWorkTask::where('created_by', $creatorId)
            ->where('status', StaffWorkTask::STATUS_IN_PROGRESS)->count();
        $pending = StaffWorkTask::where('created_by', $creatorId)
            ->where('status', StaffWorkTask::STATUS_PENDING)->count();
        $overdue = StaffWorkTask::where('created_by', $creatorId)
            ->where('due_date', '<', now()->format('Y-m-d'))
            ->whereNotIn('status', [StaffWorkTask::STATUS_COMPLETED, StaffWorkTask::STATUS_CANCELLED])
            ->count();
        $dueToday = StaffWorkTask::where('created_by', $creatorId)
            ->where('due_date', now()->format('Y-m-d'))
            ->whereNotIn('status', [StaffWorkTask::STATUS_COMPLETED, StaffWorkTask::STATUS_CANCELLED])
            ->count();

        // My tasks (if user is employee)
        $myTasks = StaffWorkTask::where('created_by', $creatorId)
            ->where('employee_id', $user->id)->count();
        $myCompleted = StaffWorkTask::where('created_by', $creatorId)
            ->where('employee_id', $user->id)
            ->where('status', StaffWorkTask::STATUS_COMPLETED)->count();
        $myPending = StaffWorkTask::where('created_by', $creatorId)
            ->where('employee_id', $user->id)
            ->whereNotIn('status', [StaffWorkTask::STATUS_COMPLETED, StaffWorkTask::STATUS_CANCELLED])
            ->count();

        return [
            'total_tasks' => $total,
            'completed_tasks' => $completed,
            'in_progress_tasks' => $inProgress,
            'pending_tasks' => $pending,
            'overdue_tasks' => $overdue,
            'due_today' => $dueToday,
            'my_tasks' => [
                'total' => $myTasks,
                'completed' => $myCompleted,
                'pending' => $myPending,
                'completion_rate' => $myTasks > 0 ? round(($myCompleted / $myTasks) * 100) : 0,
            ]
        ];
    }
}