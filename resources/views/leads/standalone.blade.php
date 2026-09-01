<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lead Management System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .stat-card { background: white; border-radius: 12px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .kanban-column { background: #f8f9fa; border-radius: 12px; border: 1px solid #e9ecef; }
        .lead-card { background: white; border-radius: 10px; padding: 0.75rem; margin-bottom: 0.75rem; cursor: grab; transition: all 0.2s; border: 1px solid #e9ecef; }
        .lead-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); transform: translateY(-2px); }
        .lead-title { font-weight: 600; margin-bottom: 0.5rem; }
        .lead-info { font-size: 0.75rem; color: #6c757d; margin-bottom: 0.25rem; }
        .lead-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid #f0f2f5; }
        .badge-assigned { background: #e7f5ff; color: #0c63e4; padding: 0.2rem 0.5rem; border-radius: 20px; font-size: 0.7rem; }
        .score-high { background: #d4edda; color: #155724; }
        .score-medium { background: #fff3cd; color: #856404; }
        .score-low { background: #f8d7da; color: #721c24; }
        .score-badge { font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 20px; }
        .kanban-body { min-height: 400px; max-height: 500px; overflow-y: auto; padding: 0.75rem; }
        .kanban-header { padding: 0.75rem; background: white; border-bottom: 1px solid #e9ecef; border-radius: 12px 12px 0 0; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Lead Management</h2>
            <div>
                <button type="button" class="btn btn-outline-primary" id="socialConnectBtn">
                    <i class="bi bi-wifi"></i> Connect Social
                </button>
                <button type="button" class="btn btn-primary" id="createLeadBtn">
                    <i class="bi bi-plus-circle"></i> New Lead
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <p class="text-muted mb-1">Total Leads</p>
                    <h2 class="fw-bold mb-0">{{ $totalLeads }}</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <p class="text-muted mb-1">Assigned</p>
                    <h2 class="fw-bold mb-0">{{ $assignedLeads ?? 0 }}</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <p class="text-muted mb-1">Unassigned</p>
                    <h2 class="fw-bold mb-0">{{ $unassignedLeads ?? 0 }}</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <p class="text-muted mb-1">Social Leads</p>
                    <h2 class="fw-bold mb-0">{{ $socialLeads ?? 0 }}</h2>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select class="form-select" id="pipelineFilter">
                            <option value="">All Pipelines</option>
                            @foreach($pipelines as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="sourceFilter">
                            <option value="">All Sources</option>
                            <option value="facebook">Facebook</option>
                            <option value="instagram">Instagram</option>
                            <option value="whatsapp">WhatsApp</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="searchLead" class="form-control" placeholder="Search...">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary w-100" id="resetBtn">Reset</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="row">
            @foreach($stages as $stage)
                <div class="col-md-3 mb-4">
                    <div class="kanban-column">
                        <div class="kanban-header">
                            {{ $stage->name }}
                            <span class="badge bg-secondary">{{ $leads->where('stage_id', $stage->id)->count() }}</span>
                        </div>
                        <div class="kanban-body" data-stage-id="{{ $stage->id }}">
                            @foreach($leads->where('stage_id', $stage->id) as $lead)
                                <div class="lead-card" data-lead-id="{{ $lead->id }}">
                                    <div class="lead-title">
                                        <a href="{{ route('leads.show', $lead->id) }}" class="text-dark text-decoration-none">
                                            {{ $lead->name }}
                                        </a>
                                    </div>
                                    <div class="lead-info">
                                        <i class="bi bi-envelope"></i> {{ $lead->email ?? 'No email' }}
                                    </div>
                                    @if($lead->phone)
                                    <div class="lead-info">
                                        <i class="bi bi-telephone"></i> {{ $lead->phone }}
                                    </div>
                                    @endif
                                    <div class="lead-footer">
                                        <span class="badge-assigned">
                                            <i class="bi bi-person"></i> {{ $lead->users->first()->name ?? 'Unassigned' }}
                                        </span>
                                        <span class="score-badge score-{{ $lead->lead_score >= 70 ? 'high' : ($lead->lead_score >= 40 ? 'medium' : 'low') }}">
                                            {{ $lead->lead_score ?? 0 }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Create Lead Modal -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Create New Lead</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="name" class="form-control mb-2" placeholder="Name *">
                    <input type="email" id="email" class="form-control mb-2" placeholder="Email *">
                    <input type="text" id="phone" class="form-control mb-2" placeholder="Phone">
                    <textarea id="notes" class="form-control" rows="2" placeholder="Notes"></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="saveLead">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Social Modal -->
    <div class="modal fade" id="socialModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Connect Social Media</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <button class="list-group-item list-group-item-action platform-btn" data-platform="facebook">
                            <i class="bi bi-facebook"></i> Facebook
                        </button>
                        <button class="list-group-item list-group-item-action platform-btn" data-platform="instagram">
                            <i class="bi bi-instagram"></i> Instagram
                        </button>
                        <button class="list-group-item list-group-item-action platform-btn" data-platform="whatsapp">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Credentials Modal -->
    <div class="modal fade" id="credModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="credTitle">Enter Credentials</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="credBody">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    // Store current platform
    let currentPlatform = '';

    // DOM Elements
    const socialBtn = document.getElementById('socialConnectBtn');
    const createBtn = document.getElementById('createLeadBtn');
    const saveLead = document.getElementById('saveLead');
    const resetBtn = document.getElementById('resetBtn');
    const pipelineFilter = document.getElementById('pipelineFilter');
    const sourceFilter = document.getElementById('sourceFilter');
    const searchLead = document.getElementById('searchLead');

    // Open social modal
    if (socialBtn) {
        socialBtn.onclick = function(e) {
            e.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('socialModal'));
            modal.show();
        };
    }

    // Platform selection
    document.querySelectorAll('.platform-btn').forEach(btn => {
        btn.onclick = function(e) {
            e.preventDefault();
            currentPlatform = this.dataset.platform;
            
            // Close social modal
            bootstrap.Modal.getInstance(document.getElementById('socialModal')).hide();
            
            // Show credentials modal
            showCredentialsModal();
        };
    });

    function showCredentialsModal() {
        const title = document.getElementById('credTitle');
        const body = document.getElementById('credBody');
        
        let html = '';
        if (currentPlatform === 'facebook') {
            title.innerHTML = 'Facebook Credentials';
            html = `
                <div class="mb-3">
                    <label>Page ID</label>
                    <input type="text" id="fbPageId" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Access Token</label>
                    <input type="password" id="fbToken" class="form-control">
                </div>
                <button class="btn btn-primary w-100" id="fetchBtn">Fetch Leads</button>
            `;
        } else if (currentPlatform === 'instagram') {
            title.innerHTML = 'Instagram Credentials';
            html = `
                <div class="mb-3">
                    <label>Business ID</label>
                    <input type="text" id="igId" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Access Token</label>
                    <input type="password" id="igToken" class="form-control">
                </div>
                <button class="btn btn-primary w-100" id="fetchBtn">Fetch Leads</button>
            `;
        } else if (currentPlatform === 'whatsapp') {
            title.innerHTML = 'WhatsApp Credentials';
            html = `
                <div class="mb-3">
                    <label>Phone Number ID</label>
                    <input type="text" id="waId" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Access Token</label>
                    <input type="password" id="waToken" class="form-control">
                </div>
                <button class="btn btn-primary w-100" id="fetchBtn">Fetch Leads</button>
            `;
        }
        
        body.innerHTML = html;
        const modal = new bootstrap.Modal(document.getElementById('credModal'));
        modal.show();
        
        // Add fetch button handler after modal is shown
        setTimeout(() => {
            const fetchBtn = document.getElementById('fetchBtn');
            if (fetchBtn) {
                fetchBtn.onclick = function(e) {
                    e.preventDefault();
                    fetchLeads();
                };
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
        bootstrap.Modal.getInstance(document.getElementById('credModal')).hide();
        
        Swal.fire({
            title: 'Fetching Leads...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                Swal.fire('Success!', data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                Swal.fire('Error', data.error || 'Failed', 'error');
            }
        })
        .catch(err => {
            Swal.close();
            Swal.fire('Error', err.message, 'error');
        });
    }

    // Create Lead
    if (createBtn) {
        createBtn.onclick = function(e) {
            e.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('createModal'));
            modal.show();
        };
    }

    if (saveLead) {
        saveLead.onclick = function(e) {
            e.preventDefault();
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            
            if (!name || !email) {
                Swal.fire('Error', 'Name and email required', 'error');
                return;
            }
            
            bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
            
            Swal.fire({ title: 'Creating...', didOpen: () => Swal.showLoading() });
            
            fetch('{{ route("leads.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    name: name,
                    email: email,
                    phone: document.getElementById('phone').value,
                    notes: document.getElementById('notes').value
                })
            })
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire('Success!', 'Lead created', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('Error', data.error || 'Failed', 'error');
                }
            })
            .catch(err => {
                Swal.close();
                Swal.fire('Error', err.message, 'error');
            });
        };
    }

    // Filters
    function applyFilters() {
        const params = new URLSearchParams();
        if (pipelineFilter?.value) params.set('pipeline_id', pipelineFilter.value);
        if (sourceFilter?.value) params.set('source', sourceFilter.value);
        if (searchLead?.value) params.set('search', searchLead.value);
        window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    }
    
    if (pipelineFilter) pipelineFilter.onchange = applyFilters;
    if (sourceFilter) sourceFilter.onchange = applyFilters;
    
    let timeout;
    if (searchLead) {
        searchLead.onkeyup = function() {
            clearTimeout(timeout);
            timeout = setTimeout(applyFilters, 500);
        };
    }
    
    if (resetBtn) {
        resetBtn.onclick = function() {
            window.location.href = window.location.pathname;
        };
    }
    </script>
</body>
</html>