{{-- resources/views/attendance/_details_modal.blade.php --}}
@php
    $employee = $data['employee'];
    $attendance = $data['attendance'];
    $photos = $data['photos'];
    $workedHours = $data['worked_hours'];
    $date = $data['date'];
@endphp

<div class="modal-header">
    <h5 class="modal-title">
        <i class="ti ti-user me-2"></i>
        {{ $employee->name }} - Attendance Details
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row">
        {{-- Employee Info --}}
        <div class="col-12 mb-3">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <img src="{{ asset('uploads/avatar/' . ($employee->user->avatar ?? 'avatar.png')) }}" 
                         class="rounded-circle" 
                         style="width: 64px; height: 64px; object-fit: cover;">
                </div>
                <div>
                    <h6 class="mb-0">{{ $employee->name }}</h6>
                    <small class="text-muted">{{ $employee->designation->name ?? 'No Designation' }}</small><br>
                    <small class="text-muted">
                        <i class="ti ti-mail me-1"></i> {{ $employee->email }}
                    </small>
                    <br>
                    <small class="text-muted">
                        <i class="ti ti-calendar me-1"></i> {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                    </small>
                </div>
            </div>
        </div>

        {{-- Attendance Summary Times --}}
        <div class="col-12 mb-3">
            <div class="card bg-light">
                <div class="card-body p-3">
                    <div class="row text-center">
                        <div class="col-3">
                            <h6 class="mb-0">{{ $attendance && $attendance->clock_in != '00:00:00' ? \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') : '--:--' }}</h6>
                            <small class="text-muted">Clock In</small>
                        </div>
                        <div class="col-3">
                            <h6 class="mb-0">{{ $attendance && $attendance->break_start != '00:00:00' ? \Carbon\Carbon::parse($attendance->break_start)->format('h:i A') : '--:--' }}</h6>
                            <small class="text-muted">Break Start</small>
                        </div>
                        <div class="col-3">
                            <h6 class="mb-0">{{ $attendance && $attendance->break_end != '00:00:00' ? \Carbon\Carbon::parse($attendance->break_end)->format('h:i A') : '--:--' }}</h6>
                            <small class="text-muted">Break End</small>
                        </div>
                        <div class="col-3">
                            <h6 class="mb-0">{{ $attendance && $attendance->clock_out != '00:00:00' ? \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') : '--:--' }}</h6>
                            <small class="text-muted">Clock Out</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Photos Section --}}
        <div class="col-12">
            <h6 class="mb-3"><i class="ti ti-camera me-2"></i> Attendance Photos</h6>
            <div class="row g-3">
                {{-- Punch In --}}
                <div class="col-md-3 col-6">
                    <div class="card h-100 photo-card" onclick="zoomImage('{{ $photos['punch_in'] ?? '' }}', 'Punch In Photo')">
                        <div class="card-body text-center p-2">
                            @if($photos['punch_in'])
                                <div class="photo-container">
                                    <img src="{{ $photos['punch_in'] }}" alt="Punch In" class="img-fluid">
                                    <span class="photo-badge">
                                        <i class="ti ti-check me-1"></i> Captured
                                    </span>
                                </div>
                            @else
                                <div class="photo-placeholder">
                                    <div class="text-center">
                                        <i class="ti ti-camera-off text-muted" style="font-size: 24px;"></i>
                                        <br>
                                        <small class="text-muted">No photo</small>
                                    </div>
                                </div>
                            @endif
                            <small class="d-block text-truncate mt-1 fw-bold">Punch In</small>
                            <small class="text-muted" style="font-size: 10px;">
                                {{ $attendance && $attendance->clock_in != '00:00:00' ? \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') : '--:--' }}
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Break Start --}}
                <div class="col-md-3 col-6">
                    <div class="card h-100 photo-card" onclick="zoomImage('{{ $photos['break_in'] ?? '' }}', 'Break Start Photo')">
                        <div class="card-body text-center p-2">
                            @if($photos['break_in'])
                                <div class="photo-container">
                                    <img src="{{ $photos['break_in'] }}" alt="Break Start" class="img-fluid">
                                    <span class="photo-badge">
                                        <i class="ti ti-check me-1"></i> Captured
                                    </span>
                                </div>
                            @else
                                <div class="photo-placeholder">
                                    <div class="text-center">
                                        <i class="ti ti-camera-off text-muted" style="font-size: 24px;"></i>
                                        <br>
                                        <small class="text-muted">No photo</small>
                                    </div>
                                </div>
                            @endif
                            <small class="d-block text-truncate mt-1 fw-bold">Break Start</small>
                            <small class="text-muted" style="font-size: 10px;">
                                {{ $attendance && $attendance->break_start != '00:00:00' ? \Carbon\Carbon::parse($attendance->break_start)->format('h:i A') : '--:--' }}
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Break End --}}
                <div class="col-md-3 col-6">
                    <div class="card h-100 photo-card" onclick="zoomImage('{{ $photos['break_out'] ?? '' }}', 'Break End Photo')">
                        <div class="card-body text-center p-2">
                            @if($photos['break_out'])
                                <div class="photo-container">
                                    <img src="{{ $photos['break_out'] }}" alt="Break End" class="img-fluid">
                                    <span class="photo-badge">
                                        <i class="ti ti-check me-1"></i> Captured
                                    </span>
                                </div>
                            @else
                                <div class="photo-placeholder">
                                    <div class="text-center">
                                        <i class="ti ti-camera-off text-muted" style="font-size: 24px;"></i>
                                        <br>
                                        <small class="text-muted">No photo</small>
                                    </div>
                                </div>
                            @endif
                            <small class="d-block text-truncate mt-1 fw-bold">Break End</small>
                            <small class="text-muted" style="font-size: 10px;">
                                {{ $attendance && $attendance->break_end != '00:00:00' ? \Carbon\Carbon::parse($attendance->break_end)->format('h:i A') : '--:--' }}
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Punch Out --}}
                <div class="col-md-3 col-6">
                    <div class="card h-100 photo-card" onclick="zoomImage('{{ $photos['punch_out'] ?? '' }}', 'Punch Out Photo')">
                        <div class="card-body text-center p-2">
                            @if($photos['punch_out'])
                                <div class="photo-container">
                                    <img src="{{ $photos['punch_out'] }}" alt="Punch Out" class="img-fluid">
                                    <span class="photo-badge">
                                        <i class="ti ti-check me-1"></i> Captured
                                    </span>
                                </div>
                            @else
                                <div class="photo-placeholder">
                                    <div class="text-center">
                                        <i class="ti ti-camera-off text-muted" style="font-size: 24px;"></i>
                                        <br>
                                        <small class="text-muted">No photo</small>
                                    </div>
                                </div>
                            @endif
                            <small class="d-block text-truncate mt-1 fw-bold">Punch Out</small>
                            <small class="text-muted" style="font-size: 10px;">
                                {{ $attendance && $attendance->clock_out != '00:00:00' ? \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') : '--:--' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Info --}}
        <div class="col-12 mt-3">
            <div class="card bg-light">
                <div class="card-body p-3">
                    <div class="row text-center">
                        <div class="col-4">
                            <h6 class="mb-0">{{ $workedHours }}</h6>
                            <small class="text-muted">Worked Hours</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">{{ $attendance ? $attendance->late : '00:00:00' }}</h6>
                            <small class="text-muted">Late</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">{{ $attendance ? $attendance->overtime : '00:00:00' }}</h6>
                            <small class="text-muted">Overtime</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>