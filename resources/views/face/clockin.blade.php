@extends('layouts.admin')

@section('page-title', 'Face ID Attendance')

@php
    use Illuminate\Support\Facades\Gate;
    
    // Get employee info
    $employee = Auth::user()->employee;
    $userHasFaceEnrolled = $employee && !empty($employee->face_descriptor);
    
    // Check if already clocked in today
    $today = date('Y-m-d');
    $isClockedIn = false;
    $isClockedOut = false;
    $attendance = null;
    if ($employee) {
        $attendance = \App\Models\AttendanceEmployee::where('employee_id', $employee->id)
                        ->where('date', $today)
                        ->first();
        if ($attendance) {
            $isClockedIn = $attendance->clock_in != '00:00:00';
            $isClockedOut = $attendance->clock_out != '00:00:00';
        }
    }
    
    // Get office location for display
    $officeLocation = \App\Models\Utility::getOfficeLocation();
@endphp

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="fa fa-user-circle me-2"></i>
                        Face ID Attendance
                    </h4>
                    <small class="opacity-75">Auto verification • One face at a time</small>
                </div>

                <div class="card-body text-center p-4 p-md-5">

                    {{-- Office Location Info --}}
                    @if($officeLocation['restriction_enabled'])
                        <div class="alert alert-info mb-3" style="font-size:13px;">
                            <i class="fa fa-map-marker-alt me-2"></i>
                            <strong>{{ __('Office Location:') }}</strong>
                            {{ $officeLocation['latitude'] ?? 'Not set' }}, {{ $officeLocation['longitude'] ?? 'Not set' }}
                            <br>
                            <i class="fa fa-expand me-2"></i>
                            <strong>{{ __('Allowed Radius:') }}</strong>
                            {{ $officeLocation['radius'] ?? 300 }} {{ __('meters') }}
                            <br>
                            <small class="text-muted">{{ __('You must be within this radius to punch in from office mode.') }}</small>
                        </div>
                    @endif

                    {{-- Attendance Status --}}
                    @if($attendance)
                        <div class="alert {{ $isClockedOut ? 'alert-secondary' : ($isClockedIn ? 'alert-success' : 'alert-warning') }} mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fa {{ $isClockedOut ? 'fa-clock' : ($isClockedIn ? 'fa-check-circle' : 'fa-clock') }} me-2"></i>
                                    <strong>{{ __('Status') }}:</strong>
                                    @if($isClockedOut)
                                        {{ __('Clocked Out') }}
                                        <span class="badge bg-secondary ms-2">{{ $attendance->clock_out ?? '' }}</span>
                                    @elseif($isClockedIn)
                                        {{ __('Clocked In') }}
                                        <span class="badge bg-success ms-2">{{ $attendance->clock_in ?? '' }}</span>
                                        @if($attendance->status == 'Half Day')
                                            <span class="badge bg-warning ms-2">🌓 {{ __('Half Day') }}</span>
                                        @endif
                                    @else
                                        {{ __('Not Clocked In') }}
                                    @endif
                                </div>
                                @if($isClockedIn && !$isClockedOut)
                                    <span class="badge bg-danger pulse">
                                        <i class="fa fa-circle me-1" style="font-size: 8px;"></i> {{ __('Live') }}
                                    </span>
                                @endif
                            </div>
                            @if($isClockedIn && $attendance->clock_out == '00:00:00')
                                <div class="mt-2">
                                    <small class="text-muted">
                                        {{ __('Worked:') }} 
                                        @php
                                            $start = \Carbon\Carbon::parse($attendance->clock_in);
                                            $now = \Carbon\Carbon::now();
                                            $diff = $start->diff($now);
                                            echo $diff->format('%H:%I');
                                        @endphp
                                        {{ __('Threshold:') }} {{ $employee->half_day_threshold ?? 4.0 }} {{ __('hrs') }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Camera with Scanner Overlay --}}
                    <div class="position-relative d-inline-block mb-4" style="max-width:640px; width:100%;">
                        <div class="video-container" style="position:relative; border-radius:12px; overflow:hidden; background:#000; aspect-ratio:4/3;">
                            
                            {{-- Video Feed --}}
                            <video id="video" 
                                   width="100%" 
                                   height="100%" 
                                   autoplay 
                                   muted 
                                   playsinline
                                   style="display:block; object-fit:cover; width:100%; height:100%;">
                            </video>

                            {{-- Canvas Overlay for Bounding Box and Scanning Lines --}}
                            <canvas id="overlayCanvas" 
                                    style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:5;">
                            </canvas>

                            {{-- Scanning Effect Overlay --}}
                            <div id="scanningOverlay" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:6; display:none;">
                                {{-- Scanning Line Animation --}}
                                <div id="scanLine" style="
                                    position:absolute;
                                    left:10%;
                                    width:80%;
                                    height:3px;
                                    background: linear-gradient(90deg, transparent, #00ff88, #00ff88, transparent);
                                    box-shadow: 0 0 20px #00ff88, 0 0 60px #00ff88;
                                    border-radius:50%;
                                    animation: scanDown 2.5s ease-in-out infinite;
                                "></div>
                                
                                {{-- Corner brackets --}}
                                <div style="position:absolute; top:15%; left:15%; width:20px; height:20px; border-top:3px solid #00ff88; border-left:3px solid #00ff88; filter:drop-shadow(0 0 10px #00ff88);"></div>
                                <div style="position:absolute; top:15%; right:15%; width:20px; height:20px; border-top:3px solid #00ff88; border-right:3px solid #00ff88; filter:drop-shadow(0 0 10px #00ff88);"></div>
                                <div style="position:absolute; bottom:15%; left:15%; width:20px; height:20px; border-bottom:3px solid #00ff88; border-left:3px solid #00ff88; filter:drop-shadow(0 0 10px #00ff88);"></div>
                                <div style="position:absolute; bottom:15%; right:15%; width:20px; height:20px; border-bottom:3px solid #00ff88; border-right:3px solid #00ff88; filter:drop-shadow(0 0 10px #00ff88);"></div>
                            </div>

                            {{-- Status Indicator --}}
                            <div id="scanStatus" style="
                                position:absolute;
                                bottom:20px;
                                left:50%;
                                transform:translateX(-50%);
                                z-index:10;
                                background:rgba(0,0,0,0.7);
                                color:#fff;
                                padding:8px 20px;
                                border-radius:20px;
                                font-size:13px;
                                font-weight:500;
                                display:none;
                                align-items:center;
                                gap:8px;
                                backdrop-filter:blur(10px);
                                border:1px solid rgba(255,255,255,0.1);
                            ">
                                <span id="scanStatusDot" style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#00ff88;"></span>
                                <span id="scanStatusText">Scanning...</span>
                            </div>

                            {{-- Loading Overlay --}}
                            <div id="cameraLoading" 
                                 class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center"
                                 style="background:rgba(0,0,0,0.85); z-index:20; border-radius:12px;">
                                <div class="spinner-border text-light mb-3" role="status" style="width:3rem;height:3rem;"></div>
                                <div class="text-white fw-medium" id="loadingText">Loading AI models...</div>
                                <small class="text-white-50 mt-1">Please wait a moment</small>
                            </div>
                        </div>
                    </div>

                    {{-- Status / Result --}}
                    <div id="result" class="mb-3"></div>

                    {{-- User Info (shown after success) --}}
                    <div id="userCard" class="d-none">
                        <div class="d-flex align-items-center justify-content-center gap-3 p-3 bg-light rounded">
                            <img id="userAvatar" src="{{ asset('assets/img/user-avatar.png') }}" 
                                 class="rounded-circle border border-primary" 
                                 width="50" height="50" style="object-fit:cover;">
                            <div class="text-start">
                                <div class="fw-bold" id="userName">—</div>
                                <small class="text-muted" id="userRole">Employee</small>
                            </div>
                            <span class="badge bg-success">
                                <i class="fa fa-check-circle me-1"></i> Verified
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== LOCATION MODAL ===== --}}
<div class="modal fade" id="locationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-map-marker-alt text-primary me-2"></i>
                    Select Location
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="text-muted mb-4">Where are you working from today?</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <button id="officeBtn" class="btn btn-primary btn-lg px-4">
                        <i class="fa fa-building me-2"></i> Office
                    </button>
                    <button id="remoteBtn" class="btn btn-secondary btn-lg px-4">
                        <i class="fa fa-home me-2"></i> Remote
                    </button>
                </div>
                <div id="locationInfo" class="mt-3 text-muted small"></div>
            </div>
        </div>
    </div>
