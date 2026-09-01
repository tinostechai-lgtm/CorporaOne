@extends('layouts.admin')

@section('page-title')
    {{ __('Employee Task Tracker') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Task Tracker') }}</li>
@endsection

@section('action-btn')
    @if(Auth::user()->type == 'company')
        <a href="{{ route('employee.task.tracker.create') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i> {{ __('Create Task') }}
        </a>
    @endif
@endsection

@section('content')
<style>
    /* Stats Card Styles */
    .stats-card {
        border: none;
        border-radius: 12px;
        transition: all 0.2s;
    }
    
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }
    
    .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Employee Card Styles */
    .employee-list-scroll {
        max-height: 550px;
        overflow-y: auto;
        padding-right: 5px;
    }
    
    .employee-list-scroll::-webkit-scrollbar {
        width: 4px;
    }
    
    .employee-list-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .employee-list-scroll::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    
    .employee-stat-card {
        border: 1px solid #eef2f6;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 12px;
        transition: all 0.2s;
        background: #fff;
    }
    
    .employee-stat-card:hover {
        border-color: #e0e4e9;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    
    .stat-badge {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 8px;
        text-align: center;
    }
    
    /* Table Styles */
    .tasks-table-wrapper {
        overflow-x: auto;
    }
    
    .tasks-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .tasks-table thead th {
        background: #f8f9fa;
        padding: 14px 12px;
        font-size: 12px;
        font-weight: 600;
        color: #4b5563;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
        text-align: left;
    }
    
    .tasks-table tbody td {
        padding: 14px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
        font-size: 13px;
    }
    
    .tasks-table tbody tr:hover {
        background: #fafbfc;
    }
    
    /* Task ID */
    .task-id {
        font-family: monospace;
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        background: #f3f4f6;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-block;
    }
    
    /* Task Title */
    .task-title {
        font-weight: 500;
        color: #1f2937;
        text-decoration: none;
        font-size: 13px;
    }
    
    .task-title:hover {
        color: #6fd943;
        text-decoration: underline;
    }
    
    /* Priority Badges */
    .priority-low {
        background: #e5e7eb;
        color: #4b5563;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        display: inline-block;
    }
    
    .priority-medium {
        background: #dbeafe;
        color: #1e40af;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        display: inline-block;
    }
    
    .priority-high {
        background: #fed7aa;
        color: #9b2c1d;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        display: inline-block;
    }
    
    .priority-urgent {
        background: #fee2e2;
        color: #991b1b;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        display: inline-block;
    }
    
    /* Status Badges */
    .status-pending {
        background: #fef3c7;
        color: #b45309;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        display: inline-block;
    }
    
    .status-progress {
        background: #dbeafe;
        color: #1e40af;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        display: inline-block;
    }
    
    .status-completed {
        background: #d1fae5;
        color: #065f46;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        display: inline-block;
    }
    
    .status-hold {
        background: #e5e7eb;
        color: #4b5563;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        display: inline-block;
    }
    
    /* Progress Bar */
    .progress-bar-custom {
        width: 80px;
        height: 4px;
        background: #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: #6fd943;
        border-radius: 10px;
    }
    
    /* Action Button */
    .action-btn {
        background: transparent;
        border: none;
        padding: 6px 8px;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .action-btn:hover {
        background: #f3f4f6;
    }
    
    /* Due Date */
    .due-date {
        font-size: 12px;
        color: #6b7280;
    }
    
    .due-date.overdue {
        color: #dc2626;
        font-weight: 500;
    }
    
    /* Avatar */
    .avatar-small {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    /* Column Widths */
    .col-id { width: 8%; }
    .col-task { width: 22%; }
    .col-assign { width: 18%; }
    .col-priority { width: 10%; }
    .col-date { width: 12%; }
    .col-progress { width: 12%; }
    .col-status { width: 10%; }
    .col-action { width: 8%; }
</style>

<div class="row">
    <div class="col-12">
        <!-- Statistics Cards Row - Clickable Filter Buttons -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <a href="?status=" class="text-decoration-none">
                    <div class="card stats-card shadow-sm {{ request('status') == '' ? 'border-primary border-2' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted text-uppercase small">Total Tasks</span>
                                    <h2 class="mb-0 fw-bold mt-1">{{ $dashboardStats['total_tasks'] ?? 0 }}</h2>
                                </div>
                                <div class="stats-icon bg-primary bg-opacity-10">
                                    <i class="ti ti-list-check fs-4 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <a href="?status=completed" class="text-decoration-none">
                    <div class="card stats-card shadow-sm {{ request('status') == 'completed' ? 'border-success border-2' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted text-uppercase small">Completed</span>
                                    <h2 class="mb-0 fw-bold mt-1 text-success">{{ $dashboardStats['completed_tasks'] ?? 0 }}</h2>
                                </div>
                                <div class="stats-icon bg-success bg-opacity-10">
                                    <i class="ti ti-check fs-4 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <a href="?status=in_progress" class="text-decoration-none">
                    <div class="card stats-card shadow-sm {{ request('status') == 'in_progress' ? 'border-warning border-2' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted text-uppercase small">In Progress</span>
                                    <h2 class="mb-0 fw-bold mt-1 text-warning">{{ $dashboardStats['in_progress_tasks'] ?? 0 }}</h2>
                                </div>
                                <div class="stats-icon bg-warning bg-opacity-10">
                                    <i class="ti ti-clock fs-4 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <a href="?status=overdue" class="text-decoration-none">
                    <div class="card stats-card shadow-sm {{ request('status') == 'overdue' ? 'border-danger border-2' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted text-uppercase small">Overdue</span>
                                    <h2 class="mb-0 fw-bold mt-1 text-danger">{{ $dashboardStats['overdue_tasks'] ?? 0 }}</h2>
                                </div>
                                <div class="stats-icon bg-danger bg-opacity-10">
                                    <i class="ti ti-alert-triangle fs-4 text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Employee</label>
                        <select id="employee_id" class="form-select" onchange="filterTasks()">
                            <option value="">All Employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ $selectedEmployee == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Status</label>
                        <select id="status" class="form-select" onchange="filterTasks()">
                            <option value="">All Status</option>
                            @foreach($statuses as $key => $status)
                                <option value="{{ $key }}" {{ $selectedStatus == $key ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Priority</label>
                        <select id="priority" class="form-select" onchange="filterTasks()">
                            <option value="">All Priorities</option>
                            @foreach($priorities as $key => $priority)
                                <option value="{{ $key }}" {{ $selectedPriority == $key ? 'selected' : '' }}>
                                    {{ $priority }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('employee.task.tracker') }}" class="btn btn-secondary w-100">
                            <i class="ti ti-refresh me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Two Column Layout -->
        <div class="row g-4">
            <!-- Left Column - Employee Performance -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-transparent pt-4 pb-2">
                        <h5 class="mb-0 fw-semibold">Employee Performance</h5>
                        <small class="text-muted">Task completion overview</small>
                    </div>
                    <div class="card-body pt-2">
                        <div class="employee-list-scroll">
                            @forelse($employeeStats as $stat)
                                <div class="employee-stat-card">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <img src="{{ $stat['employee']->profile_image ? asset('storage/uploads/profile/' . $stat['employee']->profile_image) : asset('assets/img/user.png') }}" 
                                             class="avatar-small rounded-circle">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-semibold">{{ $stat['employee']->name }}</h6>
                                            <small class="text-muted">{{ ucfirst($stat['employee']->type) }}</small>
                                        </div>
                                        <span class="badge bg-primary">{{ $stat['total_tasks'] }}</span>
                                    </div>
                                    <div class="row g-2 text-center">
                                        <div class="col-4">
                                            <div class="stat-badge">
                                                <div class="fw-bold text-success">{{ $stat['completed_tasks'] }}</div>
                                                <small class="text-muted">Completed</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="stat-badge">
                                                <div class="fw-bold text-warning">{{ $stat['in_progress_tasks'] }}</div>
                                                <small class="text-muted">Progress</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="stat-badge">
                                                <div class="fw-bold text-danger">{{ $stat['overdue_tasks'] }}</div>
                                                <small class="text-muted">Overdue</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">Completion Rate</span>
                                            <span class="fw-semibold text-success">{{ $stat['completion_rate'] }}%</span>
                                        </div>
                                        <div class="progress-bar-custom w-100">
                                            <div class="progress-fill" style="width: {{ $stat['completion_rate'] }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="ti ti-users fs-1 text-muted d-block mb-2"></i>
                                    <p class="text-muted">No employee data</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Tasks Table -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-transparent pt-4 pb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 fw-semibold">All Tasks</h5>
                            <small class="text-muted">Task list and status</small>
                        </div>
                        <span class="badge bg-primary">{{ $tasks->count() }} Total</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="tasks-table-wrapper">
                            <table class="tasks-table">
                                <thead>
                                    <tr>
                                        <th class="col-id">ID</th>
                                        <th class="col-task">Task Name</th>
                                        <th class="col-assign">Assigned To</th>
                                        <th class="col-priority">Priority</th>
                                        <th class="col-date">Due Date</th>
                                        <th class="col-progress">Progress</th>
                                        <th class="col-status">Status</th>
                                        <th class="col-action"></th>
                                    </thead>
                                <tbody>
                                    @forelse($tasks as $task)
                                    <tr>
                                        <td class="col-id"><span class="task-id">{{ $task->task_id }}</span></td>
                                        <td class="col-task">
                                            <a href="{{ route('employee.task.tracker.show', Crypt::encrypt($task->id)) }}" class="task-title">
                                                {{ \Illuminate\Support\Str::limit($task->title, 35) }}
                                            </a>
                                        </td>
                                        <td class="col-assign">
                                            @if($task->employee)
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ $task->employee->profile_image ? asset('storage/uploads/profile/' . $task->employee->profile_image) : asset('assets/img/user.png') }}" 
                                                         class="avatar-small rounded-circle">
                                                    <span class="small">{{ $task->employee->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted small">Unassigned</span>
                                            @endif
                                        </td>
                                        <td class="col-priority">
                                            @if($task->priority == 'low')
                                                <span class="priority-low">Low</span>
                                            @elseif($task->priority == 'medium')
                                                <span class="priority-medium">Medium</span>
                                            @elseif($task->priority == 'high')
                                                <span class="priority-high">High</span>
                                            @else
                                                <span class="priority-urgent">Urgent</span>
                                            @endif
                                        </td>
                                        <td class="col-date">
                                            <div class="due-date {{ $task->isOverdue() ? 'overdue' : '' }}">
                                                {{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}
                                                @if($task->isOverdue())
                                                    <i class="ti ti-alert-triangle ms-1" style="font-size: 12px;"></i>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="col-progress">
                                            <div class="progress-bar-custom">
                                                <div class="progress-fill" style="width: {{ $task->progress }}%"></div>
                                            </div>
                                            <div class="small text-muted mt-1">{{ $task->progress }}%</div>
                                        </td>
                                        <td class="col-status">
                                            @if($task->status == 'completed')
                                                <span class="status-completed">Completed</span>
                                            @elseif($task->status == 'in_progress')
                                                <span class="status-progress">In Progress</span>
                                            @elseif($task->status == 'pending')
                                                <span class="status-pending">Pending</span>
                                            @else
                                                <span class="status-hold">On Hold</span>
                                            @endif
                                        </td>
                                        <td class="col-action">
                                            <div class="dropdown">
                                                <button class="action-btn" type="button" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="{{ route('employee.task.tracker.show', Crypt::encrypt($task->id)) }}"><i class="ti ti-eye me-2"></i> View</a></li>
                                                    @if($task->status != 'completed')
                                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#statusModal{{ $task->id }}"><i class="ti ti-check me-2"></i> Update Status</a></li>
                                                    @endif
                                                    <li><a class="dropdown-item" href="{{ route('employee.task.tracker.edit', Crypt::encrypt($task->id)) }}"><i class="ti ti-edit me-2"></i> Edit</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="if(confirm('Are you sure?')) document.getElementById('delete-form-{{ $task->id }}').submit()"><i class="ti ti-trash me-2"></i> Delete</a></li>
                                                </ul>
                                            </div>
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['employee.task.tracker.destroy', Crypt::encrypt($task->id)], 'id' => 'delete-form-' . $task->id]) !!}
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>

                                    <!-- Status Update Modal -->
                                    @if($task->status != 'completed')
                                    <div class="modal fade" id="statusModal{{ $task->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                {!! Form::open(['route' => ['employee.task.tracker.update-status', $task->id], 'method' => 'POST']) !!}
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Status</label>
                                                        <select name="status" class="form-select">
                                                            <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                            <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                            <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                            <option value="on_hold" {{ $task->status == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Progress (%)</label>
                                                        <input type="number" name="progress" class="form-control" min="0" max="100" value="{{ $task->progress }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Completion Notes</label>
                                                        <textarea name="completion_notes" class="form-control" rows="2"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                                {!! Form::close() !!}
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="ti ti-clipboard-list fs-1 text-muted d-block mb-2"></i>
                                            <p class="text-muted mb-3">No tasks found</p>
                                            <a href="{{ route('employee.task.tracker.create') }}" class="btn btn-primary btn-sm">Create Your First Task</a>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
    function filterTasks() {
        var employeeId = $('#employee_id').val();
        var status = $('#status').val();
        var priority = $('#priority').val();
        
        var url = '{{ route("employee.task.tracker") }}';
        var params = [];
        
        if (employeeId) params.push('employee_id=' + encodeURIComponent(employeeId));
        if (status) params.push('status=' + encodeURIComponent(status));
        if (priority) params.push('priority=' + encodeURIComponent(priority));
        
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        window.location.href = url;
    }
</script>
@endpush