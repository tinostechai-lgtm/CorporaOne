@extends('layouts.admin')

@section('page-title', 'Manage Holidays')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hrm.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Holidays</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create holiday')
            <a href="#" data-size="lg" data-url="{{ route('holiday.create') }}" data-ajax-popup="true"
                data-bs-toggle="tooltip" data-title="{{ __('Create New Holiday') }}"
                class="btn btn-sm btn-primary me-1">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@push('css')
<style>
    .badge-holiday { background: #51459d; color: white; }
    .badge-week-off { background: #28a745; color: white; }
    .badge-paid-leave { background: #17a2b8; color: white; }
    .badge-unpaid-leave { background: #fd7e14; color: white; }
    .badge-sick-leave { background: #dc3545; color: white; }
    .badge-casual-leave { background: #6f42c1; color: white; }
    .badge-maternity-leave { background: #d63384; color: white; }
    .badge-paternity-leave { background: #0d6efd; color: white; }
    .badge-compensatory-off { background: #20c997; color: white; }
    .badge-other { background: #6c757d; color: white; }
    
    .type-badge {
        font-size: 10px;
        padding: 3px 10px;
        border-radius: 20px;
        font-weight: 600;
        text-transform: capitalize;
        display: inline-block;
    }
    
    .week-off-days-badge {
        font-size: 9px;
        padding: 2px 8px;
        border-radius: 12px;
        background: #e9ecef;
        color: #495057;
        margin: 1px;
        display: inline-block;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .action-btn .btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        padding: 0;
    }
    .action-btn .btn i {
        font-size: 14px;
    }

    .status-badge {
        font-size: 11px;
        padding: 4px 12px;
        border-radius: 20px;
    }

    .holiday-occasion {
        font-weight: 600;
    }

    .recurring-badge {
        font-size: 9px;
        padding: 2px 8px;
        border-radius: 10px;
        background: #e3f2fd;
        color: #0d6efd;
    }

    .paid-badge {
        font-size: 10px;
        padding: 2px 10px;
        border-radius: 10px;
    }

    .leave-duration-badge {
        font-size: 9px;
        padding: 2px 8px;
        border-radius: 10px;
        background: #f8f9fa;
        color: #495057;
    }

    .date-range-display {
        font-size: 12px;
    }

    .days-count {
        font-size: 10px;
        color: #6c757d;
    }

    .week-off-container {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
    }

    .week-off-day {
        font-size: 9px;
        padding: 2px 8px;
        border-radius: 12px;
        background: #e9ecef;
        color: #495057;
    }

    .week-off-day.active {
        background: #28a745;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table datatable" id="holidays-table">
                        <thead>
                            <tr>
                                <th>{{ __('#') }}</th>
                                <th>{{ __('Title / Occasion') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Date / Details') }}</th>
                                <th>{{ __('Week Off Days') }}</th>
                                <th>{{ __('Paid') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($holidays as $index => $holiday)
                                @php
                                    // ============================================================
                                    // STATUS CALCULATION
                                    // ============================================================
                                    $isWeekOff = $holiday->type == 'week_off';
                                    
                                    if ($isWeekOff) {
                                        // Week off is always active
                                        $statusColor = 'success';
                                        $statusText = 'Active';
                                        $statusIcon = 'ti ti-calendar-check';
                                    } else {
                                        // Regular holiday/leave status
                                        $startDate = \Carbon\Carbon::parse($holiday->date);
                                        $endDate = \Carbon\Carbon::parse($holiday->end_date);
                                        $today = \Carbon\Carbon::today();
                                        
                                        if ($endDate->isPast()) {
                                            $statusColor = 'secondary';
                                            $statusText = 'Past';
                                            $statusIcon = 'ti ti-calendar-off';
                                        } elseif ($startDate->isToday() || $endDate->isToday() || ($startDate <= $today && $endDate >= $today)) {
                                            $statusColor = 'warning';
                                            $statusText = 'Ongoing';
                                            $statusIcon = 'ti ti-calendar-stats';
                                        } else {
                                            $statusColor = 'primary';
                                            $statusText = 'Upcoming';
                                            $statusIcon = 'ti ti-calendar-up';
                                        }
                                    }
                                    
                                    // ============================================================
                                    // TYPE BADGE CLASS
                                    // ============================================================
                                    $typeClass = match($holiday->type) {
                                        'holiday' => 'badge-holiday',
                                        'week_off' => 'badge-week-off',
                                        'paid_leave' => 'badge-paid-leave',
                                        'unpaid_leave' => 'badge-unpaid-leave',
                                        'sick_leave' => 'badge-sick-leave',
                                        'casual_leave' => 'badge-casual-leave',
                                        'maternity_leave' => 'badge-maternity-leave',
                                        'paternity_leave' => 'badge-paternity-leave',
                                        'compensatory_off' => 'badge-compensatory-off',
                                        default => 'badge-other',
                                    };
                                    
                                    $typeIcon = match($holiday->type) {
                                        'holiday' => 'calendar-event',
                                        'week_off' => 'calendar-week',
                                        'paid_leave' => 'money',
                                        'unpaid_leave' => 'money-off',
                                        'sick_leave' => 'heart',
                                        'casual_leave' => 'users',
                                        'maternity_leave' => 'baby-carriage',
                                        'paternity_leave' => 'baby',
                                        'compensatory_off' => 'clock',
                                        default => 'file'
                                    };
                                    
                                    $typeLabel = $holiday->type_label ?? ucfirst(str_replace('_', ' ', $holiday->type));
                                    
                                    // ============================================================
                                    // WEEK OFF DAYS DISPLAY
                                    // ============================================================
                                    $weekOffDaysDisplay = '<span class="text-muted">-</span>';
                                    $hasWeekOffDays = false;
                                    
                                    if ($isWeekOff && !empty($holiday->week_off_days)) {
                                        $days = $holiday->week_off_days;
                                        if (is_string($days)) {
                                            $days = json_decode($days, true);
                                        }
                                        
                                        $dayNames = [
                                            1 => 'Mon',
                                            2 => 'Tue',
                                            3 => 'Wed',
                                            4 => 'Thu',
                                            5 => 'Fri',
                                            6 => 'Sat',
                                            7 => 'Sun'
                                        ];
                                        
                                        $fullDayNames = [
                                            1 => 'Monday',
                                            2 => 'Tuesday',
                                            3 => 'Wednesday',
                                            4 => 'Thursday',
                                            5 => 'Friday',
                                            6 => 'Saturday',
                                            7 => 'Sunday'
                                        ];
                                        
                                        $weekOffDaysArray = [];
                                        if (is_array($days) && !empty($days)) {
                                            $hasWeekOffDays = true;
                                            foreach ($days as $day) {
                                                if (isset($dayNames[$day])) {
                                                    $weekOffDaysArray[] = '<span class="week-off-days-badge" title="' . $fullDayNames[$day] . '">' . $dayNames[$day] . '</span>';
                                                }
                                            }
                                            $weekOffDaysDisplay = implode(' ', $weekOffDaysArray);
                                        }
                                    }
                                    
                                    // ============================================================
                                    // DATE RANGE DISPLAY
                                    // ============================================================
                                    if ($isWeekOff) {
                                        $dateRangeDisplay = 'Recurring Weekly';
                                        $daysDiff = 0;
                                    } else {
                                        $startDate = \Carbon\Carbon::parse($holiday->date);
                                        $endDate = \Carbon\Carbon::parse($holiday->end_date);
                                        $dateRangeDisplay = $startDate->format('d M Y');
                                        if ($endDate->format('Y-m-d') != $startDate->format('Y-m-d')) {
                                            $dateRangeDisplay .= ' - ' . $endDate->format('d M Y');
                                        }
                                        $daysDiff = $startDate->diffInDays($endDate) + 1;
                                    }
                                    
                                    // ============================================================
                                    // DURATION DISPLAY
                                    // ============================================================
                                    $durationDisplay = '-';
                                    if (in_array($holiday->type, ['paid_leave', 'unpaid_leave', 'sick_leave', 'casual_leave', 'maternity_leave', 'paternity_leave', 'compensatory_off', 'other'])) {
                                        $durationMap = [
                                            'full_day' => 'Full Day',
                                            'half_day' => 'Half Day',
                                            'first_half' => 'First Half',
                                            'second_half' => 'Second Half'
                                        ];
                                        $durationDisplay = $durationMap[$holiday->leave_duration] ?? $holiday->leave_duration;
                                    }
                                    
                                    // ============================================================
                                    // IS PAID DISPLAY
                                    // ============================================================
                                    $isPaidDisplay = $holiday->is_paid ? 
                                        '<span class="badge bg-success paid-badge"><i class="ti ti-check me-1"></i>Paid</span>' : 
                                        '<span class="badge bg-secondary paid-badge"><i class="ti ti-x me-1"></i>Unpaid</span>';
                                    
                                    // ============================================================
                                    // OCCASION DISPLAY
                                    // ============================================================
                                    $occasionDisplay = '<span class="holiday-occasion">' . e($holiday->occasion) . '</span>';
                                    if ($isWeekOff) {
                                        $occasionDisplay .= ' <span class="recurring-badge"><i class="ti ti-refresh me-1"></i>Recurring</span>';
                                    }
                                    if ($holiday->type != 'week_off' && $statusText == 'Ongoing') {
                                        $occasionDisplay .= ' <span class="badge bg-warning status-badge">Today</span>';
                                    }
                                    if (!empty($holiday->leave_reason)) {
                                        $occasionDisplay .= '<br><small class="text-muted"><i class="ti ti-info-circle me-1"></i>' . e(Str::limit($holiday->leave_reason, 50)) . '</small>';
                                    }
                                    if (!empty($holiday->description)) {
                                        $occasionDisplay .= '<br><small class="text-muted"><i class="ti ti-notes me-1"></i>' . e(Str::limit($holiday->description, 40)) . '</small>';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{!! $occasionDisplay !!}</td>
                                    <td>
                                        <span class="type-badge {{ $typeClass }}">
                                            <i class="ti ti-{{ $typeIcon }} me-1"></i>
                                            {{ $typeLabel }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="date-range-display fw-semibold">{{ $dateRangeDisplay }}</span>
                                        @if(!$isWeekOff)
                                            <br>
                                            <span class="days-count">
                                                <i class="ti ti-clock me-1"></i>
                                                {{ $daysDiff }} day(s)
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isWeekOff && $hasWeekOffDays)
                                            <div class="week-off-container">
                                                {!! $weekOffDaysDisplay !!}
                                            </div>
                                        @elseif($isWeekOff)
                                            <span class="text-muted">No days selected</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{!! $isPaidDisplay !!}</td>
                                    <td>
                                        @if(in_array($holiday->type, ['paid_leave', 'unpaid_leave', 'sick_leave', 'casual_leave', 'maternity_leave', 'paternity_leave', 'compensatory_off', 'other']))
                                            <span class="leave-duration-badge">{{ $durationDisplay }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusColor }} status-badge">
                                            <i class="{{ $statusIcon }} me-1"></i>
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @can('edit holiday')
                                                <a href="#" data-size="lg" data-url="{{ route('holiday.edit', $holiday->id) }}"
                                                    data-ajax-popup="true" data-bs-toggle="tooltip"
                                                    data-title="{{ __('Edit Holiday') }}" class="btn btn-sm btn-info">
                                                    <i class="ti ti-pencil"></i>
                                                </a>
                                            @endcan
                                            @can('delete holiday')
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['holiday.destroy', $holiday->id], 'class' => 'd-inline']) !!}
                                                <button type="submit" class="btn btn-sm btn-danger show_confirm" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                                {!! Form::close() !!}
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    // Confirm delete
    $(document).on('click', '.show_confirm', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this holiday?')) {
            $(this).closest('form').submit();
        }
    });

    // DataTable initialization with custom order
    $(document).ready(function() {
        $('#holidays-table').DataTable({
            order: [[7, 'asc']], // Order by status column
            columnDefs: [
                {
                    targets: 7, // Status column
                    orderData: [7]
                }
            ],
            language: {
                paginate: {
                    previous: "<i class='ti ti-chevron-left'>",
                    next: "<i class='ti ti-chevron-right'>"
                }
            }
        });
    });
</script>
@endpush