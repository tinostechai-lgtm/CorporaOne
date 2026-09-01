<?php

namespace App\Http\Controllers;

use App\Models\StaffWorkTask;
use App\Models\User;
use App\Models\Department;
use App\Models\Project;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class StaffWorkTaskController extends Controller
{
    /**
     * Display a listing of staff tasks with dashboard stats.
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
        $creatorId = Auth::user()->creatorId();
        $userType = Auth::user()->type;
        
        // Get employees for dropdown (only for company)
        $employees = collect();
        if (in_array($userType, ['company', 'accountant'])) {
            $employees = User::where('created_by', $creatorId)
                ->where('type', '!=', 'client')
                ->where('type', '!=', 'super admin')
                ->get();
        }
        
        // Filter parameters
        $selectedEmployee = $request->get('employee_id');
        $selectedStatus = $request->get('status');
        $selectedPriority = $request->get('priority');
        $selectedDepartment = $request->get('department_id');
        
        // Build query
        $query = StaffWorkTask::where('created_by', $creatorId)
            ->with(['employee', 'department', 'project']);
        
        // EMPLOYEE: Only own tasks
        if ($userType == 'Employee') {
            $query->where('employee_id', Auth::id());
        }
        
        // Company filters
        if (!empty($selectedEmployee) && in_array($userType, ['company', 'accountant'])) {
            $query->where('employee_id', $selectedEmployee);
        }
        
        if (!empty($selectedStatus)) {
            $query->where('status', $selectedStatus);
        }
        
        if (!empty($selectedPriority)) {
            $query->where('priority', $selectedPriority);
        }
        
        if (!empty($selectedDepartment)) {
            $query->where('department_id', $selectedDepartment);
        }
        
        $tasks = $query->orderBy('due_date', 'asc')->get();
        
        // Status and priority options
        $statuses = StaffWorkTask::getStatuses();
        $priorities = StaffWorkTask::getPriorities();
        
        // Departments for filter (only company)
        $departments = collect();
        if (in_array($userType, ['company', 'accountant'])) {
            $departments = Department::where('created_by', $creatorId)
                ->get()
                ->pluck('name', 'id');
        }
        
        // Employee stats (only company/accountant)
        $employeeStats = [];
        if (in_array($userType, ['company', 'accountant'])) {
            foreach ($employees as $employee) {
                $empQuery = StaffWorkTask::where('created_by', $creatorId)
                    ->where('employee_id', $employee->id);
                
                $employeeStats[$employee->id] = [
                    'employee' => $employee,
                    'total_tasks' => $empQuery->count(),
                    'completed_tasks' => $empQuery->clone()->where('status', StaffWorkTask::STATUS_COMPLETED)->count(),
                    'in_progress_tasks' => $empQuery->clone()->where('status', StaffWorkTask::STATUS_IN_PROGRESS)->count(),
                    'pending_tasks' => $empQuery->clone()->where('status', StaffWorkTask::STATUS_PENDING)->count(),
                    'overdue_tasks' => $empQuery->clone()
                        ->where('due_date', '<', now()->format('Y-m-d'))
                        ->whereNotIn('status', [StaffWorkTask::STATUS_COMPLETED, StaffWorkTask::STATUS_CANCELLED])
                        ->count(),
                    'completion_rate' => 0
                ];
                
                $total = $employeeStats[$employee->id]['total_tasks'];
                if ($total > 0) {
                    $employeeStats[$employee->id]['completion_rate'] = round(
                        ($employeeStats[$employee->id]['completed_tasks'] / $total) * 100
                    );
                }
            }
        } else {
            // Employee shows own stats
            $empQuery = StaffWorkTask::where('created_by', $creatorId)
                ->where('employee_id', Auth::id());
            $employeeStats[Auth::id()] = [
                'employee' => Auth::user(),
                'total_tasks' => $empQuery->count(),
                'completed_tasks' => $empQuery->clone()->where('status', StaffWorkTask::STATUS_COMPLETED)->count(),
                'in_progress_tasks' => $empQuery->clone()->where('status', StaffWorkTask::STATUS_IN_PROGRESS)->count(),
                'pending_tasks' => $empQuery->clone()->where('status', StaffWorkTask::STATUS_PENDING)->count(),
                'overdue_tasks' => $empQuery->clone()
                    ->where('due_date', '<', now()->format('Y-m-d'))
                    ->whereNotIn('status', [StaffWorkTask::STATUS_COMPLETED, StaffWorkTask::STATUS_CANCELLED])
                    ->count(),
                'completion_rate' => 0
            ];
            $totalEmp = $employeeStats[Auth::id()]['total_tasks'];
            if ($totalEmp > 0) {
                $employeeStats[Auth::id()]['completion_rate'] = round(
                    ($employeeStats[Auth::id()]['completed_tasks'] / $totalEmp) * 100
                );
            }
        }
        
        $dashboardStats = $this->getDashboardStats();
        
        return view('employee_task_tracker.index', compact(
            'tasks',
            'employees',
            'statuses',
            'priorities',
            'departments',
            'employeeStats',
            'dashboardStats',
            'selectedEmployee',
            'selectedStatus',
            'selectedPriority',
            'selectedDepartment'
        ));
    }
    
    /**
     * Get dashboard statistics for the task tracker.
     */
    private function getDashboardStats()
    {
        $creatorId = Auth::user()->creatorId();
        
        $totalTasks = StaffWorkTask::where('created_by', $creatorId)->count();
        $completedTasks = StaffWorkTask::where('created_by', $creatorId)
            ->where('status', StaffWorkTask::STATUS_COMPLETED)
            ->count();
        $inProgressTasks = StaffWorkTask::where('created_by', $creatorId)
            ->where('status', StaffWorkTask::STATUS_IN_PROGRESS)
            ->count();
        $pendingTasks = StaffWorkTask::where('created_by', $creatorId)
            ->where('status', StaffWorkTask::STATUS_PENDING)
            ->count();
        $overdueTasks = StaffWorkTask::where('created_by', $creatorId)
            ->where('due_date', '<', now()->format('Y-m-d'))
            ->whereNotIn('status', [StaffWorkTask::STATUS_COMPLETED, StaffWorkTask::STATUS_CANCELLED])
            ->count();
        $dueToday = StaffWorkTask::where('created_by', $creatorId)
            ->where('due_date', now()->format('Y-m-d'))
            ->whereNotIn('status', [StaffWorkTask::STATUS_COMPLETED, StaffWorkTask::STATUS_CANCELLED])
            ->count();
        
        // My tasks (assigned to current user)
        $myTasks = StaffWorkTask::where('created_by', $creatorId)
            ->where('employee_id', Auth::id())
            ->count();
        $myCompletedTasks = StaffWorkTask::where('created_by', $creatorId)
            ->where('employee_id', Auth::id())
            ->where('status', StaffWorkTask::STATUS_COMPLETED)
            ->count();
        $myPendingTasks = StaffWorkTask::where('created_by', $creatorId)
            ->where('employee_id', Auth::id())
            ->whereNotIn('status', [StaffWorkTask::STATUS_COMPLETED, StaffWorkTask::STATUS_CANCELLED])
            ->count();
        
        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'pending_tasks' => $pendingTasks,
            'overdue_tasks' => $overdueTasks,
            'due_today' => $dueToday,
            'my_tasks' => [
                'total' => $myTasks,
                'completed' => $myCompletedTasks,
                'pending' => $myPendingTasks,
                'completion_rate' => $myTasks > 0 ? round(($myCompletedTasks / $myTasks) * 100) : 0,
            ]
        ];
    }
    
    /**
     * Show the form for creating a new staff task.
     */
    public function create()
    {
        if (Auth::check()) {
            $employees = User::where('created_by', Auth::user()->creatorId())
                ->where('type', '!=', 'client')
                ->where('type', '!=', 'super admin')
                ->get()
                ->pluck('name', 'id');
            $employees->prepend('Select Employee', '');
            
            $departments = Department::where('created_by', Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $departments->prepend('Select Department', '');
            
            $projects = Project::where('created_by', Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $projects->prepend('Select Project', '');
            
            $statuses = StaffWorkTask::getStatuses();
            $priorities = StaffWorkTask::getPriorities();
            
            return view('employee_task_tracker.create', compact('employees', 'departments', 'projects', 'statuses', 'priorities'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    /**
     * Store a newly created staff task.
     */
    public function store(Request $request)
    {
        if (Auth::check()) {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'employee_id' => 'required|exists:users,id',
                'due_date' => 'required|date',
                'priority' => 'required|in:low,medium,high,urgent',
                'description' => 'nullable|string',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first())->withInput();
            }

            DB::beginTransaction();
            try {
                $task = new StaffWorkTask();
                $task->task_id = StaffWorkTask::generateTaskNumber();
                $task->title = $request->title;
                $task->description = $request->description;
                $task->employee_id = $request->employee_id;
                $task->assigned_by = Auth::user()->id;
                $task->department_id = $request->department_id;
                $task->project_id = $request->project_id;
                $task->start_date = $request->start_date ?? now();
                $task->due_date = $request->due_date;
                $task->priority = $request->priority;
                $task->status = StaffWorkTask::STATUS_PENDING;
                $task->progress = 0;
                $task->remarks = $request->remarks;
                $task->created_by = Auth::user()->creatorId();
                $task->save();
                
                DB::commit();
                
                return redirect()->route('employee.task.tracker')
                    ->with('success', __('Task created successfully. Task #: ') . $task->task_id);
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', __('Something went wrong: ') . $e->getMessage())->withInput();
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    /**
     * Display the specified staff task.
     */
    public function show($id)
    {
        if (Auth::check()) {
            try {
                $id = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Task not found.'));
            }

            $task = StaffWorkTask::with(['employee', 'assignedBy', 'project', 'department', 'createdBy'])
                ->find($id);

            if (!$task || $task->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Task not found or permission denied.'));
            }

            return view('employee_task_tracker.show', compact('task'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    /**
     * Show the form for editing the specified staff task.
     */
    public function edit($id)
    {
        if (Auth::check()) {
            try {
                $id = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Task not found.'));
            }

            $task = StaffWorkTask::find($id);

            if (!$task || $task->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Task not found or permission denied.'));
            }

            // Only allow editing if status is pending
            if ($task->status != StaffWorkTask::STATUS_PENDING) {
                return redirect()->back()->with('error', __('Only pending tasks can be edited.'));
            }

            $employees = User::where('created_by', Auth::user()->creatorId())
                ->where('type', '!=', 'client')
                ->where('type', '!=', 'super admin')
                ->get()
                ->pluck('name', 'id');
            $employees->prepend('Select Employee', '');
            
            $departments = Department::where('created_by', Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $departments->prepend('Select Department', '');
            
            $projects = Project::where('created_by', Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $projects->prepend('Select Project', '');
            
            $statuses = StaffWorkTask::getStatuses();
            $priorities = StaffWorkTask::getPriorities();

            return view('employee_task_tracker.edit', compact('task', 'employees', 'departments', 'projects', 'statuses', 'priorities'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    /**
     * Update the specified staff task.
     */
    public function update(Request $request, $id)
    {
        if (Auth::check()) {
            try {
                $id = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Task not found.'));
            }

            $task = StaffWorkTask::find($id);

            if (!$task || $task->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Task not found or permission denied.'));
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'employee_id' => 'required|exists:users,id',
                'due_date' => 'required|date',
                'priority' => 'required|in:low,medium,high,urgent',
                'status' => 'required|in:pending,in_progress,completed,cancelled,overdue,on_hold',
                'progress' => 'required|integer|min:0|max:100',
                'description' => 'nullable|string',
                'remarks' => 'nullable|string',
                'completion_notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first())->withInput();
            }

            DB::beginTransaction();
            try {
                $task->title = $request->title;
                $task->description = $request->description;
                $task->employee_id = $request->employee_id;
                $task->department_id = $request->department_id;
                $task->project_id = $request->project_id;
                $task->due_date = $request->due_date;
                $task->priority = $request->priority;
                $task->status = $request->status;
                $task->progress = $request->progress;
                $task->remarks = $request->remarks;
                
                if ($request->status == StaffWorkTask::STATUS_COMPLETED && empty($task->completed_at)) {
                    $task->completed_at = now();
                    $task->completion_notes = $request->completion_notes;
                }
                
                $task->save();
                
                DB::commit();

                return redirect()->route('employee.task.tracker')
                    ->with('success', __('Task updated successfully.'));
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', __('Something went wrong: ') . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    /**
     * Update task status only (AJAX endpoint).
     */
    public function updateStatus(Request $request, $id)
    {
        if (Auth::check()) {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,in_progress,completed,cancelled,overdue,on_hold',
                'progress' => 'nullable|integer|min:0|max:100',
                'completion_notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $task = StaffWorkTask::find($id);

            if (!$task || $task->created_by != Auth::user()->creatorId()) {
                return response()->json(['error' => __('Task not found or permission denied.')], 404);
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

            return response()->json(['success' => __('Task status updated successfully.')]);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }
    
    /**
     * Remove the specified staff task.
     */
    public function destroy($id)
    {
        if (Auth::check()) {
            try {
                $id = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Task not found.'));
            }

            $task = StaffWorkTask::find($id);

            if (!$task || $task->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Task not found or permission denied.'));
            }

            $task->delete();

            return redirect()->route('employee.task.tracker')
                ->with('success', __('Task deleted successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    /**
     * Get tasks for a specific employee (AJAX).
     */
    public function getEmployeeTasks($employeeId)
    {
        if (Auth::check()) {
            $tasks = StaffWorkTask::where('employee_id', $employeeId)
                ->where('created_by', Auth::user()->creatorId())
                ->orderBy('due_date', 'asc')
                ->get();
                
            return response()->json([
                'success' => true,
                'tasks' => $tasks
            ]);
        }
        
        return response()->json(['error' => __('Permission denied.')], 403);
    }
    
    /**
     * Get task statistics for dashboard widget (AJAX).
     */
    public function getTaskStats()
    {
        $stats = $this->getDashboardStats();
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    /**
     * Bulk assign tasks to employee.
     */
    public function bulkAssign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_ids' => 'required',
            'employee_id' => 'required|exists:users,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        
        $taskIds = explode(',', $request->task_ids);
        $employeeId = $request->employee_id;
        $creatorId = Auth::user()->creatorId();
        
        $updated = 0;
        foreach ($taskIds as $taskId) {
            $task = StaffWorkTask::find($taskId);
            if ($task && $task->created_by == $creatorId) {
                $task->employee_id = $employeeId;
                $task->save();
                $updated++;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => __($updated . ' tasks assigned successfully.')
        ]);
    }
    
    /**
     * Get tasks for calendar view.
     */
    public function calendarTasks()
    {
        $tasks = StaffWorkTask::where('created_by', Auth::user()->creatorId())
            ->get();
        
        $events = [];
        foreach ($tasks as $task) {
            $events[] = [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->due_date,
                'url' => route('employee.task.tracker.show', Crypt::encrypt($task->id)),
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
}