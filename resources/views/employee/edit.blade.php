@extends('layouts.admin')
@section('page-title')
    {{__('Edit Employee')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('employee.index')}}">{{__('Employee')}}</a></li>
    <li class="breadcrumb-item">{{$employeesId}}</li>
@endsection

@section('content')
<div class="row">
{{ Form::model($employee, array('route' => array('employee.update', $employee->id), 'method' => 'PUT' , 'enctype' => 'multipart/form-data', 'class'=>'needs-validation', 'novalidate')) }}
    <div class="row">
        <div class="col-md-6 ">
            <div class="card emp_details">
                <div class="card-header"><h6 class="mb-0">{{__('Personal Detail')}}</h6></div>
                <div class="card-body employee-detail-edit-body">

                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('name', __('Name'),['class'=>'form-label']) !!}<x-required></x-required>
                            {!! Form::text('name', null, ['class' => 'form-control','required' => 'required','placeholder'=>__('Enter employee name')]) !!}
                        </div>
                        <div class="form-group col-md-6">
                            <x-mobile label="{{__('Phone')}}" name="phone" value="{{$employee->phone}}" required placeholder="Enter employee phone"></x-mobile>
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('dob', __('Date of Birth'),['class'=>'form-label']) !!}<x-required></x-required>
                            {!! Form::date('dob', null, ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('gender', __('Gender'),['class'=>'form-label']) !!}<x-required></x-required>
                            <div class="d-flex radio-check mt-2">
                                <div class="form-check form-check-inline form-group">
                                    <input type="radio" id="g_male" value="Male" name="gender" class="form-check-input" {{($employee->gender == 'Male')?'checked':''}} required>
                                    <label class="form-check-label" for="g_male">{{__('Male')}}</label>
                                </div>
                                <div class="form-check form-check-inline form-group">
                                    <input type="radio" id="g_female" value="Female" name="gender" class="form-check-input" {{($employee->gender == 'Female')?'checked':''}} required>
                                    <label class="form-check-label" for="g_female">{{__('Female')}}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('address', __('Address'),['class'=>'form-label']) !!}<x-required></x-required>
                        {!! Form::textarea('address',null, ['class' => 'form-control','rows'=>2, 'required' => 'required','placeholder'=>__('Enter employee address')]) !!}
                    </div>
                    @if(\Auth::user()->type=='employee')
                        {!! Form::submit('Update', ['class' => 'btn-create btn-xs badge-blue radius-10px float-right']) !!}
                    @endif
                </div>
            </div>
        </div>
        @if(\Auth::user()->type!='Employee')
            <div class="col-md-6 ">
                <div class="card emp_details">
                    <div class="card-header"><h6 class="mb-0">{{__('Company Detail')}}</h6></div>
                    <div class="card-body employee-detail-edit-body">
                        <div class="row">
                            @csrf
                            <div class="form-group col-md-12">
                                {!! Form::label('employee_id', __('Employee ID'),['class'=>'form-label']) !!}
                                {!! Form::text('employee_id',$employeesId, ['class' => 'form-control','disabled'=>'disabled']) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {{ Form::label('branch_id', __('Branch'),['class'=>'form-label']) }}<x-required></x-required>
                                {{ Form::select('branch_id', $branches,null, array('class' => 'form-control select','required'=>'required','id' => 'branch_id')) }}
                            </div>
                            <div class="form-group col-md-6">
                                {{ Form::label('department_id', __('Department'),['class'=>'form-label']) }}<x-required></x-required>
                                {{ Form::select('department_id', $departments,null, array('class' => 'form-control select','required'=>'required','id' => 'department_id')) }}
                            </div>
                            <div class="form-group col-md-6">
                                {{ Form::label('designation_id', __('Designation'),['class'=>'form-label']) }}<x-required></x-required>
                                {{ Form::select('designation_id', $designations,null, array('class' => 'form-control select','required'=>'required','id' => 'designation_id')) }}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('company_doj', 'Company Date Of Joining',['class'=>'form-label']) !!}<x-required></x-required>
                                {!! Form::date('company_doj', null, ['class' => 'form-control ','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-md-6 ">
                <div class="employee-detail-wrap ">
                    <div class="card emp_details">
                        <div class="card-header"><h6 class="mb-0">{{__('Company Detail')}}</h6></div>
                        <div class="card-body employee-detail-edit-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong>{{__('Branch')}}</strong>
                                        <span>{{!empty($employee->branch)?$employee->branch->name:''}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info font-style">
                                        <strong>{{__('Department')}}</strong>
                                        <span>{{!empty($employee->department)?$employee->department->name:''}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info font-style">
                                        <strong>{{__('Designation')}}</strong>
                                        <span>{{!empty($employee->designation)?$employee->designation->name:''}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info">
                                        <strong>{{__('Date Of Joining')}}</strong>
                                        <span>{{\Auth::user()->dateFormat($employee->company_doj)}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if(\Auth::user()->type!='Employee')
        <div class="row">
            <!-- Document Section -->
            <div class="col-md-6 ">
                <div class="card emp_details">
                    <div class="card-header"><h6 class="mb-0">{{__('Document')}}</h6></div>
                    <div class="card-body employee-detail-edit-body">
                        @php
                            $employeedoc = $employee->documents()->pluck('document_value',__('document_id'));
                        @endphp
                        @foreach($documents as $key=>$document)
                            <div class="row">
                                <div class="form-group col-12">
                                    <div class="float-left col-4">
                                        <label for="document" class="float-left pt-1 form-label">{{ $document->name }} @if($document->is_required == 1) <x-required></x-required> @endif</label>
                                    </div>
                                    <div class="float-right col-4">
                                        <input type="hidden" name="emp_doc_id[{{ $document->id}}]" id="" value="{{$document->id}}">
                                        <div class="choose-file form-group">
                                            <label for="document[{{ $document->id }}]">
                                                <input class="form-control file-validate @if(!empty($employeedoc[$document->id])) float-left @endif @error('document') is-invalid @enderror border-0" @if($document->is_required == 1 && empty($employeedoc[$document->id]) ) required @endif name="document[{{ $document->id}}]"  onchange="document.getElementById('{{'blah'.$key}}').src = window.URL.createObjectURL(this.files[0])" type="file"  data-filename="{{ $document->id.'_filename'}}">
                                                <p id="" class="file-error text-danger"></p>
                                            </label>
                                            <p class="{{ $document->id.'_filename'}}"></p>
                                            @php
                                                $logo=\App\Models\Utility::get_file('uploads/document/');
                                            @endphp
                                            <img id="{{'blah'.$key}}" src="{{ (isset($employeedoc[$document->id]) && !empty($employeedoc[$document->id])?$logo.'/'.$employeedoc[$document->id]:'') }}"  width="25%" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Bank Account Detail Section -->
            <div class="col-md-6">
                <div class="card emp_details">
                    <div class="card-header"><h6 class="mb-0">{{__('Bank Account Detail')}}</h6></div>
                    <div class="card-body employee-detail-edit-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('account_holder_name', __('Account Holder Name'),['class'=>'form-label']) !!}
                                {!! Form::text('account_holder_name', null, ['class' => 'form-control','placeholder'=>__('Enter account holder name')]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('account_number', __('Account Number'),['class'=>'form-label']) !!}
                                {!! Form::number('account_number', null, ['class' => 'form-control','placeholder'=>__('Enter account number')]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('bank_name', __('Bank Name'),['class'=>'form-label']) !!}
                                {!! Form::text('bank_name', null, ['class' => 'form-control', 'placeholder'=>__('Enter bank name')]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('bank_identifier_code', __('Bank Identifier Code'),['class'=>'form-label']) !!}
                                {!! Form::text('bank_identifier_code',null, ['class' => 'form-control','placeholder'=>__('Enter bank identifier code')]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('branch_location', __('Branch Location'),['class'=>'form-label']) !!}
                                {!! Form::text('branch_location',null, ['class' => 'form-control','placeholder'=>__('Enter branch location')]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('tax_payer_id', __('Tax Payer Id'),['class'=>'form-label']) !!}
                                {!! Form::text('tax_payer_id',null, ['class' => 'form-control','placeholder'=>__('Enter tax payer id')]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- HALF DAY & LATE ACCESS CALCULATION MODULE -->
        <!-- ============================================================ -->
        <div class="row">
            <div class="col-12">
                <div class="card emp_details">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{__('Attendance Rules')}}</h6>
                            <span class="badge bg-primary">{{__('Per Employee Setting')}}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <strong>{{__('How Half Day & Late Access Work:')}}</strong>
                                    <ul class="mb-0 mt-1">
                                        <li><strong>{{__('Normal Employee:')}}</strong> {{__('If late by even 1 minute → Half Day (regardless of total hours worked)')}}</li>
                                        <li><strong>{{__('Employee with Late Access:')}}</strong> {{__('Allowed to be late up to X minutes. If within allowed time → Full Day. If beyond allowed time → Half Day.')}}</li>
                                        <li class="text-warning"><i class="ti ti-alert-triangle me-1"></i> {{__('Half Day is determined by lateness, NOT by total hours worked for these employees.')}}</li>
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="row">
                                    <!-- ============================================ -->
                                    <!-- LATE ACCESS TOGGLE -->
                                    <!-- ============================================ -->
                                    <div class="form-group col-md-12">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="late_access_enabled" 
                                                name="late_access_enabled" 
                                                {{ (old('late_access_enabled', $employee->late_access_enabled ?? false)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="late_access_enabled">
                                                <strong>{{__('Enable Late Access')}}</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted">
                                            {{__('Allow this employee to be late without affecting attendance status')}}
                                        </small>
                                    </div>

                                    <!-- ============================================ -->
                                    <!-- ALLOWED LATE MINUTES -->
                                    <!-- ============================================ -->
                                    <div class="form-group col-md-12" id="lateMinutesGroup" style="{{ (old('late_access_enabled', $employee->late_access_enabled ?? false)) ? '' : 'display:none;' }}">
                                        {{ Form::label('late_allowed_minutes', __('Allowed Late Minutes'), ['class' => 'form-label']) }}
                                        <div class="input-group">
                                            {{ Form::number('late_allowed_minutes', 
                                                old('late_allowed_minutes', $employee->late_allowed_minutes ?? 60), 
                                                [
                                                    'class' => 'form-control',
                                                    'step' => '5',
                                                    'min' => '0',
                                                    'max' => '120',
                                                    'id' => 'late_allowed_minutes',
                                                    'placeholder' => 'e.g., 60'
                                                ]) 
                                            }}
                                            <span class="input-group-text">{{__('minutes')}}</span>
                                        </div>
                                        <small class="text-muted">
                                            {{__('Employee can be late up to this many minutes and still get Full Day')}}
                                        </small>
                                    </div>

                                    <!-- ============================================ -->
                                    <!-- SAMPLE SCENARIOS -->
                                    <!-- ============================================ -->
                                    <div class="form-group col-md-12 mt-2">
                                        <label class="form-label">{{__('Quick Presets')}}</label>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setLateAccess(30)">
                                                <i class="ti ti-clock me-1"></i>30 min
                                            </button>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="setLateAccess(60)">
                                                <i class="ti ti-clock me-1"></i>1 hour
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setLateAccess(90)">
                                                <i class="ti ti-clock me-1"></i>1.5 hours
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setLateAccess(120)">
                                                <i class="ti ti-clock me-1"></i>2 hours
                                            </button>
                                        </div>
                                    </div>

                                    <!-- ============================================ -->
                                    <!-- HALF DAY THRESHOLD (Only used if half day is based on hours) -->
                                    <!-- ============================================ -->
                                    <div class="form-group col-md-12 mt-2">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="enable_half_day" 
                                                name="enable_half_day" 
                                                {{ (old('enable_half_day', $employee->enable_half_day ?? true)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="enable_half_day">
                                                {{__('Enable Half Day Calculation')}}
                                            </label>
                                        </div>
                                        <small class="text-muted">
                                            {{__('Uncheck to never mark as Half Day')}}
                                        </small>
                                    </div>

                                    <div class="form-group col-md-12" id="thresholdGroup" style="{{ (old('enable_half_day', $employee->enable_half_day ?? true)) ? '' : 'display:none;' }}">
                                        {{ Form::label('half_day_threshold', __('Half Day Threshold (Hours)'), ['class' => 'form-label']) }}
                                        <div class="input-group">
                                            {{ Form::number('half_day_threshold', 
                                                old('half_day_threshold', $employee->half_day_threshold ?? 4.0), 
                                                [
                                                    'class' => 'form-control',
                                                    'step' => '0.5',
                                                    'min' => '1',
                                                    'max' => '8',
                                                    'id' => 'half_day_threshold',
                                                    'placeholder' => 'e.g., 4.0'
                                                ]) 
                                            }}
                                            <span class="input-group-text">{{__('hours')}}</span>
                                        </div>
                                        <small class="text-muted">
                                            {{__('Default: 4.0 hours (50% of 8-hour workday)')}}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card bg-light border-0 h-100">
                                    <div class="card-header bg-transparent border-0">
                                        <h6 class="mb-0">{{__('How it works for this employee')}}</h6>
                                    </div>
                                    <div class="card-body pt-0" id="halfDayPreview">
                                        <!-- Scenario 1: Late Access Enabled -->
                                        <div class="row g-2" id="lateAccessPreview">
                                            <div class="col-12">
                                                <div class="d-flex align-items-center p-2 bg-white rounded">
                                                    <span class="badge bg-success me-2" style="width: 40px;">✅</span>
                                                    <div>
                                                        <strong>{{__('Full Day (Late Access)')}}:</strong>
                                                        <span id="fullDayLateLabel" class="text-muted">Late by ≤ 60 minutes</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex align-items-center p-2 bg-white rounded">
                                                    <span class="badge bg-warning me-2" style="width: 40px;">🌓</span>
                                                    <div>
                                                        <strong>{{__('Half Day (Late Access)')}}:</strong>
                                                        <span id="halfDayLateLabel" class="text-muted">Late by &gt; 60 minutes</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr>

                                        <!-- Scenario 2: Late Access Disabled -->
                                        <div class="row g-2" id="noLateAccessPreview">
                                            <div class="col-12">
                                                <div class="d-flex align-items-center p-2 bg-white rounded">
                                                    <span class="badge bg-success me-2" style="width: 40px;">✅</span>
                                                    <div>
                                                        <strong>{{__('Full Day (No Late Access)')}}:</strong>
                                                        <span id="fullDayNoLateLabel" class="text-muted">On time (at or before start time)</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex align-items-center p-2 bg-white rounded">
                                                    <span class="badge bg-warning me-2" style="width: 40px;">🌓</span>
                                                    <div>
                                                        <strong>{{__('Half Day (No Late Access)')}}:</strong>
                                                        <span id="halfDayNoLateLabel" class="text-muted">Late by even 1 minute</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 p-2 bg-warning bg-opacity-10 rounded">
                                            <small class="text-warning">
                                                <i class="ti ti-alert-triangle me-1"></i>
                                                <strong>{{__('Note:')}}</strong>
                                                {{__('Half Day is based on lateness, NOT total hours worked for these employees.')}}
                                            </small>
                                        </div>
                                        
                                        <div class="mt-2 p-2 bg-white rounded">
                                            <small class="text-muted">
                                                <i class="ti ti-info-circle me-1"></i>
                                                {{__('Status is calculated automatically when employee clocks in.')}}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ============================================================ -->
        <!-- END: Half Day & Late Access Module -->
        <!-- ============================================================ -->

        <div class="row">
            <div class="col-12">
                <input type="submit" value="{{__('Update')}}" class="btn btn-primary float-end">
            </div>
        </div>
    @endif
{!! Form::close() !!}
</div>
@endsection

@push('script-page')
    <script type="text/javascript">
        // ============================================================
        // BRANCH -> DEPARTMENT AJAX
        // ============================================================
        $(document).on('change', '#branch_id', function() {
            var branch_id = $(this).val();
            getDepartment(branch_id);
        });

        function getDepartment(branch_id)
        {
            var data = {
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            }

            $.ajax({
                url: '{{ route('employee.getdepartment') }}',
                method: 'POST',
                data: data,
                success: function(data) {
                    $('#department_id').empty();
                    $('#department_id').append('<option value="" disabled>{{ __('Select any Department') }}</option>');

                    $.each(data, function(key, value) {
                        $('#department_id').append('<option value="' + key + '">' + value + '</option>');
                    });
                    $('#department_id').val('');
                }
            });
        }

        // ============================================================
        // DEPARTMENT -> DESIGNATION AJAX
        // ============================================================
        function getDesignation(did) {
            $.ajax({
                url: '{{route('employee.json')}}',
                type: 'POST',
                data: {
                    "department_id": did, 
                    "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    $('#designation_id').empty();
                    $('#designation_id').append('<option value="">Select any Designation</option>');
                    $.each(data, function (key, value) {
                        var select = '';
                        if (key == '{{ $employee->designation_id }}') {
                            select = 'selected';
                        }
                        $('#designation_id').append('<option value="' + key + '"  ' + select + '>' + value + '</option>');
                    });
                }
            });
        }

        $(document).ready(function () {
            var d_id = $('#department_id').val();
            var designation_id = '{{ $employee->designation_id }}';
            getDesignation(d_id);
        });

        $(document).on('change', 'select[name=department_id]', function () {
            var department_id = $(this).val();
            getDesignation(department_id);
        });

        // ============================================================
        // LATE ACCESS - TOGGLE & PREVIEW
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            const lateAccessToggle = document.getElementById('late_access_enabled');
            const lateMinutesGroup = document.getElementById('lateMinutesGroup');
            const lateMinutesInput = document.getElementById('late_allowed_minutes');
            const fullDayLateLabel = document.getElementById('fullDayLateLabel');
            const halfDayLateLabel = document.getElementById('halfDayLateLabel');

            // Toggle late minutes group visibility
            if (lateAccessToggle) {
                lateAccessToggle.addEventListener('change', function() {
                    if (this.checked) {
                        lateMinutesGroup.style.display = 'block';
                    } else {
                        lateMinutesGroup.style.display = 'none';
                    }
                    updateLatePreview();
                });
            }

            // Update preview when late minutes change
            if (lateMinutesInput) {
                lateMinutesInput.addEventListener('input', function() {
                    updateLatePreview();
                });
            }

            function updateLatePreview() {
                const isEnabled = lateAccessToggle ? lateAccessToggle.checked : false;
                const minutes = parseInt(lateMinutesInput ? lateMinutesInput.value : 60) || 60;
                
                if (fullDayLateLabel) {
                    fullDayLateLabel.textContent = 'Late by ≤ ' + minutes + ' minutes';
                }
                if (halfDayLateLabel) {
                    halfDayLateLabel.textContent = 'Late by > ' + minutes + ' minutes';
                }
            }

            // Initial update
            updateLatePreview();

            // ============================================================
            // HALF DAY THRESHOLD - TOGGLE
            // ============================================================
            const halfDayToggle = document.getElementById('enable_half_day');
            const thresholdGroup = document.getElementById('thresholdGroup');

            if (halfDayToggle) {
                halfDayToggle.addEventListener('change', function() {
                    if (this.checked) {
                        thresholdGroup.style.display = 'block';
                    } else {
                        thresholdGroup.style.display = 'none';
                    }
                });
            }
        });

        // ============================================================
        // QUICK PRESETS
        // ============================================================
        function setLateAccess(minutes) {
            const input = document.getElementById('late_allowed_minutes');
            if (input) {
                input.value = minutes;
                input.dispatchEvent(new Event('input'));
            }
            // Also enable late access if not already enabled
            const toggle = document.getElementById('late_access_enabled');
            if (toggle && !toggle.checked) {
                toggle.checked = true;
                toggle.dispatchEvent(new Event('change'));
            }
        }
    </script>

    <style>
        .half-day-preset-btn {
            transition: all 0.2s ease;
        }
        .half-day-preset-btn:hover {
            transform: scale(1.05);
        }
        .half-day-preset-btn.active {
            border-color: var(--bs-primary);
            background-color: var(--bs-primary);
            color: white;
        }
        #halfDayPreview .bg-white {
            transition: background-color 0.2s ease;
        }
        #halfDayPreview .bg-white:hover {
            background-color: #f8f9fa !important;
        }
        .badge[style*="width: 40px;"] {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
    </style>
@endpush