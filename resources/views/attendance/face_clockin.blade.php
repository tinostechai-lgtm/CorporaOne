@extends('layouts.admin')

@section('page-title', 'My Face ID Attendance')

@section('content')
<style>
    .face-container {
        max-width: 500px;
        margin: 0 auto;
    }
    .face-container video {
        width: 100%;
        height: auto;
        background: #000;
        border-radius: 12px;
        aspect-ratio: 4/3;
        object-fit: cover;
    }
    .face-container .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: rgba(0,0,0,0.7);
        border-radius: 12px;
        color: white;
        z-index: 10;
    }
    .face-container .overlay .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(255,255,255,0.3);
        border-top: 4px solid #fff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .status-badge {
        font-size: 14px;
        padding: 8px 16px;
        border-radius: 30px;
        display: inline-block;
    }
    .status-badge.clocked-in { background: #28a745; color: white; }
    .status-badge.clocked-out { background: #dc3545; color: white; }
    .status-badge.on-break { background: #ffc107; color: black; }
    .status-badge.late { background: #fd7e14; color: white; }
    .status-badge.not-punched { background: #6c757d; color: white; }
    .status-badge.not-enrolled { background: #dc3545; color: white; }
    
    .action-buttons .btn {
        min-width: 140px;
        padding: 12px 24px;
        font-weight: 600;
        border-radius: 30px;
        transition: all 0.3s ease;
    }
    .action-buttons .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }
    .action-buttons .btn-tea {
        background: #f59e0b;
        color: white;
        border: none;
    }
    .action-buttons .btn-tea:hover {
        background: #d97706;
    }
    .action-buttons .btn-punch-out {
        background: #dc3545;
        color: white;
        border: none;
    }
    .action-buttons .btn-punch-out:hover {
        background: #b91c1c;
    }
    .action-buttons .btn-clock-in {
        background: #10b981;
        color: white;
        border: none;
    }
    .action-buttons .btn-clock-in:hover {
        background: #059669;
    }
    .action-buttons .btn-enroll {
        background: #6c5ce7;
        color: white;
        border: none;
    }
    .action-buttons .btn-enroll:hover {
        background: #5b4bd5;
    }
    
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
    
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .today-info {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 20px;
    }
    .today-info .label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .today-info .value {
        font-size: 20px;
        font-weight: 600;
    }
    .enrollment-warning {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .enrollment-warning h6 {
        color: #92400e;
        margin-bottom: 5px;
    }
    .enrollment-warning p {
        color: #78350f;
        margin-bottom: 10px;
    }
    .enrollment-status {
        font-size: 13px;
        padding: 8px 15px;
        border-radius: 8px;
        display: inline-block;
    }
    .enrollment-status.enrolled {
        background: #d1fae5;
        color: #065f46;
    }
    .enrollment-status.not-enrolled {
        background: #fee2e2;
        color: #991b1b;
    }
</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="fa fa-user-circle me-2"></i>
                        My Face ID Attendance
                    </h4>
                    <small class="opacity-75">Mark your attendance using face recognition</small>
                </div>

                <div class="card-body p-4">
                    
                    <!-- Today's Status -->
                    <div class="today-info">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="label">Today's Date</div>
                                <div class="value">{{ date('d M Y') }}</div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="label">Your Status</div>
                                <div id="myStatusBadge" class="status-badge not-punched mt-1">Not Clocked In</div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="label">Today's Work Hours</div>
                                <div class="value" id="todayWorkHours">--:--</div>
                            </div>
                        </div>
                    </div>

                    <!-- Enrollment Status -->
                    <div id="enrollmentStatusContainer" class="mb-3">
                        <div id="enrollmentStatus" class="enrollment-status not-enrolled">
                            <i class="fas fa-circle me-2"></i>
                            <span id="enrollmentStatusText">Checking enrollment status...</span>
                        </div>
                    </div>

                    <!-- Enrollment Warning -->
                    <div id="enrollmentWarning" class="enrollment-warning d-none">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i> Face Not Enrolled</h6>
                        <p>You need to enroll your face before you can mark attendance using Face ID.</p>
                        <a href="{{ route('face.enroll.page') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-user-plus me-1"></i> Enroll Your Face Now
                        </a>
                    </div>

                    <!-- Camera -->
                    <div class="face-container position-relative mb-4">
                        <video id="video" autoplay muted playsinline></video>
                        
                        <!-- Overlay -->
                        <div id="cameraOverlay" class="overlay">
                            <div class="spinner"></div>
                            <p class="mt-3 mb-0" id="overlayText">Loading AI models...</p>
                            <small class="text-white-50">Please wait</small>
                        </div>
                        
                        <!-- Detection Canvas -->
                        <canvas id="overlayCanvas" style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:5;pointer-events:none;border-radius:12px;"></canvas>
                    </div>

                    <!-- Status Message -->
                    <div id="statusMessage" class="text-center mb-3">
                        <span class="text-muted">Ready for face recognition</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons text-center">
                        <button id="clockInBtn" class="btn btn-clock-in d-none">
                            <i class="fa fa-sign-in-alt me-2"></i>Clock In
                        </button>
                        <button id="teaBreakBtn" class="btn btn-tea d-none">
                            <i class="fa fa-coffee me-2"></i>Start Tea Break
                        </button>
                        <button id="endBreakBtn" class="btn btn-tea d-none" style="background: #8b5cf6;">
                            <i class="fa fa-check me-2"></i>End Break
                        </button>
                        <button id="punchOutBtn" class="btn btn-punch-out d-none">
                            <i class="fa fa-sign-out-alt me-2"></i>Punch Out
                        </button>
                        <button id="enrollBtn" class="btn btn-enroll d-none">
                            <i class="fa fa-user-plus me-2"></i>Enroll Face
                        </button>
                    </div>

                    <!-- Last Recognition -->
                    <div id="lastRecognition" class="text-center text-muted small mt-3 d-none">
                        Last recognized: <span id="lastRecognitionName"></span> at <span id="lastRecognitionTime"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/face-api/face-api.min.js') }}?v={{ time() }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const video = document.getElementById('video');
    const canvas = document.getElementById('overlayCanvas');
    const cameraOverlay = document.getElementById('cameraOverlay');
    const overlayText = document.getElementById('overlayText');
    const statusMessage = document.getElementById('statusMessage');
    const myStatusBadge = document.getElementById('myStatusBadge');
    const todayWorkHours = document.getElementById('todayWorkHours');
    const lastRecognition = document.getElementById('lastRecognition');
    const lastRecognitionName = document.getElementById('lastRecognitionName');
    const lastRecognitionTime = document.getElementById('lastRecognitionTime');
    const enrollmentStatus = document.getElementById('enrollmentStatus');
    const enrollmentStatusText = document.getElementById('enrollmentStatusText');
    const enrollmentWarning = document.getElementById('enrollmentWarning');
    
    const clockInBtn = document.getElementById('clockInBtn');
    const teaBreakBtn = document.getElementById('teaBreakBtn');
    const endBreakBtn = document.getElementById('endBreakBtn');
    const punchOutBtn = document.getElementById('punchOutBtn');
    const enrollBtn = document.getElementById('enrollBtn');
    
    // State
    let modelsLoaded = false;
    let isProcessing = false;
    let stream = null;
    let scanInterval = null;
    let lastFaceId = null;
    let lastProcessTime = 0;
    const cooldown = 10000;
    let currentStatus = 'not_punched';
    let isEnrolled = false;
    let userEmployeeId = null;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    
    // Check enrollment status on load
    checkEnrollmentStatus();
    
    // Check enrollment status
    async function checkEnrollmentStatus() {
        try {
            const response = await fetch('{{ route("face.enrollment.status") }}', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            
            if (data.success) {
                userEmployeeId = data.employee_id;
                isEnrolled = data.enrolled;
                
                if (isEnrolled) {
                    enrollmentStatus.className = 'enrollment-status enrolled';
                    enrollmentStatusText.textContent = '✅ Face Enrolled';
                    enrollmentWarning.classList.add('d-none');
                    enrollBtn.classList.add('d-none');
                } else {
                    enrollmentStatus.className = 'enrollment-status not-enrolled';
                    enrollmentStatusText.textContent = '❌ Face Not Enrolled';
                    enrollmentWarning.classList.remove('d-none');
                    enrollBtn.classList.remove('d-none');
                    // Disable all other buttons
                    clockInBtn.classList.add('d-none');
                    teaBreakBtn.classList.add('d-none');
                    endBreakBtn.classList.add('d-none');
                    punchOutBtn.classList.add('d-none');
                    updateStatus('Please enroll your face first', 'warning');
                }
            }
        } catch (error) {
            console.error('Enrollment check error:', error);
        }
    }
    
    // Toast function
    function showToast(message, type = 'success', duration = 4000) {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        const icons = {
            success: 'fa fa-check-circle',
            error: 'fa fa-times-circle',
            info: 'fa fa-info-circle',
            warning: 'fa fa-exclamation-triangle'
        };
        toast.className = `toast-message ${type}`;
        toast.innerHTML = `
            <span><i class="${icons[type] || icons.info}"></i></span>
            <span>${message}</span>
            <button class="btn btn-sm btn-link text-white" onclick="this.parentElement.remove()" style="margin-left:auto;">&times;</button>
        `;
        container.appendChild(toast);
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideOutRight 0.5s ease forwards';
                setTimeout(() => toast.remove(), 500);
            }
        }, duration);
    }
    
    // Update status
    function updateStatus(message, type = 'info') {
        const colors = {
            info: 'text-info',
            success: 'text-success',
            warning: 'text-warning',
            danger: 'text-danger'
        };
        statusMessage.innerHTML = `<span class="${colors[type] || 'text-info'}">${message}</span>`;
    }
    
    // Update badge
    function updateBadge(status) {
        const map = {
            'clocked_in': { class: 'clocked-in', text: '✅ Clocked In' },
            'clocked_out': { class: 'clocked-out', text: '❌ Clocked Out' },
            'on_break': { class: 'on-break', text: '☕ On Break' },
            'late': { class: 'late', text: '⏰ Late In' },
            'not_punched': { class: 'not-punched', text: '⏳ Not Clocked In' },
            'not_enrolled': { class: 'not-enrolled', text: '❌ Not Enrolled' }
        };
        const data = map[status] || map['not_punched'];
        myStatusBadge.className = `status-badge ${data.class}`;
        myStatusBadge.textContent = data.text;
        currentStatus = status;
    }
    
    // Update buttons based on status and enrollment
    function updateButtons(status) {
        // Hide all buttons first
        clockInBtn.classList.add('d-none');
        teaBreakBtn.classList.add('d-none');
        endBreakBtn.classList.add('d-none');
        punchOutBtn.classList.add('d-none');
        enrollBtn.classList.add('d-none');
        
        // If not enrolled, show only enroll button
        if (!isEnrolled) {
            enrollBtn.classList.remove('d-none');
            return;
        }
        
        if (status === 'clocked_in' || status === 'late') {
            teaBreakBtn.classList.remove('d-none');
            punchOutBtn.classList.remove('d-none');
            teaBreakBtn.textContent = '☕ Start Tea Break';
            teaBreakBtn.className = 'btn btn-tea';
        } else if (status === 'on_break') {
            endBreakBtn.classList.remove('d-none');
            punchOutBtn.classList.remove('d-none');
        } else if (status === 'not_punched') {
            clockInBtn.classList.remove('d-none');
        } else if (status === 'clocked_out') {
            // No actions available
        }
    }
    
    // Load user status
    async function loadUserStatus() {
        try {
            const response = await fetch('{{ route("attendance.face.status") }}', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            
            if (data.clocked_in) {
                const status = data.on_break ? 'on_break' : (data.clocked_out ? 'clocked_out' : 'clocked_in');
                updateBadge(status);
                updateButtons(status);
                if (data.worked_hours) {
                    todayWorkHours.textContent = data.worked_hours;
                }
            } else {
                updateBadge('not_punched');
                updateButtons('not_punched');
            }
        } catch (error) {
            console.error('Status load error:', error);
        }
    }
    
    // Mark attendance action
    async function markAction(action, data = {}) {
        if (isProcessing) return;
        isProcessing = true;
        
        updateStatus('Processing...', 'info');
        
        try {
            const response = await fetch('{{ route("attendance.attendance") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ action, ...data })
            });
            
            const result = await response.json();
            
            if (result.success) {
                const messages = {
                    'tea_break_in': '☕ Tea break started! Enjoy!',
                    'tea_break_out': '✅ Break ended! Welcome back!',
                    'punch_out': '👋 Punched out successfully! See you tomorrow!',
                    'clock_in': '✅ Clocked in successfully! Welcome!'
                };
                showToast(messages[action] || result.message, 'success');
                updateStatus(result.message, 'success');
                await loadUserStatus();
            } else {
                showToast(result.message || 'Action failed', 'error');
                updateStatus(result.message || 'Action failed', 'danger');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
            updateStatus('Error: ' + error.message, 'danger');
        }
        
        isProcessing = false;
        setTimeout(() => {
            if (!isProcessing) {
                updateStatus('Ready for face recognition', 'info');
            }
        }, 3000);
    }
    
    // Enroll button click handler
    enrollBtn.addEventListener('click', function() {
        window.location.href = '{{ route("face.enroll.page") }}';
    });
    
    // Clock In
    clockInBtn.addEventListener('click', function() {
        markAction('clock_in');
    });
    
    // Tea Break
    teaBreakBtn.addEventListener('click', function() {
        markAction('tea_break_in');
    });
    
    // End Break
    endBreakBtn.addEventListener('click', function() {
        markAction('tea_break_out');
    });
    
    // Punch Out
    punchOutBtn.addEventListener('click', function() {
        markAction('punch_out');
    });
    
    // Initialize Face API
    async function initFaceAPI() {
        overlayText.textContent = 'Loading AI models...';
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri('/js/face-api/weights'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/js/face-api/weights'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/js/face-api/weights')
            ]);
            modelsLoaded = true;
            overlayText.textContent = 'Starting camera...';
            await startCamera();
        } catch (error) {
            overlayText.textContent = '❌ Failed to load AI models';
            console.error('Face API error:', error);
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
            
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            
            cameraOverlay.style.display = 'none';
            updateStatus('Ready for face recognition', 'info');
            
            // Load user status
            await loadUserStatus();
            
            // Start scanning
            startScanning();
        } catch (error) {
            overlayText.textContent = '❌ Camera access denied';
            console.error('Camera error:', error);
        }
    }
    
    // Start scanning
    function startScanning() {
        if (scanInterval) clearInterval(scanInterval);
        scanInterval = setInterval(scanForFace, 1500);
    }
    
    // Scan for face
    async function scanForFace() {
        if (!modelsLoaded || isProcessing) return;
        if (currentStatus === 'clocked_out') return;
        if (!isEnrolled) {
            updateStatus('Please enroll your face first', 'warning');
            return;
        }
        
        try {
            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                inputSize: 320,
                scoreThreshold: 0.5
            }))
            .withFaceLandmarks()
            .withFaceDescriptor();
            
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            if (!detection) {
                return;
            }
            
            // Draw face detection
            const box = detection.detection.box;
            ctx.strokeStyle = '#00ff00';
            ctx.lineWidth = 2;
            ctx.strokeRect(box.x, box.y, box.width, box.height);
            
            // Generate face ID
            const faceId = Array.from(detection.descriptor).slice(0, 10).join(',');
            const now = Date.now();
            
            // Check cooldown
            if (faceId === lastFaceId && now - lastProcessTime < cooldown) {
                return;
            }
            
            lastFaceId = faceId;
            lastProcessTime = now;
            
            // Verify face
            updateStatus('Verifying face...', 'info');
            
            const response = await fetch('{{ route("attendance.verify.face") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    descriptor: Array.from(detection.descriptor)
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Show last recognition
                lastRecognition.classList.remove('d-none');
                lastRecognitionName.textContent = data.user_name;
                lastRecognitionTime.textContent = new Date().toLocaleTimeString();
                
                // Check if this is the logged-in user
                if (data.user_id == {{ Auth::id() }}) {
                    updateStatus(`Welcome back ${data.user_name}!`, 'success');
                    showToast(`Recognized: ${data.user_name}`, 'success', 2000);
                    
                    // If not clocked in, prompt to clock in
                    if (currentStatus === 'not_punched') {
                        clockInBtn.classList.remove('d-none');
                        updateStatus('Click "Clock In" to start your day', 'info');
                    } else if (currentStatus === 'clocked_in' || currentStatus === 'late') {
                        // Show action buttons
                        updateButtons(currentStatus);
                        updateStatus('Choose an action', 'info');
                    }
                } else {
                    updateStatus(`Not your face. Please show your face.`, 'warning');
                }
            } else {
                updateStatus(data.message || 'Face not recognized', 'warning');
            }
        } catch (error) {
            console.error('Scan error:', error);
        }
    }
    
    // Cleanup
    window.addEventListener('beforeunload', function() {
        if (scanInterval) clearInterval(scanInterval);
        if (stream) stream.getTracks().forEach(t => t.stop());
    });
    
    // Start
    initFaceAPI();
    
    // Refresh status every 30 seconds
    setInterval(loadUserStatus, 30000);
});
</script>
@endsection