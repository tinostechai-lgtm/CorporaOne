@extends('layouts.admin')

@section('page-title')
    {{__('Social Media Connect')}}
@endsection

@push('css-page')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        /* Modern UI Colors - Matching the index page */
        .stat-card {
            background: white;
            border-radius: 12px;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            transition: all 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .social-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e9ecef;
            transition: all 0.3s;
            cursor: pointer;
            overflow: hidden;
        }
        .social-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: transparent;
        }
        .social-card.facebook:hover { border-top: 4px solid #1877f2; }
        .social-card.instagram:hover { border-top: 4px solid #e4405f; }
        .social-card.whatsapp:hover { border-top: 4px solid #25d366; }
        .social-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 32px;
        }
        .social-icon.facebook { background: #e7f5ff; color: #1877f2; }
        .social-icon.instagram { background: #fce4ec; color: #e4405f; }
        .social-icon.whatsapp { background: #e8f5e9; color: #25d366; }
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-gradient:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .recent-lead-item {
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .recent-lead-item:hover {
            background: #f8f9fa;
            border-left-color: #667eea;
        }
        .platform-badge {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        .platform-badge.facebook { background: #1877f2; color: white; }
        .platform-badge.instagram { background: #e4405f; color: white; }
        .platform-badge.whatsapp { background: #25d366; color: white; }
    </style>
@endpush

@push('script-page')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function openFacebookModal() {
            new bootstrap.Modal(document.getElementById('facebookModal')).show();
        }

        function openInstagramModal() {
            new bootstrap.Modal(document.getElementById('instagramModal')).show();
        }

        function openWhatsAppModal() {
            new bootstrap.Modal(document.getElementById('whatsAppModal')).show();
        }

        function fetchFacebookLeads() {
            const pageId = document.getElementById('fbPageId').value;
            const token = document.getElementById('fbToken').value;
            
            if (!pageId || !token) {
                Swal.fire('Error', 'Please enter both Page ID and Access Token', 'error');
                return;
            }
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('facebookModal'));
            modal.hide();
            
            Swal.fire({
                title: 'Fetching Facebook Leads...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            fetch('{{ route("leads.fetch-facebook") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ pageId: pageId, accessToken: token })
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
                    }).then(() => {
                        location.reload();
                    });
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
                Swal.fire('Error', error.message, 'error');
            });
        }

        function fetchInstagramLeads() {
            const businessId = document.getElementById('igBusinessId').value;
            const token = document.getElementById('igToken').value;
            
            if (!businessId || !token) {
                Swal.fire('Error', 'Please enter both Business ID and Access Token', 'error');
                return;
            }
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('instagramModal'));
            modal.hide();
            
            Swal.fire({
                title: 'Fetching Instagram Leads...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            fetch('{{ route("leads.fetch-instagram") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ businessId: businessId, accessToken: token })
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
                    }).then(() => {
                        location.reload();
                    });
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
                Swal.fire('Error', error.message, 'error');
            });
        }

        function fetchWhatsAppLeads() {
            const phoneId = document.getElementById('waPhoneId').value;
            const token = document.getElementById('waToken').value;
            
            if (!phoneId || !token) {
                Swal.fire('Error', 'Please enter both Phone Number ID and Access Token', 'error');
                return;
            }
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('whatsAppModal'));
            modal.hide();
            
            Swal.fire({
                title: 'Fetching WhatsApp Leads...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            fetch('{{ route("leads.fetch-whatsapp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ phoneNumberId: phoneId, accessToken: token })
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
                    }).then(() => {
                        location.reload();
                    });
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
                Swal.fire('Error', error.message, 'error');
            });
        }
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('leads.index')}}">{{__('Lead')}}</a></li>
    <li class="breadcrumb-item active">{{__('Social Connect')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('leads.index') }}" data-bs-toggle="tooltip" title="{{__('Back to Leads')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-arrow-left"></i> {{__('Back')}}
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">{{__('Facebook Leads')}}</p>
                        <h2 class="fw-bold mb-0">{{ $facebookLeads ?? 0 }}</h2>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <i class="ti ti-brand-facebook text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">{{__('Instagram Leads')}}</p>
                        <h2 class="fw-bold mb-0">{{ $instagramLeads ?? 0 }}</h2>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10">
                        <i class="ti ti-brand-instagram text-danger fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">{{__('WhatsApp Leads')}}</p>
                        <h2 class="fw-bold mb-0">{{ $whatsappLeads ?? 0 }}</h2>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10">
                        <i class="ti ti-brand-whatsapp text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Social Connect Cards -->
    <div class="row g-4 mb-5">
        <!-- Facebook Card -->
        <div class="col-md-4">
            <div class="social-card facebook p-4 text-center">
                <div class="social-icon facebook mx-auto">
                    <i class="ti ti-brand-facebook"></i>
                </div>
                <h4 class="fw-bold mb-2">Facebook</h4>
                <p class="text-muted small mb-3">
                    {{__('Connect Facebook Page to fetch leads from Lead Ads. Automatically import leads with complete contact information.')}}
                </p>
                <div class="mb-3">
                    <span class="badge bg-primary bg-opacity-10 text-primary me-1">{{__('Lead Ads')}}</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary me-1">{{__('Page Integration')}}</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary">{{__('Auto Sync')}}</span>
                </div>
                <button type="button" class="btn btn-gradient w-100" onclick="openFacebookModal()">
                    <i class="ti ti-plug me-1"></i> {{__('Connect Facebook')}}
                </button>
            </div>
        </div>

        <!-- Instagram Card -->
        <div class="col-md-4">
            <div class="social-card instagram p-4 text-center">
                <div class="social-icon instagram mx-auto">
                    <i class="ti ti-brand-instagram"></i>
                </div>
                <h4 class="fw-bold mb-2">Instagram</h4>
                <p class="text-muted small mb-3">
                    {{__('Connect Instagram Business to fetch leads from ads. Capture leads from Instagram feed and stories.')}}
                </p>
                <div class="mb-3">
                    <span class="badge bg-danger bg-opacity-10 text-danger me-1">{{__('Business Account')}}</span>
                    <span class="badge bg-danger bg-opacity-10 text-danger me-1">{{__('Ad Integration')}}</span>
                    <span class="badge bg-danger bg-opacity-10 text-danger">{{__('Lead Forms')}}</span>
                </div>
                <button type="button" class="btn btn-gradient w-100" onclick="openInstagramModal()">
                    <i class="ti ti-plug me-1"></i> {{__('Connect Instagram')}}
                </button>
            </div>
        </div>

        <!-- WhatsApp Card -->
        <div class="col-md-4">
            <div class="social-card whatsapp p-4 text-center">
                <div class="social-icon whatsapp mx-auto">
                    <i class="ti ti-brand-whatsapp"></i>
                </div>
                <h4 class="fw-bold mb-2">WhatsApp</h4>
                <p class="text-muted small mb-3">
                    {{__('Connect WhatsApp Business to fetch leads from chats. Automatically capture customer inquiries.')}}
                </p>
                <div class="mb-3">
                    <span class="badge bg-success bg-opacity-10 text-success me-1">{{__('Business API')}}</span>
                    <span class="badge bg-success bg-opacity-10 text-success me-1">{{__('Chat Leads')}}</span>
                    <span class="badge bg-success bg-opacity-10 text-success">{{__('Auto Reply')}}</span>
                </div>
                <button type="button" class="btn btn-gradient w-100" onclick="openWhatsAppModal()">
                    <i class="ti ti-plug me-1"></i> {{__('Connect WhatsApp')}}
                </button>
            </div>
        </div>
    </div>

    <!-- Recent Social Leads Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">
                    <i class="ti ti-clock-history me-2"></i>{{__('Recently Fetched Social Leads')}}
                </h5>
                <span class="badge bg-secondary">{{ $recentSocialLeads->count() ?? 0 }} {{__('Leads')}}</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">{{__('Lead Name')}}</th>
                            <th>{{__('Email')}}</th>
                            <th>{{__('Phone')}}</th>
                            <th>{{__('Platform')}}</th>
                            <th>{{__('Fetched On')}}</th>
                            <th>{{__('Status')}}</th>
                            <th class="pe-3">{{__('Action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSocialLeads ?? [] as $lead)
                            <tr class="recent-lead-item">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="platform-badge {{ $lead->lead_source }}">
                                            <i class="ti ti-brand-{{ $lead->lead_source == 'facebook' ? 'facebook' : ($lead->lead_source == 'instagram' ? 'instagram' : 'whatsapp') }}"></i>
                                        </div>
                                        <strong>{{ $lead->name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $lead->email ?? '-' }}</td>
                                <td>{{ $lead->phone ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $lead->lead_source == 'facebook' ? 'primary' : ($lead->lead_source == 'instagram' ? 'danger' : 'success') }} bg-opacity-10 text-{{ $lead->lead_source == 'facebook' ? 'primary' : ($lead->lead_source == 'instagram' ? 'danger' : 'success') }}">
                                        {{ ucfirst($lead->lead_source) }}
                                    </span>
                                </td>
                                <td>
                                    <i class="ti ti-calendar me-1"></i>
                                    {{ $lead->created_at->format('M d, Y') }}
                                    <small class="text-muted">{{ $lead->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if($lead->stage_id)
                                        <span class="badge bg-success bg-opacity-10 text-success">{{__('Processed')}}</span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning">{{__('New')}}</span>
                                    @endif
                                </td>
                                <td class="pe-3">
                                    <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="{{__('View Lead')}}">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="ti ti-brand-facebook fs-1 d-block mb-2"></i>
                                    {{__('No social leads fetched yet. Connect a platform to get started!')}}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Facebook Modal -->
<div class="modal fade" id="facebookModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="ti ti-brand-facebook me-2"></i>{{__('Connect Facebook')}}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{__('Facebook Page ID')}}</label>
                    <input type="text" id="fbPageId" class="form-control" placeholder="{{__('Enter your Facebook Page ID')}}">
                    <small class="text-muted">{{__('Find your Page ID in Facebook Business Settings → Pages → Page ID')}}</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{__('Access Token')}}</label>
                    <input type="password" id="fbToken" class="form-control" placeholder="{{__('Enter your Access Token')}}">
                    <small class="text-muted">{{__('Generate from Facebook Developer Portal → Tools → Graph API Explorer')}}</small>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="ti ti-info-circle me-1"></i>
                    {{__('Need help?')}} 
                    <a href="#" target="_blank" class="alert-link">{{__('How to get Facebook Page ID and Access Token')}}</a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                <button type="button" class="btn btn-primary" onclick="fetchFacebookLeads()">
                    <i class="ti ti-cloud-download me-1"></i> {{__('Fetch Leads')}}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Instagram Modal -->
<div class="modal fade" id="instagramModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="ti ti-brand-instagram me-2"></i>{{__('Connect Instagram')}}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{__('Instagram Business ID')}}</label>
                    <input type="text" id="igBusinessId" class="form-control" placeholder="{{__('Enter your Instagram Business ID')}}">
                    <small class="text-muted">{{__('Find your Business ID in Instagram Business Settings → Account')}}</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{__('Access Token')}}</label>
                    <input type="password" id="igToken" class="form-control" placeholder="{{__('Enter your Access Token')}}">
                    <small class="text-muted">{{__('Generate from Facebook Developer Portal with instagram_basic permission')}}</small>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="ti ti-info-circle me-1"></i>
                    {{__('Instagram leads come through Facebook Lead Ads with Instagram placements.')}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                <button type="button" class="btn btn-danger" onclick="fetchInstagramLeads()">
                    <i class="ti ti-cloud-download me-1"></i> {{__('Fetch Leads')}}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Modal -->
<div class="modal fade" id="whatsAppModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="ti ti-brand-whatsapp me-2"></i>{{__('Connect WhatsApp')}}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{__('Phone Number ID')}}</label>
                    <input type="text" id="waPhoneId" class="form-control" placeholder="{{__('Enter your WhatsApp Phone Number ID')}}">
                    <small class="text-muted">{{__('Find your Phone Number ID in WhatsApp Business API → Phone Numbers')}}</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{__('Access Token')}}</label>
                    <input type="password" id="waToken" class="form-control" placeholder="{{__('Enter your Access Token')}}">
                    <small class="text-muted">{{__('Generate from Meta Developer Portal with whatsapp_business_messaging permission')}}</small>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="ti ti-info-circle me-1"></i>
                    {{__('WhatsApp Business API is required for lead fetching.')}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                <button type="button" class="btn btn-success" onclick="fetchWhatsAppLeads()">
                    <i class="ti ti-cloud-download me-1"></i> {{__('Fetch Leads')}}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection