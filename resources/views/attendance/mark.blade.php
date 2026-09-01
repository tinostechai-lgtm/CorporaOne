@extends('layouts.admin')

@section('page-title', 'Mark Attendance')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-fingerprint me-2"></i>Face Recognition Attendance
                        <span class="float-end">
                            <span id="statusBadge" class="badge bg-warning">Waiting...</span>
                        </span>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Camera Column -->
                        <div class="col-lg-8">
                            <div class="position-relative">
                                <video id="video" width="100%" height="500" autoplay muted playsinline
                                       class="rounded shadow" style="background:#1a1a1a; object-fit:cover;"></video>
                                
                                <!-- Overlay for face detection -->
                                <canvas id="overlay" style="position:absolute;top:0;left:0;width:100%;height:100%;"></canvas>
                                
                                <!-- Status Overlay -->
                                <div id="scanStatus" class="position-absolute top-50 start-50 translate-middle text-white text-center">
                                    <div class="bg-dark bg-opacity-75 p-4 rounded-3">
                                        <h3><i class="fas fa-camera me-2"></i>Looking for faces...</h3>
                                        <small class="text-light">Please stand 1-2 feet away</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button id="startScanBtn" class="btn btn-success btn-lg">
                                    <i class="fas fa-play me-2"></i>Start Auto-Scan
                                </button>
                                <button id="stopScanBtn" class="btn btn-danger btn-lg d-none">
                                    <i class="fas fa-stop me-2"></i>Stop Scan
                                </button>
                            </div>
                        </div>
                        
                        <!-- Log Column -->
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header bg-dark text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-clock me-2"></i>Today's Attendance Log
                                        <span class="float-end" id="logCount">0</span>
                                    </h5>
                                </div>
                                <div class="card-body p-0" style="max-height:500px; overflow-y:auto;">
                                    <div id="attendanceLog" class="list-group list-group-flush">
                                        <div class="list-group-item text-center text-muted py-4">
                                            <i class="fas fa-user-clock fa-2x d-block mb-2"></i>
                                            No attendance marked yet
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Today's Stats -->
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <h5 class="text-success" id="presentCount">0</h5>
                                            <small>Present</small>
                                        </div>
                                        <div class="col-4">
                                            <h5 class="text-danger" id="absentCount">0</h5>
                                            <small>Absent</small>
                                        </div>
                                        <div class="col-4">
                                            <h5 class="text-info" id="totalCount">0</h5>
                                            <small>Total</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Face Recognition Scripts -->
