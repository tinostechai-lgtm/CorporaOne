@extends('layouts.admin')

@section('page-title', 'Attendance Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hrm.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Attendance Dashboard</li>
@endsection

@push('css')
<style>
    .bg-purple {
        background-color: #6f42c1;
        color: white;
    }
    .bg-half-day {
        background: #f59e0b;
        color: #000 !important;
    }
    .bg-face-id {
        background: #6c5ce7;
        color: white;
    }
    .bg-face-id-success {
        background: #00b894;
        color: white;
    }
    .bg-face-id-failed {
        background: #e17055;
        color: white;
    }
    .bg-web {
        background: #0984e3;
        color: white;
    }
    .bg-flutter {
        background: #00b894;
        color: white;
    }
    .bg-manual {
        background: #636e72;
        color: white;
    }
    .table th {
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table td {
        vertical-align: middle;
        font-size: 0.82rem;
    }
    .badge {
        font-weight: 500;
    }
    .badge.bg-light.text-muted {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
    }
    .avatar-initials {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
        color: #667eea;
        background: #f0f2ff;
        border: 2px solid #667eea;
        text-transform: uppercase;
        flex-shrink: 0;
    }
    .employee-avatar-wrapper {
        position: relative;
        display: inline-block;
    }
    .employee-avatar-wrapper .status-dot {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
    }
    .employee-avatar-wrapper .status-dot.present { background: #28a745; }
    .employee-avatar-wrapper .status-dot.absent { background: #dc3545; }
    .employee-avatar-wrapper .status-dot.half-day { background: #f59e0b; }
    .employee-avatar-wrapper .status-dot.week-off { background: #0dcaf0; }
    .employee-avatar-wrapper .status-dot.holiday { background: #0d6efd; }
    .employee-avatar-wrapper .status-dot.leave { background: #6f42c1; }
    
    .half-day-threshold-info {
        font-size: 8px;
        color: #92400e;
        background: #fef3c7;
        padding: 1px 6px;
        border-radius: 10px;
        margin-left: 4px;
    }
    .half-day-progress {
        width: 50px;
        height: 3px;
        background: #e9ecef;
        border-radius: 2px;
        overflow: hidden;
        display: inline-block;
        margin-left: 4px;
    }
    .half-day-progress .bar {
        height: 100%;
        border-radius: 2px;
        transition: width 0.5s ease;
    }
    .half-day-progress .bar.warning { background: #f59e0b; }
    .half-day-progress .bar.success { background: #28a745; }
    .half-day-progress .bar.danger { background: #dc3545; }
    .half-day-progress .bar.info { background: #0dcaf0; }
    
    .employee-name-with-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    .table-responsive {
        overflow-x: auto;
    }
    .table {
        min-width: 1600px;
    }
    .badge-pill {
        border-radius: 50px;
        padding: 3px 10px;
        font-size: 10px;
    }

    /* Source & Verification Badges */
    .source-badge {
        font-size: 7px;
        padding: 1px 5px;
        border-radius: 8px;
        white-space: nowrap;
        display: inline-block;
    }
    .source-badge.web {
        background: #e8f0fe;
        color: #1967d2;
    }
    .source-badge.flutter {
        background: #e0f7fa;
        color: #00695c;
    }
    .source-badge.manual {
        background: #f5f5f5;
        color: #616161;
    }
    .verification-badge {
        font-size: 7px;
        padding: 1px 6px;
        border-radius: 8px;
        white-space: nowrap;
        display: inline-block;
    }
    .verification-badge.verified {
        background: #d1fae5;
        color: #065f46;
    }
    .verification-badge.failed {
        background: #fee2e2;
        color: #991b1b;
    }
    .verification-badge.mobile {
        background: #dbeafe;
        color: #1e40af;
    }
    .verification-badge.manual {
        background: #f3f4f6;
        color: #374151;
    }

    /* Stats Cards */
    .stats-card {
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
</style>
@endpush

@section('content')
<div class="row">
    {{-- ===== DATE FILTER ===== --}}
    <div class="col-12 mb-3">
        <form method="GET" action="{{ route('attendance.dashboard') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label for="date" class="form-label">Date</label>
                <input type="date" name="date" id="date" class="form-control form-control-sm" value="{{ $date ?? date('Y-m-d') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
            <div class="col-auto">
                <a href="{{ route('attendance.dashboard') }}" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>

    {{-- ===== SUMMARY STATS CARDS ===== --}}
    <div class="col-12 mb-3">
        <div class="row g-2">
            <div class="col-md-2 col-6">
                <div class="card bg-success bg-opacity-10 border-0 stats-card">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-0 text-success">{{ $totals['present'] ?? 0 }}</h6>
                        <small class="text-muted">Present</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card bg-danger bg-opacity-10 border-0 stats-card">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-0 text-danger">{{ $totals['absent'] ?? 0 }}</h6>
                        <small class="text-muted">Absent</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card bg-warning bg-opacity-10 border-0 stats-card">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-0 text-warning">{{ $totals['half_day'] ?? 0 }}</h6>
                        <small class="text-muted">Half Day</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card bg-info bg-opacity-10 border-0 stats-card">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-0 text-info">{{ $totals['week_off'] ?? 0 }}</h6>
                        <small class="text-muted">Week Off</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card bg-primary bg-opacity-10 border-0 stats-card">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-0 text-primary">{{ $totals['holiday'] ?? 0 }}</h6>
                        <small class="text-muted">Holiday</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card bg-purple bg-opacity-10 border-0 stats-card">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-0 text-purple">{{ ($totals['paid_leave'] ?? 0) + ($totals['unpaid_leave'] ?? 0) }}</h6>
                        <small class="text-muted">Leave</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== VERIFICATION & SOURCE STATS ROW ===== --}}
        <div class="row g-2 mt-1">
            <div class="col-md-3 col-6">
                <div class="card border-0 stats-card" style="border-left: 4px solid #6c5ce7 !important;">
                    <div class="card-body p-2 d-flex align-items-center">
                        <div class="me-2">
                            <i class="ti ti-face-id" style="color: #6c5ce7; font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0" style="font-size: 0.9rem;">
                                {{ $totals['verified'] ?? 0 }}
                                <span class="badge bg-face-id ms-1" style="background: #6c5ce7; font-size: 7px;">
                                    {{ $totals['verified_percent'] ?? 0 }}%
                                </span>
                            </h6>
                            <small class="text-muted" style="font-size: 0.6rem;">Face Verified</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 stats-card" style="border-left: 4px solid #0984e3 !important;">
                    <div class="card-body p-2 d-flex align-items-center">
                        <div class="me-2">
                            <i class="ti ti-devices text-primary" style="font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0" style="font-size: 0.9rem;">
                                <span class="badge bg-web" style="font-size: 7px;">🌐 {{ $totals['web_source'] ?? 0 }}</span>
                                <span class="badge bg-flutter" style="font-size: 7px;">📱 {{ $totals['flutter_source'] ?? 0 }}</span>
                            </h6>
                            <small class="text-muted" style="font-size: 0.6rem;">Web / Mobile Sources</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 stats-card" style="border-left: 4px solid #00b894 !important;">
                    <div class="card-body p-2 d-flex align-items-center">
                        <div class="me-2">
                            <i class="ti ti-check-circle text-success" style="font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0" style="font-size: 0.9rem;">
                                <span class="badge bg-face-id-success" style="font-size: 7px;">✅ {{ $totals['verified'] ?? 0 }}</span>
                                <span class="badge bg-face-id-failed" style="font-size: 7px;">❌ {{ $totals['unverified'] ?? 0 }}</span>
                            </h6>
                            <small class="text-muted" style="font-size: 0.6rem;">Verified / Failed</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 stats-card" style="border-left: 4px solid #f59e0b !important;">
                    <div class="card-body p-2 d-flex align-items-center">
                        <div class="me-2">
                            <i class="ti ti-hourglass-half text-warning" style="font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0" style="font-size: 0.9rem;">
                                <span class="badge bg-half-day" style="font-size: 7px;">🌓 {{ $totals['half_day'] ?? 0 }}</span>
                                <span class="badge bg-warning text-dark" style="font-size: 7px;">⏳ {{ $totals['half_day_live'] ?? 0 }}</span>
                            </h6>
                            <small class="text-muted" style="font-size: 0.6rem;">Half Day / Live</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== EMPLOYEE ATTENDANCE TABLE ===== --}}
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Employee</th>
                        <th>Phone</th>
                        <th>Emp ID</th>
                        <th>Job Title</th>
                        <th>Status</th>
                        <th>Source</th>
                        <th>Verification</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Worked Hrs</th>
                        <th>Late</th>
                        <th>Early Leave</th>
                        <th>Overtime</th>
                        <th>Paid Leave</th>
                        <th>Unpaid Leave</th>
                        <th>OT (Working)</th>
                        <th>OT (Week Off)</th>
                        <th>OT (Holiday)</th>
                        <th>Holiday</th>
                        <th>Week Off</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employeeDetails as $index => $item)
                        @php
                            $status = $item->status;
                            $badgeColor = match($status) {
                                'Present' => 'success',
                                'Absent' => 'danger',
                                'Half Day' => 'half-day',
                                'Week Off' => 'info',
                                'Holiday' => 'primary',
                                'Paid Leave' => 'purple',
                                'Unpaid Leave' => 'secondary',
                                default => 'secondary',
                            };
                            
                            // ============================================================
                            // SOURCE & VERIFICATION DATA
                            // ============================================================
                            $attendance = $item->attendance ?? null;
                            $source = $attendance ? ($attendance->source ?? 'manual') : 'none';
                            $isVerified = $attendance ? ($attendance->is_verified ?? false) : false;
                            $faceConfidence = $attendance ? ($attendance->face_confidence ?? null) : null;
                            $isFaceId = $attendance && $attendance->marked_by == 'face_recognition';
                            
                            $sourceIcon = match($source) {
                                'web' => '🌐',
                                'flutter' => '📱',
                                'manual' => '📝',
                                default => '❓'
                            };
                            $sourceLabel = match($source) {
                                'web' => 'Web',
                                'flutter' => 'Flutter',
                                'manual' => 'Manual',
                                default => ucfirst($source)
                            };
                            $sourceClass = match($source) {
                                'web' => 'web',
                                'flutter' => 'flutter',
                                'manual' => 'manual',
                                default => 'manual'
                            };
                            
                            // Verification badge
                            if ($source === 'web' && $faceConfidence !== null) {
                                if ($isVerified) {
                                    $verificationBadge = '✅ ' . round($faceConfidence) . '%';
                                    $verificationClass = 'verified';
                                } else {
                                    $verificationBadge = '❌ ' . round($faceConfidence) . '%';
                                    $verificationClass = 'failed';
                                }
                            } elseif ($source === 'flutter') {
                                $verificationBadge = '📱 Mobile';
                                $verificationClass = 'mobile';
                            } elseif ($source === 'manual') {
                                $verificationBadge = '📝 Manual';
                                $verificationClass = 'manual';
                            } else {
                                $verificationBadge = '--';
                                $verificationClass = 'manual';
                            }
                            
                            // Get avatar with initials fallback
                            $defaultAvatar = asset('assets/img/user-avatar.png');
                            $avatarUrl = $defaultAvatar;
                            $employeeName = $item->employee->name ?? 'User';
                            $userInitial = strtoupper(substr($employeeName, 0, 1));
                            
                            // Check user avatar
                            if (!empty($item->employee) && !empty($item->employee->user) && !empty($item->employee->user->avatar)) {
                                $avatar = $item->employee->user->avatar;
                                $possiblePaths = [
                                    'uploads/avatar/' . $avatar,
                                    'storage/uploads/avatar/' . $avatar,
                                    'storage/avatar/' . $avatar,
                                    'avatar/' . $avatar,
                                ];
                                
                                foreach ($possiblePaths as $path) {
                                    $fullPath = public_path($path);
                                    if (file_exists($fullPath)) {
                                        $avatarUrl = asset($path) . '?v=' . time();
                                        break;
                                    }
                                }
                                
                                if ($avatarUrl == $defaultAvatar) {
                                    try {
                                        $utilityUrl = \App\Models\Utility::get_file('uploads/avatar/' . $avatar);
                                        if ($utilityUrl && filter_var($utilityUrl, FILTER_VALIDATE_URL)) {
                                            $avatarUrl = $utilityUrl . '?v=' . time();
                                        }
                                    } catch (\Exception $e) {}
                                }
                            }
                            
                            // Check employee avatar
                            if ($avatarUrl == $defaultAvatar && !empty($item->employee->avatar)) {
                                $avatar = $item->employee->avatar;
                                $possiblePaths = [
                                    'uploads/avatar/' . $avatar,
                                    'storage/uploads/avatar/' . $avatar,
                                    'storage/avatar/' . $avatar,
                                    'avatar/' . $avatar,
                                ];
                                
                                foreach ($possiblePaths as $path) {
                                    $fullPath = public_path($path);
                                    if (file_exists($fullPath)) {
                                        $avatarUrl = asset($path) . '?v=' . time();
                                        break;
                                    }
                                }
                            }
                            
                            // Overtime details
                            $overtimeType = property_exists($item, 'overtime_type') ? $item->overtime_type : null;
                            $overtime = property_exists($item, 'overtime') ? $item->overtime : '00:00:00';
                            $leaveType = property_exists($item, 'leave_type') ? $item->leave_type : null;
                            $halfDayThreshold = $item->half_day_threshold ?? 4.0;
                            $isHalfDay = $item->half_day ?? false;
                            
                            // Calculate worked hours float for progress
                            $workedHoursFloat = 0;
                            if ($item->worked_hours != '00:00:00') {
                                $parts = explode(':', $item->worked_hours);
                                if (count($parts) >= 2) {
                                    $workedHoursFloat = intval($parts[0]) + (intval($parts[1]) / 60);
                                }
                            }
                            $halfDayProgress = 0;
                            if ($workedHoursFloat > 0 && $halfDayThreshold > 0) {
                                $halfDayProgress = min(100, ($workedHoursFloat / $halfDayThreshold) * 100);
                            }
                            
                            $statusDot = match($status) {
                                'Present' => 'present',
                                'Absent' => 'absent',
                                'Half Day' => 'half-day',
                                'Week Off' => 'week-off',
                                'Holiday' => 'holiday',
                                'Paid Leave', 'Unpaid Leave' => 'leave',
                                default => 'absent',
                            };
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="employee-avatar-wrapper">
                                        @if($avatarUrl && $avatarUrl != $defaultAvatar)
                                            <img src="{{ $avatarUrl }}"
                                                 class="rounded-circle me-2" width="32" height="32" style="object-fit:cover;"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="avatar-initials d-none me-2"></div>
                                        @else
                                            <div class="avatar-initials me-2">{{ $userInitial }}</div>
                                        @endif
                                        <span class="status-dot {{ $statusDot }}"></span>
                                    </div>
                                    <div>
                                        <div class="employee-name-with-badge">
                                            <span>{{ $employeeName }}</span>
                                            @if($isFaceId)
                                                <span class="badge {{ $isVerified ? 'bg-face-id-success' : 'bg-face-id-failed' }}" style="font-size: 7px; padding: 1px 6px;">
                                                    <i class="ti ti-face-id"></i>
                                                    @if($isVerified)
                                                        ✅ {{ round($faceConfidence) }}%
                                                    @else
                                                        ❌
                                                    @endif
                                                </span>
                                            @endif
                                            @if($isHalfDay)
                                                <span class="badge bg-half-day" style="font-size: 7px; padding: 1px 6px;">
                                                    <i class="ti ti-hourglass-half"></i>
                                                </span>
                                            @endif
                                            @if($source !== 'none')
                                                <span class="source-badge {{ $sourceClass }}" style="font-size: 7px;">
                                                    {{ $sourceIcon }} {{ $sourceLabel }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item->employee->phone ?? '-' }}</td>
                            <td>{{ $item->employee->employee_id ?? '-' }}</td>
                            <td>{{ $item->employee->designation ? $item->employee->designation->name : '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $badgeColor }} rounded-pill px-2 py-1" style="font-size: 11px;">
                                    {{ $status }}
                                </span>
                                @if($isHalfDay)
                                    <span class="half-day-threshold-info">
                                        {{ $halfDayThreshold }}hrs
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($attendance && $source !== 'none')
                                    <span class="source-badge {{ $sourceClass }}">
                                        {{ $sourceIcon }} {{ $sourceLabel }}
                                        @if($source === 'web' && $faceConfidence !== null)
                                            <span class="badge bg-light text-dark ms-1" style="font-size: 6px;">
                                                {{ round($faceConfidence) }}%
                                            </span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance && $source !== 'none')
                                    <span class="verification-badge {{ $verificationClass }}">
                                        {{ $verificationBadge }}
                                        @if($source === 'web' && $faceConfidence !== null)
                                            <br>
                                            <small class="text-muted" style="font-size: 6px;">
                                                Conf: {{ round($faceConfidence) }}%
                                            </small>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                @if($item->clock_in != '00:00:00')
                                    {{ \Carbon\Carbon::parse($item->clock_in)->format('h:i A') }}
                                    @if($source === 'web')
                                        <i class="ti ti-globe text-primary ms-1" style="font-size: 10px;" data-bs-toggle="tooltip" title="Web Punch In"></i>
                                    @elseif($source === 'flutter')
                                        <i class="ti ti-phone text-info ms-1" style="font-size: 10px;" data-bs-toggle="tooltip" title="Mobile Punch In"></i>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->clock_out != '00:00:00')
                                    {{ \Carbon\Carbon::parse($item->clock_out)->format('h:i A') }}
                                    @if($source === 'web' && $isVerified)
                                        <i class="ti ti-check-circle text-success ms-1" style="font-size: 10px;" data-bs-toggle="tooltip" title="Face Verified"></i>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $item->worked_hours }}</span>
                                @if($item->clock_in != '00:00:00')
                                    <div class="half-day-progress">
                                        <div class="bar {{ $isHalfDay ? 'warning' : ($workedHoursFloat >= $halfDayThreshold ? 'success' : 'info') }}" 
                                             style="width: {{ $halfDayProgress }}%;"></div>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($item->late)
                                    <span class="badge bg-warning text-dark">Yes</span>
                                @else
                                    <span class="badge bg-light text-muted">No</span>
                                @endif
                            </td>
                            <td>
                                @if($item->early_leave)
                                    <span class="badge bg-dark">Yes</span>
                                @else
                                    <span class="badge bg-light text-muted">No</span>
                                @endif
                            </td>
                            <td>
                                @if($overtime != '00:00:00')
                                    <span class="badge bg-dark">{{ $overtime }}</span>
                                @else
                                    <span class="badge bg-light text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($leaveType == 'Paid')
                                    <span class="badge bg-purple">Yes</span>
                                @else
                                    <span class="badge bg-light text-muted">No</span>
                                @endif
                            </td>
                            <td>
                                @if($leaveType == 'Unpaid')
                                    <span class="badge bg-secondary">Yes</span>
                                @else
                                    <span class="badge bg-light text-muted">No</span>
                                @endif
                            </td>
                            <td>
                                @if($overtimeType == 'working_day')
                                    <span class="badge bg-dark">{{ $overtime }}</span>
                                @else
                                    <span class="badge bg-light text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($overtimeType == 'week_off')
                                    <span class="badge bg-dark">{{ $overtime }}</span>
                                @else
                                    <span class="badge bg-light text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($overtimeType == 'holiday')
                                    <span class="badge bg-dark">{{ $overtime }}</span>
                                @else
                                    <span class="badge bg-light text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($status == 'Holiday')
                                    <span class="badge bg-primary">Yes</span>
                                @else
                                    <span class="badge bg-light text-muted">No</span>
                                @endif
                            </td>
                            <td>
                                @if($status == 'Week Off')
                                    <span class="badge bg-info">Yes</span>
                                @else
                                    <span class="badge bg-light text-muted">No</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="21" class="text-center py-3">No employee records found for the selected date.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('js')
<script>
    // Auto-submit date filter on change
    document.getElementById('date')?.addEventListener('change', function() {
        this.closest('form').submit();
    });

    // Initialize tooltips
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush
@endsection