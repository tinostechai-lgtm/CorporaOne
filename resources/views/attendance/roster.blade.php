@extends('layouts.admin')

@section('page-title', 'Company Roster')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hrm.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('attendance.dashboard') }}">Attendance</a></li>
    <li class="breadcrumb-item active">Roster</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">
                        <i class="ti ti-calendar-week me-2"></i> Attendance Roster
                    </h5>
                    <small class="text-muted">
                        Week of {{ \Carbon\Carbon::parse($startOfWeek)->format('M d, Y') }}
                        to {{ \Carbon\Carbon::parse($endOfWeek)->format('M d, Y') }}
                    </small>
                </div>
                <div class="btn-group">
                    @php
                        $currentStart = $startOfWeek ?? date('Y-m-d', strtotime('monday this week'));
                        $prevWeek = date('Y-m-d', strtotime($currentStart . ' -7 days'));
                        $nextWeek = date('Y-m-d', strtotime($currentStart . ' +7 days'));
                    @endphp
                    <a href="{{ route('attendance.roster', ['week' => $prevWeek]) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-chevron-left"></i> Prev
                    </a>
                    <a href="{{ route('attendance.roster', ['week' => date('Y-m-d', strtotime('monday this week'))]) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-calendar"></i> This Week
                    </a>
                    <a href="{{ route('attendance.roster', ['week' => $nextWeek]) }}"
                       class="btn btn-sm btn-outline-secondary">
                        Next <i class="ti ti-chevron-right"></i>
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 160px;">Employee</th>
                                @php
                                    $daysOfWeek = $daysOfWeek ?? [];
                                    if (empty($daysOfWeek) && isset($startOfWeek)) {
                                        $start = strtotime($startOfWeek);
                                        for ($i = 0; $i < 7; $i++) {
                                            $daysOfWeek[] = date('Y-m-d', strtotime("+$i days", $start));
                                        }
                                    }
                                    $today = date('Y-m-d');
                                @endphp
                                @foreach($daysOfWeek as $day)
                                    <th class="text-center" style="min-width: 95px;">
                                        {{ \Carbon\Carbon::parse($day)->format('D') }}
                                        <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($day)->format('d M') }}</small>
                                        @if($day == $today)
                                            <br>
                                            <span class="badge bg-primary" style="font-size: 0.6rem;">Today</span>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employeeStatuses ?? [] as $item)
                                @php
                                    $employee = $item->employee;
                                    $dailyStatus = $item->dailyStatus ?? [];
                                    
                                    // Get avatar URL
                                    $avatar = null;
                                    $avatarUrl = null;
                                    $defaultAvatar = asset('assets/img/user-avatar.png');
                                    
                                    if (isset($employee->user) && $employee->user) {
                                        $avatar = $employee->user->avatar;
                                    } elseif (isset($employee->avatar)) {
                                        $avatar = $employee->avatar;
                                    }
                                    
                                    if ($avatar) {
                                        $avatarPath = 'uploads/avatar/' . $avatar;
                                        if (file_exists(public_path($avatarPath))) {
                                            $avatarUrl = asset($avatarPath);
                                        } else {
                                            $storagePath = 'storage/uploads/avatar/' . $avatar;
                                            if (file_exists(public_path($storagePath))) {
                                                $avatarUrl = asset($storagePath);
                                            }
                                        }
                                    }
                                    
                                    if (!$avatarUrl) {
                                        $avatarUrl = $defaultAvatar;
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $avatarUrl }}" 
                                                 class="rounded-circle me-2" 
                                                 width="35" 
                                                 height="35" 
                                                 style="object-fit:cover;"
                                                 onerror="this.src='{{ $defaultAvatar }}'">
                                            <div>
                                                <div class="fw-semibold">{{ $employee->name }}</div>
                                                <small class="text-muted">{{ $employee->designation->name ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    @foreach($daysOfWeek as $day)
                                        @php
                                            $statusData = $dailyStatus[$day] ?? null;
                                            
                                            // Handle both array and object formats
                                            if ($statusData) {
                                                if (is_array($statusData)) {
                                                    $status      = $statusData['status'] ?? 'not_punched';
                                                    $clockIn     = $statusData['clock_in'] ?? '--:--';
                                                    $clockOut    = $statusData['clock_out'] ?? '--:--';
                                                    $source      = $statusData['source'] ?? 'manual';
                                                    $markedBy    = $statusData['marked_by'] ?? 'manual';
                                                    $isVerified  = $statusData['is_verified'] ?? false;
                                                    $faceConfidence = $statusData['face_confidence'] ?? null;
                                                } else {
                                                    $status      = $statusData->status ?? 'not_punched';
                                                    $clockIn     = $statusData->clock_in ?? '--:--';
                                                    $clockOut    = $statusData->clock_out ?? '--:--';
                                                    $source      = $statusData->source ?? 'manual';
                                                    $markedBy    = $statusData->marked_by ?? 'manual';
                                                    $isVerified  = $statusData->is_verified ?? false;
                                                    $faceConfidence = $statusData->face_confidence ?? null;
                                                }
                                            } else {
                                                $status      = 'not_punched';
                                                $clockIn     = '--:--';
                                                $clockOut    = '--:--';
                                                $source      = 'manual';
                                                $markedBy    = 'manual';
                                                $isVerified  = false;
                                                $faceConfidence = null;
                                            }
                                            
                                            $isFutureDate = strtotime($day) > strtotime($today);
                                            
                                            // Badge color for status
                                            $badgeColor = match($status) {
                                                'in', 'Present', 'Clocked In' => 'success',
                                                'out' => 'secondary',
                                                'not_punched', 'Absent' => 'warning',
                                                'break' => 'info',
                                                'late' => 'danger',
                                                'early_leave' => 'dark',
                                                'Overtime' => 'primary',
                                                'Holiday', 'Maternity Leave', 'Paternity Leave' => 'purple',
                                                'Weekend', 'Week Off', 'Upcoming' => 'secondary',
                                                'Paid Leave', 'Casual Leave' => 'info',
                                                'Unpaid Leave', 'Sick Leave' => 'warning',
                                                'Bereavement Leave' => 'dark',
                                                'Compensatory Off' => 'primary',
                                                default => 'secondary',
                                            };
                                            
                                            // Override for future dates
                                            if ($isFutureDate && !in_array($status, ['Holiday', 'Weekend', 'Upcoming', 'Week Off'])) {
                                                $status = 'Upcoming';
                                                $badgeColor = 'secondary';
                                            }

                                            // ===== SOURCE + VERIFICATION (from ApiFaceAttendanceController) =====
                                            $sourceLabel = match($source) {
                                                'web'     => '🌐 Web',
                                                'flutter' => '📱 Mobile',
                                                default   => '📝 Manual',
                                            };
                                            $sourceClass = match($source) {
                                                'web'     => 'web',
                                                'flutter' => 'flutter',
                                                default   => 'manual',
                                            };

                                            // Mirror getVerificationBadgeUnified()
                                            $verificationBadge = '';
                                            $verificationClass = 'manual';
                                            
                                            if ($status !== 'not_punched' && $status !== 'Upcoming' && $status !== 'Weekend' && $status !== 'Holiday') {
                                                if ($source === 'web') {
                                                    if ($isVerified) {
                                                        $verificationBadge = '✅ ' . round($faceConfidence ?? 0) . '%';
                                                        $verificationClass = 'verified';
                                                    } elseif ($faceConfidence !== null) {
                                                        $verificationBadge = '❌ ' . round($faceConfidence) . '%';
                                                        $verificationClass = 'failed';
                                                    } else {
                                                        $verificationBadge = '⚠️ Manual';
                                                        $verificationClass = 'manual';
                                                    }
                                                } elseif ($source === 'flutter') {
                                                    $verificationBadge = '📱 App';
                                                    $verificationClass = 'mobile';
                                                } else {
                                                    $verificationBadge = '📝 Manual';
                                                    $verificationClass = 'manual';
                                                }
                                            }
                                        @endphp
                                        <td class="text-center">
                                            <span class="badge bg-{{ $badgeColor }} rounded-pill px-2 py-1" style="font-size: 0.75rem; min-width: 60px;">
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </span>

                                            @if($clockIn != '--:--' && $clockIn != '00:00:00')
                                                <br>
                                                <small class="text-muted" style="font-size: 0.68rem;">
                                                    <i class="ti ti-clock"></i>
                                                    {{ \Carbon\Carbon::parse($clockIn)->format('h:i A') }}
                                                    @if($clockOut != '--:--' && $clockOut != '00:00:00')
                                                        – {{ \Carbon\Carbon::parse($clockOut)->format('h:i A') }}
                                                    @endif
                                                </small>
                                            @endif

                                            {{-- Source + Verification badges --}}
                                            @if($verificationBadge)
                                                <br>
                                                <span class="source-badge {{ $sourceClass }}" style="font-size: 0.65rem; margin-top: 2px;">
                                                    {{ $sourceLabel }}
                                                </span>
                                                <br>
                                                <span class="verification-badge {{ $verificationClass }}" style="font-size: 0.65rem;">
                                                    {{ $verificationBadge }}
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($daysOfWeek) + 1 }}" class="text-center py-5">
                                        <i class="ti ti-users display-4 text-muted d-block mb-3"></i>
                                        <p class="text-muted">No employees found.</p>
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
@endsection

