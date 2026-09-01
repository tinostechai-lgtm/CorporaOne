<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Social Lead Fetcher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-wifi"></i> Social Media Lead Fetcher</h4>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <p>Connect your social media accounts to fetch leads automatically</p>
                </div>
                
                <div class="d-flex justify-content-center gap-3">
                    <button class="btn btn-outline-primary btn-lg platform-btn" data-platform="facebook">
                        <i class="bi bi-facebook fs-1 d-block"></i>
                        Facebook
                    </button>
                    <button class="btn btn-outline-danger btn-lg platform-btn" data-platform="instagram">
                        <i class="bi bi-instagram fs-1 d-block"></i>
                        Instagram
                    </button>
                    <button class="btn btn-outline-success btn-lg platform-btn" data-platform="whatsapp">
                        <i class="bi bi-whatsapp fs-1 d-block"></i>
                        WhatsApp
                    </button>
                </div>
                
                <div id="result" class="mt-4"></div>
            </div>
        </div>
    </div>

    <!-- Credentials Modal -->
    <div class="modal fade" id="credModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="credTitle">Enter Credentials</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="credBody">
                </div>
            </div>
        </div>
    </div>

    <script>
    let currentPlatform = '';

    // Platform selection
    document.querySelectorAll('.platform-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            currentPlatform = this.dataset.platform;
            showCredentialsModal();
        });
    });

    function showCredentialsModal() {
        const title = document.getElementById('credTitle');
        const body = document.getElementById('credBody');
        
        let html = '';
        if (currentPlatform === 'facebook') {
            title.innerHTML = '<i class="bi bi-facebook"></i> Facebook Credentials';
            html = `
                <div class="mb-3">
                    <label class="form-label fw-bold">Facebook Page ID</label>
                    <input type="text" id="fbPageId" class="form-control" placeholder="Enter your Facebook Page ID">
                    <small class="text-muted">Find your Page ID in Facebook Business Settings</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Access Token</label>
                    <input type="password" id="fbToken" class="form-control" placeholder="Enter your Access Token">
                    <small class="text-muted">Generate from Facebook Developer Portal</small>
                </div>
                <button class="btn btn-primary w-100" id="fetchBtn">Fetch Leads</button>
            `;
        } else if (currentPlatform === 'instagram') {
            title.innerHTML = '<i class="bi bi-instagram"></i> Instagram Credentials';
            html = `
                <div class="mb-3">
                    <label class="form-label fw-bold">Instagram Business ID</label>
                    <input type="text" id="igId" class="form-control" placeholder="Enter your Instagram Business ID">
                    <small class="text-muted">Find your Business ID in Instagram Business Settings</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Access Token</label>
                    <input type="password" id="igToken" class="form-control" placeholder="Enter your Access Token">
                    <small class="text-muted">Generate from Facebook Developer Portal</small>
                </div>
                <button class="btn btn-primary w-100" id="fetchBtn">Fetch Leads</button>
            `;
        } else if (currentPlatform === 'whatsapp') {
            title.innerHTML = '<i class="bi bi-whatsapp"></i> WhatsApp Credentials';
            html = `
                <div class="mb-3">
                    <label class="form-label fw-bold">Phone Number ID</label>
                    <input type="text" id="waId" class="form-control" placeholder="Enter your WhatsApp Phone Number ID">
                    <small class="text-muted">Find your Phone Number ID in WhatsApp Business API</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Access Token</label>
                    <input type="password" id="waToken" class="form-control" placeholder="Enter your Access Token">
                    <small class="text-muted">Generate from Meta Developer Portal</small>
                </div>
                <button class="btn btn-primary w-100" id="fetchBtn">Fetch Leads</button>
            `;
        }
        
        body.innerHTML = html;
        const modal = new bootstrap.Modal(document.getElementById('credModal'));
        modal.show();
        
        // Add fetch button handler
        setTimeout(() => {
            const fetchBtn = document.getElementById('fetchBtn');
            if (fetchBtn) {
                fetchBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchLeads();
                });
            }
        }, 100);
    }

    function fetchLeads() {
        let url = '';
        let data = {};
        
        if (currentPlatform === 'facebook') {
            url = '{{ route("leads.fetch-facebook") }}';
            data = {
                pageId: document.getElementById('fbPageId')?.value,
                accessToken: document.getElementById('fbToken')?.value
            };
        } else if (currentPlatform === 'instagram') {
            url = '{{ route("leads.fetch-instagram") }}';
            data = {
                businessId: document.getElementById('igId')?.value,
                accessToken: document.getElementById('igToken')?.value
            };
        } else if (currentPlatform === 'whatsapp') {
            url = '{{ route("leads.fetch-whatsapp") }}';
            data = {
                phoneNumberId: document.getElementById('waId')?.value,
                accessToken: document.getElementById('waToken')?.value
            };
        }
        
        // Close modal
        const credModal = bootstrap.Modal.getInstance(document.getElementById('credModal'));
        if (credModal) credModal.hide();
        
        Swal.fire({
            title: 'Fetching Leads...',
            text: 'Please wait while we fetch leads from ' + currentPlatform,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
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
            Swal.close();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    confirmButtonText: 'OK'
                });
                // NO PAGE RELOAD HERE!
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.error || 'Failed to fetch leads',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: error.message,
                confirmButtonText: 'OK'
            });
        });
    }
    </script>
</body>
</html>