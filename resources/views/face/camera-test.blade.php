<!DOCTYPE html>
<html>
<head>
    <title>Camera Test</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Camera Test</h1>
    <video id="video" width="640" height="480" autoplay muted playsinline style="background:#000; border:1px solid #ccc;"></video>
    <br>
    <button onclick="startCamera()">Start Camera</button>
    <button onclick="capturePhoto()">Capture Photo</button>
    <button onclick="sendToServer()">Send to Server</button>
    <br><br>
    <img id="capturedImage" style="border:1px solid #ccc; max-width:300px;">
    <div id="status"></div>

    <script>
        let stream = null;
        let capturedImageData = null;

        function startCamera() {
            const video = document.getElementById('video');
            const status = document.getElementById('status');
            
            status.innerHTML = '⏳ Requesting camera...';
            
            navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
            })
            .then(function(mediaStream) {
                stream = mediaStream;
                video.srcObject = mediaStream;
                video.play();
                status.innerHTML = '✅ Camera is working!';
                status.style.color = 'green';
            })
            .catch(function(error) {
                console.error('Camera error:', error);
                status.innerHTML = '❌ Camera error: ' + error.message;
                status.style.color = 'red';
            });
        }

        function capturePhoto() {
            const video = document.getElementById('video');
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            capturedImageData = canvas.toDataURL('image/jpeg', 0.9);
            document.getElementById('capturedImage').src = capturedImageData;
            document.getElementById('status').innerHTML = '✅ Photo captured!';
        }

        function sendToServer() {
            if (!capturedImageData) {
                document.getElementById('status').innerHTML = '⚠️ Please capture a photo first.';
                return;
            }

            const status = document.getElementById('status');
            status.innerHTML = '⏳ Sending to server...';

            fetch('/face/enroll-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    face_image_base64: capturedImageData,
                    test: true
                })
            })
            .then(response => response.json())
            .then(data => {
                status.innerHTML = '✅ Server response: ' + JSON.stringify(data);
                status.style.color = 'green';
            })
            .catch(error => {
                status.innerHTML = '❌ Error: ' + error.message;
                status.style.color = 'red';
            });
        }

        // Auto-start camera on page load
        window.onload = function() {
            startCamera();
        };
    </script>
</body>
</html>