@push('css')
<style>
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
    .badge.bg-purple {
        background-color: #6f42c1 !important;
        color: #fff !important;
    }
    .badge.bg-dark {
        background-color: #343a40 !important;
        color: #fff !important;
    }
    .table .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    .table .text-muted small {
        font-size: 0.65rem;
    }

    /* Source & Verification badges */
    .source-badge {
        font-size: 0.65rem;
        padding: 1px 6px;
        border-radius: 8px;
        font-weight: 600;
        white-space: nowrap;
        display: inline-block;
        margin-top: 2px;
    }
    .source-badge.web { background: #e0e7ff; color: #3730a3; }
    .source-badge.flutter { background: #d1fae5; color: #065f46; }
    .source-badge.manual { background: #f3f4f6; color: #374151; }

    .verification-badge {
        font-size: 0.65rem;
        padding: 1px 6px;
        border-radius: 8px;
        font-weight: 600;
        white-space: nowrap;
        display: inline-block;
        margin-top: 1px;
    }
    .verification-badge.verified { background: #d1fae5; color: #065f46; }
    .verification-badge.failed { background: #fee2e2; color: #991b1b; }
    .verification-badge.manual { background: #f3f4f6; color: #374151; }
    .verification-badge.mobile { background: #dbeafe; color: #1e40af; }
</style>
@endpush