{{-- resources/views/attendance/_details_sidebar.blade.php --}}
@php
    $employee = $data['employee'];
    $attendance = $data['attendance'];
    $photos = $data['photos'];
    $workedHours = $data['worked_hours'];
    $date = $data['date'];
    
    // Helper function to check if photo exists
    function hasPhoto($photo) {
        return $photo && !empty($photo) && $photo != 'null';
    }
    
    // Determine status
    $statusClass = 'off';
    $statusText = 'Offline';
    if ($attendance && $attendance->clock_in != '00:00:00' && $attendance->clock_out == '00:00:00') {
        $statusClass = 'live';
        $statusText = 'Live';
    } elseif ($attendance && $attendance->clock_out != '00:00:00') {
        $statusClass = 'out';
        $statusText = 'Clocked Out';
    }
@endphp

<div class="sidebar-scroll">
    {{-- Employee Info --}}
    <div class="text-center mb-3">
        <img src="{{ asset('uploads/avatar/' . ($employee->user->avatar ?? 'avatar.png')) }}" 
             class="sidebar-avatar" 
             alt="{{ $employee->name }}">
        <div class="sidebar-employee-name mt-2">{{ $employee->name }}</div>
        <div class="sidebar-employee-designation">{{ $employee->designation->name ?? 'No Designation' }}</div>
        <div class="sidebar-employee-email">
            <i class="ti ti-mail me-1"></i> {{ $employee->email }}
        </div>
        <div class="sidebar-employee-email">
            <i class="ti ti-id me-1"></i> ID: {{ $employee->employee_id }}
        </div>
        <div class="sidebar-employee-email">
            <i class="ti ti-phone me-1"></i> {{ $employee->phone ?? 'N/A' }}
        </div>
        <div class="mt-2">
            <span class="sidebar-employee-status {{ $statusClass }}">
                <i class="ti ti-{{ $statusClass == 'live' ? 'circle-filled' : ($statusClass == 'out' ? 'clock-off' : 'user-off') }} me-1"></i>
                {{ $statusText }}
            </span>
        </div>
    </div>

    <hr>

    {{-- Timeline --}}
    <div class="sidebar-section-title">
        <i class="ti ti-clock me-2"></i> Attendance Timeline
    </div>
    <div class="sidebar-timeline">
        
        {{-- 1. Punch In --}}
        @php
            $isCompleted = $attendance && $attendance->clock_in != '00:00:00';
            $isCurrent = $isCompleted && $attendance->clock_out == '00:00:00';
            $stepClass = $isCompleted ? ($isCurrent ? 'current' : 'completed') : 'missing';
            $punchInTime = $attendance && $attendance->clock_in != '00:00:00' ? \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') : 'Not recorded';
        @endphp
        <div class="sidebar-timeline-item {{ $stepClass }}">
            <span class="time-label {{ $stepClass }}">
                <i class="ti ti-{{ $isCompleted ? 'check-circle' : 'alert-circle' }} me-1"></i>
                Step 1
            </span>
            <div class="event-title">Punch In</div>
            <div class="event-time">
                <i class="ti ti-clock me-1"></i> {{ $punchInTime }}
                @if($attendance && $attendance->clock_in != '00:00:00' && isset($attendance->late) && $attendance->late != '00:00:00')
                    <span class="text-warning">(Late: {{ $attendance->late }})</span>
                @endif
            </div>
            @if(hasPhoto($photos['punch_in']))
                <div class="event-photo" onclick="zoomImage('{{ $photos['punch_in'] }}', 'Punch In - {{ $punchInTime }}')">
                    <img src="{{ $photos['punch_in'] }}" alt="Punch In Face">
                    <span class="photo-badge"><i class="ti ti-camera"></i> Face ID</span>
                </div>
            @else
                <div class="no-photo">
                    <i class="ti ti-camera-off"></i>
                    No face captured
                </div>
            @endif
        </div>

        {{-- 2. Break Start --}}
        @php
            $isCompleted = $attendance && $attendance->break_start != '00:00:00';
            $isCurrent = $isCompleted && ($attendance->break_end == '00:00:00' || $attendance->break_end == null);
            $stepClass = $isCompleted ? ($isCurrent ? 'current' : 'completed') : 'missing';
            $breakStartTime = $attendance && $attendance->break_start != '00:00:00' ? \Carbon\Carbon::parse($attendance->break_start)->format('h:i A') : 'Not recorded';
        @endphp
        <div class="sidebar-timeline-item {{ $stepClass }}">
            <span class="time-label {{ $stepClass }}">
                <i class="ti ti-{{ $isCompleted ? 'check-circle' : 'alert-circle' }} me-1"></i>
                Step 2
            </span>
            <div class="event-title">Break Start</div>
            <div class="event-time">
                <i class="ti ti-clock me-1"></i> {{ $breakStartTime }}
            </div>
            @if(hasPhoto($photos['break_in']))
                <div class="event-photo" onclick="zoomImage('{{ $photos['break_in'] }}', 'Break Start - {{ $breakStartTime }}')">
                    <img src="{{ $photos['break_in'] }}" alt="Break Start Face">
                    <span class="photo-badge"><i class="ti ti-camera"></i> Face ID</span>
                </div>
            @else
                <div class="no-photo">
                    <i class="ti ti-camera-off"></i>
                    No face captured
                </div>
            @endif
        </div>

        {{-- 3. Break End --}}
        @php
            $isCompleted = $attendance && $attendance->break_end != '00:00:00';
            $stepClass = $isCompleted ? 'completed' : 'missing';
            $breakEndTime = $attendance && $attendance->break_end != '00:00:00' ? \Carbon\Carbon::parse($attendance->break_end)->format('h:i A') : 'Not recorded';
        @endphp
        <div class="sidebar-timeline-item {{ $stepClass }}">
            <span class="time-label {{ $stepClass }}">
                <i class="ti ti-{{ $isCompleted ? 'check-circle' : 'alert-circle' }} me-1"></i>
                Step 3
            </span>
            <div class="event-title">Break End</div>
            <div class="event-time">
                <i class="ti ti-clock me-1"></i> {{ $breakEndTime }}
            </div>
            @if(hasPhoto($photos['break_out']))
                <div class="event-photo" onclick="zoomImage('{{ $photos['break_out'] }}', 'Break End - {{ $breakEndTime }}')">
                    <img src="{{ $photos['break_out'] }}" alt="Break End Face">
                    <span class="photo-badge"><i class="ti ti-camera"></i> Face ID</span>
                </div>
            @else
                <div class="no-photo">
                    <i class="ti ti-camera-off"></i>
                    No face captured
                </div>
            @endif
        </div>

        {{-- 4. Punch Out --}}
        @php
            $isCompleted = $attendance && $attendance->clock_out != '00:00:00';
            $stepClass = $isCompleted ? 'completed' : 'missing';
            $punchOutTime = $attendance && $attendance->clock_out != '00:00:00' ? \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') : 'Not recorded';
        @endphp
        <div class="sidebar-timeline-item {{ $stepClass }}">
            <span class="time-label {{ $stepClass }}">
                <i class="ti ti-{{ $isCompleted ? 'check-circle' : 'alert-circle' }} me-1"></i>
                Step 4
            </span>
            <div class="event-title">Punch Out</div>
            <div class="event-time">
                <i class="ti ti-clock me-1"></i> {{ $punchOutTime }}
                @if($attendance && $attendance->clock_out != '00:00:00' && isset($attendance->overtime) && $attendance->overtime != '00:00:00')
                    <span class="text-success">(Overtime: {{ $attendance->overtime }})</span>
                @endif
            </div>
            @if(hasPhoto($photos['punch_out']))
                <div class="event-photo" onclick="zoomImage('{{ $photos['punch_out'] }}', 'Punch Out - {{ $punchOutTime }}')">
                    <img src="{{ $photos['punch_out'] }}" alt="Punch Out Face">
                    <span class="photo-badge"><i class="ti ti-camera"></i> Face ID</span>
                </div>
            @else
                <div class="no-photo">
                    <i class="ti ti-camera-off"></i>
                    No face captured
                </div>
            @endif
        </div>
    </div>

    <hr>

    {{-- Stats Footer --}}
    <div class="sidebar-footer-stats">
        <div class="row">
            <div class="col-4 stat-item">
                <div class="stat-value">{{ $workedHours }}</div>
                <div class="stat-label">Worked Hours</div>
            </div>
            <div class="col-4 stat-item danger">
                <div class="stat-value">{{ $attendance && $attendance->late != '00:00:00' ? $attendance->late : '00:00' }}</div>
                <div class="stat-label">Late</div>
            </div>
            <div class="col-4 stat-item success">
                <div class="stat-value">{{ $attendance && $attendance->overtime != '00:00:00' ? $attendance->overtime : '00:00' }}</div>
                <div class="stat-label">Overtime</div>
            </div>
        </div>
    </div>

    <div class="text-center mt-3">
        <small class="text-muted">
            <i class="ti ti-calendar me-1"></i> {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
        </small>
    </div>
</div>