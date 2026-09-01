@extends('layouts.admin')

@section('page-title', 'Daily Attendance')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hrm.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('attendance.dashboard') }}">Attendance</a></li>
    <li class="breadcrumb-item active">Daily</li>
@endsection

@push('css')
<style>
    .bg-opacity-10 {
        opacity: 0.15;
    }
    .table th {
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    .table td {
        vertical-align: middle;
    }
    .badge.bg-warning {
        color: #000 !important;
    }
    .badge.bg-half-day {
        background: #f59e0b;
        color: #000 !important;
    }
    .badge.bg-face-id {
        background: #6c5ce7;
        color: #fff !important;
    }
    .badge.bg-face-id-success {
        background: #00b894;
        color: #fff !important;
    }
    .badge.bg-face-id-failed {
        background: #e17055;
        color: #fff !important;
    }
    .badge.bg-web {
        background: #0984e3;
        color: #fff !important;
    }
    .badge.bg-flutter {
        background: #00b894;
        color: #fff !important;
    }
    .badge.bg-manual {
        background: #636e72;
        color: #fff !important;
    }
    .half-day-threshold-info {
        font-size: 9px;
        color: #92400e;
        background: #fef3c7;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 4px;
    }
    .half-day-progress {
        width: 60px;
        height: 4px;
        background: #e9ecef;
        border-radius: 2px;
        overflow: hidden;
        display: inline-block;
        margin-left: 8px;
    }
    .half-day-progress .bar {
        height: 100%;
        border-radius: 2px;
        transition: width 0.5s ease;
    }
    .half-day-progress .bar.warning {
        background: #f59e0b;
    }
    .half-day-progress .bar.success {
        background: #28a745;
    }
    .half-day-progress .bar.danger {
        background: #dc3545;
    }
    .half-day-progress .bar.info {
        background: #0dcaf0;
    }
    .avatar-initials {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 600;
        color: #667eea;
        background: #f0f2ff;
        border: 2px solid #667eea;
        text-transform: uppercase;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }
    .avatar-initials:hover {
        transform: scale(1.05);
        box-shadow: 0 0 15px rgba(102, 126, 234, 0.2);
    }
    .status-badge-with-halfday {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    /* ===== AVATAR / PHOTO - Same as Live Blade ===== */
    .avatar-wrapper {
        position: relative;
        display: inline-block;
        flex-shrink: 0;
        cursor: pointer;
    }
    .avatar-wrapper .photo-indicator {
        position: absolute;
        bottom: -2px;
        right: -2px;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        font-size: 8px;
        color: #fff;
        font-weight: bold;
    }
    .avatar-wrapper .photo-indicator.mobile { background: #10b981; }
    .avatar-wrapper .photo-indicator.web { background: #3b82f6; }
    .avatar-wrapper .photo-indicator.manual { background: #6b7280; }
    .avatar-wrapper .photo-indicator.face-id { background: #8b5cf6; }
    .avatar-wrapper .photo-indicator.half-day {
        background: #f59e0b;
        animation: pulse-warning 1.5s infinite;
    }
    .avatar-wrapper img {
        width: 35px;
        height: 35px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    .avatar-wrapper img:hover {
        transform: scale(1.05);
        border-color: #667eea;
    }
    .avatar-wrapper img[src*="user-avatar.png"] {
        opacity: 0.8;
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
        font-size: 8px;
        padding: 2px 8px;
        border-radius: 10px;
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

    .stats-card {
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    @keyframes pulse-warning {
        0% { opacity: 1; }
        50% { opacity: 0.6; }
        100% { opacity: 1; }
    }
    .half-day-indicator {
        animation: pulse-warning 1.5s infinite;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ti ti-calendar me-2"></i> Daily Attendance
                </h5>
                <span class="badge bg-primary rounded-pill px-3 py-2">
                    <i class="ti ti-calendar me-1"></i>
                    {{ \Carbon\Carbon::parse($date)->format('d M, Y') }}
                </span>
            </div>

            {{-- ===== STATS CARDS ===== --}}
            <div class="card-body pb-0">
                <div class="row g-3">
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="card bg-primary bg-opacity-10 border-0 shadow-sm stats-card">
                            <div class="card-body p-3 text-center">
                                <i class="ti ti-users text-primary fs-4"></i>
                                <h5 class="mt-1 mb-0">{{ $statusCounts['all'] ?? 0 }}</h5>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="card bg-success bg-opacity-10 border-0 shadow-sm stats-card">
                            <div class="card-body p-3 text-center">
                                <i class="ti ti-clock text-success fs-4"></i>
                                <h5 class="mt-1 mb-0">{{ $statusCounts['in'] ?? 0 }}</h5>
                                <small class="text-muted">In</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="card bg-secondary bg-opacity-10 border-0 shadow-sm stats-card">
                            <div class="card-body p-3 text-center">
                                <i class="ti ti-logout text-secondary fs-4"></i>
                                <h5 class="mt-1 mb-0">{{ $statusCounts['out'] ?? 0 }}</h5>
                                <small class="text-muted">Out</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="card bg-warning bg-opacity-10 border-0 shadow-sm stats-card">
                            <div class="card-body p-3 text-center">
                                <i class="ti ti-alert-triangle text-warning fs-4"></i>
                                <h5 class="mt-1 mb-0">{{ $statusCounts['late'] ?? 0 }}</h5>
                                <small class="text-muted">Late</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="card bg-dark bg-opacity-10 border-0 shadow-sm stats-card">
                            <div class="card-body p-3 text-center">
                                <i class="ti ti-hourglass-empty text-dark fs-4"></i>
                                <h5 class="mt-1 mb-0">{{ $statusCounts['early_leave'] ?? 0 }}</h5>
                                <small class="text-muted">Early Out</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="card bg-half-day bg-opacity-10 border-0 shadow-sm stats-card" style="background: #fef3c7;">
                            <div class="card-body p-3 text-center">
                                <i class="ti ti-hourglass-half text-warning fs-4"></i>
                                <h5 class="mt-1 mb-0" style="color: #92400e;">{{ $statusCounts['half_day'] ?? 0 }}</h5>
                                <small class="text-muted" style="color: #92400e;">Half Day</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== VERIFICATION & SOURCE STATS ROW ===== --}}
                <div class="row g-3 mt-2">
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm stats-card" style="border-left: 4px solid #6c5ce7 !important;">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="me-3">
                                    <i class="ti ti-face-id text-face-id fs-4" style="color: #6c5ce7;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">
                                        {{ $statusCounts['verified'] ?? 0 }}
                                        <span class="badge bg-face-id ms-1" style="background: #6c5ce7; font-size: 8px;">
                                            {{ $statusCounts['verified_percent'] ?? 0 }}%
                                        </span>
                                    </h6>
                                    <small class="text-muted">Face Verified</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm stats-card" style="border-left: 4px solid #0984e3 !important;">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="me-3">
                                    <i class="ti ti-devices text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">
                                        <span class="badge bg-web">🌐 {{ $statusCounts['web_source'] ?? 0 }}</span>
                                        <span class="badge bg-flutter">📱 {{ $statusCounts['flutter_source'] ?? 0 }}</span>
                                    </h6>
                                    <small class="text-muted">Web / Mobile Sources</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm stats-card" style="border-left: 4px solid #00b894 !important;">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="me-3">
                                    <i class="ti ti-check-circle text-success fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">
                                        <span class="badge bg-face-id-success">✅ Verified</span>
                                        <span class="badge bg-face-id-failed">❌ Failed</span>
                                    </h6>
                                    <small class="text-muted">
                                        {{ $statusCounts['verified'] ?? 0 }} Verified / 
                                        {{ $statusCounts['unverified'] ?? 0 }} Unverified
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== FILTERS ===== --}}
            <div class="card-body">
                <form method="GET" action="{{ route('attendance.daily') }}" id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Date</label>
                        <input type="date" class="form-control form-control-sm" name="date" value="{{ $date }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Branch</label>
                        <select class="form-select form-select-sm" name="branch" id="branchFilter">
                            <option value="">All Branches</option>
                            @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Department</label>
                        <select class="form-select form-select-sm" name="department" id="departmentFilter">
                            <option value="">All Departments</option>
                            @foreach($departments ?? [] as $dept)
                                <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Status</label>
                        <select class="form-select form-select-sm" name="status" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="in" {{ request('status') == 'in' ? 'selected' : '' }}>In</option>
                            <option value="out" {{ request('status') == 'out' ? 'selected' : '' }}>Out</option>
                            <option value="not_punched" {{ request('status') == 'not_punched' ? 'selected' : '' }}>No Punch</option>
                            <option value="break" {{ request('status') == 'break' ? 'selected' : '' }}>Break</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                            <option value="early_leave" {{ request('status') == 'early_leave' ? 'selected' : '' }}>Early Leave</option>
                            <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>🌓 Half Day</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>✅ Verified</option>
                            <option value="unverified" {{ request('status') == 'unverified' ? 'selected' : '' }}>❌ Unverified</option>
                            <option value="web" {{ request('status') == 'web' ? 'selected' : '' }}>🌐 Web</option>
                            <option value="flutter" {{ request('status') == 'flutter' ? 'selected' : '' }}>📱 Mobile</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Search Staff</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" name="search" placeholder="Name..." value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('attendance.daily') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-refresh"></i>
                        </a>
                    </div>
                </form>
            </div>

            {{-- ===== STAFF LIST (Table) ===== --}}
            <div class="card-body pt-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">
                        Showing <strong>{{ $employeeStatuses->count() }}</strong> employees
                        @if(request('status') == 'half_day')
                            <span class="badge bg-warning ms-2">🌓 Half Day filtered</span>
                        @endif
                        @if(request('status') == 'verified')
                            <span class="badge bg-face-id-success ms-2">✅ Verified</span>
                        @endif
                        @if(request('status') == 'web')
                            <span class="badge bg-web ms-2">🌐 Web Source</span>
                        @endif
                        @if(request('status') == 'flutter')
                            <span class="badge bg-flutter ms-2">📱 Mobile Source</span>
                        @endif
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
                                <th>Branch</th>
                                <th>Department</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Worked Hours</th>
                                <th>Status</th>
                                <th>Source</th>
                                <th>Verification</th>
                                <th>Half Day</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employeeStatuses as $index => $item)
                                @php
                                    $employee = $item->employee;
                                    $attendance = $item->attendance;
                                    $status = $item->status;
                                    $isHalfDay = $item->isHalfDay ?? false;
                                    $halfDayThreshold = $item->half_day_threshold ?? 4.0;
                                    $lateAccessEnabled = $item->late_access_enabled ?? false;
                                    $lateAllowedMinutes = $item->late_allowed_minutes ?? 60;
                                    $isLive = $item->isLive ?? false;
                                    $isOnBreak = $item->isOnBreak ?? false;

                                    // ============================================================
                                    // SOURCE + VERIFICATION - Same as Live Blade
                                    // ============================================================
                                    $source = $attendance->source ?? 'manual';
                                    $markedBy = $attendance->marked_by ?? 'manual';
                                    $isVerified = $attendance->is_verified ?? false;
                                    $faceConfidence = $attendance->face_confidence ?? null;

                                    $isFaceId = in_array($markedBy, ['face_recognition', 'face_recognition_failed'])
                                                || ($source === 'web' && $faceConfidence !== null);

                                    $sourceLabel = match($source) {
                                        'web' => '🌐 Web',
                                        'flutter' => '📱 Mobile',
                                        default => '📝 Manual',
                                    };
                                    $sourceClass = match($source) {
                                        'web' => 'web',
                                        'flutter' => 'flutter',
                                        default => 'manual',
                                    };
                                    $sourceIcon = match($source) {
                                        'web' => '🌐',
                                        'flutter' => '📱',
                                        'manual' => '📝',
                                        default => '❓'
                                    };

                                    // ============================================================
                                    // PHOTO HANDLING - Same as Live Blade
                                    // ============================================================
                                    $defaultAvatar = asset('assets/img/user-avatar.png');
                                    $finalPhotoUrl = $defaultAvatar;
                                    $hasRealPhoto = false;
                                    $photoLabel = 'Profile';
                                    $photoType = 'manual';
                                    $photoCount = 0;

                                    if ($attendance) {
                                        $photoFields = [
                                            'punch_photo'      => ['label' => 'Clock In',  'type' => 'clock-in'],
                                            'break_in_photo'   => ['label' => 'Break In',  'type' => 'break-in'],
                                            'break_out_photo'  => ['label' => 'Break Out', 'type' => 'break-out'],
                                            'punch_out_photo'  => ['label' => 'Punch Out', 'type' => 'punch-out'],
                                        ];

                                        foreach ($photoFields as $field => $info) {
                                            if (!empty($attendance->$field)) {
                                                $path = 'uploads/attendance/' . $attendance->$field;
                                                $fullPath = public_path($path);
                                                if (file_exists($fullPath)) {
                                                    $finalPhotoUrl = asset($path) . '?v=' . time();
                                                    $hasRealPhoto = true;
                                                    $photoLabel = $info['label'];
                                                    $photoType = $info['type'];
                                                    $photoCount++;
                                                    break;
                                                }
                                            }
                                        }
                                    }

                                    // Fallback to avatar
                                    if (!$hasRealPhoto) {
                                        if ($employee->user && !empty($employee->user->avatar) && $employee->user->avatar !== 'avatar.png') {
                                            $avatarPath = public_path('uploads/avatar/' . $employee->user->avatar);
                                            if (file_exists($avatarPath)) {
                                                $finalPhotoUrl = asset('uploads/avatar/' . $employee->user->avatar) . '?v=' . time();
                                                $hasRealPhoto = true;
                                                $photoLabel = 'Profile';
                                                $photoType = 'profile';
                                                $photoCount = 1;
                                            }
                                        }
                                        if (!$hasRealPhoto && !empty($employee->avatar)) {
                                            $avatarPath = public_path('uploads/avatar/' . $employee->avatar);
                                            if (file_exists($avatarPath)) {
                                                $finalPhotoUrl = asset('uploads/avatar/' . $employee->avatar) . '?v=' . time();
                                                $hasRealPhoto = true;
                                                $photoLabel = 'Profile';
                                                $photoType = 'profile';
                                                $photoCount = 1;
                                            }
                                        }
                                    }

                                    // User name and initials
                                    $userName = $employee->name ?? 'User';
                                    $userInitial = strtoupper(substr($userName, 0, 1));

                                    // Indicator type - Same as Live Blade
                                    $indicatorType = 'manual';
                                    if ($source === 'flutter') {
                                        $indicatorType = 'mobile';
                                    } elseif ($isFaceId && $hasRealPhoto) {
                                        $indicatorType = 'face-id';
                                    } elseif ($isHalfDay) {
                                        $indicatorType = 'half-day';
                                    } elseif ($source === 'web') {
                                        $indicatorType = 'web';
                                    }

                                    // ============================================================
                                    // VERIFICATION BADGE - Same as Live Blade
                                    // ============================================================
                                    $verificationBadge = '📝 Manual';
                                    $verificationClass = 'manual';
                                    if ($source === 'web') {
                                        if ($isVerified) {
                                            $verificationBadge = '✅ Face Verified (' . round($faceConfidence ?? 0) . '%)';
                                            $verificationClass = 'verified';
                                        } elseif ($faceConfidence !== null) {
                                            $verificationBadge = '❌ Face Failed (' . round($faceConfidence) . '%)';
                                            $verificationClass = 'failed';
                                        } else {
                                            $verificationBadge = '⚠️ Manual (Web)';
                                            $verificationClass = 'manual';
                                        }
                                    } elseif ($source === 'flutter') {
                                        $verificationBadge = '📱 Mobile App';
                                        $verificationClass = 'mobile';
                                    }

                                    // Calculate worked hours
                                    $workedHoursDisplay = '--:--';
                                    $workedHoursFloat = 0;
                                    if ($attendance && $attendance->clock_in != '00:00:00') {
                                        if ($attendance->clock_out != '00:00:00') {
                                            $start = \Carbon\Carbon::parse($attendance->clock_in);
                                            $end = \Carbon\Carbon::parse($attendance->clock_out);
                                            $diff = $start->diff($end);
                                            $workedHoursDisplay = $diff->format('%H:%I');
                                            $workedHoursFloat = ($diff->h * 60 + $diff->i) / 60;
                                        } else {
                                            $start = \Carbon\Carbon::parse($attendance->clock_in);
                                            $now = \Carbon\Carbon::now();
                                            $diff = $start->diff($now);
                                            $workedHoursDisplay = $diff->format('%H:%I');
                                            $workedHoursFloat = ($diff->h * 60 + $diff->i) / 60;
                                        }
                                    }

                                    // Calculate half day progress
                                    $halfDayProgress = 0;
                                    if ($workedHoursFloat > 0 && $halfDayThreshold > 0) {
                                        $halfDayProgress = min(100, ($workedHoursFloat / $halfDayThreshold) * 100);
                                    }

                                    // Determine half day status display
                                    $halfDayStatusText = 'Not Half Day';
                                    $halfDayProgressClass = 'success';
                                    if ($isHalfDay) {
                                        $halfDayStatusText = '🌓 Half Day';
                                        $halfDayProgressClass = 'warning';
                                    } elseif ($workedHoursFloat >= $halfDayThreshold) {
                                        $halfDayStatusText = '✅ Full Day';
                                        $halfDayProgressClass = 'success';
                                    } elseif ($workedHoursFloat > 0) {
                                        $halfDayStatusText = '⏳ In Progress';
                                        $halfDayProgressClass = 'info';
                                    }

                                    // Status badge color
                                    $badgeColor = match($status) {
                                        'in' => 'success',
                                        'out' => 'secondary',
                                        'not_punched' => 'warning',
                                        'break' => 'info',
                                        'late' => 'danger',
                                        'early_leave' => 'dark',
                                        'half_day' => 'half-day',
                                        default => 'secondary',
                                    };

                                    $statusLabel = match($status) {
                                        'in' => 'Clocked In',
                                        'out' => 'Clocked Out',
                                        'not_punched' => 'No Punch',
                                        'break' => 'On Break',
                                        'late' => 'Late In',
                                        'early_leave' => 'Early Leave',
                                        'half_day' => '🌓 Half Day',
                                        default => ucfirst(str_replace('_', ' ', $status)),
                                    };

                                    // Clock In/Out times
                                    $clockInTime = $attendance && $attendance->clock_in != '00:00:00' 
                                        ? \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') 
                                        : '--:--';
                                    $clockOutTime = $attendance && $attendance->clock_out != '00:00:00' 
                                        ? \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') 
                                        : '--:--';
                                @endphp
                                <tr data-source="{{ $source }}" data-verified="{{ $isVerified ? '1' : '0' }}">
                                    <td>{{ $index + 1 }}</td>

                                    {{-- EMPLOYEE COLUMN - Same as Live Blade --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-wrapper" onclick="openPhotoGallery({{ $employee->id }})" style="cursor: pointer;">
                                                @if($hasRealPhoto)
                                                    <img src="{{ $finalPhotoUrl }}"
                                                         alt="{{ $userName }}"
                                                         class="rounded-circle me-2"
                                                         style="width: 35px; height: 35px; object-fit: cover; border: 2px solid #e5e7eb;"
                                                         data-bs-toggle="tooltip"
                                                         title="{{ $photoLabel }} Photo"
                                                         loading="lazy"
                                                         onerror="this.onerror=null; this.src='{{ asset('assets/img/user-avatar.png') }}'">
                                                @else
                                                    <div class="avatar-initials me-2"
                                                         data-bs-toggle="tooltip"
                                                         title="{{ $userName }}">
                                                        {{ $userInitial }}
                                                    </div>
                                                @endif

                                                @if($hasRealPhoto || $source !== 'manual')
                                                    <span class="photo-indicator {{ $indicatorType }}"
                                                          data-bs-toggle="tooltip"
                                                          title="{{ $source === 'flutter' ? '📱 Mobile App' : ($isFaceId ? 'Face ID' : $photoLabel) }}">
                                                        @if($source === 'flutter')
                                                            <i class="ti ti-device-mobile"></i>
                                                        @elseif($isFaceId)
                                                            <i class="ti ti-face-id"></i>
                                                        @elseif($isHalfDay)
                                                            <i class="ti ti-hourglass-half"></i>
                                                        @elseif($source === 'web')
                                                            <i class="ti ti-globe"></i>
                                                        @else
                                                            <i class="ti ti-camera"></i>
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>

                                            <div>
                                                <div class="fw-semibold">
                                                    {{ $userName }}
                                                    @if($isFaceId)
                                                        <span class="badge {{ $isVerified ? 'bg-face-id-success' : 'bg-face-id-failed' }}" style="font-size: 8px; padding: 2px 6px;">
                                                            <i class="ti ti-face-id"></i>
                                                            @if($isVerified)
                                                                ✅ {{ round($faceConfidence) }}%
                                                            @else
                                                                ❌
                                                            @endif
                                                        </span>
                                                    @endif
                                                    @if($source !== 'none')
                                                        <span class="source-badge {{ $sourceClass }}" style="font-size: 7px;">
                                                            {{ $sourceIcon }} {{ $sourceLabel }}
                                                        </span>
                                                    @endif
                                                    @if($isLive)
                                                        <span class="badge bg-success" style="font-size: 7px; padding: 2px 6px;">
                                                            <i class="ti ti-circle-filled" style="font-size: 6px;"></i> Live
                                                        </span>
                                                    @endif
                                                    @if($isOnBreak)
                                                        <span class="badge bg-warning" style="font-size: 7px; padding: 2px 6px;">
                                                            <i class="ti ti-coffee" style="font-size: 6px;"></i> Break
                                                        </span>
                                                    @endif
                                                    @if($photoCount > 1)
                                                        <span class="badge bg-info" style="font-size: 7px; padding: 2px 6px;">
                                                            <i class="ti ti-camera me-1"></i> {{ $photoCount }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <small class="text-muted">{{ $employee->designation->name ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>{{ $employee->branch->name ?? '-' }}</td>
                                    <td>{{ $employee->department->name ?? '-' }}</td>

                                    {{-- Clock In --}}
                                    <td>
                                        @if($attendance && $attendance->clock_in != '00:00:00')
                                            <span class="fw-semibold">{{ $clockInTime }}</span>
                                            @if($item->isLate ?? false)
                                                <span class="badge bg-danger ms-1" style="font-size: 8px;">Late</span>
                                            @endif
                                            @if($source === 'web')
                                                <i class="ti ti-globe text-primary ms-1" style="font-size: 10px;" data-bs-toggle="tooltip" title="Web Punch In"></i>
                                            @elseif($source === 'flutter')
                                                <i class="ti ti-phone text-info ms-1" style="font-size: 10px;" data-bs-toggle="tooltip" title="Mobile Punch In"></i>
                                            @endif
                                            @if($hasRealPhoto)
                                                <i class="ti ti-camera text-{{ $source === 'flutter' ? 'success' : 'primary' }} ms-1" style="font-size: 10px;" data-bs-toggle="tooltip" title="Has Clock In Photo"></i>
                                            @endif
                                        @else
                                            <span class="text-muted">--:--</span>
                                        @endif
                                    </td>

                                    {{-- Clock Out --}}
                                    <td>
                                        @if($attendance && $attendance->clock_out != '00:00:00')
                                            <span class="fw-semibold">{{ $clockOutTime }}</span>
                                            @if($source === 'web' && $isVerified)
                                                <i class="ti ti-check-circle text-success ms-1" style="font-size: 10px;" data-bs-toggle="tooltip" title="Face Verified"></i>
                                            @endif
                                        @else
                                            <span class="text-muted">--:--</span>
                                        @endif
                                    </td>

                                    {{-- Worked Hours --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-dark">{{ $workedHoursDisplay }}</span>
                                            @if($attendance && $attendance->clock_in != '00:00:00')
                                                <div class="half-day-progress">
                                                    <div class="bar {{ $halfDayProgressClass }}" style="width: {{ $halfDayProgress }}%;"></div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        <div class="status-badge-with-halfday">
                                            <span class="badge bg-{{ $badgeColor }} rounded-pill px-3 py-2">
                                                {{ $statusLabel }}
                                            </span>
                                            @if($isHalfDay)
                                                <span class="half-day-threshold-info">
                                                    <i class="ti ti-hourglass-half me-1"></i>
                                                    {{ $halfDayThreshold }}hrs
                                                </span>
                                            @endif
                                            @if($lateAccessEnabled)
                                                <span class="badge bg-info" style="font-size: 8px; padding: 2px 6px;">
                                                    <i class="ti ti-clock"></i> {{ $lateAllowedMinutes }}m grace
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Source --}}
                                    <td>
                                        @if($attendance && $source !== 'none')
                                            <span class="source-badge {{ $sourceClass }}">
                                                {{ $sourceIcon }} {{ $sourceLabel }}
                                                @if($source === 'web' && $faceConfidence !== null)
                                                    <span class="badge bg-light text-dark ms-1" style="font-size: 7px;">
                                                        {{ round($faceConfidence) }}%
                                                    </span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>

                                    {{-- Verification --}}
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

                                    {{-- Half Day --}}
                                    <td>
                                        @if($attendance && $attendance->clock_in != '00:00:00')
                                            <div class="d-flex flex-column align-items-start" style="min-width: 100px;">
                                                <div class="progress w-100" style="height: 4px;">
                                                    <div class="progress-bar {{ $isHalfDay ? 'bg-warning' : 'bg-success' }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $halfDayProgress }}%;" 
                                                         aria-valuenow="{{ $halfDayProgress }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between w-100 mt-1">
                                                    <span class="badge {{ $isHalfDay ? 'bg-warning' : ($workedHoursFloat >= $halfDayThreshold ? 'bg-success' : 'bg-info') }}" 
                                                          style="font-size: 8px; padding: 2px 6px;">
                                                        {{ $halfDayStatusText }}
                                                        @if($isHalfDay)
                                                            <span class="half-day-indicator" style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #f59e0b; animation: pulse-warning 1.5s infinite; margin-left: 4px;"></span>
                                                        @endif
                                                    </span>
                                                    <small class="text-muted" style="font-size: 8px;">
                                                        {{ $halfDayThreshold }}hrs
                                                    </small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5">
                                        <i class="ti ti-user-off display-4 text-muted d-block mb-3"></i>
                                        <p class="text-muted">No attendance records found for this date.</p>
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

@push('js')
<script>
    $(document).ready(function() {
        // Auto-submit on dropdown change
        $('#branchFilter, #departmentFilter, #statusFilter').on('change', function() {
            $('#filterForm').submit();
        });

        // Debounce search
        let searchTimer;
        $('input[name="search"]').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                $('#filterForm').submit();
            }, 500);
        });

        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    // Photo Gallery function - Same as Live Blade
    function openPhotoGallery(employeeId) {
        // ... your existing photo gallery function ...
    }
</script>
@endpush

@push('css')
<style>
    .bg-opacity-10 {
        opacity: 0.15;
    }
    .table th {
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    .table td {
        vertical-align: middle;
    }
    .badge.bg-warning {
        color: #000 !important;
    }
    .badge.bg-half-day {
        background: #f59e0b;
        color: #000 !important;
    }
    .badge.bg-face-id {
        background: #6c5ce7 !important;
        color: #fff !important;
    }
    .badge.bg-face-id-success {
        background: #00b894 !important;
        color: #fff !important;
    }
    .badge.bg-face-id-failed {
        background: #e17055 !important;
        color: #fff !important;
    }
    .badge.bg-web {
        background: #0984e3 !important;
        color: #fff !important;
    }
    .badge.bg-flutter {
        background: #00b894 !important;
        color: #fff !important;
    }
    .badge.bg-manual {
        background: #636e72 !important;
        color: #fff !important;
    }
    .half-day-threshold-info {
        font-size: 9px;
        color: #92400e;
        background: #fef3c7;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 4px;
    }
    .half-day-progress {
        width: 60px;
        height: 4px;
        background: #e9ecef;
        border-radius: 2px;
        overflow: hidden;
        display: inline-block;
        margin-left: 8px;
    }
    .half-day-progress .bar {
        height: 100%;
        border-radius: 2px;
        transition: width 0.5s ease;
    }
    .half-day-progress .bar.warning {
        background: #f59e0b;
    }
    .half-day-progress .bar.success {
        background: #28a745;
    }
    .half-day-progress .bar.danger {
        background: #dc3545;
    }
    .half-day-progress .bar.info {
        background: #0dcaf0;
    }
    .avatar-initials {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 600;
        color: #667eea;
        background: #f0f2ff;
        border: 2px solid #667eea;
        text-transform: uppercase;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }
    .avatar-initials:hover {
        transform: scale(1.05);
        box-shadow: 0 0 15px rgba(102, 126, 234, 0.2);
    }
    .status-badge-with-halfday {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    /* ===== AVATAR / PHOTO - Same as Live Blade ===== */
    .avatar-wrapper {
        position: relative;
        display: inline-block;
        flex-shrink: 0;
        cursor: pointer;
    }
    .avatar-wrapper .photo-indicator {
        position: absolute;
        bottom: -2px;
        right: -2px;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        font-size: 8px;
        color: #fff;
        font-weight: bold;
    }
    .avatar-wrapper .photo-indicator.mobile { background: #10b981; }
    .avatar-wrapper .photo-indicator.web { background: #3b82f6; }
    .avatar-wrapper .photo-indicator.manual { background: #6b7280; }
    .avatar-wrapper .photo-indicator.face-id { background: #8b5cf6; }
    .avatar-wrapper .photo-indicator.half-day {
        background: #f59e0b;
        animation: pulse-warning 1.5s infinite;
    }
    .avatar-wrapper img {
        width: 35px;
        height: 35px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    .avatar-wrapper img:hover {
        transform: scale(1.05);
        border-color: #667eea;
    }
    .avatar-wrapper img[src*="user-avatar.png"] {
        opacity: 0.8;
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
        font-size: 8px;
        padding: 2px 8px;
        border-radius: 10px;
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

    .stats-card {
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    @keyframes pulse-warning {
        0% { opacity: 1; }
        50% { opacity: 0.6; }
        100% { opacity: 1; }
    }
    .half-day-indicator {
        animation: pulse-warning 1.5s infinite;
    }
</style>
@endpush
@endsection