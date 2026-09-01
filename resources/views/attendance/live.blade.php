@extends('layouts.admin')

@section('page-title', 'Live Attendance')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hrm.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('attendance.dashboard') }}">Attendance</a></li>
    <li class="breadcrumb-item active">Live</li>
@endsection

@push('css')
<style>
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
    .badge.bg-warning {
        color: #000 !important;
    }
    .badge.bg-half-day {
        background: #f59e0b;
        color: #000 !important;
    }
    .badge.bg-half-day-live {
        background: #f59e0b;
        color: #000 !important;
        animation: pulse-warning 2s infinite;
    }
    .badge.bg-face-id {
        background: #6c5ce7;
        color: white;
    }
    .badge.bg-face-id-success {
        background: #00b894;
        color: white;
    }
    .badge.bg-face-id-failed {
        background: #e17055;
        color: white;
    }
    .badge.bg-web {
        background: #0984e3;
        color: white;
    }
    .badge.bg-flutter {
        background: #00b894;
        color: white;
    }
    .badge.bg-manual {
        background: #636e72;
        color: white;
    }
    @keyframes pulse-warning {
        0% { opacity: 1; }
        50% { opacity: 0.6; }
        100% { opacity: 1; }
    }
    .half-day-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 4px;
        animation: pulse-warning 1.5s infinite;
    }
    .half-day-indicator.active {
        background: #f59e0b;
    }
    .half-day-threshold-info {
        font-size: 10px;
        color: #92400e;
        background: #fef3c7;
        padding: 2px 8px;
        border-radius: 10px;
        margin-left: 4px;
    }

    /* ===== PHOTO INDICATOR ===== */
    .avatar-wrapper {
        position: relative;
        display: inline-block;
    }
    .avatar-wrapper .photo-indicator {
        position: absolute;
        bottom: -2px;
        right: -2px;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        font-size: 9px;
        color: #fff;
        font-weight: bold;
    }
    .avatar-wrapper .photo-indicator.clock-in { background: #0d6efd; }
    .avatar-wrapper .photo-indicator.break-in { background: #f59e0b; }
    .avatar-wrapper .photo-indicator.break-out { background: #8b5cf6; }
    .avatar-wrapper .photo-indicator.punch-out { background: #dc3545; }
    .avatar-wrapper .photo-indicator.profile { background: #6c757d; }
    .avatar-wrapper .photo-indicator.face-id { background: #6c5ce7; }
    .avatar-wrapper .photo-indicator.face-id-success { background: #00b894; }
    .avatar-wrapper .photo-indicator.face-id-failed { background: #e17055; }
    .avatar-wrapper .photo-indicator.half-day { background: #f59e0b; animation: pulse-warning 1.5s infinite; }

    .photo-type-badge {
        font-size: 9px;
        padding: 2px 8px;
        border-radius: 10px;
        margin-left: 4px;
        font-weight: 600;
    }
    .photo-type-badge.clock-in { background: #0d6efd; color: white; }
    .photo-type-badge.break-in { background: #f59e0b; color: white; }
    .photo-type-badge.break-out { background: #8b5cf6; color: white; }
    .photo-type-badge.punch-out { background: #dc3545; color: white; }
    .photo-type-badge.profile { background: #6c757d; color: white; }
    .photo-type-badge.face-id { background: #6c5ce7; color: white; }

    .break-status {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 12px;
        white-space: nowrap;
    }
    .break-status.on-break { background: #fef3c7; color: #92400e; }
    .break-status.not-on-break { background: #e2e3e5; color: #41464b; }
    .break-status.returned { background: #d1fae5; color: #065f46; }

    /* ===== PHOTO GALLERY IN PROFILE SECTION ===== */
    .profile-section {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        margin-bottom: 20px;
    }
    .profile-section .profile-header {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 20px;
    }
    .profile-section .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e5e7eb;
    }
    .profile-section .profile-avatar-wrapper {
        position: relative;
        display: inline-block;
    }
    .profile-section .profile-avatar-wrapper .status-dot {
        position: absolute;
        bottom: 4px;
        right: 4px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid #fff;
    }
    .profile-section .profile-avatar-wrapper .status-dot.present { background: #28a745; }
    .profile-section .profile-avatar-wrapper .status-dot.absent { background: #dc3545; }
    .profile-section .profile-avatar-wrapper .status-dot.half-day { background: #f59e0b; }
    .profile-section .profile-avatar-wrapper .status-dot.week-off { background: #0dcaf0; }
    .profile-section .profile-avatar-wrapper .status-dot.holiday { background: #0d6efd; }

    .profile-section .profile-name { font-size: 20px; font-weight: 600; margin: 0; }
    .profile-section .profile-designation { color: #6b7280; font-size: 14px; margin: 0; }
    .profile-section .profile-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }

    /* Photo Gallery Grid */
    .photo-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 12px;
        margin-top: 15px;
    }
    .photo-gallery .photo-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
        aspect-ratio: 1;
        background: #f9fafb;
        cursor: pointer;
    }
    .photo-gallery .photo-item:hover {
        transform: scale(1.03);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-color: #667eea;
        z-index: 10;
    }
    .photo-gallery .photo-item img { width: 100%; height: 100%; object-fit: cover; }
    .photo-gallery .photo-item .photo-label {
        position: absolute; bottom: 0; left: 0; right: 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.7));
        color: white; padding: 8px 10px; font-size: 10px; font-weight: 600;
        text-align: center; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .photo-gallery .photo-item .photo-verified {
        position: absolute; top: 8px; right: 8px; background: rgba(0, 184, 148, 0.9);
        color: white; border-radius: 50%; width: 24px; height: 24px;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; border: 2px solid #fff;
    }
    .photo-gallery .photo-item .photo-failed {
        position: absolute; top: 8px; right: 8px; background: rgba(225, 112, 85, 0.9);
        color: white; border-radius: 50%; width: 24px; height: 24px;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; border: 2px solid #fff;
    }
    .photo-gallery .photo-item .photo-source {
        position: absolute; top: 8px; left: 8px; font-size: 14px; background: rgba(0,0,0,0.5);
        border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center;
        justify-content: center; border: 2px solid rgba(255,255,255,0.3);
    }
    .photo-gallery .photo-item .photo-time {
        position: absolute; bottom: 30px; left: 0; right: 0; color: rgba(255,255,255,0.8);
        font-size: 9px; text-align: center; text-shadow: 0 1px 3px rgba(0,0,0,0.5);
    }

    .photo-gallery-empty { text-align: center; padding: 30px; color: #6b7280; }
    .photo-gallery-empty i { font-size: 48px; color: #d1d5db; display: block; margin-bottom: 10px; }

    /* Modal for photo view */
    .photo-view-modal .modal-body {
        padding: 0; display: flex; align-items: center; justify-content: center;
        min-height: 400px; background: #1a1a2e;
    }
    .photo-view-modal .modal-body img { max-width: 100%; max-height: 70vh; object-fit: contain; }
    .photo-view-modal .modal-footer { background: #1a1a2e; border: none; justify-content: center; color: #fff; }
    .photo-view-modal .modal-footer .photo-info { display: flex; gap: 20px; align-items: center; flex-wrap: wrap; color: #fff; }
    .photo-view-modal .modal-footer .photo-info .label { color: #9ca3af; font-size: 11px; }
    .photo-view-modal .modal-footer .photo-info .value { font-size: 13px; font-weight: 500; }

    /* Half Day Stats Card */
    .half-day-stats-card { border-left: 4px solid #f59e0b !important; }
    .half-day-stats-card .card-body { background: linear-gradient(135deg, #fef3c7 0%, #fff 100%); }

    /* Stats Cards */
    .stats-card { transition: all 0.3s ease; }
    .stats-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

    /* Source & Verification Badges */
    .source-badge { font-size: 7px; padding: 1px 5px; border-radius: 8px; white-space: nowrap; display: inline-block; }
    .source-badge.web { background: #e8f0fe; color: #1967d2; }
    .source-badge.flutter { background: #e0f7fa; color: #00695c; }
    .source-badge.manual { background: #f5f5f5; color: #616161; }
    .verification-badge { font-size: 7px; padding: 1px 6px; border-radius: 8px; white-space: nowrap; display: inline-block; }
    .verification-badge.verified { background: #d1fae5; color: #065f46; }
    .verification-badge.failed { background: #fee2e2; color: #991b1b; }
    .verification-badge.mobile { background: #dbeafe; color: #1e40af; }
    .verification-badge.manual { background: #f3f4f6; color: #374151; }

    /* Initials Avatar */
    .avatar-initials {
        width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center;
        justify-content: center; font-size: 16px; font-weight: 600; color: #667eea;
        background: #f0f2ff; border: 2px solid #667eea; text-transform: uppercase;
        transition: all 0.3s ease; flex-shrink: 0;
    }
    .avatar-initials:hover { transform: scale(1.05); box-shadow: 0 0 15px rgba(102, 126, 234, 0.2); }

    /* Prevent image reload loops */
    .avatar-wrapper img { transition: opacity 0.3s ease; background: #f8f9fa; }
    .avatar-wrapper img[src*="user-avatar.png"] { opacity: 0.8; }
    .avatar-wrapper img:not([src]) { opacity: 0; }

    .loading-overlay { position: relative; }
    .loading-overlay::after {
        content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255,255,255,0.7); z-index: 10;
        display: flex; align-items: center; justify-content: center;
    }

    @media (max-width: 768px) {
        .table th, .table td { font-size: 0.7rem; }
        .avatar-wrapper .photo-indicator { width: 16px; height: 16px; font-size: 7px; }
        .photo-type-badge { font-size: 7px; padding: 1px 5px; }
        .avatar-initials { width: 32px; height: 32px; font-size: 12px; }
        .photo-gallery { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
        .profile-section .profile-header { flex-direction: column; text-align: center; }
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ti ti-device-tv me-2"></i> Live Attendance
                </h5>
                <div>
                    <span class="badge bg-primary rounded-pill px-3 py-2 me-2" id="totalStaffBadge">
                        <i class="ti ti-users me-1"></i> 
                        <span id="totalStaffCount">{{ $employeeStatuses->count() }}</span>
                        <span class="text-muted" style="font-weight: 400; font-size: 0.7rem;">
                            ({{ $statusCounts['all'] ?? 0 }} total)
                        </span>
                    </span>
                    <span class="badge bg-info rounded-pill px-3 py-2">
                        <i class="ti ti-calendar me-1"></i> {{ date('d M Y') }}
                    </span>
                    <span class="badge bg-success rounded-pill px-3 py-2 ms-2" id="liveIndicator">
                        <i class="ti ti-circle-filled" style="font-size: 8px; color: #00ff00;"></i> Live
                    </span>
                </div>
            </div>

            {{-- ===== STATS CARDS ===== --}}
            <div class="card-body pb-0">
                <div class="row g-3" id="statsCards">
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="card stats-card bg-primary bg-opacity-10 border-0 shadow-sm">
                            <div class="card-body p-3 text-center">
                                <i class="ti ti-users text-primary fs-2"></i>
                                <h6 class="mt-2 mb-0 stats-count" data-stat="all">{{ $statusCounts['all'] ?? 0 }}</h6>
                                <small class="text-muted">Total Staff</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="card stats-card bg-success bg-opacity-10 border-0 shadow-sm">
                            <div class="card-body p-3 text-center">
                                <i class="ti ti-clock-check text-success fs-2"></i>
                                <h6 class="mt-2 mb-0 stats-count" data-stat="in">{{ $statusCounts['in'] ?? 0 }}</h6>
                                <small class="text-muted">Clocked In</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="card stats-card bg-secondary bg-opacity-10 border-0 shadow-sm">
                            <div class="card-body p-3 text-center">
                                <i class="ti ti-clock-off text-secondary fs-2"></i>
                                <h6 class="mt-2 mb-0 stats-count" data-stat="out">{{ $statusCounts['out'] ?? 0 }}</h6>
                                <small class="text-muted">Clocked Out</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="card stats-card bg-warning bg-opacity-10 border-0 shadow-sm">
                            <div class="card-body p-3 text-center">
                                <i class="ti ti-alert-triangle text-warning fs-2"></i>
                                <h6 class="mt-2 mb-0 stats-count" data-stat="late">{{ $statusCounts['late'] ?? 0 }}</h6>
                                <small class="text-muted">Late</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="card stats-card bg-info bg-opacity-10 border-0 shadow-sm">
                            <div class="card-body p-3 text-center">
                                <i class="ti ti-coffee text-info fs-2"></i>
                                <h6 class="mt-2 mb-0 stats-count" data-stat="break">{{ $statusCounts['break'] ?? 0 }}</h6>
                                <small class="text-muted">On Break</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="card stats-card bg-dark bg-opacity-10 border-0 shadow-sm">
                            <div class="card-body p-3 text-center">
                                <i class="ti ti-hourglass-empty text-dark fs-2"></i>
                                <h6 class="mt-2 mb-0 stats-count" data-stat="early_leave">{{ $statusCounts['early_leave'] ?? 0 }}</h6>
                                <small class="text-muted">Early Leave</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== HALF DAY & VERIFICATION STATS ROW ===== --}}
                <div class="row g-3 mt-1">
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card half-day-stats-card border-0 shadow-sm stats-card">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="me-3">
                                    <i class="ti ti-hourglass-half text-warning fs-2"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0" id="halfDayTotalCount">
                                        {{ ($statusCounts['half_day'] ?? 0) + ($statusCounts['half_day_live'] ?? 0) }}
                                    </h6>
                                    <small class="text-muted">Total Half Day</small>
                                </div>
                                <div class="ms-auto">
                                    <span class="badge bg-warning rounded-pill" id="halfDayLiveBadge" style="{{ ($statusCounts['half_day_live'] ?? 0) > 0 ? '' : 'display:none;' }}">
                                        <span class="half-day-indicator active"></span>
                                        <span id="halfDayLiveCount">{{ $statusCounts['half_day_live'] ?? 0 }}</span> Live
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm stats-card" style="border-left: 4px solid #6c5ce7 !important;">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="me-3">
                                    <i class="ti ti-face-id" style="color: #6c5ce7; font-size: 1.5rem;"></i>
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
                                    <i class="ti ti-devices text-primary" style="font-size: 1.5rem;"></i>
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
                                    <i class="ti ti-check-circle text-success" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">
                                        <span class="badge bg-face-id-success">✅ {{ $statusCounts['verified'] ?? 0 }}</span>
                                        <span class="badge bg-face-id-failed">❌ {{ $statusCounts['unverified'] ?? 0 }}</span>
                                    </h6>
                                    <small class="text-muted">Verified / Failed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== FILTERS ===== --}}
            <div class="card-body pt-3">
                <div class="d-flex flex-wrap gap-2 mb-3" id="statusFilter">
                    @php
                        $statusList = [
                            'all' => ['label' => 'All', 'icon' => 'ti ti-list', 'class' => 'btn-primary'],
                            'in' => ['label' => 'In', 'icon' => 'ti ti-clock-check', 'class' => 'btn-success'],
                            'out' => ['label' => 'Out', 'icon' => 'ti ti-clock-off', 'class' => 'btn-secondary'],
                            'not_punched' => ['label' => 'No Punch', 'icon' => 'ti ti-alert-circle', 'class' => 'btn-warning'],
                            'break' => ['label' => 'Break', 'icon' => 'ti ti-coffee', 'class' => 'btn-info'],
                            'late' => ['label' => 'Late', 'icon' => 'ti ti-alert-triangle', 'class' => 'btn-warning'],
                            'early_leave' => ['label' => 'Early Leave', 'icon' => 'ti ti-hourglass-empty', 'class' => 'btn-dark'],
                            'half_day' => ['label' => '🌓 Half Day', 'icon' => 'ti ti-hourglass-half', 'class' => 'btn-warning'],
                            'verified' => ['label' => '✅ Verified', 'icon' => 'ti ti-check-circle', 'class' => 'btn-success'],
                            'unverified' => ['label' => '❌ Unverified', 'icon' => 'ti ti-x-circle', 'class' => 'btn-danger'],
                            'web' => ['label' => '🌐 Web', 'icon' => 'ti ti-globe', 'class' => 'btn-primary'],
                            'flutter' => ['label' => '📱 Mobile', 'icon' => 'ti ti-phone', 'class' => 'btn-info'],
                        ];
                    @endphp
                    @foreach($statusList as $key => $data)
                        <a href="#" 
                           class="btn btn-sm status-filter-btn {{ request('status', 'all') == $key ? $data['class'] : 'btn-outline-'.str_replace('btn-', '', $data['class']) }}"
                           data-status="{{ $key }}">
                            <i class="{{ $data['icon'] }} me-1"></i>
                            {{ $data['label'] }}
                            <span class="badge bg-light text-dark ms-1 status-badge" data-status="{{ $key }}">{{ $statusCounts[$key] ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>

                <form id="filterForm" class="row g-2 align-items-end" onsubmit="return false;">
                    <div class="col-md-3">
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
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Shift</label>
                        <select class="form-select form-select-sm" name="shift" id="shiftFilter">
                            <option value="">All Shifts</option>
                            @foreach($shifts ?? [] as $shift)
                                <option value="{{ $shift->id }}" {{ request('shift') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Search Staff</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" name="search" id="searchStaff"
                                   placeholder="Name..." value="{{ request('search') }}" autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="status" id="hiddenStatus" value="{{ request('status', 'all') }}">
                </form>
            </div>

            {{-- ===== STAFF LIST ===== --}}
            <div class="card-body pt-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small" id="staffListInfo">
                        Showing <strong id="visibleStaffCount">{{ $employeeStatuses->count() }}</strong> of <strong id="totalStaffCountDisplay">{{ $statusCounts['all'] ?? 0 }}</strong> employees
                        @if(request('status') == 'in')
                            <span class="badge bg-success ms-2">Includes late employees</span>
                        @endif
                        @if(request('status') == 'half_day')
                            <span class="badge bg-warning ms-2">🌓 Half Day employees</span>
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
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2" id="refreshBtn">
                            <i class="ti ti-refresh me-1"></i> Refresh
                        </button>
                        <a href="{{ route('attendance.live') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-rotate-clockwise me-1"></i> Reset
                        </a>
                    </div>
                </div>

                <div id="staffListContainer">
                    @if($employeeStatuses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="staffTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Employee / Photo</th>
                                        <th>Photo Type</th>
                                        <th>Phone</th>
                                        <th>Employee ID</th>
                                        <th>Job Title</th>
                                        <th>Status</th>
                                        <th>Source</th>
                                        <th>Verification</th>
                                        <th>Clock In</th>
                                        <th>Clock Out</th>
                                        <th>Break Out</th>
                                        <th>Break In</th>
                                        <th>Earliest</th>
                                        <th>Worked Hours</th>
                                        <th>Half Day</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="staffTableBody">
                                    @foreach($employeeStatuses as $index => $item)
                                        @php
                                            $user = $item->user;
                                            $employee = $item->employee;
                                            $attendance = $item->attendance;
                                            $status = $item->status;
                                            $isLate = $item->isLate ?? false;
                                            $isEarlyLeave = $item->isEarlyLeave ?? false;
                                            $isLive = $item->isLive ?? false;
                                            $isClockedIn = $item->isClockedIn ?? false;
                                            $isHalfDay = $item->isHalfDay ?? false;
                                            $halfDayThreshold = $item->half_day_threshold ?? 4.0;
                                            
                                            // Calculate worked hours display
                                            $workedHoursDisplay = '--:--';
                                            if ($attendance && $attendance->clock_in != '00:00:00') {
                                                $start = \Carbon\Carbon::parse($attendance->clock_in);
                                                $end = ($attendance->clock_out != '00:00:00') ? \Carbon\Carbon::parse($attendance->clock_out) : \Carbon\Carbon::now();
                                                $diff = $start->diff($end);
                                                $workedHoursDisplay = $diff->format('%H:%I');
                                            }
                                            
                                            // ============================================================
                                            // SOURCE & VERIFICATION DATA
                                            // ============================================================
                                            $source = $attendance ? ($attendance->source ?? 'manual') : 'none';
                                            $isVerified = $attendance ? ($attendance->is_verified ?? false) : false;
                                            $faceConfidence = $attendance ? ($attendance->face_confidence ?? null) : null;
                                            $isFaceId = $attendance && $attendance->marked_by == 'face_recognition';
                                            
                                            $sourceIcon = match($source) { 'web' => '🌐', 'flutter' => '📱', 'manual' => '📝', default => '❓' };
                                            $sourceLabel = match($source) { 'web' => 'Web', 'flutter' => 'Flutter', 'manual' => 'Manual', default => ucfirst($source) };
                                            $sourceClass = match($source) { 'web' => 'web', 'flutter' => 'flutter', 'manual' => 'manual', default => 'manual' };
                                            
                                            // Verification badge
                                            if ($source === 'web' && $faceConfidence !== null) {
                                                $verificationBadge = $isVerified ? '✅ Verified ' . round($faceConfidence) . '%' : '❌ Failed ' . round($faceConfidence) . '%';
                                                $verificationClass = $isVerified ? 'verified' : 'failed';
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
                                            
                                            // ============================================================
                                            // PHOTO HANDLING - WITH FACE ID SUPPORT
                                            // ============================================================
                                            $finalPhotoUrl = asset('assets/img/user-avatar.png');
                                            $hasRealPhoto = false;
                                            $photoLabel = 'Profile';
                                            $photoType = 'profile';
                                            $photoCount = 0;
                                            $isFaceIdPhoto = false;
                                            
                                            // Collect all photos for gallery
                                            $photos = [];
                                            
                                            if ($attendance) {
                                                $photoFields = [
                                                    'punch_photo' => ['label' => 'Clock In', 'type' => 'clock-in', 'time' => $attendance->clock_in],
                                                    'break_in_photo' => ['label' => 'Break In', 'type' => 'break-in', 'time' => $attendance->tea_break_in],
                                                    'break_out_photo' => ['label' => 'Break Out', 'type' => 'break-out', 'time' => $attendance->tea_break_out],
                                                    'punch_out_photo' => ['label' => 'Punch Out', 'type' => 'punch-out', 'time' => $attendance->clock_out],
                                                ];
                                                
                                                foreach ($photoFields as $field => $info) {
                                                    if (!empty($attendance->$field)) {
                                                        $path = 'uploads/attendance/' . $attendance->$field;
                                                        $fullPath = public_path($path);
                                                        if (file_exists($fullPath)) {
                                                            $photos[] = [
                                                                'url' => asset($path),
                                                                'label' => $info['label'],
                                                                'type' => $info['type'],
                                                                'time' => $info['time'] && $info['time'] != '00:00:00' ? \Carbon\Carbon::parse($info['time'])->format('h:i A') : null,
                                                                'is_face_verified' => $isFaceId && $isVerified,
                                                                'is_face_failed' => $isFaceId && !$isVerified,
                                                                'source' => $source,
                                                                'source_icon' => $sourceIcon,
                                                            ];
                                                            if (!$hasRealPhoto) {
                                                                $finalPhotoUrl = asset($path);
                                                                $hasRealPhoto = true;
                                                                $photoLabel = $info['label'];
                                                                $photoType = $info['type'];
                                                            }
                                                            $photoCount++;
                                                        }
                                                    }
                                                }
                                            }
                                            
                                            if (!$hasRealPhoto && $user) {
                                                $avatarFile = $user->avatar ?? 'avatar.png';
                                                if ($avatarFile && $avatarFile !== 'avatar.png' && $avatarFile !== 'user-avatar.png') {
                                                    $avatarPath = public_path('uploads/avatar/' . $avatarFile);
                                                    if (file_exists($avatarPath)) {
                                                        $finalPhotoUrl = asset('uploads/avatar/' . $avatarFile);
                                                        $hasRealPhoto = true;
                                                        $photoLabel = 'Profile';
                                                        $photoType = 'profile';
                                                        $photoCount = 1;
                                                    }
                                                }
                                            }
                                            
                                            if (!$hasRealPhoto && $employee && !empty($employee->avatar)) {
                                                $avatarFile = $employee->avatar;
                                                if ($avatarFile && $avatarFile !== 'avatar.png' && $avatarFile !== 'user-avatar.png') {
                                                    $avatarPath = public_path('uploads/avatar/' . $avatarFile);
                                                    if (file_exists($avatarPath)) {
                                                        $finalPhotoUrl = asset('uploads/avatar/' . $avatarFile);
                                                        $hasRealPhoto = true;
                                                        $photoLabel = 'Profile';
                                                        $photoType = 'profile';
                                                        $photoCount = 1;
                                                    }
                                                }
                                            }
                                            
                                            $userInitial = '';
                                            $userName = $user->name ?? $employee->name ?? 'User';
                                            $nameParts = explode(' ', $userName);
                                            foreach ($nameParts as $part) {
                                                if (!empty($part)) {
                                                    $userInitial .= strtoupper(substr($part, 0, 1));
                                                }
                                            }
                                            $userInitial = substr($userInitial, 0, 2);
                                            
                                            $photoUrlWithCache = $hasRealPhoto ? $finalPhotoUrl . '?v=' . time() : $finalPhotoUrl;
                                            
                                            $indicatorType = $photoType;
                                            if ($isFaceId && $hasRealPhoto) {
                                                $indicatorType = $isVerified ? 'face-id-success' : 'face-id-failed';
                                            } elseif ($isHalfDay) {
                                                $indicatorType = 'half-day';
                                            }
                                            
                                            // ===== TEA BREAK TIMES =====
                                            $breakOutTime = '--:-- --';
                                            $breakInTime = '--:-- --';
                                            $isOnBreak = false;
                                            
                                            if ($attendance) {
                                                if ($attendance->tea_break_out && $attendance->tea_break_out != '00:00:00') {
                                                    $breakOutTime = \Carbon\Carbon::parse($attendance->tea_break_out)->format('h:i A');
                                                    $isOnBreak = true;
                                                }
                                                if ($attendance->tea_break_in && $attendance->tea_break_in != '00:00:00') {
                                                    $breakInTime = \Carbon\Carbon::parse($attendance->tea_break_in)->format('h:i A');
                                                    if ($attendance->tea_break_out && $attendance->tea_break_out != '00:00:00') {
                                                        $isOnBreak = false;
                                                    }
                                                }
                                            }

                                            // ===== BADGE COLOR =====
                                            $badgeColor = match($status) {
                                                'in' => 'success',
                                                'late' => 'warning',
                                                'out' => 'secondary',
                                                'early_leave' => 'dark',
                                                'break' => 'info',
                                                'not_punched' => 'warning',
                                                'half_day' => 'half-day',
                                                default => 'secondary',
                                            };
                                            
                                            // ===== STATUS LABEL =====
                                            $statusLabel = match($status) {
                                                'in' => 'Clocked In',
                                                'late' => 'Late In',
                                                'out' => 'Clocked Out',
                                                'early_leave' => 'Early Leave',
                                                'break' => 'On Break',
                                                'not_punched' => 'No Punch',
                                                'half_day' => '🌓 Half Day',
                                                default => ucfirst(str_replace('_', ' ', $status)),
                                            };
                                            
                                            // ===== EARLIEST (Clock In Time) =====
                                            $earliest = '--:-- --';
                                            $lateDuration = '';
                                            if ($attendance && $attendance->clock_in != '00:00:00') {
                                                $earliest = \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A');
                                                if ($isLate) {
                                                    $startTime = \App\Models\Utility::getValByName('company_start_time') ?? '09:00:00';
                                                    $clockIn = \Carbon\Carbon::parse($attendance->clock_in);
                                                    $start = \Carbon\Carbon::parse($startTime);
                                                    $diff = $start->diff($clockIn);
                                                    $lateDuration = ' (Late by ' . $diff->format('%Hh %Im') . ')';
                                                }
                                            }

                                            // ===== HALF DAY PROGRESS =====
                                            $halfDayProgress = 0;
                                            $halfDayStatus = 'Not Half Day';
                                            $halfDayClass = '';
                                            if ($attendance && $attendance->clock_in != '00:00:00') {
                                                $clockInTime = strtotime($attendance->clock_in);
                                                $currentTime = $attendance->clock_out != '00:00:00' ? strtotime($attendance->clock_out) : time();
                                                $workedSeconds = $currentTime - $clockInTime;
                                                $workedHoursFloat = $workedSeconds / 3600;
                                                
                                                if ($workedHoursFloat > 0 && $halfDayThreshold > 0) {
                                                    $halfDayProgress = min(100, ($workedHoursFloat / $halfDayThreshold) * 100);
                                                }
                                                
                                                if ($isHalfDay) {
                                                    $halfDayStatus = '🌓 Half Day';
                                                    $halfDayClass = 'bg-warning text-dark';
                                                } elseif ($workedHoursFloat >= $halfDayThreshold) {
                                                    $halfDayStatus = '✅ Full Day';
                                                    $halfDayClass = 'bg-success text-white';
                                                } elseif ($workedHoursFloat > 0) {
                                                    $halfDayStatus = '⏳ In Progress';
                                                    $halfDayClass = 'bg-info text-white';
                                                }
                                            }
                                        @endphp
                                        <tr data-employee-id="{{ $employee->id }}" data-status="{{ $status }}" data-source="{{ $source }}" data-verified="{{ $isVerified ? '1' : '0' }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-wrapper" style="cursor: pointer;" onclick="openProfileGallery({{ $employee->id }})">
                                                        @if($hasRealPhoto)
                                                            <img src="{{ $photoUrlWithCache }}" 
                                                                 alt="{{ $userName }}"
                                                                 class="rounded-circle me-2"
                                                                 style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #e5e7eb;"
                                                                 data-bs-toggle="tooltip" 
                                                                 title="Click to view all photos"
                                                                 loading="lazy"
                                                                 onerror="this.onerror=null; this.src='{{ asset('assets/img/user-avatar.png') }}'">
                                                        @else
                                                            <div class="avatar-initials me-2"
                                                                 style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 600; color: #667eea; background: #f0f2ff; border: 2px solid #667eea;"
                                                                 data-bs-toggle="tooltip" 
                                                                 title="{{ $userName }}">
                                                                {{ $userInitial }}
                                                            </div>
                                                        @endif
                                                        
                                                        @if($hasRealPhoto || $isHalfDay)
                                                            <span class="photo-indicator {{ $indicatorType }}" 
                                                                  data-bs-toggle="tooltip" 
                                                                  title="{{ $isFaceId ? ($isVerified ? '✅ Face ID Verified ' . round($faceConfidence) . '%' : '❌ Face ID Failed ' . round($faceConfidence) . '%') : ($isHalfDay ? 'Half Day' : $photoLabel) }} Photo">
                                                                @if($isFaceId)
                                                                    <i class="ti ti-face-id"></i>
                                                                @elseif($isHalfDay)
                                                                    <i class="ti ti-hourglass-half"></i>
                                                                @else
                                                                    <i class="ti ti-camera"></i>
                                                                @endif
                                                            </span>
                                                        @endif
                                                        
                                                        @if($isFaceId)
                                                            <span class="badge {{ $isVerified ? 'bg-face-id-success' : 'bg-face-id-failed' }} ms-1" style="font-size: 8px; padding: 2px 6px;">
                                                                <i class="ti ti-face-id me-1"></i>
                                                                @if($isVerified)
                                                                    ✅ {{ round($faceConfidence) }}%
                                                                @else
                                                                    ❌ {{ round($faceConfidence) }}%
                                                                @endif
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <span>{{ $userName }}</span>
                                                        @if($photoCount > 1)
                                                            <br>
                                                            <span class="badge bg-info" style="font-size: 8px; padding: 2px 6px;">
                                                                <i class="ti ti-camera me-1"></i> {{ $photoCount }} photos
                                                            </span>
                                                        @endif
                                                        @if($isLive)
                                                            <br>
                                                            <span class="badge bg-success rounded-pill" style="font-size: 8px; padding: 2px 6px;">
                                                                <i class="ti ti-circle-filled" style="font-size: 6px;"></i> Live
                                                            </span>
                                                        @endif
                                                        @if($isOnBreak)
                                                            <br>
                                                            <span class="badge bg-warning rounded-pill" style="font-size: 8px; padding: 2px 6px;">
                                                                <i class="ti ti-coffee" style="font-size: 6px;"></i> Break
                                                            </span>
                                                        @endif
                                                        @if($isHalfDay && $isLive)
                                                            <br>
                                                            <span class="badge bg-half-day-live rounded-pill" style="font-size: 8px; padding: 2px 6px;">
                                                                <span class="half-day-indicator active"></span> Half Day
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($hasRealPhoto)
                                                    <span class="photo-type-badge {{ $photoType }}">
                                                        <i class="ti ti-camera me-1"></i>
                                                        {{ $photoLabel }}
                                                        @if($isFaceId)
                                                            <span class="badge {{ $isVerified ? 'bg-face-id-success' : 'bg-face-id-failed' }} ms-1" style="font-size: 8px;">
                                                                <i class="ti ti-face-id"></i>
                                                                {{ $isVerified ? '✅' : '❌' }}
                                                            </span>
                                                        @endif
                                                    </span>
                                                    @if($photoCount > 1)
                                                        <br>
                                                        <small class="text-muted">+{{ $photoCount - 1 }} more</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted" style="font-size: 11px;">No Photo</span>
                                                @endif
                                            </td>
                                            <td>{{ $employee->phone ?? '-' }}</td>
                                            <td>{{ $employee->employee_id }}</td>
                                            <td>{{ $employee->designation->name ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $badgeColor }} rounded-pill px-3 py-1">
                                                    <i class="ti ti-circle-filled me-1" style="font-size: 8px;"></i>
                                                    {{ $statusLabel }}
                                                    @if($isLate && $status == 'in')
                                                        <span class="ms-1">(Late)</span>
                                                    @endif
                                                    @if($isFaceId && $isVerified)
                                                        <span class="badge bg-face-id-success ms-1" style="font-size: 8px;">
                                                            <i class="ti ti-check-circle"></i> 
                                                            {{ round($faceConfidence) }}%
                                                        </span>
                                                    @endif
                                                </span>
                                                @if($isHalfDay)
                                                    <span class="half-day-threshold-info">
                                                        <i class="ti ti-hourglass-half me-1"></i>
                                                        {{ $halfDayThreshold }}hrs threshold
                                                    </span>
                                                @endif
                                            </td>
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
                                            <td>
                                                @if($attendance && $source !== 'none')
                                                    <span class="verification-badge {{ $verificationClass }}">
                                                        {{ $verificationBadge }}
                                                        @if($source === 'web' && $faceConfidence !== null)
                                                            <br>
                                                            <small class="text-muted" style="font-size: 7px;">
                                                                Confidence: {{ round($faceConfidence) }}%
                                                            </small>
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="text-muted">--</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($attendance && $attendance->clock_in != '00:00:00')
                                                    {{ \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') }}
                                                    @if(isset($attendance->punch_photo) && !empty($attendance->punch_photo))
                                                        <i class="ti ti-camera text-primary ms-1" data-bs-toggle="tooltip" title="Has Clock In Photo"></i>
                                                    @endif
                                                    @if($isFaceId)
                                                        <i class="ti ti-face-id {{ $isVerified ? 'text-success' : 'text-danger' }} ms-1" data-bs-toggle="tooltip" title="{{ $isVerified ? 'Face ID Verified' : 'Face ID Failed' }}"></i>
                                                    @endif
                                                    @if($source === 'web')
                                                        <i class="ti ti-globe text-primary ms-1" data-bs-toggle="tooltip" title="Web Punch In"></i>
                                                    @elseif($source === 'flutter')
                                                        <i class="ti ti-phone text-info ms-1" data-bs-toggle="tooltip" title="Mobile Punch In"></i>
                                                    @endif
                                                @else
                                                    <span class="text-muted">--:-- --</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($attendance && $attendance->clock_out != '00:00:00')
                                                    {{ \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') }}
                                                    @if(isset($attendance->punch_out_photo) && !empty($attendance->punch_out_photo))
                                                        <i class="ti ti-camera text-danger ms-1" data-bs-toggle="tooltip" title="Has Punch Out Photo"></i>
                                                    @endif
                                                    @if($attendance->source === 'web' && $isVerified)
                                                        <i class="ti ti-check-circle text-success ms-1" data-bs-toggle="tooltip" title="Face Verified"></i>
                                                    @endif
                                                @else
                                                    <span class="text-muted">--:-- --</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($attendance && $attendance->tea_break_out && $attendance->tea_break_out != '00:00:00')
                                                    <span class="break-status {{ $isOnBreak ? 'on-break' : 'returned' }}">
                                                        {{ $breakOutTime }}
                                                        @if(isset($attendance->break_out_photo) && !empty($attendance->break_out_photo))
                                                            <i class="ti ti-camera text-purple ms-1" data-bs-toggle="tooltip" title="Has Break Out Photo"></i>
                                                        @endif
                                                        @if($isOnBreak)
                                                            <i class="ti ti-coffee ms-1"></i>
                                                        @else
                                                            <i class="ti ti-check text-success ms-1"></i>
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="text-muted">--:-- --</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($attendance && $attendance->tea_break_in && $attendance->tea_break_in != '00:00:00')
                                                    <span class="break-status returned">
                                                        {{ $breakInTime }}
                                                        @if(isset($attendance->break_in_photo) && !empty($attendance->break_in_photo))
                                                            <i class="ti ti-camera text-warning ms-1" data-bs-toggle="tooltip" title="Has Break In Photo"></i>
                                                        @endif
                                                        <i class="ti ti-check text-success ms-1"></i>
                                                    </span>
                                                @else
                                                    <span class="text-muted">--:-- --</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-bold">{{ $earliest }}</span>
                                                @if($isLate && $attendance && $attendance->clock_in != '00:00:00')
                                                    <br>
                                                    <small class="text-danger">
                                                        <i class="ti ti-alert-triangle"></i> {{ $lateDuration }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $workedHoursDisplay }}</span>
                                                @if($isHalfDay && $attendance && $attendance->clock_in != '00:00:00')
                                                    <br>
                                                    <small class="text-warning">
                                                        <i class="ti ti-hourglass-half"></i> 
                                                        {{ number_format($halfDayProgress, 0) }}% of {{ $halfDayThreshold }}hrs
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($attendance && $attendance->clock_in != '00:00:00')
                                                    <div class="d-flex flex-column align-items-start">
                                                        <div class="progress" style="width: 100%; height: 4px;">
                                                            <div class="progress-bar {{ $isHalfDay ? 'bg-warning' : 'bg-success' }}" 
                                                                 role="progressbar" 
                                                                 style="width: {{ min(100, $halfDayProgress) }}%;" 
                                                                 aria-valuenow="{{ min(100, $halfDayProgress) }}" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <span class="badge {{ $halfDayClass }} mt-1" style="font-size: 9px;">
                                                            {{ $halfDayStatus }}
                                                            @if($isHalfDay)
                                                                <span class="half-day-indicator active"></span>
                                                            @endif
                                                        </span>
                                                        <small class="text-muted" style="font-size: 8px;">
                                                            Threshold: {{ $halfDayThreshold }}hrs
                                                        </small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">--</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="openProfileGallery({{ $employee->id }})" title="View Photos">
                                                    <i class="ti ti-photo"></i>
                                                </button>
                                                @if($isHalfDay)
                                                    <button class="btn btn-sm btn-outline-warning mt-1" title="Half Day (Threshold: {{ $halfDayThreshold }}hrs)" data-bs-toggle="tooltip" data-bs-placement="top">
                                                        <i class="ti ti-hourglass-half"></i>
                                                    </button>
                                                @endif
                                                @if($isFaceId)
                                                    <button class="btn btn-sm {{ $isVerified ? 'btn-outline-success' : 'btn-outline-danger' }} mt-1" 
                                                            title="{{ $isVerified ? 'Face ID Verified ' . round($faceConfidence) . '%' : 'Face ID Failed ' . round($faceConfidence) . '%' }}" 
                                                            data-bs-toggle="tooltip" 
                                                            data-bs-placement="top">
                                                        <i class="ti ti-face-id"></i>
                                                        @if($isVerified)
                                                            ✅
                                                        @else
                                                            ❌
                                                        @endif
                                                    </button>
                                                @endif
                                                @if($source === 'web')
                                                    <button class="btn btn-sm btn-outline-primary mt-1" title="Web Source" data-bs-toggle="tooltip" data-bs-placement="top">
                                                        <i class="ti ti-globe"></i>
                                                    </button>
                                                @elseif($source === 'flutter')
                                                    <button class="btn btn-sm btn-outline-info mt-1" title="Mobile Source" data-bs-toggle="tooltip" data-bs-placement="top">
                                                        <i class="ti ti-phone"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-user-off display-4 text-muted"></i>
                            <p class="mt-3 text-muted">No employees matched your filters.</p>
                            <p class="text-muted small">
                                <a href="{{ route('attendance.live') }}" class="btn btn-sm btn-primary mt-2">
                                    <i class="ti ti-refresh me-1"></i> Reset Filters
                                </a>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- ===== PHOTO GALLERY MODAL (Profile Section) ===== -->
<!-- ============================================================ -->
<div class="modal fade photo-view-modal" id="profileGalleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #1a1a2e; border: none;">
                <h5 class="modal-title text-white">
                    <i class="ti ti-photo me-2"></i>
                    <span id="galleryEmployeeName">Employee Photos</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="galleryContent" style="background: #1a1a2e; min-height: 400px;">
                <div class="text-center py-5">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-light">Loading photos...</p>
                </div>
            </div>
            <div class="modal-footer" style="background: #1a1a2e; border: none;">
                <div class="photo-info" id="galleryFooterInfo">
                    <span class="text-muted">Click on any photo to view full size</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- ===== SINGLE PHOTO VIEW MODAL ===== -->
<!-- ============================================================ -->
<div class="modal fade photo-view-modal" id="singlePhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="background: transparent; border: none;">
            <div class="modal-body" style="background: #1a1a2e; padding: 0; min-height: 80vh; display: flex; align-items: center; justify-content: center; position: relative;">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close" style="z-index: 10;"></button>
                <img id="singlePhotoImage" src="" alt="Photo" style="max-width: 100%; max-height: 85vh; object-fit: contain;">
                <div id="singlePhotoInfo" class="position-absolute bottom-0 start-0 end-0 p-3" style="background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <span id="singlePhotoLabel" class="fw-bold"></span>
                            <span id="singlePhotoTime" class="ms-2 text-muted"></span>
                        </div>
                        <div class="d-flex gap-2">
                            <span id="singlePhotoSource" class="badge bg-dark"></span>
                            <span id="singlePhotoVerification" class="badge"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // ===== STATUS FILTER BUTTONS =====
        $('.status-filter-btn').on('click', function(e) {
            e.preventDefault();
            let status = $(this).data('status');
            $('#hiddenStatus').val(status);
            
            // Update active state
            $('.status-filter-btn').removeClass('btn-primary btn-success btn-secondary btn-warning btn-info btn-dark btn-danger');
            $('.status-filter-btn').each(function() {
                let $btn = $(this);
                let btnClass = $btn.data('status') === status ? 
                    $btn.data('class') : 
                    'btn-outline-' + $btn.data('class').replace('btn-', '');
                $btn.removeClass().addClass('btn btn-sm ' + btnClass + ' status-filter-btn');
            });
            
            refreshStaffList();
        });

        // ===== FILTER CHANGES =====
        $('#branchFilter, #departmentFilter, #shiftFilter').on('change', function() {
            refreshStaffList();
        });

        // ===== SEARCH =====
        let searchTimer;
        $('#searchStaff').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                refreshStaffList();
            }, 500);
        });

        $('#searchStaff').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                refreshStaffList();
            }
        });

        $('#searchBtn').on('click', function() {
            refreshStaffList();
        });

        // ===== REFRESH BUTTON =====
        $('#refreshBtn').on('click', function() {
            let $this = $(this);
            $this.html('<i class="ti ti-loader ti-spin me-1"></i> Loading...');
            refreshStaffList(function() {
                $this.html('<i class="ti ti-refresh me-1"></i> Refresh');
            });
        });

        // ===== AUTO REFRESH EVERY 30 SECONDS =====
        let refreshInterval = setInterval(function() {
            window.autoRefresh = true;
            refreshStaffList(function() {
                window.autoRefresh = false;
            });
        }, 30000);

        // Stop auto-refresh when page is hidden
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearInterval(refreshInterval);
            } else {
                refreshInterval = setInterval(function() {
                    window.autoRefresh = true;
                    refreshStaffList(function() {
                        window.autoRefresh = false;
                    });
                }, 30000);
            }
        });

        // ===== FUNCTION: Refresh staff list via AJAX =====
        function refreshStaffList(callback) {
            let currentStatus = $('#hiddenStatus').val() || 'all';
            let branch = $('#branchFilter').val() || '';
            let department = $('#departmentFilter').val() || '';
            let search = $('#searchStaff').val() || '';
            
            // Show loading state only for manual refresh
            if (!window.autoRefresh) {
                $('#staffListContainer').html(`
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Updating staff list...</p>
                    </div>
                `);
            }
            
            $.ajax({
                url: '{{ route("attendance.refresh") }}',
                type: 'GET',
                data: {
                    status: currentStatus,
                    branch: branch,
                    department: department,
                    search: search
                },
                success: function(response) {
                    if (response.html) {
                        let $newContent = $(response.html);
                        let $newTableBody = $newContent.find('#staffTableBody');
                        let $newEmptyState = $newContent.find('.text-center.py-5');
                        
                        if ($newTableBody.length) {
                            $('#staffTableBody').html($newTableBody.html());
                        } else if ($newEmptyState.length) {
                            $('#staffListContainer').html($newEmptyState);
                        }
                        
                        // Update status counts
                        if (response.statusCounts) {
                            updateStatusCounts(response.statusCounts);
                        }
                        
                        // Reinitialize tooltips for new content
                        $('[data-bs-toggle="tooltip"]').tooltip();
                        
                        // Update visible staff count
                        let visibleCount = $('#staffTableBody tr').length;
                        let totalCount = $('#totalStaffCount').text();
                        $('#visibleStaffCount').text(visibleCount);
                        $('#totalStaffCountDisplay').text(totalCount);
                    }
                    
                    if (callback) callback();
                },
                error: function(xhr) {
                    console.error('Refresh error:', xhr);
                    if (callback) callback();
                    
                    if (!window.autoRefresh) {
                        $('#staffListContainer').html(`
                            <div class="text-center py-4 text-danger">
                                <i class="ti ti-alert-triangle display-4"></i>
                                <p class="mt-2">Failed to refresh data. Please try again.</p>
                                <button class="btn btn-sm btn-primary mt-2" onclick="location.reload()">
                                    <i class="ti ti-refresh me-1"></i> Reload Page
                                </button>
                            </div>
                        `);
                    }
                }
            });
        }

        // ===== FUNCTION: Update status count badges =====
        function updateStatusCounts(counts) {
            // Update filter badges
            $('.status-badge').each(function() {
                let status = $(this).data('status');
                let count = counts[status] || 0;
                $(this).text(count);
            });
            
            // Update stats cards
            $('.stats-count').each(function() {
                let stat = $(this).data('stat');
                let count = counts[stat] || 0;
                $(this).text(count);
            });
            
            // Update total staff count
            $('#totalStaffCount').text(counts.all || 0);
            $('#totalStaffCountDisplay').text(counts.all || 0);
            
            // Update half day stats
            let halfDayTotal = (counts.half_day || 0) + (counts.half_day_live || 0);
            $('#halfDayTotalCount').text(halfDayTotal);
            
            let liveHalfDay = counts.half_day_live || 0;
            if (liveHalfDay > 0) {
                $('#halfDayLiveBadge').show();
                $('#halfDayLiveCount').text(liveHalfDay);
            } else {
                $('#halfDayLiveBadge').hide();
            }
        }

        // ============================================================
        // ===== PHOTO GALLERY IN PROFILE SECTION =====
        // ============================================================
        window.openProfileGallery = function(employeeId) {
            const modal = new bootstrap.Modal(document.getElementById('profileGalleryModal'));
            const content = document.getElementById('galleryContent');
            const nameSpan = document.getElementById('galleryEmployeeName');
            const footerInfo = document.getElementById('galleryFooterInfo');
            
            // Show loading
            content.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-light">Loading photos...</p>
                </div>
            `;
            footerInfo.innerHTML = `<span class="text-muted">Loading employee info...</span>`;
            
            modal.show();
            
            // Fetch employee details and photos via AJAX
            fetch('{{ route("attendance.details") }}?employee_id=' + employeeId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        content.innerHTML = `
                            <div class="text-center py-5 text-danger">
                                <i class="ti ti-alert-triangle display-4"></i>
                                <p class="mt-2">${data.error}</p>
                            </div>
                        `;
                        footerInfo.innerHTML = `<span class="text-muted">Error loading photos</span>`;
                        return;
                    }
                    
                    // Set employee name
                    nameSpan.textContent = data.employee.name || 'Employee Photos';
                    
                    // Build gallery
                    let html = '';
                    let hasPhotos = false;
                    
                    // Profile header
                    let statusDot = 'absent';
                    if (data.attendance) {
                        if (data.attendance.status === 'Present') statusDot = 'present';
                        else if (data.attendance.status === 'Half Day') statusDot = 'half-day';
                        else if (data.attendance.status === 'Week Off') statusDot = 'week-off';
                        else if (data.attendance.status === 'Holiday') statusDot = 'holiday';
                    }
                    
                    // Show employee profile header with photos count
                    let photoCount = 0;
                    if (data.photos) {
                        Object.keys(data.photos).forEach(key => {
                            if (data.photos[key]) photoCount++;
                        });
                    }
                    
                    html += `
                        <div class="profile-section" style="background: #2a2a3e; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                            <div class="d-flex align-items-center gap-4 flex-wrap">
                                <div class="profile-avatar-wrapper">
                                    <img src="${data.employee.avatar || '{{ asset('assets/img/user-avatar.png') }}'}" 
                                         alt="${data.employee.name}" 
                                         class="profile-avatar"
                                         style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #e5e7eb;">
                                    <span class="status-dot ${statusDot}" style="position: absolute; bottom: 4px; right: 4px; width: 16px; height: 16px; border-radius: 50%; border: 2px solid #fff; background: ${statusDot === 'present' ? '#28a745' : statusDot === 'half-day' ? '#f59e0b' : '#dc3545'};"></span>
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="text-white mb-0">${data.employee.name}</h4>
                                    <p class="text-muted mb-1">${data.employee.designation || ''} ${data.employee.department ? '| ' + data.employee.department : ''}</p>
                                    <div class="d-flex gap-2 flex-wrap mt-2">
                                        <span class="badge bg-info">Employee ID: ${data.employee.employee_id}</span>
                                        ${data.attendance ? `<span class="badge ${data.attendance.status === 'Present' ? 'bg-success' : data.attendance.status === 'Half Day' ? 'bg-warning text-dark' : 'bg-secondary'}">Status: ${data.attendance.status || 'Not Clocked In'}</span>` : ''}
                                        ${data.attendance && data.attendance.source ? `<span class="badge bg-primary">Source: ${data.attendance.source}</span>` : ''}
                                        ${data.attendance && data.attendance.is_verified ? `<span class="badge bg-success">✅ Verified ${data.attendance.face_confidence ? '(' + Math.round(data.attendance.face_confidence) + '%)' : ''}</span>` : ''}
                                        <span class="badge bg-dark">📸 ${photoCount} Photos</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Photos grid
                    if (data.photos && photoCount > 0) {
                        html += `<h5 class="text-white mb-3"><i class="ti ti-photo me-2"></i>Attendance Photos</h5>`;
                        html += `<div class="photo-gallery">`;
                        
                        const photoTypes = [
                            { key: 'punch_in', label: 'Clock In', icon: 'ti ti-login', color: '#0d6efd' },
                            { key: 'break_in', label: 'Break In', icon: 'ti ti-coffee', color: '#f59e0b' },
                            { key: 'break_out', label: 'Break Out', icon: 'ti ti-logout', color: '#8b5cf6' },
                            { key: 'punch_out', label: 'Punch Out', icon: 'ti ti-logout', color: '#dc3545' },
                        ];
                        
                        photoTypes.forEach(type => {
                            if (data.photos && data.photos[type.key]) {
                                hasPhotos = true;
                                const time = data.attendance && data.attendance[type.key.replace('_', '')] 
                                    ? new Date('1970-01-01T' + data.attendance[type.key.replace('_', '')]).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) 
                                    : 'N/A';
                                
                                const isVerified = data.attendance && data.attendance.is_verified;
                                const source = data.attendance ? data.attendance.source : 'manual';
                                const sourceIcon = source === 'web' ? '🌐' : source === 'flutter' ? '📱' : '📝';
                                const isFaceId = data.attendance && data.attendance.marked_by === 'face_recognition';
                                
                                html += `
                                    <div class="photo-item" onclick="viewSinglePhoto('${data.photos[type.key]}', '${type.label}', '${time}', '${source}', '${isVerified}', '${isFaceId ? Math.round(data.attendance.face_confidence || 0) : 0}')">
                                        <img src="${data.photos[type.key]}" alt="${type.label} Photo" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('assets/img/user-avatar.png') }}'">
                                        <div class="photo-label">${type.label}</div>
                                        ${isVerified && isFaceId ? `<div class="photo-verified"><i class="ti ti-check"></i></div>` : ''}
                                        ${!isVerified && isFaceId ? `<div class="photo-failed"><i class="ti ti-x"></i></div>` : ''}
                                        <div class="photo-source">${sourceIcon}</div>
                                        <div class="photo-time">${time}</div>
                                    </div>
                                `;
                            }
                        });
                        
                        html += `</div>`;
                        
                        // If no photos in the specific types, check for any photos
                        if (!hasPhotos) {
                            // Try to show any available photos
                            let anyPhoto = false;
                            for (let key in data.photos) {
                                if (data.photos[key]) {
                                    anyPhoto = true;
                                    html += `
                                        <div class="photo-item" onclick="viewSinglePhoto('${data.photos[key]}', '${key.replace('_', ' ').toUpperCase()}', '', 'manual', 'false', '0')">
                                            <img src="${data.photos[key]}" alt="${key} Photo" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('assets/img/user-avatar.png') }}'">
                                            <div class="photo-label">${key.replace('_', ' ').toUpperCase()}</div>
                                        </div>
                                    `;
                                }
                            }
                            if (anyPhoto) {
                                html += `</div>`;
                                hasPhotos = true;
                            }
                        }
                    }
                    
                    if (!hasPhotos) {
                        html += `
                            <div class="photo-gallery-empty">
                                <i class="ti ti-photo-off"></i>
                                <p>No attendance photos available for this employee</p>
                                <small class="text-muted">Photos are captured during Clock In, Break, and Clock Out</small>
                            </div>
                        `;
                    }
                    
                    content.innerHTML = html;
                    footerInfo.innerHTML = `
                        <span class="text-muted">📸 ${photoCount} photos • Click any photo to view full size</span>
                        ${data.attendance && data.attendance.source ? `<span class="badge ${data.attendance.source === 'web' ? 'bg-primary' : data.attendance.source === 'flutter' ? 'bg-info' : 'bg-secondary'} ms-2">Source: ${data.attendance.source}</span>` : ''}
                        ${data.attendance && data.attendance.is_verified ? `<span class="badge bg-success ms-2">✅ Verified</span>` : ''}
                    `;
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="text-center py-5 text-danger">
                            <i class="ti ti-alert-triangle display-4"></i>
                            <p class="mt-2">Failed to load photos</p>
                            <small class="text-muted">${error.message}</small>
                        </div>
                    `;
                    footerInfo.innerHTML = `<span class="text-muted">Error loading photos</span>`;
                });
        };

        // ============================================================
        // ===== SINGLE PHOTO VIEW =====
        // ============================================================
        window.viewSinglePhoto = function(url, label, time, source, isVerified, confidence) {
            const modal = new bootstrap.Modal(document.getElementById('singlePhotoModal'));
            document.getElementById('singlePhotoImage').src = url;
            document.getElementById('singlePhotoLabel').textContent = label;
            document.getElementById('singlePhotoTime').textContent = time || 'N/A';
            
            const sourceIcon = source === 'web' ? '🌐 Web' : source === 'flutter' ? '📱 Mobile' : '📝 Manual';
            document.getElementById('singlePhotoSource').textContent = sourceIcon;
            document.getElementById('singlePhotoSource').className = `badge ${source === 'web' ? 'bg-primary' : source === 'flutter' ? 'bg-info' : 'bg-secondary'}`;
            
            const verificationEl = document.getElementById('singlePhotoVerification');
            if (isVerified === 'true' || isVerified === true) {
                verificationEl.textContent = `✅ Verified ${confidence ? confidence + '%' : ''}`;
                verificationEl.className = 'badge bg-success';
            } else if (source === 'web') {
                verificationEl.textContent = `❌ Failed ${confidence ? confidence + '%' : ''}`;
                verificationEl.className = 'badge bg-danger';
            } else if (source === 'flutter') {
                verificationEl.textContent = '📱 No Verification';
                verificationEl.className = 'badge bg-info';
            } else {
                verificationEl.textContent = '📝 Manual';
                verificationEl.className = 'badge bg-secondary';
            }
            
            modal.show();
        };
    });
</script>
@endpush
@endsection