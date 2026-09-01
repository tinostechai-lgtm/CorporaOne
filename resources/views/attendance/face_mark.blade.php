@extends('layouts.admin')

@section('page-title', 'Face Recognition Attendance')

@php
    use App\Models\Employee;
    use App\Models\AttendanceEmployee;
    use Carbon\Carbon;
    
    $today = Carbon::today()->format('Y-m-d');
    
    // Get total employees
    $totalEmployees = Employee::where('created_by', Auth::user()->creatorId())->count();
    
    // Get enrolled employees (with face_descriptor)
    $totalEnrolled = Employee::where('created_by', Auth::user()->creatorId())
                        ->whereNotNull('face_descriptor')
                        ->count();
    
    // Get present today
    $presentToday = AttendanceEmployee::whereDate('date', $today)
                        ->where('status', 'Present')
                        ->distinct('employee_id')
                        ->count('employee_id');
    
    // Get half day today
    $halfDayToday = AttendanceEmployee::whereDate('date', $today)
                        ->where('status', 'Half Day')
                        ->distinct('employee_id')
                        ->count('employee_id');
    
    // Get absent (total - present - halfday)
    $absentToday = max(0, $totalEmployees - $presentToday - $halfDayToday);
    
    // Get on break count
    $onBreakCount = AttendanceEmployee::whereDate('date', $today)
                        ->where('clock_in', '!=', '00:00:00')
                        ->where('clock_out', '00:00:00')
                        ->where('tea_break_out', '!=', '00:00:00')
                        ->where('tea_break_in', '00:00:00')
                        ->count();
    
    // Get today's log
    $todayLog = AttendanceEmployee::with('employee')
                    ->whereDate('date', $today)
                    ->where('clock_in', '!=', '00:00:00')
                    ->orderBy('clock_in', 'desc')
                    ->limit(50)
                    ->get();
@endphp

