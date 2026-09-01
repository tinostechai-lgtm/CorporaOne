@extends('layouts.admin')

@section('page-title', 'Submit Work Report')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hrm.dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('workreport.my') }}">{{ __('Work Reports') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Submit Report') }}</li>
@endsection

@push('css')
<style>
    .work-report-form .card {
        border: none;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .work-report-form .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 15px 20px;
        border-radius: 10px 10px 0 0 !important;
    }
    .work-report-form .card-body {
        padding: 20px;
    }
    .work-report-form .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .work-report-form .btn-submit {
        padding: 10px 40px;
        font-weight: 600;
        border-radius: 50px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        transition: all 0.3s ease;
    }
    .work-report-form .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    }
    .work-report-form .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    .work-report-form .attendance-summary {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 15px;
        border-radius: 8px;
    }
    .work-report-form .attendance-summary .label {
        color: #6c757d;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .work-report-form .attendance-summary .value {
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }
    .work-report-form .quick-task-check {
        padding: 8px 12px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .work-report-form .quick-task-check:hover {
        background: #f8f9fa;
    }
    .work-report-form .quick-task-check input[type="checkbox"] {
        margin-right: 8px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid work-report-form">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fa fa-file-text me-2 text-primary"></i>
                        {{ __('Submit Work Report') }}
                    </h5>
                    <small class="text-muted">{{ date('d M Y') }}</small>
                </div>
                <div class="card-body">
                    
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle me-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle me-2"></i>
                            {{ session('info') }}
                        </div>
                    @endif

                    {{-- Attendance Summary --}}
                    <div class="attendance-summary mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="label">{{ __('Employee') }}</div>
                                <div class="value">{{ $employee->name ?? Auth::user()->name }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="label">{{ __('Date') }}</div>
                                <div class="value">{{ date('d M Y') }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="label">{{ __('Clock In') }}</div>
                                <div class="value">{{ $attendance ? date('h:i A', strtotime($attendance->clock_in)) : '--:--' }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="label">{{ __('Status') }}</div>
                                <div class="value">
                                    @if($attendance && $attendance->clock_out == '00:00:00')
                                        <span class="badge bg-success">Clocked In</span>
                                    @elseif($attendance && $attendance->clock_out != '00:00:00')
                                        <span class="badge bg-secondary">Clocked Out</span>
                                    @else
                                        <span class="badge bg-warning">Not Clocked In</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('workreport.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $employee->id ?? Auth::user()->employee->id }}">
                        <input type="hidden" name="date" value="{{ date('Y-m-d') }}">
                        <input type="hidden" name="attendance_id" value="{{ $attendance->id ?? '' }}">
                        <input type="hidden" name="clock_in" value="{{ $attendance->clock_in ?? '' }}">

                        <div class="row">
                            {{-- Work Description --}}
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fa fa-notes me-2 text-primary"></i>
                                            {{ __('Work Description') }}
                                            <span class="text-danger">*</span>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="work_description" class="form-label">{{ __('What did you work on today?') }}</label>
                                            <textarea class="form-control @error('work_description') is-invalid @enderror" 
                                                      id="work_description" 
                                                      name="work_description" 
                                                      rows="4" 
                                                      placeholder="{{ __('Describe your work today in detail...') }}"
                                                      required>{{ old('work_description') }}</textarea>
                                            @error('work_description')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="text-muted">{{ __('Minimum 10 characters required') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            {{-- Quick Tasks --}}
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fa fa-list-check me-2 text-primary"></i>
                                            {{ __('Quick Tasks') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @foreach(['Meeting', 'Email', 'Coding', 'Documentation', 'Design', 'Testing', 'Other'] as $task)
                                            <div class="quick-task-check">
                                                <input type="checkbox" 
                                                       id="task_{{ strtolower($task) }}" 
                                                       value="{{ $task }}" 
                                                       name="quick_tasks[]"
                                                       {{ in_array($task, old('quick_tasks', [])) ? 'checked' : '' }}>
                                                <label for="task_{{ strtolower($task) }}">{{ __($task) }}</label>
                                            </div>
                                        @endforeach
                                        <div class="quick-task-check mt-2">
                                            <input type="text" class="form-control form-control-sm" 
                                                   id="custom_task" 
                                                   name="custom_task" 
                                                   placeholder="{{ __('Custom task...') }}"
                                                   value="{{ old('custom_task') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Achievements --}}
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fa fa-trophy me-2 text-warning"></i>
                                            {{ __('Achievements') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="achievements" class="form-label">{{ __('What did you achieve today?') }}</label>
                                            <textarea class="form-control @error('achievements') is-invalid @enderror" 
                                                      id="achievements" 
                                                      name="achievements" 
                                                      rows="3" 
                                                      placeholder="{{ __('List your achievements for today...') }}">{{ old('achievements') }}</textarea>
                                            @error('achievements')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            {{-- Challenges --}}
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fa fa-exclamation-triangle me-2 text-danger"></i>
                                            {{ __('Challenges') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="challenges" class="form-label">{{ __('Any challenges faced today?') }}</label>
                                            <textarea class="form-control @error('challenges') is-invalid @enderror" 
                                                      id="challenges" 
                                                      name="challenges" 
                                                      rows="3" 
                                                      placeholder="{{ __('Describe any challenges or blockers...') }}">{{ old('challenges') }}</textarea>
                                            @error('challenges')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tomorrow's Plan --}}
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fa fa-calendar-plus me-2 text-success"></i>
                                            {{ __('Tomorrow\'s Plan') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="tomorrow_plan" class="form-label">{{ __('What do you plan to do tomorrow?') }}</label>
                                            <textarea class="form-control @error('tomorrow_plan') is-invalid @enderror" 
                                                      id="tomorrow_plan" 
                                                      name="tomorrow_plan" 
                                                      rows="3" 
                                                      placeholder="{{ __('Plan your tasks for tomorrow...') }}">{{ old('tomorrow_plan') }}</textarea>
                                            @error('tomorrow_plan')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Hourly Breakdown --}}
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fa fa-hourglass-half me-2 text-info"></i>
                                            {{ __('Hourly Breakdown') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="hours_project" class="form-label">{{ __('Project Work') }}</label>
                                                    <input type="number" 
                                                           class="form-control @error('hours_project') is-invalid @enderror" 
                                                           id="hours_project" 
                                                           name="hours_project" 
                                                           min="0" 
                                                           max="12" 
                                                           step="0.5" 
                                                           placeholder="{{ __('Hours') }}"
                                                           value="{{ old('hours_project', 0) }}">
                                                    @error('hours_project')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="hours_meeting" class="form-label">{{ __('Meetings') }}</label>
                                                    <input type="number" 
                                                           class="form-control @error('hours_meeting') is-invalid @enderror" 
                                                           id="hours_meeting" 
                                                           name="hours_meeting" 
                                                           min="0" 
                                                           max="12" 
                                                           step="0.5" 
                                                           placeholder="{{ __('Hours') }}"
                                                           value="{{ old('hours_meeting', 0) }}">
                                                    @error('hours_meeting')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="hours_admin" class="form-label">{{ __('Admin/Other') }}</label>
                                                    <input type="number" 
                                                           class="form-control @error('hours_admin') is-invalid @enderror" 
                                                           id="hours_admin" 
                                                           name="hours_admin" 
                                                           min="0" 
                                                           max="12" 
                                                           step="0.5" 
                                                           placeholder="{{ __('Hours') }}"
                                                           value="{{ old('hours_admin', 0) }}">
                                                    @error('hours_admin')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-2">
                                            <i class="fa fa-info-circle me-1"></i>
                                            {{ __('Total hours should add up to your worked hours.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Attachment --}}
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fa fa-paperclip me-2 text-secondary"></i>
                                            {{ __('Attachment') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="attachment" class="form-label">{{ __('Upload supporting file (optional)') }}</label>
                                            <input type="file" 
                                                   class="form-control @error('attachment') is-invalid @enderror" 
                                                   id="attachment" 
                                                   name="attachment"
                                                   accept=".pdf,.doc,.docx,.jpg,.png,.jpeg">
                                            @error('attachment')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="text-muted">{{ __('Max file size: 10MB. Allowed: PDF, DOC, DOCX, JPG, PNG') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-submit" id="submitBtn">
                                    <i class="fa fa-paper-plane me-2"></i>
                                    {{ __('Submit Work Report') }}
                                </button>
                                <a href="{{ route('workreport.my') }}" class="btn btn-secondary ms-2">
                                    <i class="fa fa-arrow-left me-2"></i>
                                    {{ __('Back') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const submitBtn = document.getElementById('submitBtn');
        const form = document.querySelector('form');

        form.addEventListener('submit', function(e) {
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> {{ __("Submitting...") }}';
        });

        // Auto-calculate total hours with warning
        const hoursProject = document.getElementById('hours_project');
        const hoursMeeting = document.getElementById('hours_meeting');
        const hoursAdmin = document.getElementById('hours_admin');

        function checkTotalHours() {
            const total = (parseFloat(hoursProject.value) || 0) + 
                         (parseFloat(hoursMeeting.value) || 0) + 
                         (parseFloat(hoursAdmin.value) || 0);
            
            // Check if total exceeds 24
            if (total > 24) {
                alert('{{ __("Total hours cannot exceed 24 hours. Please adjust.") }}');
                return false;
            }
            return true;
        }

        hoursProject.addEventListener('change', checkTotalHours);
        hoursMeeting.addEventListener('change', checkTotalHours);
        hoursAdmin.addEventListener('change', checkTotalHours);
    });
</script>
@endpush