</div>

{{-- ===== ACTION MODAL ===== --}}
<div class="modal fade" id="actionModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-tasks text-primary me-2"></i>
                    Choose Action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="text-muted mb-4">What would you like to do?</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <button id="teaBreakBtn" class="btn btn-warning btn-lg px-4">
                        <i class="fa fa-coffee me-2"></i> Tea Break
                    </button>
                    <button id="punchOutBtn" class="btn btn-danger btn-lg px-4">
                        <i class="fa fa-sign-out-alt me-2"></i> Punch Out
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== ENROLL MODAL ===== --}}
<div class="modal fade" id="enrollModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-user-plus text-success me-2"></i>
                    Enroll New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p class="text-muted mb-3">Please scan your face to enroll</p>

                <div style="position:relative; max-width:480px; margin:0 auto; background:#000; border-radius:12px; overflow:hidden;">
                    <video id="modalVideo" 
                           width="100%" 
                           autoplay muted playsinline
                           style="display:block; aspect-ratio:4/3; object-fit:cover;">
                    </video>
                    <div style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none;">
                        <div style="position:absolute; top:20%; left:20%; width:60%; height:60%; border:2px solid rgba(0,255,136,0.3); border-radius:50%;"></div>
                        <div style="position:absolute; top:10%; left:10%; width:80%; height:80%; border:1px solid rgba(0,255,136,0.15); border-radius:50%;"></div>
                    </div>
                </div>

                <div class="mt-3">
                    <button id="enrollBtn" class="btn btn-success btn-lg px-5" disabled>
                        <i class="fa fa-camera me-2"></i> Enroll Face
                    </button>
                </div>

                <div id="enrollResult" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

