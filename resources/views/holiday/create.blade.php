{{Form::open(array('url'=>'holiday','method'=>'post', 'class'=>'needs-validation', 'novalidate'))}}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
        $leaveTypes = \App\Models\LeaveType::where('created_by', Auth::user()->creatorId())->get();
    @endphp
    @if($plan->chatgpt == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['holiday']) }}"
           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    
    {{-- ============================================================
    HOLIDAY TYPE SELECTION
    ============================================================ --}}
    <div class="row">
        <div class="form-group col-md-12">
            {{Form::label('type',__('Holiday/Leave Type'),['class'=>'form-label'])}}<x-required></x-required>
            <select name="type" id="holidayType" class="form-control" required onchange="toggleHolidaySections(this.value)">
                <option value="">{{ __('Select Type') }}</option>
                <option value="holiday">{{ __('Holiday (Date Range)') }}</option>
                <option value="week_off">{{ __('Week Off (Recurring)') }}</option>
                <option value="paid_leave">{{ __('Paid Leave') }}</option>
                <option value="unpaid_leave">{{ __('Unpaid Leave') }}</option>
                <option value="sick_leave">{{ __('Sick Leave') }}</option>
                <option value="casual_leave">{{ __('Casual Leave') }}</option>
                <option value="maternity_leave">{{ __('Maternity Leave') }}</option>
                <option value="paternity_leave">{{ __('Paternity Leave') }}</option>
                <option value="compensatory_off">{{ __('Compensatory Off') }}</option>
                <option value="other">{{ __('Other') }}</option>
            </select>
            <small class="text-muted">{{ __('Select what type of holiday/leave you want to create') }}</small>
        </div>
    </div>

    {{-- ============================================================
    HOLIDAY SECTION (Date Range) - FIXED is_paid naming
    ============================================================ --}}
    <div id="holidaySection" style="display: none; border: 2px solid #e9ecef; border-radius: 8px; padding: 15px; margin-top: 10px; background: #f8f9fa;">
        <div class="row">
            <div class="col-12">
                <h6 class="text-primary"><i class="ti ti-calendar-event me-2"></i>{{ __('Holiday Details') }}</h6>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12">
                {{Form::label('occasion',__('Occasion Name'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::text('occasion',null,array('class'=>'form-control', 'placeholder'=>__('Enter Occasion (e.g., Christmas, New Year)')))}}</div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                {{Form::label('date',__('Start Date'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::date('date',null,array('class'=>'form-control', 'min' => date('Y-m-d')))}}
            </div>
            <div class="form-group col-md-6">
                {{Form::label('end_date',__('End Date'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::date('end_date',null,array('class'=>'form-control', 'min' => date('Y-m-d')))}}
            </div>
        </div>
        <div class="row mt-2">
            <div class="form-group col-md-6">
                {{Form::label('holiday_is_paid',__('Is Paid?'),['class'=>'form-label'])}}<x-required></x-required>
                <div class="form-check form-switch mt-2">
                    <input type="hidden" name="holiday_is_paid" value="0">
                    <input type="checkbox" class="form-check-input" name="holiday_is_paid" id="holiday_is_paid_checkbox" value="1" checked>
                    <label class="form-check-label" for="holiday_is_paid_checkbox">{{ __('Yes (Paid)') }}</label>
                </div>
            </div>
            <div class="form-group col-md-6">
                {{Form::label('applicable_to',__('Applicable To'),['class'=>'form-label'])}}<x-required></x-required>
                <select name="applicable_to" class="form-control" id="applicable_to_holiday" onchange="toggleDepartment(this.value, 'holidayDepartments')">
                    <option value="all">{{ __('All Employees') }}</option>
                    <option value="specific">{{ __('Specific Departments') }}</option>
                </select>
            </div>
        </div>
        <div class="row mt-2" id="holidayDepartments" style="display: none;">
            <div class="form-group col-md-12">
                {{Form::label('departments',__('Select Departments'),['class'=>'form-label'])}}<x-required></x-required>
                <select name="departments[]" class="form-control select2" multiple>
                    @if(isset($departments) && $departments->count() > 0)
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>

    {{-- ============================================================
    WEEK OFF SECTION (Day Selection) - FIXED is_paid naming
    ============================================================ --}}
    <div id="weekOffSection" style="display: none; border: 2px solid #e9ecef; border-radius: 8px; padding: 15px; margin-top: 10px; background: #f8f9fa;">
        <div class="row">
            <div class="col-12">
                <h6 class="text-primary"><i class="ti ti-calendar-week me-2"></i>{{ __('Week Off Details') }}</h6>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12">
                {{Form::label('week_off_days',__('Select Week Off Days'),['class'=>'form-label'])}}<x-required></x-required>
                <div class="row g-2">
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="week_off_days[]" value="1" id="day_mon">
                            <label class="form-check-label" for="day_mon">{{ __('Monday') }}</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="week_off_days[]" value="2" id="day_tue">
                            <label class="form-check-label" for="day_tue">{{ __('Tuesday') }}</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="week_off_days[]" value="3" id="day_wed">
                            <label class="form-check-label" for="day_wed">{{ __('Wednesday') }}</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="week_off_days[]" value="4" id="day_thu">
                            <label class="form-check-label" for="day_thu">{{ __('Thursday') }}</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="week_off_days[]" value="5" id="day_fri">
                            <label class="form-check-label" for="day_fri">{{ __('Friday') }}</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="week_off_days[]" value="6" id="day_sat">
                            <label class="form-check-label" for="day_sat">{{ __('Saturday') }}</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="week_off_days[]" value="7" id="day_sun">
                            <label class="form-check-label" for="day_sun">{{ __('Sunday') }}</label>
                        </div>
                    </div>
                </div>
                <small class="text-muted">{{ __('Check the days that are considered week off (recurring weekly)') }}</small>
            </div>
        </div>
        <div class="row mt-2">
            <div class="form-group col-md-6">
                {{Form::label('weekoff_is_paid',__('Is Paid?'),['class'=>'form-label'])}}<x-required></x-required>
                <div class="form-check form-switch mt-2">
                    <input type="hidden" name="weekoff_is_paid" value="0">
                    <input type="checkbox" class="form-check-input" name="weekoff_is_paid" id="weekoff_is_paid_checkbox" value="1" checked>
                    <label class="form-check-label" for="weekoff_is_paid_checkbox">{{ __('Yes (Paid)') }}</label>
                </div>
            </div>
            <div class="form-group col-md-6">
                {{Form::label('week_off_applicable',__('Applicable To'),['class'=>'form-label'])}}<x-required></x-required>
                <select name="week_off_applicable" class="form-control" id="week_off_applicable" onchange="toggleDepartment(this.value, 'weekOffDepartments')">
                    <option value="all">{{ __('All Employees') }}</option>
                    <option value="specific">{{ __('Specific Departments') }}</option>
                </select>
            </div>
        </div>
        <div class="row mt-2" id="weekOffDepartments" style="display: none;">
            <div class="form-group col-md-12">
                {{Form::label('week_off_departments',__('Select Departments'),['class'=>'form-label'])}}<x-required></x-required>
                <select name="week_off_departments[]" class="form-control select2" multiple>
                    @if(isset($departments) && $departments->count() > 0)
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>

    {{-- ============================================================
    LEAVE SECTION - FIXED is_paid naming
    ============================================================ --}}
    <div id="leaveSection" style="display: none; border: 2px solid #e9ecef; border-radius: 8px; padding: 15px; margin-top: 10px; background: #f8f9fa;">
        <div class="row">
            <div class="col-12">
                <h6 class="text-primary"><i class="ti ti-file-text me-2"></i>{{ __('Leave Details') }}</h6>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                {{Form::label('leave_type_id',__('Leave Type'),['class'=>'form-label'])}}<x-required></x-required>
                <select name="leave_type_id" class="form-control" id="leave_type_select">
                    <option value="">{{ __('Select Leave Type') }}</option>
                    @if(isset($leaveTypes) && $leaveTypes->count() > 0)
                        @foreach($leaveTypes as $leaveType)
                            <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                        @endforeach
                    @endif
                    <option value="custom">{{ __('Custom Leave') }}</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                {{Form::label('leave_duration',__('Duration'),['class'=>'form-label'])}}<x-required></x-required>
                <select name="leave_duration" class="form-control">
                    <option value="full_day">{{ __('Full Day') }}</option>
                    <option value="half_day">{{ __('Half Day') }}</option>
                    <option value="first_half">{{ __('First Half') }}</option>
                    <option value="second_half">{{ __('Second Half') }}</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                {{Form::label('leave_date_from',__('From Date'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::date('leave_date_from',null,array('class'=>'form-control', 'min' => date('Y-m-d')))}}
            </div>
            <div class="form-group col-md-6">
                {{Form::label('leave_date_to',__('To Date'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::date('leave_date_to',null,array('class'=>'form-control', 'min' => date('Y-m-d')))}}
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12">
                {{Form::label('leave_reason',__('Reason for Leave'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::textarea('leave_reason',null,array('class'=>'form-control','rows'=>2, 'placeholder'=>__('Please provide reason for leave...')))}}
            </div>
        </div>
        <div class="row mt-2">
            <div class="form-group col-md-6">
                {{Form::label('leave_is_paid',__('Is Paid?'),['class'=>'form-label'])}}<x-required></x-required>
                <div class="form-check form-switch mt-2">
                    <input type="hidden" name="leave_is_paid" value="0">
                    <input type="checkbox" class="form-check-input" name="leave_is_paid" id="leave_is_paid_checkbox" value="1" checked>
                    <label class="form-check-label" for="leave_is_paid_checkbox">{{ __('Yes (Paid)') }}</label>
                </div>
            </div>
            <div class="form-group col-md-6">
                {{Form::label('applicable_to',__('Applicable To'),['class'=>'form-label'])}}<x-required></x-required>
                <select name="applicable_to" class="form-control" id="applicable_to_leave" onchange="toggleDepartment(this.value, 'leaveDepartments')">
                    <option value="all">{{ __('All Employees') }}</option>
                    <option value="specific">{{ __('Specific Departments') }}</option>
                </select>
            </div>
        </div>
        <div class="row mt-2" id="leaveDepartments" style="display: none;">
            <div class="form-group col-md-12">
                {{Form::label('departments',__('Select Departments'),['class'=>'form-label'])}}<x-required></x-required>
                <select name="departments[]" class="form-control select2" multiple>
                    @if(isset($departments) && $departments->count() > 0)
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>

    {{-- ============================================================
    DESCRIPTION
    ============================================================ --}}
    <div class="row mt-3">
        <div class="form-group col-md-12">
            {{Form::label('description',__('Description (Optional)'),['class'=>'form-label'])}}
            {{Form::textarea('description',null,array('class'=>'form-control','rows'=>2, 'placeholder'=>__('Additional notes or details...')))}}
        </div>
    </div>
    
    @if (isset($settings['google_calendar_enable']) && $settings['google_calendar_enable'] == 'on')
        <div class="row mt-2">
            <div class="form-group col-md-12">
                {{Form::label('synchronize_type',__('Synchronize in Google Calendar ?'),array('class'=>'form-label')) }}
                <div class="form-switch">
                    <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow" value="google_calender">
                    <label class="form-check-label" for="switch-shadow"></label>
                </div>
            </div>
        </div>
    @endif
    
    <div class="row mt-2">
        <div class="col-12">
            <div class="alert alert-info" role="alert">
                <i class="ti ti-info-circle me-2"></i>
                <small>{{ __('This will be reflected in the Attendance Dashboard with the appropriate status.') }}</small>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>

{{Form::close()}}

<script>
// ============================================================
// GLOBAL FUNCTIONS - Called directly from HTML onchange
// ============================================================

// Toggle sections based on type
function toggleHolidaySections(type) {
    // Get all section elements
    var holidaySection = document.getElementById('holidaySection');
    var weekOffSection = document.getElementById('weekOffSection');
    var leaveSection = document.getElementById('leaveSection');
    
    // Hide all sections
    if (holidaySection) holidaySection.style.display = 'none';
    if (weekOffSection) weekOffSection.style.display = 'none';
    if (leaveSection) leaveSection.style.display = 'none';
    
    // Show the relevant section
    if (type === 'holiday') {
        if (holidaySection) holidaySection.style.display = 'block';
    } else if (type === 'week_off') {
        if (weekOffSection) weekOffSection.style.display = 'block';
    } else if (type === 'paid_leave' || type === 'unpaid_leave' || type === 'sick_leave' || 
               type === 'casual_leave' || type === 'maternity_leave' || type === 'paternity_leave' || 
               type === 'compensatory_off' || type === 'other') {
        if (leaveSection) leaveSection.style.display = 'block';
    }
}

// Toggle department selection
function toggleDepartment(value, targetId) {
    var target = document.getElementById(targetId);
    if (target) {
        target.style.display = (value === 'specific') ? 'block' : 'none';
    }
}

// ============================================================
// AUTO-TRIGGER ON PAGE LOAD
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    // Check if type is already selected
    var holidayType = document.getElementById('holidayType');
    if (holidayType && holidayType.value) {
        toggleHolidaySections(holidayType.value);
    }
    
    // ============================================================
    // ENSURE IS_PAID CHECKBOX VALUE IS CORRECTLY SENT
    // Each section now has its own unique is_paid field
    // ============================================================
    
    // Holiday is_paid checkbox
    var holidayIsPaid = document.getElementById('holiday_is_paid_checkbox');
    if (holidayIsPaid) {
        holidayIsPaid.addEventListener('change', function() {
            // When checkbox is checked, it sends '1'
            // When unchecked, the hidden input sends '0'
            console.log('Holiday Is Paid:', this.checked);
        });
    }
    
    // Week Off is_paid checkbox
    var weekoffIsPaid = document.getElementById('weekoff_is_paid_checkbox');
    if (weekoffIsPaid) {
        weekoffIsPaid.addEventListener('change', function() {
            console.log('Week Off Is Paid:', this.checked);
        });
    }
    
    // Leave is_paid checkbox
    var leaveIsPaid = document.getElementById('leave_is_paid_checkbox');
    if (leaveIsPaid) {
        leaveIsPaid.addEventListener('change', function() {
            console.log('Leave Is Paid:', this.checked);
        });
    }
    
    // ============================================================
    // VALIDATE END DATE >= START DATE (Holiday)
    // ============================================================
    var startDate = document.getElementById('date');
    var endDate = document.getElementById('end_date');
    
    if (startDate && endDate) {
        startDate.addEventListener('change', function() {
            var startVal = this.value;
            if (startVal) {
                endDate.setAttribute('min', startVal);
                if (endDate.value && endDate.value < startVal) {
                    endDate.value = startVal;
                }
            }
        });
    }
    
    // ============================================================
    // VALIDATE END DATE >= START DATE (Leave)
    // ============================================================
    var leaveFromDate = document.getElementById('leave_date_from');
    var leaveToDate = document.getElementById('leave_date_to');
    
    if (leaveFromDate && leaveToDate) {
        leaveFromDate.addEventListener('change', function() {
            var startVal = this.value;
            if (startVal) {
                leaveToDate.setAttribute('min', startVal);
                if (leaveToDate.value && leaveToDate.value < startVal) {
                    leaveToDate.value = startVal;
                }
            }
        });
    }
    
    // ============================================================
    // APPLICABLE TO - TOGGLE DEPARTMENT SELECTION
    // ============================================================
    var applicableToHoliday = document.getElementById('applicable_to_holiday');
    if (applicableToHoliday) {
        applicableToHoliday.addEventListener('change', function() {
            var target = document.getElementById('holidayDepartments');
            if (target) {
                target.style.display = this.value === 'specific' ? 'block' : 'none';
            }
        });
    }
    
    var applicableToLeave = document.getElementById('applicable_to_leave');
    if (applicableToLeave) {
        applicableToLeave.addEventListener('change', function() {
            var target = document.getElementById('leaveDepartments');
            if (target) {
                target.style.display = this.value === 'specific' ? 'block' : 'none';
            }
        });
    }
    
    var weekOffApplicable = document.getElementById('week_off_applicable');
    if (weekOffApplicable) {
        weekOffApplicable.addEventListener('change', function() {
            var target = document.getElementById('weekOffDepartments');
            if (target) {
                target.style.display = this.value === 'specific' ? 'block' : 'none';
            }
        });
    }
    
    // ============================================================
    // SELECT2 INITIALIZATION (if available)
    // ============================================================
    if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            placeholder: "{{ __('Select departments...') }}",
            allowClear: true,
            width: '100%'
        });
    }
});
</script>