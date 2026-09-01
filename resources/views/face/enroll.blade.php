@extends('layouts.admin')

@section('page-title', 'Face Enrollment')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4>Face Enrollment – One Time Only</h4>
                </div>
                <div class="card-body text-center p-5">

                    @php
                        $canEnroll = \Auth::user()->can('create face id attendance') || \Auth::user()->can('manage face id attendance');
                        $employee = \Auth::user()->employee;
                        $isEnrolled = $employee && !empty($employee->face_descriptor);
                    @endphp

                    @if(!$canEnroll)
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Permission Denied!</strong>
                            <p class="mb-0 mt-1">You don't have permission to enroll faces. Please contact your administrator.</p>
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary mt-3">
                                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                            </a>
                        </div>
                    @else
                        @if($isEnrolled)
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Your face is already enrolled!</strong>
                                <p class="mb-0 mt-1">You can re-enroll if needed. This will update your face data.</p>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>First time enrollment</strong>
                                <p class="mb-0 mt-1">Please stand 1-2 feet away from the camera and look directly at it.</p>
                            </div>
                        @endif

                        <video id="video" width="100%" height="480" autoplay muted playsinline
                               class="rounded shadow mb-4" style="max-width:640px;background:#000;"></video>

                        <div id="loadingStatus" class="text-muted mb-3">
                            <i class="fas fa-spinner fa-spin me-2"></i> Loading AI models…
                        </div>

                        <button id="enrollBtn" class="btn btn-primary btn-lg px-5" disabled>
                            @if($isEnrolled)
                                Re-enroll My Face
                            @else
                                Enroll My Face
                            @endif
                        </button>

                        <div id="result" class="mt-4"></div>

                        <!-- Tips -->
                        <div class="mt-4 p-3 bg-light rounded text-start">
                            <h6 class="mb-2"><i class="fas fa-lightbulb text-warning me-2"></i> Tips for best results:</h6>
                            <ul class="small text-muted mb-0">
                                <li>Ensure good lighting on your face</li>
                                <li>Remove glasses or sunglasses if possible</li>
                                <li>Look directly at the camera</li>
                                <li>Keep a neutral expression</li>
                                <li>Stand 1-2 feet away from the camera</li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- LIVEWIRE-PROOF SCRIPT — WILL WORK NO MATTER WHAT --}}
<script src="{{ asset('js/face-api/face-api.min.js') }}?v={{ time() }}"></script>
<script>
    // This version survives Livewire DOM replacements forever
    function initFaceEnrollmentWhenReady() {
        const btn    = document.getElementById('enrollBtn');
        const video  = document.getElementById('video');
        const result = document.getElementById('result');
        const loadingStatus = document.getElementById('loadingStatus');

        if (!btn || !video || !result || !loadingStatus) {
            // Button not ready yet → try again in 100ms
            setTimeout(initFaceEnrollmentWhenReady, 100);
            return;
        }

        // Button exists → start!
        console.log('Button found – starting face enrollment');

        if (typeof faceapi === 'undefined') {
            loadingStatus.innerHTML = '<span class="text-danger">❌ face-api.js failed to load!</span>';
            result.innerHTML = '<div class="alert alert-danger">face-api.js failed to load! Please refresh the page.</div>';
            return;
        }

        loadingStatus.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Loading AI models… (15–30 sec first time)';
        result.innerHTML = '';

        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('/js/face-api/weights'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/js/face-api/weights'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/js/face-api/weights')
        ])
        .then(() => {
            loadingStatus.innerHTML = '<span class="text-success">✅ Models loaded successfully!</span>';
            startCamera();
        })
        .catch((error) => {
            console.error('Model load error:', error);
            loadingStatus.innerHTML = '<span class="text-danger">❌ Failed to load AI models</span>';
            result.innerHTML = '<div class="alert alert-danger">Failed to load AI models. Please refresh and try again.</div>';
        });

        function startCamera() {
            navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                } 
            })
            .then(stream => {
                video.srcObject = stream;
                btn.disabled = false;
                result.innerHTML = '<div class="alert alert-success">✅ Ready! Click "Enroll My Face"</div>';
                loadingStatus.innerHTML = '<span class="text-success">✅ Camera ready</span>';
            })
            .catch((error) => {
                console.error('Camera error:', error);
                loadingStatus.innerHTML = '<span class="text-danger">❌ Camera access denied</span>';
                result.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Camera permission denied!</strong>
                        <p class="mb-0 mt-1">Please allow camera access in your browser settings and refresh the page.</p>
                    </div>
                `;
            });
        }

        // ============================================================
        // ENROLL BUTTON CLICK HANDLER - AUTO-DETECTS EMPLOYEE
        // ============================================================
        btn.addEventListener('click', async () => {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Enrolling...';
            result.innerHTML = '<div class="alert alert-info">📸 Capturing face…</div>';

            try {
                const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) {
                    result.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No face detected. Please look directly at the camera and try again.
                        </div>
                    `;
                    btn.disabled = false;
                    btn.innerHTML = 'Enroll My Face';
                    return;
                }

                // 🟢 Send ONLY the face_descriptor - employee_id is auto-detected on backend
                const response = await fetch('{{ route('face.enroll') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        face_descriptor: Array.from(detection.descriptor)
                    })
                });

                const data = await response.json();

                if (data.success) {
                    result.innerHTML = `
                        <div class="alert alert-success">
                            <h4><i class="fas fa-check-circle me-2"></i> ✅ Success!</h4>
                            <p class="mb-2">Your face has been enrolled successfully.</p>
                            <p class="small text-muted">Employee: <strong>${data.data.employee_name || 'You'}</strong></p>
                            <p class="small text-muted">Enrolled at: ${new Date().toLocaleString()}</p>
                            <div class="mt-3">
                                <a href="{{ route('face.clockin') }}" class="btn btn-success me-2">
                                    <i class="fas fa-arrow-right me-2"></i> Go to Attendance
                                </a>
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-home me-2"></i> Dashboard
                                </a>
                            </div>
                        </div>
                    `;
                    btn.innerHTML = '✅ Enrolled';
                    btn.className = 'btn btn-success btn-lg px-5';
                } else {
                    result.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Failed to enroll face</strong>
                            <p class="mb-0 mt-1">${data.message || 'Unknown error occurred. Please try again.'}</p>
                        </div>
                    `;
                    btn.disabled = false;
                    btn.innerHTML = 'Try Again';
                    btn.className = 'btn btn-warning btn-lg px-5';
                }
            } catch (error) {
                console.error('Enrollment error:', error);
                result.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Server error</strong>
                        <p class="mb-0 mt-1">${error.message || 'Please try again later.'}</p>
                    </div>
                `;
                btn.disabled = false;
                btn.innerHTML = 'Retry';
            }
        });
    }

    // Start checking immediately
    initFaceEnrollmentWhenReady();

    // Also re-check on Livewire events (extra safety)
    document.addEventListener('livewire:load', initFaceEnrollmentWhenReady);
    document.addEventListener('livewire:update', initFaceEnrollmentWhenReady);

    // Clean up video stream when leaving page
    document.addEventListener('beforeunload', function() {
        const video = document.getElementById('video');
        if (video && video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
        }
    });
</script>

<style>
    #video {
        background: #1a1a2e;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }
    .alert {
        border-radius: 12px;
    }
    .card {
        border-radius: 16px;
        overflow: hidden;
    }
    .card-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    .btn {
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
</style>
@endsection