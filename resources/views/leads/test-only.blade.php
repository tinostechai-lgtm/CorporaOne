<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test - No Reload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Test Social Connect - NO RELOAD</h3>
            </div>
            <div class="card-body">
                <div id="message" class="mb-3"></div>
                
                <div class="d-flex gap-3 justify-content-center">
                    <button type="button" class="btn btn-primary" id="testFacebookBtn">
                        <i class="bi bi-facebook"></i> Test Facebook
                    </button>
                    <button type="button" class="btn btn-danger" id="testInstagramBtn">
                        <i class="bi bi-instagram"></i> Test Instagram
                    </button>
                    <button type="button" class="btn btn-success" id="testWhatsAppBtn">
                        <i class="bi bi-whatsapp"></i> Test WhatsApp
                    </button>
                </div>
                
                <div class="mt-4">
                    <div id="result" class="alert alert-info">
                        Click a button to test AJAX
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Helper function to make AJAX calls
    function makeTestCall(platform, url, data) {
        const resultDiv = document.getElementById('result');
        resultDiv.innerHTML = '<div class="spinner-border text-primary" role="status"></div> Calling API...';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `<div class="alert alert-success">
                    <strong>✅ Success!</strong><br>
                    Platform: ${platform}<br>
                    Message: ${data.message}<br>
                    Full Response: <pre class="mt-2">${JSON.stringify(data, null, 2)}</pre>
                </div>`;
            } else {
                resultDiv.innerHTML = `<div class="alert alert-danger">
                    <strong>❌ Error!</strong><br>
                    Platform: ${platform}<br>
                    Error: ${data.error || 'Unknown error'}<br>
                    Full Response: <pre class="mt-2">${JSON.stringify(data, null, 2)}</pre>
                </div>`;
            }
        })
        .catch(error => {
            resultDiv.innerHTML = `<div class="alert alert-danger">
                <strong>❌ Network Error!</strong><br>
                ${error.message}
            </div>`;
        });
        
        // Return false to prevent any default behavior
        return false;
    }
    
    // Test Facebook
    const fbBtn = document.getElementById('testFacebookBtn');
    if (fbBtn) {
        fbBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            makeTestCall('Facebook', '{{ route("leads.fetch-facebook") }}', {
                pageId: 'test123',
                accessToken: 'test456'
            });
            return false;
        };
    }
    
    // Test Instagram
    const igBtn = document.getElementById('testInstagramBtn');
    if (igBtn) {
        igBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            makeTestCall('Instagram', '{{ route("leads.fetch-instagram") }}', {
                businessId: 'test123',
                accessToken: 'test456'
            });
            return false;
        };
    }
    
    // Test WhatsApp
    const waBtn = document.getElementById('testWhatsAppBtn');
    if (waBtn) {
        waBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            makeTestCall('WhatsApp', '{{ route("leads.fetch-whatsapp") }}', {
                phoneNumberId: 'test123',
                accessToken: 'test456'
            });
            return false;
        };
    }
    
    // Also prevent any form submission on the whole page
    document.querySelectorAll('form').forEach(form => {
        form.onsubmit = function(e) {
            e.preventDefault();
            return false;
        };
    });
    </script>
</body>
</html>