@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Task') }} - {{ $task->title }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employee.task.tracker') }}">{{ __('Employee Task Tracker') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Task') }}</li>
@endsection

@section('action-btn')
    <a href="{{ route('employee.task.tracker') }}" class="btn btn-sm btn-secondary">
        <i class="ti ti-arrow-left"></i> {{ __('Back') }}
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                {{ Form::model($task, ['route' => ['employee.task.tracker.update', Crypt::encrypt($task->id)], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            {{ Form::label('title', __('Task Title'), ['class' => 'form-label']) }}
                            <span class="text-danger">*</span>
                            {{ Form::text('title', null, ['class' => 'form-control', 'required' => true, 'placeholder' => __('Enter task title')]) }}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            {{ Form::label('employee_id', __('Assign To'), ['class' => 'form-label']) }}
                            <span class="text-danger">*</span>
                            {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'required' => true, 'placeholder' => __('Select Employee')]) }}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                            {{ Form::select('department_id', $departments, null, ['class' => 'form-control select2', 'placeholder' => __('Select Department')]) }}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            {{ Form::label('project_id', __('Project'), ['class' => 'form-label']) }}
                            {{ Form::select('project_id', $projects, null, ['class' => 'form-control select2', 'placeholder' => __('Select Project')]) }}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}
                            <span class="text-danger">*</span>
                            {{ Form::date('due_date', null, ['class' => 'form-control', 'required' => true]) }}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            {{ Form::label('priority', __('Priority'), ['class' => 'form-label']) }}
                            <span class="text-danger">*</span>
                            {{ Form::select('priority', [
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent'
                            ], null, ['class' => 'form-control', 'required' => true]) }}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                            <span class="text-danger">*</span>
                            {{ Form::select('status', $statuses, null, ['class' => 'form-control', 'required' => true]) }}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            {{ Form::label('progress', __('Progress (%)'), ['class' => 'form-label']) }}
                            {{ Form::number('progress', null, ['class' => 'form-control', 'min' => 0, 'max' => 100, 'step' => 1]) }}
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 4, 'placeholder' => __('Enter task description')]) }}
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            {{ Form::label('remarks', __('Remarks/Notes'), ['class' => 'form-label']) }}
                            {{ Form::textarea('remarks', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Any additional notes')]) }}
                        </div>
                    </div>
                    
                    @if($task->status == 'completed')
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            {{ Form::label('completion_notes', __('Completion Notes'), ['class' => 'form-label']) }}
                            {{ Form::textarea('completion_notes', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Completion notes')]) }}
                        </div>
                    </div>
                    @endif
                </div>
                
                <div class="text-end">
                    {{ Form::submit(__('Update Task'), ['class' => 'btn btn-primary']) }}
                    <a href="{{ route('employee.task.tracker') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2').select2({
            placeholder: "{{ __('Select an option') }}",
            allowClear: true,
            width: '100%'
        });
    }
});
</script>
@endpush