@section('content')
<style>
    .scan-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0,0,0,0.6);
        color: white;
        transition: all 0.3s ease;
        border-radius: 12px;
        z-index: 5;
    }
    .scan-overlay.success { background: rgba(0,255,0,0.3); }
    .scan-overlay.error { background: rgba(255,0,0,0.3); }
    .scan-overlay.verifying { background: rgba(255,255,0,0.3); }
    .scan-overlay.info { background: rgba(0,150,255,0.3); }
    .scan-overlay.action { background: rgba(100,50,200,0.4); }
    .employee-list {
        max-height: 400px;
        overflow-y: auto;
    }
    .employee-list::-webkit-scrollbar {
        width: 5px;
    }
    .employee-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .employee-list::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    .employee-list::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    .status-badge {
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .status-in { background: #28a745; color: white; }
    .status-out { background: #dc3545; color: white; }
    .status-late { background: #ffc107; color: black; }
    .status-break { background: #17a2b8; color: white; }
    .status-halfday { background: #f59e0b; color: black; }
    .floating-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
    }
    .face-scanner {
        position: relative;
        background: #000;
        border-radius: 12px;
        overflow: hidden;
    }
    .face-scanner video {
        width: 100%;
        height: auto;
        display: block;
    }
    .face-scanner canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 2;
    }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }
    .pulse {
        animation: pulse 2s infinite;
    }
    .name-display {
        font-size: 28px;
        font-weight: bold;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
    }
    .log-entry {
        transition: all 0.3s ease;
        border-left: 4px solid #28a745;
        padding: 10px 15px;
        border-bottom: 1px solid #e9ecef;
    }
    .log-entry:last-child {
        border-bottom: none;
    }
    .log-entry.manual {
        border-left-color: #17a2b8;
    }
    .log-entry.face {
        border-left-color: #6c5ce7;
        background: #f8f0ff;
    }
    .log-entry.break {
        border-left-color: #f59e0b;
        background: #fffbeb;
    }
    .log-entry.halfday {
        border-left-color: #f59e0b;
        background: #fef3c7;
    }
    .badge-face {
        background: #6c5ce7;
        color: white;
    }
    .badge-break {
        background: #f59e0b;
        color: white;
    }
    .badge-halfday {
        background: #f59e0b;
        color: black;
    }
    .debug-console {
        max-height: 200px;
        overflow-y: auto;
        background: #1a1a2e;
        color: #00ff00;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        padding: 10px;
        border-radius: 8px;
        margin-top: 10px;
        display: none;
    }
    .debug-console .log-entry-debug {
        padding: 2px 0;
        border-bottom: 1px solid #2a2a4e;
    }
    .debug-console .log-time {
        color: #888;
        margin-right: 10px;
    }
    .debug-console .log-success { color: #00ff00; }
    .debug-console .log-error { color: #ff4444; }
    .debug-console .log-info { color: #44aaff; }
    .debug-console .log-warning { color: #ffaa00; }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 15px;
        flex-wrap: wrap;
    }
    .action-buttons .btn {
        min-width: 120px;
        padding: 10px 20px;
        font-weight: 600;
        border-radius: 30px;
        transition: all 0.3s ease;
    }
    .action-buttons .btn:hover {
        transform: scale(1.05);
    }
    .action-buttons .btn-tea {
        background: #f59e0b;
        color: white;
        border: none;
    }
    .action-buttons .btn-tea:hover {
        background: #d97706;
        color: white;
    }
    .action-buttons .btn-tea.end-break {
        background: #8b5cf6;
    }
    .action-buttons .btn-tea.end-break:hover {
        background: #7c3aed;
    }
    .action-buttons .btn-punch-out {
        background: #dc3545;
        color: white;
        border: none;
    }
    .action-buttons .btn-punch-out:hover {
        background: #b91c1c;
        color: white;
    }
    .action-buttons .btn-cancel {
        background: #6b7280;
        color: white;
        border: none;
    }
    .action-buttons .btn-cancel:hover {
        background: #4b5563;
        color: white;
    }
    .action-buttons .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .action-buttons .btn-success-action {
        background: #10b981;
        color: white;
        border: none;
    }
    .action-buttons .btn-success-action:hover {
        background: #059669;
        color: white;
    }

    .employee-status-card {
        background: rgba(255,255,255,0.1);
        border-radius: 12px;
        padding: 15px 25px;
        margin-bottom: 15px;
        backdrop-filter: blur(10px);
    }
    .employee-status-card .status-badge-lg {
        font-size: 18px;
        padding: 8px 20px;
        border-radius: 30px;
    }

    /* Toast Notification */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
    }
    .toast-message {
        padding: 15px 20px;
        border-radius: 12px;
        color: white;
        font-weight: 500;
        margin-bottom: 10px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        animation: slideInRight 0.5s ease;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .toast-message.success { background: linear-gradient(135deg, #10b981, #059669); }
    .toast-message.error { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .toast-message.info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .toast-message.warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .toast-message .toast-icon { font-size: 24px; }
    .toast-message .toast-close {
        margin-left: auto;
        cursor: pointer;
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        opacity: 0.7;
    }
    .toast-message .toast-close:hover { opacity: 1; }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }

    /* Stats Cards */
    .stats-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .stats-card .stats-icon {
        font-size: 2rem;
        opacity: 0.3;
        position: absolute;
        right: 15px;
        bottom: 15px;
    }
    .stats-card .stats-number {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 2px;
    }
    .stats-card .stats-label {
        font-size: 0.85rem;
        opacity: 0.8;
        margin-bottom: 0;
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Stats Cards -->
        <div class="col-12 mb-4">
            <div class="row g-3">
                <div class="col-md-3 col-sm-6">
                    <div class="card stats-card bg-gradient-primary text-white position-relative" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="card-body">
                            <i class="fas fa-users stats-icon"></i>
                            <div class="stats-number" id="totalEnrolled">{{ $totalEnrolled }}</div>
                            <p class="stats-label">Total Enrolled</p>
                            <small class="opacity-75">{{ $totalEmployees }} total employees</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stats-card bg-gradient-success text-white position-relative" style="background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);">
                        <div class="card-body">
                            <i class="fas fa-check-circle stats-icon"></i>
                            <div class="stats-number" id="presentToday">{{ $presentToday }}</div>
                            <p class="stats-label">Present Today</p>
                            <small class="opacity-75">Marked attendance today</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stats-card bg-gradient-warning text-white position-relative" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);">
                        <div class="card-body">
                            <i class="fas fa-hourglass-half stats-icon"></i>
                            <div class="stats-number" id="halfDayToday">{{ $halfDayToday }}</div>
                            <p class="stats-label">Half Day</p>
                            <small class="opacity-75">Marked as half day</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stats-card bg-gradient-danger text-white position-relative" style="background: linear-gradient(135deg, #e17055 0%, #d63031 100%);">
                        <div class="card-body">
                            <i class="fas fa-user-slash stats-icon"></i>
                            <div class="stats-number" id="absentToday">{{ $absentToday }}</div>
                            <p class="stats-label">Absent</p>
                            <small class="opacity-75">Not marked yet</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Camera Section -->
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="fas fa-camera me-2"></i>Face Scanner
                            </h5>
                        </div>
                        <div class="col-auto">
                            <span id="statusBadge" class="badge bg-warning pulse">Waiting</span>
                            <span id="halfDayBadge" class="badge bg-warning ms-2 d-none">🌓 Half Day</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="face-scanner" style="background: #0a0a0a;">
                        <video id="video" autoplay muted playsinline style="width:100%; height:auto; min-height:400px;"></video>
                        <canvas id="overlay" style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:2;"></canvas>
                        
                        <!-- Scan Overlay -->
                        <div id="scanOverlay" class="scan-overlay">
                            <div class="text-center">
                                <div id="scanSpinner" class="spinner-border text-light mb-3" style="width:50px;height:50px;" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <h4 id="scanMessage">Initializing Camera...</h4>
                                <small id="scanSubMessage" class="text-light">Please allow camera access</small>
                            </div>
                        </div>
                        
                        <!-- Action Overlay -->
                        <div id="actionOverlay" class="scan-overlay d-none action">
                            <div class="text-center">
                                <div class="employee-status-card">
                                    <h3 id="actionEmployeeName" class="text-white mb-2">John Doe</h3>
                                    <div id="actionEmployeeStatus" class="mb-3">
                                        <span class="badge bg-success status-badge-lg">✅ Clocked In</span>
                                    </div>
                                    <div id="actionHalfDayInfo" class="mb-3 d-none">
                                        <span class="badge bg-warning status-badge-lg">🌓 Half Day</span>
                                        <br>
                                        <small class="text-light">Threshold: <span id="actionThreshold">4.0</span> hrs</small>
                                    </div>
                                    <p id="actionMessage" class="text-light mb-3">What would you like to do?</p>
                                </div>
                                <div class="action-buttons">
                                    <button id="actionTeaBreak" class="btn btn-tea">
                                        <i class="fas fa-coffee me-2"></i>Tea Break
                                    </button>
                                    <button id="actionPunchOut" class="btn btn-punch-out">
                                        <i class="fas fa-sign-out-alt me-2"></i>Punch Out
                                    </button>
                                    <button id="actionCancel" class="btn btn-cancel">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating Recognition Badge -->
                        <div id="recognitionBadge" class="floating-badge d-none">
                            <div id="recognitionCard" class="p-3 rounded shadow-lg" style="background: linear-gradient(135deg, #00b894, #00cec9); color: white; min-width: 200px;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle fa-2x me-3"></i>
                                    <div>
                                        <div id="recognizedName" style="font-size: 20px; font-weight: bold;">John Doe</div>
                                        <small id="recognitionTime" class="text-light">Just now</small>
                                    </div>
                                </div>
                                <div id="recognitionConfidence" class="mt-1" style="font-size: 12px; opacity: 0.8;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="btn-group" role="group">
                                <button id="startBtn" class="btn btn-success">
                                    <i class="fas fa-play me-2"></i>Start Auto-Scan
                                </button>
                                <button id="stopBtn" class="btn btn-danger d-none">
                                    <i class="fas fa-stop me-2"></i>Stop
                                </button>
                                <button id="resetBtn" class="btn btn-warning d-none">
                                    <i class="fas fa-sync me-2"></i>Reset View
                                </button>
                                <button id="debugToggleBtn" class="btn btn-secondary">
                                    <i class="fas fa-bug me-2"></i>Debug
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                <span id="frameStatus">Ready</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Debug Console -->
            <div id="debugConsole" class="debug-console mt-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong style="color: #fff;">Debug Log</strong>
                    <button class="btn btn-sm btn-danger" onclick="document.getElementById('debugConsole').style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="debugLogContainer"></div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Recent Activity -->
            <div class="card shadow">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>Today's Log
                    </h5>
                    <span class="badge bg-light text-dark" id="logCount">{{ $todayLog->count() }}</span>
                </div>
                <div class="card-body p-0 employee-list" id="logContainer">
                    @if($todayLog->count() > 0)
                        @foreach($todayLog as $log)
                            @php
                                $isFace = $log->marked_by == 'face_recognition';
                                $isBreak = $log->tea_break_out != '00:00:00' && $log->tea_break_in == '00:00:00';
                                $isHalfDay = $log->status == 'Half Day';
                                $isLate = $log->late != '00:00:00';
                                
                                $className = 'log-entry';
                                if ($isFace) $className .= ' face';
                                if ($isBreak) $className .= ' break';
                                if ($isHalfDay) $className .= ' halfday';
                            @endphp
                            <div class="{{ $className }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $log->employee->name ?? 'Unknown' }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ $log->clock_in }}
                                            @if($isLate)
                                                <span class="badge bg-warning text-dark ms-1">Late</span>
                                            @endif
                                            @if($log->clock_out && $log->clock_out != '00:00:00')
                                                <span class="badge bg-secondary ms-1">Out: {{ $log->clock_out }}</span>
                                            @endif
                                            @if($isBreak)
                                                <span class="badge badge-break ms-1">☕ Break</span>
                                            @endif
                                            @if($isHalfDay)
                                                <span class="badge badge-halfday ms-1">🌓 Half Day</span>
                                            @endif
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        @if($log->clock_out != '00:00:00')
                                            <span class="badge bg-secondary">Clocked Out</span>
                                        @elseif($isBreak)
                                            <span class="badge bg-warning">On Break</span>
                                        @elseif($isHalfDay)
                                            <span class="badge bg-warning">🌓 Half Day</span>
                                        @else
                                            <span class="badge bg-success">Present</span>
                                        @endif
                                        @if($isFace)
                                            <i class="fas fa-face-smile text-primary ms-1" title="Face Recognition"></i>
                                            <span class="badge badge-face ms-1">Face</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-user-clock fa-3x mb-3 d-block"></i>
                            <p>No attendance recorded today</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card shadow mt-3">
                <div class="card-body">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-chart-bar me-2"></i>Quick Stats
                    </h6>
                    <div class="row text-center">
                        <div class="col-3">
                            <h5 class="text-success" id="statPresent">{{ $presentToday }}</h5>
                            <small>Present</small>
                        </div>
                        <div class="col-3">
                            <h5 class="text-warning" id="statHalfDay">{{ $halfDayToday }}</h5>
                            <small>Half Day</small>
                        </div>
                        <div class="col-3">
                            <h5 class="text-danger" id="statAbsent">{{ $absentToday }}</h5>
                            <small>Absent</small>
                        </div>
                        <div class="col-3">
                            <h5 class="text-info" id="statTotal">{{ $totalEmployees }}</h5>
                            <small>Total</small>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="progress" style="height: 8px;">
                            @php
                                $presentPercent = $totalEmployees > 0 ? round(($presentToday / $totalEmployees) * 100) : 0;
                                $halfDayPercent = $totalEmployees > 0 ? round(($halfDayToday / $totalEmployees) * 100) : 0;
                                $absentPercent = $totalEmployees > 0 ? round(($absentToday / $totalEmployees) * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $presentPercent }}%;" aria-valuenow="{{ $presentPercent }}" aria-valuemin="0" aria-valuemax="100">{{ $presentPercent }}%</div>
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $halfDayPercent }}%;" aria-valuenow="{{ $halfDayPercent }}" aria-valuemin="0" aria-valuemax="100">{{ $halfDayPercent }}%</div>
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $absentPercent }}%;" aria-valuenow="{{ $absentPercent }}" aria-valuemin="0" aria-valuemax="100">{{ $absentPercent }}%</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Last Recognized -->
            <div class="card shadow mt-3">
                <div class="card-body">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-user-check me-2"></i>Last Recognized
                    </h6>
                    <div id="lastRecognized" class="text-center text-muted">
                        <p>No one recognized yet</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Face API Scripts -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const video = document.getElementById('video');
    const canvas = document.getElementById('overlay');
    const startBtn = document.getElementById('startBtn');
    const stopBtn = document.getElementById('stopBtn');
    const resetBtn = document.getElementById('resetBtn');
    const debugToggleBtn = document.getElementById('debugToggleBtn');
    const scanOverlay = document.getElementById('scanOverlay');
    const actionOverlay = document.getElementById('actionOverlay');
    const scanMessage = document.getElementById('scanMessage');
    const scanSubMessage = document.getElementById('scanSubMessage');
    const scanSpinner = document.getElementById('scanSpinner');
    const statusBadge = document.getElementById('statusBadge');
    const halfDayBadge = document.getElementById('halfDayBadge');
    const recognitionBadge = document.getElementById('recognitionBadge');
    const recognitionCard = document.getElementById('recognitionCard');
    const recognizedName = document.getElementById('recognizedName');
    const recognitionTime = document.getElementById('recognitionTime');
    const recognitionConfidence = document.getElementById('recognitionConfidence');
    const frameStatus = document.getElementById('frameStatus');
    const logContainer = document.getElementById('logContainer');
    const logCount = document.getElementById('logCount');
    const lastRecognized = document.getElementById('lastRecognized');
    const debugConsole = document.getElementById('debugConsole');
    const debugLogContainer = document.getElementById('debugLogContainer');
    const onBreakCount = document.getElementById('onBreakCount');
    
    // Action elements
    const actionEmployeeName = document.getElementById('actionEmployeeName');
    const actionEmployeeStatus = document.getElementById('actionEmployeeStatus');
    const actionHalfDayInfo = document.getElementById('actionHalfDayInfo');
    const actionThreshold = document.getElementById('actionThreshold');
    const actionMessage = document.getElementById('actionMessage');
    const actionTeaBreak = document.getElementById('actionTeaBreak');
    const actionPunchOut = document.getElementById('actionPunchOut');
    const actionCancel = document.getElementById('actionCancel');
    
    // Status elements
    const totalEnrolledEl = document.getElementById('totalEnrolled');
    const presentTodayEl = document.getElementById('presentToday');
    const halfDayTodayEl = document.getElementById('halfDayToday');
    const absentTodayEl = document.getElementById('absentToday');
    const statPresent = document.getElementById('statPresent');
    const statHalfDay = document.getElementById('statHalfDay');
    const statAbsent = document.getElementById('statAbsent');
    const statTotal = document.getElementById('statTotal');
    
    // State
    let isScanning = false;
    let scanInterval = null;
    let processedFaces = new Map();
    let isCameraReady = false;
    let stream = null;
    let recognitionCooldown = 30000;
    let debugMode = false;
    let lastRecognizedData = null;
    let pendingActionEmployee = null;
    let pendingActionStatus = null;
    let isActionMode = false;
    let isProcessingAction = false;
    
    // Today's date
    const today = new Date().toISOString().split('T')[0];
    
    // Toast notification
    function showToast(message, type = 'success', duration = 4000) {
        const container = document.getElementById('toastContainer') || createToastContainer();
        const toast = document.createElement('div');
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-times-circle',
            info: 'fas fa-info-circle',
            warning: 'fas fa-exclamation-triangle'
        };
        toast.className = `toast-message ${type}`;
        toast.innerHTML = `
            <span class="toast-icon"><i class="${icons[type] || icons.info}"></i></span>
            <span>${message}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
        `;
        container.appendChild(toast);
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideOutRight 0.5s ease forwards';
                setTimeout(() => toast.remove(), 500);
            }
        }, duration);
    }
    
    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }
    
    // Debug function
    function debugLog(message, type = 'info', data = null) {
        if (!debugMode) return;
        
        const time = new Date().toLocaleTimeString();
        const entry = document.createElement('div');
        entry.className = 'log-entry-debug';
        entry.innerHTML = `
            <span class="log-time">[${time}]</span>
            <span class="log-${type}">${message}</span>
            ${data ? `<pre style="margin: 5px 0 0 20px; color: #888; font-size: 10px;">${JSON.stringify(data, null, 2)}</pre>` : ''}
        `;
        debugLogContainer.appendChild(entry);
        debugLogContainer.scrollTop = debugLogContainer.scrollHeight;
        
        while (debugLogContainer.children.length > 50) {
            debugLogContainer.removeChild(debugLogContainer.firstChild);
        }
    }
    
    // Toggle debug
    debugToggleBtn.addEventListener('click', function() {
        debugMode = !debugMode;
        debugConsole.style.display = debugMode ? 'block' : 'none';
        this.innerHTML = debugMode ? '<i class="fas fa-bug me-2"></i>Hide Debug' : '<i class="fas fa-bug me-2"></i>Debug';
        if (debugMode) {
            debugLog('Debug mode enabled', 'info');
        }
    });
    
    // Load initial stats
    loadStats();
    loadLog();
    
    // Initialize Face API
    async function initFaceAPI() {
        updateStatus('loading', 'Loading AI Models...');
        scanMessage.textContent = 'Loading Face Recognition Models...';
        scanSubMessage.textContent = 'This may take 10-15 seconds';
        debugLog('Initializing Face API...', 'info');
        
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri('/js/face-api/weights'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/js/face-api/weights'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/js/face-api/weights')
            ]);
            
            debugLog('✅ Face API models loaded successfully', 'success');
            await startCamera();
        } catch (error) {
            debugLog('❌ Face API load error: ' + error.message, 'error');
            updateStatus('error', 'Failed to load AI models');
            scanMessage.textContent = '❌ Failed to load Face Recognition';
            scanSubMessage.textContent = 'Please refresh or check console for errors';
        }
    }
    
    // Start Camera
    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: 'user'
                }
            });
            
            video.srcObject = stream;
            await video.play();
            
            isCameraReady = true;
            debugLog('✅ Camera started successfully', 'success');
            updateStatus('ready', 'Camera Ready');
            scanMessage.innerHTML = '<i class="fas fa-camera text-success me-2"></i>Camera Ready';
            scanSubMessage.textContent = 'Click "Start Auto-Scan" to begin';
            startBtn.disabled = false;
            
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            
        } catch (error) {
            debugLog('❌ Camera error: ' + error.message, 'error');
            updateStatus('error', 'Camera Access Denied');
            scanMessage.textContent = '❌ Camera Access Denied';
            scanSubMessage.textContent = 'Please allow camera access in browser settings';
        }
    }
    
    // Start Auto-Scan
    function startAutoScan() {
        if (!isCameraReady) {
            alert('Please wait for camera to initialize');
            return;
        }
        
        if (isScanning) return;
        
        isScanning = true;
        startBtn.classList.add('d-none');
        stopBtn.classList.remove('d-none');
        resetBtn.classList.remove('d-none');
        
        processedFaces = new Map();
        debugLog('🔄 Auto-scan started', 'info');
        
        updateStatus('scanning', 'Scanning for Faces...');
        scanMessage.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Scanning for Faces';
        scanSubMessage.textContent = 'Show your face clearly to the camera';
        frameStatus.textContent = '🔄 Scanning...';
        
        scanForFace();
        scanInterval = setInterval(scanForFace, 1500);
    }
    
    // Stop Auto-Scan
    function stopAutoScan() {
        isScanning = false;
        clearInterval(scanInterval);
        scanInterval = null;
        
        debugLog('⏸️ Auto-scan stopped', 'info');
        
        startBtn.classList.remove('d-none');
        startBtn.innerHTML = '<i class="fas fa-play me-2"></i>Resume Scan';
        stopBtn.classList.add('d-none');
        resetBtn.classList.add('d-none');
        
        updateStatus('ready', 'Scan Paused');
        scanMessage.innerHTML = '<i class="fas fa-pause-circle me-2"></i>Scan Paused';
        scanSubMessage.textContent = 'Click "Resume Scan" to continue';
        frameStatus.textContent = '⏸️ Paused';
    }
    
    // Show Action Overlay
    function showActionOverlay(employee, status, halfDayThreshold = null, isHalfDay = false) {
        isActionMode = true;
        pendingActionEmployee = employee;
        pendingActionStatus = status;
        actionOverlay.classList.remove('d-none');
        scanOverlay.style.display = 'none';
        
        // Reset button states
        actionTeaBreak.disabled = false;
        actionPunchOut.disabled = false;
        actionTeaBreak.style.opacity = '1';
        actionPunchOut.style.opacity = '1';
        
        // Update button based on status
        if (status === 'on_break') {
            actionTeaBreak.innerHTML = '<i class="fas fa-coffee me-2"></i>End Break';
            actionTeaBreak.className = 'btn btn-tea end-break';
            actionMessage.textContent = 'You are on break. End your break or punch out?';
        } else {
            actionTeaBreak.innerHTML = '<i class="fas fa-coffee me-2"></i>Tea Break';
            actionTeaBreak.className = 'btn btn-tea';
            actionMessage.textContent = 'What would you like to do?';
        }
        
        actionEmployeeName.textContent = employee.name || 'Employee';
        
        // Show half day info if applicable
        if (isHalfDay && halfDayThreshold) {
            actionHalfDayInfo.classList.remove('d-none');
            actionThreshold.textContent = halfDayThreshold;
        } else {
            actionHalfDayInfo.classList.add('d-none');
        }
        
        // Determine status display
        let statusText = 'Clocked In';
        let statusClass = 'bg-success';
        
        if (status === 'on_break') {
            statusText = '☕ On Break';
            statusClass = 'bg-warning';
        } else if (status === 'clocked_out') {
            statusText = '✅ Clocked Out';
            statusClass = 'bg-secondary';
        } else if (status === 'not_clocked_in') {
            statusText = '⏳ Not Clocked In';
            statusClass = 'bg-danger';
        } else if (isHalfDay) {
            statusText = '🌓 Half Day';
            statusClass = 'bg-warning';
        }
        
        actionEmployeeStatus.innerHTML = `<span class="badge ${statusClass} status-badge-lg">${statusText}</span>`;
        
        if (status === 'not_clocked_in') {
            actionMessage.textContent = 'Please clock in first!';
            actionTeaBreak.disabled = true;
            actionPunchOut.disabled = true;
            actionTeaBreak.style.opacity = '0.5';
            actionPunchOut.style.opacity = '0.5';
        }
    }
    
    // Hide Action Overlay
    function hideActionOverlay() {
        isActionMode = false;
        pendingActionEmployee = null;
        pendingActionStatus = null;
        actionOverlay.classList.add('d-none');
        scanOverlay.style.display = 'flex';
        actionTeaBreak.disabled = false;
        actionPunchOut.disabled = false;
        actionTeaBreak.style.opacity = '1';
        actionPunchOut.style.opacity = '1';
        isProcessingAction = false;
        actionTeaBreak.innerHTML = '<i class="fas fa-coffee me-2"></i>Tea Break';
        actionTeaBreak.className = 'btn btn-tea';
        actionHalfDayInfo.classList.add('d-none');
    }
    
    // Handle Tea Break / End Break
    actionTeaBreak.addEventListener('click', async function() {
        if (!pendingActionEmployee || isProcessingAction) return;

        isProcessingAction = true;
        const isOnBreak = pendingActionStatus === 'on_break';
        const action = isOnBreak ? 'tea_break_out' : 'tea_break_in';
        const actionLabel = isOnBreak ? 'Ending Break' : 'Tea Break';
        
        debugLog(`☕ ${actionLabel} requested for: ${pendingActionEmployee.name}`, 'info');
        
        // Disable buttons
        actionTeaBreak.disabled = true;
        actionPunchOut.disabled = true;
        actionTeaBreak.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        
        try {
            const response = await fetch('{{ route("attendance.attendance") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: action,
                    employee_id: pendingActionEmployee.id
                })
            });

            const data = await response.json();
            debugLog('📥 ' + actionLabel + ' response', 'info', data);

            if (data.success) {
                hideActionOverlay();
                const successMsg = isOnBreak ? 'Break ended successfully! 🍵' : 'Tea Break Started! ☕';
                showToast(successMsg, 'success');
                
                scanMessage.innerHTML = isOnBreak 
                    ? '<i class="fas fa-check-circle text-success me-2"></i>Break Ended!'
                    : '<i class="fas fa-coffee text-warning me-2"></i>Tea Break Started!';
                scanSubMessage.textContent = isOnBreak ? 'Welcome back! 👋' : 'Enjoy your break ☕';
                updateStatus('success', isOnBreak ? 'Break Ended' : 'On Break');
                showRecognitionSuccess(pendingActionEmployee.name + (isOnBreak ? ' - Break Ended ✅' : ' - On Break ☕'), null);
                
                setTimeout(() => {
                    loadStats();
                    loadLog();
                }, 1000);
            } else {
                showToast(data.message || 'Failed to process action', 'error');
                scanMessage.innerHTML = '<i class="fas fa-exclamation-triangle text-warning me-2"></i>' + (data.message || 'Action failed');
                scanSubMessage.textContent = 'Please try again';
                updateStatus('error', 'Action failed');
                
                setTimeout(() => {
                    actionTeaBreak.disabled = false;
                    actionPunchOut.disabled = false;
                    actionTeaBreak.innerHTML = isOnBreak ? '<i class="fas fa-coffee me-2"></i>End Break' : '<i class="fas fa-coffee me-2"></i>Tea Break';
                    isProcessingAction = false;
                }, 3000);
            }
        } catch (error) {
            debugLog('❌ ' + actionLabel + ' error: ' + error.message, 'error');
            showToast('Error: ' + error.message, 'error');
            scanMessage.innerHTML = '<i class="fas fa-exclamation-triangle text-danger me-2"></i>Error';
            scanSubMessage.textContent = 'Please try again';
            
            setTimeout(() => {
                actionTeaBreak.disabled = false;
                actionPunchOut.disabled = false;
                actionTeaBreak.innerHTML = isOnBreak ? '<i class="fas fa-coffee me-2"></i>End Break' : '<i class="fas fa-coffee me-2"></i>Tea Break';
                isProcessingAction = false;
            }, 3000);
        }
    });

    // Handle Punch Out
    actionPunchOut.addEventListener('click', async function() {
        if (!pendingActionEmployee || isProcessingAction) return;

        isProcessingAction = true;
        debugLog(`🚪 Punch Out requested for: ${pendingActionEmployee.name}`, 'info');
        
        // Disable buttons
        actionTeaBreak.disabled = true;
        actionPunchOut.disabled = true;
        actionPunchOut.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        
        try {
            const response = await fetch('{{ route("attendance.attendance") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: 'punch_out',
                    employee_id: pendingActionEmployee.id
                })
            });

            const data = await response.json();
            debugLog('📥 Punch Out response', 'info', data);

            if (data.success) {
                hideActionOverlay();
                showToast('Punched Out Successfully! ✅', 'success');
                
                scanMessage.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>Punched Out Successfully!';
                scanSubMessage.textContent = 'See you tomorrow! 👋';
                updateStatus('success', 'Punched Out');
                showRecognitionSuccess(pendingActionEmployee.name + ' - Punched Out ✅', null);
                
                setTimeout(() => {
                    loadStats();
                    loadLog();
                }, 1000);
            } else {
                showToast(data.message || 'Failed to punch out', 'error');
                scanMessage.innerHTML = '<i class="fas fa-exclamation-triangle text-warning me-2"></i>' + (data.message || 'Failed to punch out');
                scanSubMessage.textContent = 'Please try again';
                updateStatus('error', 'Punch Out failed');
                
                setTimeout(() => {
                    actionTeaBreak.disabled = false;
                    actionPunchOut.disabled = false;
                    actionPunchOut.innerHTML = '<i class="fas fa-sign-out-alt me-2"></i>Punch Out';
                    isProcessingAction = false;
                }, 3000);
            }
        } catch (error) {
            debugLog('❌ Punch Out error: ' + error.message, 'error');
            showToast('Error: ' + error.message, 'error');
            scanMessage.innerHTML = '<i class="fas fa-exclamation-triangle text-danger me-2"></i>Error';
            scanSubMessage.textContent = 'Please try again';
            
            setTimeout(() => {
                actionTeaBreak.disabled = false;
                actionPunchOut.disabled = false;
                actionPunchOut.innerHTML = '<i class="fas fa-sign-out-alt me-2"></i>Punch Out';
                isProcessingAction = false;
            }, 3000);
        }
    });

    // Handle Cancel
    actionCancel.addEventListener('click', function() {
        debugLog('❌ Action cancelled by user', 'info');
        hideActionOverlay();
        isProcessingAction = false;
        scanMessage.innerHTML = '<i class="fas fa-camera me-2"></i>Scanning resumed';
        scanSubMessage.textContent = 'Show your face clearly to the camera';
        updateStatus('scanning', 'Scanning...');
        
        // Reset button states
        actionTeaBreak.disabled = false;
        actionPunchOut.disabled = false;
        actionTeaBreak.innerHTML = '<i class="fas fa-coffee me-2"></i>Tea Break';
        actionTeaBreak.className = 'btn btn-tea';
        actionPunchOut.innerHTML = '<i class="fas fa-sign-out-alt me-2"></i>Punch Out';
    });
    
    // Scan for Face
    async function scanForFace() {
        if (!isScanning || !isCameraReady || isActionMode) return;
        
        try {
            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                inputSize: 320,
                scoreThreshold: 0.5
            }))
            .withFaceLandmarks()
            .withFaceDescriptor();
            
            if (!detection) {
                updateStatus('scanning', 'No Face Detected');
                scanMessage.innerHTML = '<i class="fas fa-camera me-2"></i>Looking for Faces...';
                scanSubMessage.textContent = 'Please stand 1-2 feet from camera';
                clearCanvas();
                return;
            }
            
            drawFaceDetection(detection);
            
            const faceId = Array.from(detection.descriptor).slice(0, 15).join(',');
            const now = Date.now();
            
            if (processedFaces.has(faceId)) {
                const lastProcess = processedFaces.get(faceId);
                if (now - lastProcess < recognitionCooldown) {
                    scanMessage.innerHTML = '<i class="fas fa-clock text-info me-2"></i>Already Processed';
                    scanSubMessage.textContent = 'Waiting for new face or cooldown';
                    return;
                }
            }
            
            processedFaces.set(faceId, now);
            scanMessage.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying Identity...';
            scanSubMessage.textContent = 'Checking against database';
            updateStatus('verifying', 'Verifying...');
            scanSpinner.classList.remove('d-none');
            
            debugLog('🔍 Verifying face...', 'info');
            
            const response = await fetch('{{ route("attendance.verify.face") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    descriptor: Array.from(detection.descriptor)
                })
            });
            
            const data = await response.json();
            debugLog('📥 Verification response received', 'info', data);
            
            if (data.success) {
                debugLog(`✅ Face recognized: ${data.user_name} (${data.confidence}% confidence)`, 'success');
                
                try {
                    const statusResponse = await fetch('{{ route("attendance.face.status") }}?employee_id=' + data.employee_id, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    
                    const statusContentType = statusResponse.headers.get('content-type');
                    if (!statusContentType || !statusContentType.includes('application/json')) {
                        debugLog('⚠️ Status response is not JSON, marking attendance directly', 'warning');
                        await markAttendanceDirect(data);
                        scanSpinner.classList.add('d-none');
                        return;
                    }
                    
                    const statusData = await statusResponse.json();
                    debugLog('📥 Attendance status', 'info', statusData);
                    
                    if (!statusData.clocked_in) {
                        // Not clocked in - mark attendance
                        debugLog(`📝 Marking attendance for: ${data.user_name}`, 'info');
                        await markAttendanceDirect(data);
                    } else {
                        // Already clocked in - show action options
                        let status = 'clocked_in';
                        if (statusData.on_break) {
                            status = 'on_break';
                        } else if (statusData.clocked_out) {
                            status = 'clocked_out';
                        }
                        
                        const isHalfDay = statusData.status === 'Half Day' || false;
                        const halfDayThreshold = statusData.half_day_threshold || 4.0;
                        
                        if (isHalfDay) {
                            halfDayBadge.classList.remove('d-none');
                            halfDayBadge.textContent = '🌓 Half Day (' + halfDayThreshold + 'hrs)';
                        } else {
                            halfDayBadge.classList.add('d-none');
                        }
                        
                        debugLog(`ℹ️ ${data.user_name} is already clocked in. Status: ${status}${isHalfDay ? ' (Half Day)' : ''}`, 'info');
                        showRecognitionSuccess(data.user_name + ' - Actions Available', data.confidence);
                        scanMessage.innerHTML = `<i class="fas fa-user-check text-success me-2"></i>Welcome back ${data.user_name}!`;
                        scanSubMessage.textContent = status === 'on_break' ? 'You are on break. Choose an action below.' : 'Choose an action below';
                        updateStatus('info', 'Action Required');
                        
                        setTimeout(() => {
                            showActionOverlay(
                                { id: data.employee_id, name: data.user_name },
                                status,
                                halfDayThreshold,
                                isHalfDay
                            );
                        }, 1000);
                    }
                } catch (statusError) {
                    debugLog('⚠️ Status check error: ' + statusError.message, 'warning');
                    await markAttendanceDirect(data);
                }
            } else {
                debugLog(`❌ Face not recognized: ${data.message}`, 'error');
                scanMessage.innerHTML = '<i class="fas fa-user-slash text-danger me-2"></i>Not Recognized';
                scanSubMessage.textContent = data.message || 'Please enroll your face first or try again';
                updateStatus('error', 'Face not recognized');
                showRecognitionError();
                showToast(data.message || 'Face not recognized', 'error');
            }
            
            scanSpinner.classList.add('d-none');
            
        } catch (error) {
            debugLog('❌ Scan error: ' + error.message, 'error', { stack: error.stack });
            updateStatus('error', 'Scan Error');
            scanMessage.innerHTML = '<i class="fas fa-exclamation-triangle text-danger me-2"></i>Error Detected';
            scanSubMessage.textContent = 'Please try again';
            scanSpinner.classList.add('d-none');
            showToast('Scan error: ' + error.message, 'error');
        }
    }
    
    // Helper function to mark attendance directly
    async function markAttendanceDirect(data) {
        const markResponse = await fetch('{{ route("attendance.mark.face") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                employee_id: data.employee_id,
                user_id: data.user_id,
                date: today,
                time: new Date().toTimeString().slice(0, 8)
            })
        });
        
        const markData = await markResponse.json();
        debugLog('📥 Mark attendance response', 'info', markData);
        
        if (markData.success) {
            debugLog(`✅ Attendance marked successfully for: ${data.user_name}`, 'success');
            showRecognitionSuccess(data.user_name, data.confidence);
            scanMessage.innerHTML = `<i class="fas fa-check-circle text-success me-2"></i>Welcome ${data.user_name}!`;
            scanSubMessage.textContent = `✅ Attendance marked (${data.confidence}% confidence)`;
            updateStatus('success', `Recognized: ${data.user_name}`);
            showToast(`Welcome ${data.user_name}! Attendance marked ✅`, 'success');
            
            lastRecognizedData = {
                name: data.user_name,
                time: new Date().toLocaleTimeString(),
                confidence: data.confidence
            };
            updateLastRecognized();
            
            setTimeout(() => {
                loadStats();
                loadLog();
            }, 500);
        } else {
            debugLog(`❌ Failed to mark attendance: ${markData.message}`, 'error');
            scanMessage.innerHTML = `<i class="fas fa-exclamation-triangle text-warning me-2"></i>${markData.message || 'Failed to mark attendance'}`;
            scanSubMessage.textContent = 'Please try again or contact HR';
            updateStatus('error', 'Mark failed');
            showToast(markData.message || 'Failed to mark attendance', 'error');
        }
    }
    
    // Draw face detection on canvas
    function drawFaceDetection(detection) {
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        const box = detection.detection.box;
        
        ctx.strokeStyle = '#00ff00';
        ctx.lineWidth = 2;
        ctx.strokeRect(box.x, box.y, box.width, box.height);
        
        const landmarks = detection.landmarks;
        ctx.fillStyle = '#00ff00';
        landmarks.positions.forEach(p => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, 2, 0, 2 * Math.PI);
            ctx.fill();
        });
    }
    
    // Clear canvas
    function clearCanvas() {
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
    
    // Show recognition success
    function showRecognitionSuccess(name, confidence) {
        recognitionBadge.className = 'floating-badge';
        recognitionBadge.classList.remove('d-none');
        recognizedName.textContent = name;
        recognitionTime.textContent = new Date().toLocaleTimeString();
        if (confidence) {
            recognitionConfidence.textContent = `Confidence: ${confidence}%`;
        } else {
            recognitionConfidence.textContent = '';
        }
        
        if (confidence > 80) {
            recognitionCard.style.background = 'linear-gradient(135deg, #00b894, #00cec9)';
        } else if (confidence > 60) {
            recognitionCard.style.background = 'linear-gradient(135deg, #fdcb6e, #e17055)';
        } else {
            recognitionCard.style.background = 'linear-gradient(135deg, #e17055, #d63031)';
        }
        
        setTimeout(() => {
            recognitionBadge.classList.add('d-none');
        }, 5000);
    }
    
    // Show recognition error
    function showRecognitionError() {
        const container = document.querySelector('.face-scanner');
        container.style.border = '3px solid #ff0000';
        setTimeout(() => {
            container.style.border = 'none';
        }, 2000);
    }
    
    // Update status badge
    function updateStatus(type, message) {
        const classes = {
            'loading': 'bg-warning pulse',
            'ready': 'bg-success',
            'scanning': 'bg-info pulse',
            'verifying': 'bg-primary pulse',
            'success': 'bg-success',
            'error': 'bg-danger',
            'info': 'bg-info'
        };
        statusBadge.className = `badge ${classes[type] || 'bg-secondary'}`;
        statusBadge.textContent = message;
    }
    
    // Update last recognized
    function updateLastRecognized() {
        if (lastRecognizedData) {
            lastRecognized.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-user-check fa-2x text-success mb-2 d-block"></i>
                    <strong>${lastRecognizedData.name}</strong>
                    <br>
                    <small class="text-muted">${lastRecognizedData.time}</small>
                    <br>
                    <span class="badge bg-success">${lastRecognizedData.confidence}% confidence</span>
                </div>
            `;
        }
    }
    
    // Load stats
    async function loadStats() {
        try {
            const response = await fetch('{{ route("attendance.face.stats") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // Fallback: use PHP data
                updateStatsFromPHP();
                return;
            }
            
            const data = await response.json();
            
            if (data.success) {
                totalEnrolledEl.textContent = data.enrolled || 0;
                presentTodayEl.textContent = data.present || 0;
                halfDayTodayEl.textContent = data.half_day || 0;
                absentTodayEl.textContent = data.absent || 0;
                
                statTotal.textContent = data.total_employees || 0;
                statPresent.textContent = data.present || 0;
                statHalfDay.textContent = data.half_day || 0;
                statAbsent.textContent = data.absent || 0;
                
                let breakCount = 0;
                if (data.log) {
                    data.log.forEach(log => {
                        if (log.on_break) breakCount++;
                    });
                }
                onBreakCount.textContent = breakCount || 0;
            }
        } catch (error) {
            // Fallback: use PHP data
            updateStatsFromPHP();
        }
    }
    
    function updateStatsFromPHP() {
        totalEnrolledEl.textContent = {{ $totalEnrolled }};
        presentTodayEl.textContent = {{ $presentToday }};
        halfDayTodayEl.textContent = {{ $halfDayToday }};
        absentTodayEl.textContent = {{ $absentToday }};
        statTotal.textContent = {{ $totalEmployees }};
        statPresent.textContent = {{ $presentToday }};
        statHalfDay.textContent = {{ $halfDayToday }};
        statAbsent.textContent = {{ $absentToday }};
    }
    
    // Load log
    async function loadLog() {
        try {
            const response = await fetch('{{ route("attendance.face.stats") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return;
            }
            
            const data = await response.json();
            
            if (data.success && data.log) {
                logContainer.innerHTML = '';
                let count = 0;
                
                data.log.forEach(log => {
                    const entry = document.createElement('div');
                    const isFace = log.marked_by == 'face_recognition';
                    const isBreak = log.on_break || false;
                    const isHalfDay = log.status == 'Half Day' || false;
                    let className = 'log-entry';
                    if (isFace) className += ' face';
                    if (isBreak) className += ' break';
                    if (isHalfDay) className += ' halfday';
                    
                    entry.className = className;
                    entry.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${log.employee_name || 'Unknown'}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>${log.time || '--:--'}
                                    ${log.late && log.late != '00:00:00' ? '<span class="badge bg-warning text-dark ms-1">Late</span>' : ''}
                                    ${log.clock_out && log.clock_out != '00:00:00' ? '<span class="badge bg-secondary ms-1">Out: ' + log.clock_out + '</span>' : ''}
                                    ${isBreak ? '<span class="badge badge-break ms-1">☕ Break</span>' : ''}
                                    ${isHalfDay ? '<span class="badge badge-halfday ms-1">🌓 Half Day</span>' : ''}
                                </small>
                            </div>
                            <div class="text-end">
                                ${log.clock_out && log.clock_out != '00:00:00' ? '<span class="badge bg-secondary">Clocked Out</span>' : (isBreak ? '<span class="badge bg-warning">On Break</span>' : (isHalfDay ? '<span class="badge bg-warning">🌓 Half Day</span>' : '<span class="badge bg-success">Present</span>'))}
                                ${isFace ? '<span class="badge badge-face ms-1">Face</span>' : ''}
                            </div>
                        </div>
                    `;
                    logContainer.appendChild(entry);
                    count++;
                });
                
                logCount.textContent = count;
            }
        } catch (error) {
            // Silent fail
        }
    }
    
    // Reset view
    function resetView() {
        clearCanvas();
        hideActionOverlay();
        isProcessingAction = false;
        halfDayBadge.classList.add('d-none');
        scanMessage.innerHTML = '<i class="fas fa-camera me-2"></i>Camera Ready';
        scanSubMessage.textContent = 'Click "Start Auto-Scan" to begin';
        recognitionBadge.classList.add('d-none');
        updateStatus('ready', 'Ready');
        debugLog('🔄 View reset', 'info');
    }
    
    // Event Listeners
    startBtn.addEventListener('click', startAutoScan);
    stopBtn.addEventListener('click', stopAutoScan);
    resetBtn.addEventListener('click', resetView);
    
    // Initialize
    initFaceAPI();
    
    // Periodic stats refresh (every 30 seconds)
    setInterval(() => {
        loadStats();
        loadLog();
    }, 30000);
});
</script>
@endsection