<script src="{{ asset('js/face-api/face-api.min.js') }}?v={{ time() }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const video = document.getElementById('video');
    const canvas = document.getElementById('overlay');
    const statusBadge = document.getElementById('statusBadge');
    const scanStatus = document.getElementById('scanStatus');
    const attendanceLog = document.getElementById('attendanceLog');
    const logCount = document.getElementById('logCount');
    const startBtn = document.getElementById('startScanBtn');
    const stopBtn = document.getElementById('stopScanBtn');
    const presentCount = document.getElementById('presentCount');
    const absentCount = document.getElementById('absentCount');
    const totalCount = document.getElementById('totalCount');
    
    let isScanning = false;
    let scanInterval = null;
    let recognizedEmployees = new Set(); // Prevent duplicate marking
    let processedFaceIds = new Set(); // Track processed faces
    
    // Get today's date for checking
    const today = new Date().toISOString().split('T')[0];
    
    // Load stats on page load
    loadTodayStats();
    
    // Initialize Face API
    async function initFaceAPI() {
        updateStatus('loading', 'Loading AI models...');
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri('/js/face-api/weights'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/js/face-api/weights'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/js/face-api/weights')
            ]);
            console.log('Face API loaded successfully');
            startCamera();
        } catch (error) {
            console.error('Failed to load models:', error);
            updateStatus('error', 'Failed to load AI models');
            alert('Failed to load face recognition models. Please refresh.');
        }
    }
    
    // Start Camera
    function startCamera() {
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                width: 640, 
                height: 480,
                facingMode: 'user'
            } 
        })
        .then(stream => {
            video.srcObject = stream;
            updateStatus('ready', 'Camera ready');
            startBtn.disabled = false;
            scanStatus.querySelector('h3').innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>Camera Ready';
            scanStatus.querySelector('small').textContent = 'Click "Start Auto-Scan" to begin';
        })
        .catch(error => {
            console.error('Camera error:', error);
            updateStatus('error', 'Camera access denied');
            alert('Please allow camera access for attendance marking.');
        });
    }
    
    // Start Auto-Scan
    function startAutoScan() {
        if (isScanning) return;
        isScanning = true;
        
        startBtn.classList.add('d-none');
        stopBtn.classList.remove('d-none');
        scanStatus.querySelector('h3').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Scanning...';
        scanStatus.querySelector('small').textContent = 'Show your face clearly';
        updateStatus('scanning', 'Scanning for faces...');
        
        // Reset recognized set for this session
        recognizedEmployees = new Set();
        processedFaceIds = new Set();
        
        // Scan every 1.5 seconds
        scanInterval = setInterval(scanForFace, 1500);
        
        // Immediate first scan
        setTimeout(scanForFace, 100);
    }
    
    // Stop Auto-Scan
    function stopAutoScan() {
        isScanning = false;
        clearInterval(scanInterval);
        startBtn.classList.remove('d-none');
        stopBtn.classList.add('d-none');
        scanStatus.querySelector('h3').innerHTML = '<i class="fas fa-pause-circle me-2"></i>Paused';
        scanStatus.querySelector('small').textContent = 'Click "Start Auto-Scan" to resume';
        updateStatus('ready', 'Scan paused');
    }
    
    // Scan for Face
    async function scanForFace() {
        if (!isScanning) return;
        
        try {
            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                inputSize: 320,
                scoreThreshold: 0.5
            }))
            .withFaceLandmarks()
            .withFaceDescriptor();
            
            if (!detection) {
                updateStatus('scanning', 'No face detected');
                scanStatus.querySelector('h3').innerHTML = '<i class="fas fa-camera me-2"></i>Looking for faces...';
                scanStatus.querySelector('small').textContent = 'Please stand 1-2 feet away';
                return;
            }
            
            // Draw face detection on canvas
            drawFaceDetection(detection);
            
            // Generate a unique ID for this face (based on descriptor)
            const faceId = Array.from(detection.descriptor).slice(0, 10).join(',');
            
            // Check if we've already processed this face recently
            const now = Date.now();
            if (processedFaceIds.has(faceId)) {
                // Check if it's been less than 30 seconds since last process for this face
                const lastProcess = processedFaceIds.get(faceId);
                if (now - lastProcess < 30000) {
                    scanStatus.querySelector('h3').innerHTML = '<i class="fas fa-check text-success me-2"></i>Already scanned';
                    scanStatus.querySelector('small').textContent = 'Waiting for next face';
                    return;
                }
            }
            
            // Mark this face as processed
            processedFaceIds.set(faceId, now);
            
            // Send to server for verification
            updateStatus('verifying', 'Verifying identity...');
            scanStatus.querySelector('h3').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying...';
            scanStatus.querySelector('small').textContent = 'Checking against database';
            
            const response = await fetch('{{ route("attendance.verify") }}', {
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
            
            if (data.success && !recognizedEmployees.has(data.user_id)) {
                // New employee recognized - Mark attendance
                recognizedEmployees.add(data.user_id);
                await markAttendance(data.user_id);
                
                // Show success with name
                scanStatus.querySelector('h3').innerHTML = `<i class="fas fa-check-circle text-success me-2"></i>Welcome ${data.user_name}!`;
                scanStatus.querySelector('small').textContent = 'Attendance marked ✅';
                updateStatus('success', `Attendance marked for ${data.user_name}`);
                
                // Update stats
                loadTodayStats();
                
            } else if (data.success && recognizedEmployees.has(data.user_id)) {
                // Already marked today
                scanStatus.querySelector('h3').innerHTML = `<i class="fas fa-info-circle text-info me-2"></i>Already marked, ${data.user_name}`;
                scanStatus.querySelector('small').textContent = 'Already marked today';
                updateStatus('info', 'Already marked today');
                
            } else {
                // Not recognized
                scanStatus.querySelector('h3').innerHTML = '<i class="fas fa-user-slash text-danger me-2"></i>Not recognized';
                scanStatus.querySelector('small').textContent = 'Please enroll first or try again';
                updateStatus('error', 'Face not recognized');
            }
            
        } catch (error) {
            console.error('Scan error:', error);
            updateStatus('error', 'Scan error occurred');
        }
    }
    
    // Draw Face Detection
    function drawFaceDetection(detection) {
        const dims = video.videoWidth ? { width: video.videoWidth, height: video.videoHeight } : { width: 640, height: 480 };
        canvas.width = dims.width;
        canvas.height = dims.height;
        
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Draw bounding box
        const box = detection.detection.box;
        ctx.strokeStyle = '#00ff00';
        ctx.lineWidth = 2;
        ctx.strokeRect(box.x, box.y, box.width, box.height);
        
        // Draw landmarks
        const landmarks = detection.landmarks;
        ctx.fillStyle = '#00ff00';
        landmarks.positions.forEach(p => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, 2, 0, 2 * Math.PI);
            ctx.fill();
        });
    }
    
    // Mark Attendance
    async function markAttendance(userId) {
        try {
            const response = await fetch('{{ route("attendance.mark") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    user_id: userId,
                    date: today
                })
            });
            
            const data = await response.json();
            if (data.success) {
                addToLog(data.user_name, data.time);
                updateStatus('success', `Attendance marked for ${data.user_name} at ${data.time}`);
                
                // Play success sound (optional)
                playSound('success');
            }
        } catch (error) {
            console.error('Mark attendance error:', error);
        }
    }
    
    // Add to Log
    function addToLog(name, time) {
        const logEntry = document.createElement('div');
        logEntry.className = 'list-group-item list-group-item-success d-flex justify-content-between align-items-center';
        logEntry.innerHTML = `
            <div>
                <i class="fas fa-check-circle text-success me-2"></i>
                <strong>${name}</strong>
            </div>
            <div>
                <span class="badge bg-success">${time || new Date().toLocaleTimeString()}</span>
                <i class="fas fa-check-double text-success ms-2"></i>
            </div>
        `;
        
        // Remove "no data" message if exists
        const noData = attendanceLog.querySelector('.text-muted');
        if (noData) noData.remove();
        
        // Add to top of list
        attendanceLog.insertBefore(logEntry, attendanceLog.firstChild);
        
        // Update count
        const items = attendanceLog.querySelectorAll('.list-group-item-success');
        logCount.textContent = items.length;
    }
    
    // Update Status
    function updateStatus(type, message) {
        statusBadge.className = `badge ${getBadgeClass(type)}`;
        statusBadge.textContent = message;
    }
    
    function getBadgeClass(type) {
        const classes = {
            'loading': 'bg-warning',
            'ready': 'bg-success',
            'scanning': 'bg-info',
            'verifying': 'bg-primary',
            'success': 'bg-success',
            'error': 'bg-danger',
            'info': 'bg-info'
        };
        return classes[type] || 'bg-secondary';
    }
    
    // Play Sound
    function playSound(type) {
        // You can add audio feedback here
        // const audio = new Audio(`/sounds/${type}.mp3`);
        // audio.play();
    }
    
    // Load Today's Stats
    async function loadTodayStats() {
        try {
            const response = await fetch('{{ route("attendance.stats") }}');
            const data = await response.json();
            
            if (data.success) {
                presentCount.textContent = data.present || 0;
                absentCount.textContent = data.absent || 0;
                totalCount.textContent = data.total || 0;
                
                // Load today's log
                if (data.log && data.log.length > 0) {
                    attendanceLog.innerHTML = '';
                    data.log.forEach(entry => {
                        addToLog(entry.user_name, entry.time);
                    });
                }
            }
        } catch (error) {
            console.error('Stats error:', error);
        }
    }
    
    // Event Listeners
    startBtn.addEventListener('click', startAutoScan);
    stopBtn.addEventListener('click', stopAutoScan);
    
    // Initialize
    initFaceAPI();
});
</script>
@endsection