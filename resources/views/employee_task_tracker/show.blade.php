@extends('layouts.admin')

@section('page-title')
    {{ __('Task Details') }} - {{ $task->title }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employee.task.tracker') }}">{{ __('Employee Task Tracker') }}</a></li>
    <li class="breadcrumb-item">{{ __('Task Details') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        <a href="{{ route('employee.task.tracker') }}" class="btn btn-sm btn-secondary me-2">
            <i class="ti ti-arrow-left"></i> {{ __('Back') }}
        </a>
        @if($task->status != 'completed')
            <a href="{{ route('employee.task.tracker.edit', Crypt::encrypt($task->id)) }}" class="btn btn-sm btn-warning me-2">
                <i class="ti ti-edit"></i> {{ __('Edit') }}
            </a>
        @endif
        <a href="#" class="btn btn-sm btn-danger" onclick="if(confirm('Are you sure you want to delete this task?')) document.getElementById('delete-task-form').submit()">
            <i class="ti ti-trash"></i> {{ __('Delete') }}
        </a>
        {!! Form::open(['method' => 'DELETE', 'route' => ['employee.task.tracker.destroy', Crypt::encrypt($task->id)], 'id' => 'delete-task-form']) !!}
        {!! Form::close() !!}
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Task Information') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Task ID') }}</label>
                            <p class="form-control-static fw-bold">{{ $task->task_id }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Task Title') }}</label>
                            <p class="form-control-static">{{ $task->title }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Assigned To') }}</label>
                            <p class="form-control-static">
                                @if($task->employee)
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $task->employee->profile_image ? asset('storage/uploads/profile/' . $task->employee->profile_image) : asset('assets/img/user.png') }}" alt="{{ $task->employee->name }}" class="rounded-circle me-2" width="30" height="30">
                                        <span>{{ $task->employee->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">{{ __('Unassigned') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Assigned By') }}</label>
                            <p class="form-control-static">{{ $task->assignedBy ? $task->assignedBy->name : '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Department') }}</label>
                            <p class="form-control-static">{{ $task->department ? $task->department->name : '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Project') }}</label>
                            <p class="form-control-static">{{ $task->project ? $task->project->name : '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Start Date') }}</label>
                            <p class="form-control-static">{{ \Carbon\Carbon::parse($task->start_date)->format('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Due Date') }}</label>
                            <p class="form-control-static {{ $task->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                {{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}
                                @if($task->isOverdue())
                                    <i class="ti ti-alert-triangle text-danger ms-1" title="Overdue"></i>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Priority') }}</label>
                            <p class="form-control-static">
                                @php
                                    $priorityColors = [
                                        'low' => 'secondary',
                                        'medium' => 'info',
                                        'high' => 'warning',
                                        'urgent' => 'danger'
                                    ];
                                @endphp
                                <span class="badge badge-{{ $priorityColors[$task->priority] ?? 'secondary' }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Status') }}</label>
                            <p class="form-control-static">{!! $task->status_badge !!}</p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Progress') }}</label>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $task->progress }}%" aria-valuenow="{{ $task->progress }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $task->progress }}%
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($task->description)
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Description') }}</label>
                            <p class="form-control-static">{{ $task->description }}</p>
                        </div>
                    </div>
                    @endif
                    @if($task->remarks)
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Remarks/Notes') }}</label>
                            <p class="form-control-static">{{ $task->remarks }}</p>
                        </div>
                    </div>
                    @endif
                    @if($task->completion_notes)
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Completion Notes') }}</label>
                            <p class="form-control-static">{{ $task->completion_notes }}</p>
                        </div>
                    </div>
                    @endif
                    @if($task->completed_at)
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Completed At') }}</label>
                            <p class="form-control-static">{{ \Carbon\Carbon::parse($task->completed_at)->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Created At') }}</label>
                            <p class="form-control-static">{{ \Carbon\Carbon::parse($task->created_at)->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label text-muted">{{ __('Last Updated') }}</label>
                            <p class="form-control-static">{{ \Carbon\Carbon::parse($task->updated_at)->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($task->status != 'completed')
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Update Task Status') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select class="form-control" id="status-select">
                                <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="on_hold" {{ $task->status == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('Progress (%)') }}</label>
                            <input type="number" id="progress-input" class="form-control" min="0" max="100" value="{{ $task->progress }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary d-block w-100" id="update-status-btn">
                                <i class="ti ti-check"></i> {{ __('Update Status') }}
                            </button>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label">{{ __('Completion Notes') }}</label>
                            <textarea id="completion-notes" class="form-control" rows="2" placeholder="Enter completion notes if task is completed"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('script-page')
<script>
    $(document).ready(function() {
        $('#update-status-btn').on('click', function() {
            var status = $('#status-select').val();
            var progress = $('#progress-input').val();
            var completionNotes = $('#completion-notes').val();
            
            $.ajax({
                url: '{{ route("employee.task.tracker.update-status", $task->id) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    progress: progress,
                    completion_notes: completionNotes
                },
                success: function(response) {
                    if(response.success) {
                        toastr.success('{{ __("Task status updated successfully!") }}');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error('{{ __("Failed to update status.") }}');
                    }
                },
                error: function(xhr) {
                    toastr.error('{{ __("An error occurred.") }}');
                }
            });
        });
    });
</script>
@endpush