{{-- ===== WORK REPORT MODAL ===== --}}
<div class="modal fade" id="workReportModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title text-white">
                    <i class="fa fa-clipboard-list me-2"></i>
                    {{ __('Work Report') }} - <span id="wr_date_display">{{ date('d M Y') }}</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="wr_alert" class="alert alert-info">
                    <i class="fa fa-info-circle me-2"></i>
                    {{ __('Please fill in your work details for today before clocking out.') }}
                </div>

                <form id="workReportForm" action="{{ url('/work-report/submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="employee_id" id="wr_employee_id" value="{{ $employee->id ?? 0 }}">
                    <input type="hidden" name="attendance_id" id="wr_attendance_id" value="{{ $attendance->id ?? 0 }}">
                    <input type="hidden" name="date" id="wr_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="clock_in" id="wr_clock_in" value="{{ $attendance->clock_in ?? '' }}">
                    <input type="hidden" name="clock_out" id="wr_clock_out" value="">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header"><h6 class="mb-0"><i class="fa fa-clock me-2"></i>{{ __('Attendance Summary') }}</h6></div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr><td><strong>{{ __('Clock In:') }}</strong></td><td id="wr_summary_clock_in">{{ $attendance->clock_in ?? '--:--' }}</td></tr>
                                        <tr><td><strong>{{ __('Clock Out:') }}</strong></td><td id="wr_summary_clock_out">--:--</td></tr>
                                        <tr><td><strong>{{ __('Worked Hours:') }}</strong></td><td id="wr_summary_worked_hours">--:--</td></tr>
                                        <tr><td><strong>{{ __('Status:') }}</strong></td><td id="wr_summary_status">--</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header"><h6 class="mb-0"><i class="fa fa-list-check me-2"></i>{{ __('Quick Tasks') }}</h6></div>
                                <div class="card-body">
                                    @foreach(['Meeting', 'Email', 'Coding', 'Documentation', 'Design', 'Testing', 'Other'] as $task)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="task_{{ strtolower($task) }}" value="{{ $task }}" name="quick_tasks[]">
                                            <label class="form-check-label" for="task_{{ strtolower($task) }}">{{ __($task) }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header"><h6 class="mb-0"><i class="fa fa-notes me-2"></i>{{ __('Work Description') }}</h6></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="work_description" class="form-label">{{ __('What did you work on today?') }} <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="work_description" name="work_description" rows="3" placeholder="{{ __('Describe your work today...') }}" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header"><h6 class="mb-0"><i class="fa fa-trophy me-2"></i>{{ __('Achievements') }}</h6></div>
                                <div class="card-body">
                                    <textarea class="form-control" id="achievements" name="achievements" rows="2" placeholder="{{ __('What did you achieve today?') }}"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header"><h6 class="mb-0"><i class="fa fa-exclamation-triangle me-2"></i>{{ __('Challenges') }}</h6></div>
                                <div class="card-body">
                                    <textarea class="form-control" id="challenges" name="challenges" rows="2" placeholder="{{ __('Any challenges faced today?') }}"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header"><h6 class="mb-0"><i class="fa fa-calendar-plus me-2"></i>{{ __('Tomorrow\'s Plan') }}</h6></div>
                                <div class="card-body">
                                    <textarea class="form-control" id="tomorrow_plan" name="tomorrow_plan" rows="2" placeholder="{{ __('What do you plan to do tomorrow?') }}"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header"><h6 class="mb-0"><i class="fa fa-hourglass-half me-2"></i>{{ __('Hourly Breakdown') }}</h6></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label small">{{ __('Project Work') }}</label>
                                            <input type="number" class="form-control" name="hours_project" id="hours_project" min="0" max="12" step="0.5" placeholder="{{ __('Hours') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">{{ __('Meetings') }}</label>
                                            <input type="number" class="form-control" name="hours_meeting" id="hours_meeting" min="0" max="12" step="0.5" placeholder="{{ __('Hours') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">{{ __('Admin/Other') }}</label>
                                            <input type="number" class="form-control" name="hours_admin" id="hours_admin" min="0" max="12" step="0.5" placeholder="{{ __('Hours') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i> {{ __('Skip') }}
                </button>
                <button type="button" class="btn btn-primary" id="submitWorkReportBtn">
                    <i class="fa fa-paper-plane me-1"></i> {{ __('Submit & Punch Out') }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<style>
    .pulse {
        animation: pulse-dot 1.5s infinite;
    }
    @keyframes pulse-dot {
        0% { opacity: 1; }
        50% { opacity: 0.3; }
        100% { opacity: 1; }
    }

    /* Scanning line animation */
    @keyframes scanDown {
        0% { top: 10%; opacity: 1; }
        50% { top: 85%; opacity: 1; }
        51% { opacity: 0; }
        52% { top: 10%; opacity: 0; }
        53% { opacity: 1; }
        100% { top: 10%; opacity: 1; }
    }

    /* Pulse glow animation for scanning */
    @keyframes pulseGlow {
        0%, 100% { box-shadow: 0 0 20px rgba(0, 255, 136, 0.3), 0 0 60px rgba(0, 255, 136, 0.1); }
        50% { box-shadow: 0 0 40px rgba(0, 255, 136, 0.6), 0 0 80px rgba(0, 255, 136, 0.2); }
    }

    .scan-active {
        animation: pulseGlow 1.5s ease-in-out infinite;
    }

    /* Face detected indicator */
    .face-detected {
        animation: facePulse 0.5s ease-in-out 3;
    }

    @keyframes facePulse {
        0%, 100% { border-color: #00ff88; }
        50% { border-color: #00ff44; box-shadow: 0 0 30px rgba(0, 255, 136, 0.5); }
    }
</style>
@endpush

<script src="{{ asset('js/face-api/face-api.min.js') }}?v={{ time() }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ==================== DOM ====================
    const video          = document.getElementById('video');
    const canvas         = document.getElementById('overlayCanvas');
    const ctx            = canvas.getContext('2d');
    const result         = document.getElementById('result');
    const cameraLoading  = document.getElementById('cameraLoading');
    const loadingText    = document.getElementById('loadingText');
    const userCard       = document.getElementById('userCard');
    const userName       = document.getElementById('userName');
    const userRole       = document.getElementById('userRole');
    const userAvatar     = document.getElementById('userAvatar');
    const scanOverlay    = document.getElementById('scanningOverlay');
    const scanStatus     = document.getElementById('scanStatus');
    const scanStatusText = document.getElementById('scanStatusText');
    const scanStatusDot  = document.getElementById('scanStatusDot');
    const locationInfo   = document.getElementById('locationInfo');

    const modalVideo     = document.getElementById('modalVideo');
    const enrollBtn      = document.getElementById('enrollBtn');
    const enrollResult   = document.getElementById('enrollResult');

    const locationModal  = new bootstrap.Modal(document.getElementById('locationModal'));
    const actionModal    = new bootstrap.Modal(document.getElementById('actionModal'));
    const enrollModal    = new bootstrap.Modal(document.getElementById('enrollModal'));
    const workReportModal = new bootstrap.Modal(document.getElementById('workReportModal'));

    // ==================== STATE ====================
    let modelsLoaded     = false;
    let capturedImageData = null;
    let isProcessing     = false;
    let stream           = null;
    let autoScanInterval = null;
    let isEnrollMode     = false;
    let isClockedInState = @json($isClockedIn);
    let isClockedOutState = @json($isClockedOut);
    let employeeId       = @json($employee ? $employee->id : 0);
    let attendanceId     = @json($attendance ? $attendance->id : 0);
    let scanActive       = false;

    // Office location settings from server
    const officeLocation = @json($officeLocation);
    const officeLat = officeLocation.latitude || null;
    const officeLng = officeLocation.longitude || null;
    const officeRadius = officeLocation.radius || 300;
    const restrictionEnabled = officeLocation.restriction_enabled || false;

    // ==================== HELPERS ====================
    function showLoading(text = 'Loading AI models...') {
        loadingText.textContent = text;
        cameraLoading.classList.remove('d-none');
        cameraLoading.style.display = 'flex';
    }

    function hideLoading() {
        cameraLoading.classList.add('d-none');
        cameraLoading.style.display = 'none';
    }

    function showResult(type, message) {
        const icons = {
            success: 'check-circle',
            danger:  'exclamation-circle',
            warning: 'exclamation-triangle',
            info:    'info-circle'
        };
        result.innerHTML = `
            <div class="alert alert-${type} d-flex align-items-center justify-content-center gap-2 mb-0">
                <i class="fa fa-${icons[type] || 'info-circle'}"></i>
                <span>${message}</span>
            </div>`;
    }

    function capturePhoto(videoEl) {
        const canvas = document.createElement('canvas');
        canvas.width  = videoEl.videoWidth  || 640;
        canvas.height = videoEl.videoHeight || 480;
        canvas.getContext('2d').drawImage(videoEl, 0, 0);
        return canvas.toDataURL('image/jpeg', 0.9);
    }

    function updateCanvasSize() {
        const rect = video.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
    }

    // ==================== SCAN ANIMATION ====================
    function startScanAnimation() {
        scanActive = true;
        scanOverlay.style.display = 'block';
        scanStatus.style.display = 'flex';
        scanStatusText.textContent = 'Scanning...';
        scanStatusDot.style.background = '#00ff88';
        document.querySelector('.video-container').classList.add('scan-active');
    }

    function stopScanAnimation() {
        scanActive = false;
        scanOverlay.style.display = 'none';
        scanStatus.style.display = 'none';
        document.querySelector('.video-container').classList.remove('scan-active');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    function showFaceDetected() {
        scanStatusText.textContent = 'Face Detected!';
        scanStatusDot.style.background = '#00ff44';
        scanStatusDot.style.boxShadow = '0 0 20px #00ff44';
        document.querySelector('.video-container').classList.add('face-detected');
        setTimeout(() => {
            document.querySelector('.video-container').classList.remove('face-detected');
        }, 1500);
    }

    // ==================== DRAW BOUNDING BOX ====================
    function drawDetectionBox(detection) {
        if (!detection) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            return;
        }

        const rect = video.getBoundingClientRect();
        const scaleX = canvas.width / video.videoWidth;
        const scaleY = canvas.height / video.videoHeight;

        const box = detection.detection.box;
        const x = box.x * scaleX;
        const y = box.y * scaleY;
        const w = box.width * scaleX;
        const h = box.height * scaleY;

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Glow effect
        const gradient = ctx.createRadialGradient(
            x + w/2, y + h/2, 0,
            x + w/2, y + h/2, Math.max(w, h) * 0.8
        );
        gradient.addColorStop(0, 'rgba(0, 255, 136, 0.15)');
        gradient.addColorStop(1, 'rgba(0, 255, 136, 0)');
        ctx.fillStyle = gradient;
        ctx.fillRect(x - w/2, y - h/2, w * 2, h * 2);

        // Main bounding box with glow
        ctx.shadowColor = '#00ff88';
        ctx.shadowBlur = 30;
        ctx.strokeStyle = '#00ff88';
        ctx.lineWidth = 3;
        ctx.strokeRect(x, y, w, h);
        ctx.shadowBlur = 0;

        // Corner brackets
        const cornerSize = 15;
        const cornerGap = 5;
        
        ctx.strokeStyle = '#00ff88';
        ctx.lineWidth = 4;
        ctx.shadowColor = '#00ff88';
        ctx.shadowBlur = 15;

        // Top-left
        ctx.beginPath();
        ctx.moveTo(x + cornerGap, y);
        ctx.lineTo(x + cornerGap + cornerSize, y);
        ctx.moveTo(x, y + cornerGap);
        ctx.lineTo(x, y + cornerGap + cornerSize);
        ctx.stroke();

        // Top-right
        ctx.beginPath();
        ctx.moveTo(x + w - cornerGap, y);
        ctx.lineTo(x + w - cornerGap - cornerSize, y);
        ctx.moveTo(x + w, y + cornerGap);
        ctx.lineTo(x + w, y + cornerGap + cornerSize);
        ctx.stroke();

        // Bottom-left
        ctx.beginPath();
        ctx.moveTo(x + cornerGap, y + h);
        ctx.lineTo(x + cornerGap + cornerSize, y + h);
        ctx.moveTo(x, y + h - cornerGap);
        ctx.lineTo(x, y + h - cornerGap - cornerSize);
        ctx.stroke();

        // Bottom-right
        ctx.beginPath();
        ctx.moveTo(x + w - cornerGap, y + h);
        ctx.lineTo(x + w - cornerGap - cornerSize, y + h);
        ctx.moveTo(x + w, y + h - cornerGap);
        ctx.lineTo(x + w, y + h - cornerGap - cornerSize);
        ctx.stroke();

        ctx.shadowBlur = 0;

        // Confidence score
        ctx.fillStyle = 'rgba(0, 0, 0, 0.6)';
        ctx.fillRect(x, y - 28, 80, 24);
        ctx.fillStyle = '#00ff88';
        ctx.font = '12px monospace';
        ctx.fillText('✓ FACE DETECTED', x + 6, y - 10);
    }

    // ==================== INIT ====================
    showLoading('Loading AI models...');
    showResult('info', 'Loading AI models… (15–30 sec first time)');

    // Resize canvas when video loads
    video.addEventListener('loadedmetadata', () => {
        updateCanvasSize();
    });

    window.addEventListener('resize', () => {
        updateCanvasSize();
    });

    Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/js/face-api/weights'),
        faceapi.nets.faceLandmark68Net.loadFromUri('/js/face-api/weights'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/js/face-api/weights')
    ])
    .then(() => {
        modelsLoaded = true;
        showLoading('Starting camera...');
        startCamera();
    })
    .catch((err) => {
        console.error('Model load error:', err);
        showLoading('Failed to load models');
        showResult('danger', 'Failed to load AI models. Please refresh.');
    });

    // ==================== CAMERA ====================
    function startCamera() {
        navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
        })
        .then(mediaStream => {
            stream = mediaStream;
            video.srcObject = mediaStream;

            setTimeout(() => {
                hideLoading();
                updateCanvasSize();
                showResult('success', 'Ready! Face will be auto-detected');
                startScanAnimation();
                setTimeout(startAutoScan, 1200);
            }, 600);
        })
        .catch((err) => {
            console.error('Camera error:', err);
            showLoading('Camera access denied');
            showResult('danger', 'Camera permission denied. Please allow camera access.');
        });
    }

    // ==================== AUTO SCAN ====================
    function startAutoScan() {
        if (autoScanInterval) clearInterval(autoScanInterval);
        scanActive = true;

        autoScanInterval = setInterval(() => {
            if (!modelsLoaded || isProcessing || isEnrollMode) return;
            if (result.querySelector('.alert-success') && result.innerText.includes('Verified')) return;
            detectFace();
        }, 1000);
    }

    function detectFace() {
        if (isProcessing) return;

        faceapi
            .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 }))
            .withFaceLandmarks()
            .withFaceDescriptor()
            .then(detection => {
                if (!detection) {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    scanStatusText.textContent = 'Scanning...';
                    scanStatusDot.style.background = '#00ff88';
                    return;
                }

                drawDetectionBox(detection);
                showFaceDetected();
                capturedImageData = capturePhoto(video);
                verifyFace(detection);
            })
            .catch(() => {});
    }

    // ==================== VERIFY ====================
    function verifyFace(detection) {
        if (isProcessing) return;
        isProcessing = true;
        scanStatusText.textContent = 'Verifying...';
        scanStatusDot.style.background = '#ffaa00';

        fetch('{{ route("face.recognize") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                descriptor: Array.from(detection.descriptor),
                photo: capturedImageData,
                employee_id: employeeId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                if (data.message && data.message.toLowerCase().includes('not found')) {
                    showResult('warning', 'Face not recognized. Please enroll.');
                    setTimeout(() => {
                        enrollModal.show();
                        setupEnrollCamera();
                    }, 900);
                } else {
                    showResult('danger', data.message || 'Verification failed');
                    setTimeout(reset, 3000);
                }
                isProcessing = false;
                scanStatusText.textContent = 'Scanning...';
                scanStatusDot.style.background = '#00ff88';
                return;
            }

            handleSuccess(data);
        })
        .catch((err) => {
            console.error('Verify error:', err);
            showResult('danger', 'Server error. Try again.');
            isProcessing = false;
            scanStatusText.textContent = 'Scanning...';
            scanStatusDot.style.background = '#00ff88';
            setTimeout(reset, 3000);
        });
    }

    // ==================== SUCCESS ====================
    function handleSuccess(data) {
        showResult('success', data.message || 'Verified successfully!');
        scanStatusText.textContent = 'Verified! ✅';
        scanStatusDot.style.background = '#00ff44';
        scanStatusDot.style.boxShadow = '0 0 30px #00ff44';

        if (data.user) {
            userCard.classList.remove('d-none');
            userName.textContent  = data.user.name || 'Employee';
            userRole.textContent  = data.user.designation || 'Employee';
            userAvatar.src        = data.user.avatar || '{{ asset('assets/img/user-avatar.png') }}';
        }

        if (autoScanInterval) {
            clearInterval(autoScanInterval);
            autoScanInterval = null;
        }

        // Check if already clocked in
        if (!isClockedInState || isClockedOutState) {
            setTimeout(() => {
                locationModal.show();
                document.getElementById('officeBtn').onclick = () => markLocation('office');
                document.getElementById('remoteBtn').onclick = () => markLocation('remote');
            }, 700);
            isProcessing = false;
            return;
        }

        if (isClockedInState && !isClockedOutState) {
            setTimeout(() => {
                actionModal.show();
                document.getElementById('teaBreakBtn').onclick = () => markAction('tea_break');
                document.getElementById('punchOutBtn').onclick = () => markAction('punch_out');
            }, 700);
            isProcessing = false;
            return;
        }

        setTimeout(reset, 4000);
        isProcessing = false;
    }

    // ==================== CALCULATE DISTANCE ====================
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Earth's radius in meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // ==================== MARK LOCATION ====================
    function markLocation(mode) {
        locationModal.hide();
        showResult('info', 'Validating location...');
        scanStatusText.textContent = 'Checking Location...';
        scanStatusDot.style.background = '#ffaa00';

        const data = {
            mode: mode,
            photo: capturedImageData,
            employee_id: employeeId
        };

        // If remote mode, skip location validation
        if (mode === 'remote') {
            showResult('info', 'Remote mode selected. Proceeding...');
            sendLocationRequest(data);
            return;
        }

        // Office mode - check location
        if (mode === 'office' && navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                pos => {
                    const userLat = pos.coords.latitude;
                    const userLng = pos.coords.longitude;
                    data.location = { lat: userLat, lng: userLng };
                    
                    // Check if office location is configured
                    if (!officeLat || !officeLng) {
                        showResult('warning', 'Office location not configured. Proceeding anyway.');
                        sendLocationRequest(data);
                        return;
                    }
                    
                    // Calculate distance
                    const distance = calculateDistance(userLat, userLng, officeLat, officeLng);
                    
                    // Display location info
                    locationInfo.innerHTML = `
                        <div class="text-center">
                            <small>Your location: ${userLat.toFixed(6)}, ${userLng.toFixed(6)}</small><br>
                            <small>Distance from office: <strong>${Math.round(distance)} meters</strong></small><br>
                            <small>Allowed radius: <strong>${officeRadius} meters</strong></small>
                        </div>
                    `;

                    if (distance > officeRadius) {
                        // ❌ Outside office radius - Show error and let user choose Remote
                        showResult('danger', `
                            <div>
                                <strong>❌ You are too far from the office!</strong><br>
                                <small>Your distance: ${Math.round(distance)} meters</small><br>
                                <small>Allowed radius: ${officeRadius} meters</small><br>
                                <small>Please select <strong>Remote</strong> mode or move closer.</small>
                            </div>
                        `);
                        scanStatusText.textContent = 'Too Far! ❌';
                        scanStatusDot.style.background = '#ff0000';
                        isProcessing = false;
                        
                        // Show modal again with warning
                        setTimeout(() => {
                            locationModal.show();
                            // Update button text with warning
                            document.getElementById('officeBtn').innerHTML = `
                                <i class="fa fa-building me-2"></i> Office 
                                <span class="badge bg-danger">${Math.round(distance)}m</span>
                            `;
                            document.getElementById('remoteBtn').innerHTML = `
                                <i class="fa fa-home me-2"></i> Remote
                                <span class="badge bg-success">Recommended</span>
                            `;
                        }, 1500);
                        return;
                    }

                    // ✅ Within office radius - proceed
                    showResult('success', `✅ Location verified! Distance: ${Math.round(distance)} meters`);
                    scanStatusText.textContent = 'Location OK ✅';
                    scanStatusDot.style.background = '#00ff44';
                    sendLocationRequest(data);

                },
                (err) => {
                    console.error('Geolocation error:', err);
                    showResult('warning', 'Unable to get location. Please enable GPS or select Remote mode.');
                    scanStatusText.textContent = 'GPS Error ❌';
                    scanStatusDot.style.background = '#ff0000';
                    isProcessing = false;
                    
                    // Allow user to try remote
                    setTimeout(() => {
                        locationModal.show();
                    }, 1000);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        } else {
            // No GPS available - show warning
            showResult('warning', 'GPS not available. Please use Remote mode.');
            scanStatusText.textContent = 'GPS Unavailable ❌';
            scanStatusDot.style.background = '#ff0000';
            isProcessing = false;
            
            setTimeout(() => {
                locationModal.show();
            }, 1000);
        }
    }

    // ==================== SEND LOCATION REQUEST ====================
    function sendLocationRequest(data) {
        showResult('info', 'Processing...');
        scanStatusText.textContent = 'Punching In...';
        scanStatusDot.style.background = '#ffaa00';

        fetch('{{ route("face.mark.location") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                showResult('danger', data.message);
                scanStatusText.textContent = 'Failed ❌';
                scanStatusDot.style.background = '#ff0000';
                isProcessing = false;
                return;
            }
            
            showResult('success', data.message + ' ✅');
            scanStatusText.textContent = 'Clocked In! ✅';
            scanStatusDot.style.background = '#00ff44';
            isClockedInState = true;
            isClockedOutState = false;
            
            // Close location modal if open
            const modal = bootstrap.Modal.getInstance(document.getElementById('locationModal'));
            if (modal) modal.hide();
            
            setTimeout(() => window.location.reload(), 2000);
        })
        .catch(err => {
            console.error('Location request error:', err);
            showResult('danger', 'Error: ' + err.message);
            scanStatusText.textContent = 'Error ❌';
            scanStatusDot.style.background = '#ff0000';
            isProcessing = false;
        });
    }

    // ==================== MARK ACTION ====================
    function markAction(action) {
        actionModal.hide();

        if (action === 'punch_out') {
            showWorkReportPopup();
            isProcessing = false;
            return;
        }

        showResult('info', 'Processing...');
        scanStatusText.textContent = 'Processing...';
        scanStatusDot.style.background = '#ffaa00';

        fetch('{{ route("face.mark") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                action: action,
                photo: capturedImageData,
                employee_id: employeeId
            })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                showResult('danger', data.message);
                scanStatusText.textContent = 'Failed ❌';
                scanStatusDot.style.background = '#ff0000';
                return;
            }
            showResult('success', data.message + ' ✅');
            scanStatusText.textContent = 'Break Started! ☕';
            scanStatusDot.style.background = '#00ff44';
            setTimeout(() => window.location.reload(), 2000);
        });
        isProcessing = false;
    }

    // ==================== SHOW WORK REPORT POPUP ====================
    function showWorkReportPopup() {
        fetch('{{ route("workreport.status") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.submitted_today) {
                showResult('info', 'Work report already submitted. Punching out...');
                setTimeout(() => {
                    punchOutEmployee(null);
                }, 1000);
                return;
            }
            
            document.getElementById('wr_attendance_id').value = attendanceId;
            document.getElementById('wr_employee_id').value = employeeId;
            document.getElementById('wr_date').value = '{{ date('Y-m-d') }}';
            document.getElementById('wr_clock_in').value = '{{ $attendance->clock_in ?? '' }}';
            
            document.getElementById('wr_summary_clock_in').textContent = '{{ $attendance->clock_in ?? '--:--' }}';
            document.getElementById('wr_summary_clock_out').textContent = '--:--';
            document.getElementById('wr_summary_worked_hours').textContent = '--:--';
            document.getElementById('wr_summary_status').textContent = '--';
            document.getElementById('wr_date_display').textContent = '{{ date('d M Y') }}';
            
            workReportModal.show();
        })
        .catch(() => {
            document.getElementById('wr_attendance_id').value = attendanceId;
            document.getElementById('wr_employee_id').value = employeeId;
            document.getElementById('wr_date').value = '{{ date('Y-m-d') }}';
            document.getElementById('wr_clock_in').value = '{{ $attendance->clock_in ?? '' }}';
            document.getElementById('wr_summary_clock_in').textContent = '{{ $attendance->clock_in ?? '--:--' }}';
            workReportModal.show();
        });
    }

    // ==================== SUBMIT WORK REPORT ====================
    document.getElementById('submitWorkReportBtn').addEventListener('click', function() {
        const form = document.getElementById('workReportForm');
        const formData = new FormData(form);
        
        const now = new Date();
        const clockOut = now.toTimeString().slice(0, 8);
        formData.append('clock_out', clockOut);
        document.getElementById('wr_clock_out').value = clockOut;
        
        const submitBtn = this;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Submitting...';

        fetch('{{ url("/work-report/submit") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Server error');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showResult('success', 'Work report submitted! Punching out...');
                scanStatusText.textContent = 'Punching Out...';
                scanStatusDot.style.background = '#ffaa00';
                setTimeout(() => {
                    punchOutEmployee(clockOut);
                }, 500);
            } else {
                const errorMsg = data.message || data.errors || 'Unknown error';
                showResult('danger', 'Work report failed: ' + errorMsg);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Submit & Punch Out';
            }
        })
        .catch(err => {
            console.error('Work report error:', err);
            showResult('danger', 'Error: ' + err.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Submit & Punch Out';
        });
    });

    // ==================== PUNCH OUT EMPLOYEE ====================
    function punchOutEmployee(clockOut) {
        showResult('info', 'Punching out...');
        
        const punchOutBtn = document.getElementById('submitWorkReportBtn');
        if (punchOutBtn) {
            punchOutBtn.disabled = true;
            punchOutBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Punching out...';
        }

        const payload = {
            employee_id: employeeId,
            action: 'punch_out',
            mode: 'remote',
            photo: capturedImageData
        };

        if (clockOut) {
            payload.clock_out = clockOut;
        }

        fetch('{{ route("attendance.attendance") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showResult('success', '✅ Clocked out successfully!');
                scanStatusText.textContent = 'Clocked Out! ✅';
                scanStatusDot.style.background = '#00ff44';
                const modal = bootstrap.Modal.getInstance(document.getElementById('workReportModal'));
                if (modal) {
                    modal.hide();
                }
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showResult('danger', data.message || 'Failed to punch out');
                if (punchOutBtn) {
                    punchOutBtn.disabled = false;
                    punchOutBtn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Submit & Punch Out';
                }
            }
        })
        .catch(err => {
            console.error('Punch out error:', err);
            showResult('danger', 'Error punching out. Please try again.');
            if (punchOutBtn) {
                punchOutBtn.disabled = false;
                punchOutBtn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Submit & Punch Out';
            }
        });
    }

    // ==================== ENROLL ====================
    function setupEnrollCamera() {
        isEnrollMode = true;

        navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 480 }, height: { ideal: 360 } }
        })
        .then(stream => {
            modalVideo.srcObject = stream;
            enrollBtn.disabled = false;
        })
        .catch(() => {
            enrollResult.innerHTML = '<div class="alert alert-danger">Camera error</div>';
        });
    }

    enrollBtn.addEventListener('click', function () {
        if (isProcessing) return;
        isProcessing = true;
        enrollBtn.disabled = true;

        const enrollPhoto = capturePhoto(modalVideo);

        faceapi
            .detectSingleFace(modalVideo, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor()
            .then(detection => {
                if (!detection) {
                    enrollResult.innerHTML = '<div class="alert alert-warning">No face detected</div>';
                    enrollBtn.disabled = false;
                    isProcessing = false;
                    return;
                }

                fetch('{{ route("face.enroll") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        descriptor: Array.from(detection.descriptor),
                        photo: enrollPhoto,
                        employee_id: employeeId
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        enrollResult.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        setTimeout(() => {
                            enrollModal.hide();
                            window.location.reload();
                        }, 1500);
                    } else {
                        enrollResult.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                        enrollBtn.disabled = false;
                        isProcessing = false;
                    }
                });
            });
    });

    // ==================== RESET ====================
    function reset() {
        userCard.classList.add('d-none');
        showResult('info', 'Ready for next person');
        if (!autoScanInterval) {
            startScanAnimation();
            startAutoScan();
        }
        isProcessing = false;
        scanStatusText.textContent = 'Scanning...';
        scanStatusDot.style.background = '#00ff88';
    }

    // ==================== CANVAS SIZE ON RESIZE ====================
    window.addEventListener('resize', () => {
        updateCanvasSize();
    });

    // ==================== CLEANUP ====================
    window.addEventListener('beforeunload', () => {
        if (autoScanInterval) clearInterval(autoScanInterval);
        if (stream) stream.getTracks().forEach(t => t.stop());
    });
});
</script>