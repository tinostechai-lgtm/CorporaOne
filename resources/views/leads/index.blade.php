@extends('layouts.admin')

@section('page-title')
    {{__('Manage Leads')}} @if($pipeline) - {{$pipeline->name}} @endif
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .stat-card {
            background: white;
            border-radius: 12px;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .stat-card.active {
            border: 2px solid #667eea;
            background: linear-gradient(135deg, rgba(102,126,234,0.05) 0%, rgba(118,75,162,0.05) 100%);
        }
        .stat-card.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .stat-card:hover .stat-icon {
            transform: scale(1.05);
        }
        .filter-bar {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e9ecef;
        }
        .lead-card {
            background: white;
            border-radius: 10px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            transition: all 0.2s;
            border: 1px solid #e9ecef;
            cursor: pointer;
        }
        .lead-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        .social-badge {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
        }
        .social-badge.facebook { background: #1877f2; color: white; }
        .social-badge.instagram { background: #e4405f; color: white; }
        .social-badge.whatsapp { background: #25d366; color: white; }
        .score-high { background: #d4edda; color: #155724; }
        .score-medium { background: #fff3cd; color: #856404; }
        .score-low { background: #f8d7da; color: #721c24; }
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .modal-header.bg-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .filter-indicator {
            animation: pulse 0.5s ease;
        }
        @keyframes pulse {
            0% { opacity: 0.5; transform: scale(0.98); }
            100% { opacity: 1; transform: scale(1); }
        }
    </style>
@endpush

@push('script-page')
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).on("change", ".change-pipeline select[name=default_pipeline_id]", function () {
            $('#change-pipeline').submit();
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Lead')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <!-- List View Button -->
        <a href="{{ route('leads.list') }}" data-bs-toggle="tooltip" title="{{__('List View')}}" class="btn btn-sm btn-primary me-1">
            <i class="ti ti-list"></i>
        </a>
        
        <!-- IMPORT BUTTON - Using working route from old controller -->
        <a href="#" data-size="md" data-bs-toggle="tooltip" title="{{__('Import')}}" onclick="openImportModal()" class="btn btn-sm btn-primary me-1">
            <i class="ti ti-file-import"></i>
        </a>
        
        <!-- EXPORT BUTTON - Using working route -->
        <a href="{{ route('leads.export') }}" data-bs-toggle="tooltip" title="{{__('Export')}}" class="btn btn-sm btn-primary me-1">
            <i class="ti ti-file-export"></i>
        </a>
        
        <!-- CREATE BUTTON - Using working route -->
        <a href="#" data-size="lg" data-url="{{ route('leads.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New Lead')}}" data-title="{{__('Create Lead')}}" class="btn btn-sm btn-primary me-1">
            <i class="ti ti-plus"></i>
        </a>
        
        <!-- Social Connect Button -->
        <a href="{{ route('leads.social.connect') }}" data-bs-toggle="tooltip" title="{{__('Social Connect')}}" class="btn btn-sm btn-info">
            <i class="ti ti-wifi"></i>
        </a>
    </div>
@endsection

@section('content')
    @if($pipeline)
        <!-- Statistics Cards - Clickable Filters -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card p-3" data-filter="all" onclick="filterLeads('all')">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">{{__('Total Leads')}}</p>
                            <h2 class="fw-bold mb-0" id="totalLeadsCount">{{ $totalLeads ?? 0 }}</h2>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10">
                            <i class="ti ti-users text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="filter-badge text-muted small mt-2">
                        <i class="ti ti-click"></i> {{__('Click to view all')}}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card p-3" data-filter="assigned" onclick="filterLeads('assigned')">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">{{__('Assigned')}}</p>
                            <h2 class="fw-bold mb-0" id="assignedLeadsCount">{{ $assignedLeads ?? 0 }}</h2>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10">
                            <i class="ti ti-checkbox text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="filter-badge text-muted small mt-2">
                        <i class="ti ti-click"></i> {{__('Click to filter assigned')}}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card p-3" data-filter="unassigned" onclick="filterLeads('unassigned')">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">{{__('Unassigned')}}</p>
                            <h2 class="fw-bold mb-0" id="unassignedLeadsCount">{{ $unassignedLeads ?? 0 }}</h2>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10">
                            <i class="ti ti-user-x text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="filter-badge text-muted small mt-2">
                        <i class="ti ti-click"></i> {{__('Click to filter unassigned')}}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card p-3" data-filter="social" onclick="filterLeads('social')">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">{{__('Social Leads')}}</p>
                            <h2 class="fw-bold mb-0" id="socialLeadsCount">{{ $socialLeads ?? 0 }}</h2>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10">
                            <i class="ti ti-brand-facebook text-info fs-4"></i>
                        </div>
                    </div>
                    <div class="filter-badge text-muted small mt-2">
                        <i class="ti ti-click"></i> {{__('Click to filter social')}}
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Filter Bar -->
        <div class="filter-bar" id="activeFilterBar" style="display: none;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-filter fs-5 text-primary"></i>
                    <span class="fw-semibold">{{__('Active Filter:')}}</span>
                    <span id="activeFilterName" class="badge bg-primary"></span>
                    <span class="text-muted small" id="filterResultCount"></span>
                </div>
                <button class="btn btn-sm btn-link text-danger" onclick="resetFilters()">
                    <i class="ti ti-x"></i> {{__('Clear Filter')}}
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select class="form-select form-select-sm" id="pipelineFilter">
                        <option value="">{{__('All Pipelines')}}</option>
                        @foreach($pipelines as $id => $name)
                            <option value="{{ $id }}" {{ $pipeline->id == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" id="sourceFilter">
                        <option value="">{{__('All Sources')}}</option>
                        <option value="manual">{{__('Manual')}}</option>
                        <option value="facebook">{{__('Facebook')}}</option>
                        <option value="instagram">{{__('Instagram')}}</option>
                        <option value="whatsapp">{{__('WhatsApp')}}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" id="assignmentFilter">
                        <option value="">{{__('All Leads')}}</option>
                        <option value="assigned">{{__('Assigned')}}</option>
                        <option value="unassigned">{{__('Unassigned')}}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="ti ti-search text-muted"></i>
                        </span>
                        <input type="text" id="searchLead" class="form-control border-start-0" placeholder="{{__('Search by name, email or phone...')}}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary btn-sm w-100" onclick="resetFilters()">
                        <i class="ti ti-refresh"></i> {{__('Reset All')}}
                    </button>
                </div>
            </div>
        </div>

        <!-- Leads Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">{{ __('Total Leads') }}: {{ $totalLeads ?? 0 }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table datatable table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Email')}}</th>
                                <th>{{__('Phone')}}</th>
                                <th>{{__('Subject')}}</th>
                                <th>{{__('Stage')}}</th>
                                <th>{{__('Users')}}</th>
                                <th>{{__('Source')}}</th>
                                <th>{{__('Score')}}</th>
                                <th>{{__('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(!empty($leads) && $leads->count() > 0)
                            @foreach ($leads as $lead)
                                <tr>
                                    <td>{{ $lead->name }}</td>
                                    <td>{{ $lead->email ?? '-' }}</td>
                                    <td>{{ $lead->phone ?? '-' }}</td>
                                    <td>{{ $lead->subject }}</td>
                                    <td>{{ !empty($lead->stage) ? $lead->stage->name : '-' }}</td>
                                    <td>
                                        @foreach($lead->users as $user)
                                            <span class="badge bg-secondary">{{ $user->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($lead->lead_source && $lead->lead_source != 'manual')
                                            <span class="badge bg-info">{{ ucfirst($lead->lead_source) }}</span>
                                        @else
                                            <span class="badge bg-secondary">Manual</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $lead->lead_score >= 70 ? 'bg-success' : ($lead->lead_score >= 40 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $lead->lead_score ?? 0 }}
                                        </span>
                                    </td>
                                    @if(Auth::user()->type != 'client')
                                        <td class="Action">
                                            <span>
                                                @can('view lead')
                                                    @if($lead->is_active)
                                                        <div class="action-btn me-2 d-inline-block">
                                                            <a href="{{route('leads.show',$lead->id)}}" class="mx-3 btn btn-sm align-items-center bg-warning" data-size="xl" data-bs-toggle="tooltip" title="{{__('View')}}" data-title="{{__('Lead Detail')}}">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endcan
                                                @can('edit lead')
                                                    <div class="action-btn me-2 d-inline-block">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bg-info" data-url="{{ route('leads.edit',$lead->id) }}" data-ajax-popup="true" data-size="xl" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Lead Edit')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete lead')
                                                    <div class="action-btn d-inline-block">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['leads.destroy', $lead->id],'id'=>'delete-form-'.$lead->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            <tr class="font-style">
                                <td colspan="9" class="text-center">{{ __('No data available in table') }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            {{ __('No pipeline found. Please create a pipeline first.') }}
        </div>
    @endif
@endsection

<script>
let currentFilter = 'all';

// ========== FILTER LEADS VIA AJAX ==========
function filterLeads(filter) {
    currentFilter = filter;
    
    document.querySelectorAll('.stat-card').forEach(card => {
        card.classList.remove('active');
    });
    document.querySelector(`.stat-card[data-filter="${filter}"]`).classList.add('active');
    
    showLoading();
    
    let url = new URL(window.location.href);
    url.searchParams.set('filter', filter);
    
    fetch(url.toString(), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            updateTableData(data.leads);
            updateStatCounts(data.stat_counts);
            showFilterBar(filter, data.total_count);
        } else {
            Swal.fire('Error', data.error || 'Failed to filter leads', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        Swal.fire('Error', error.message, 'error');
    });
}

function updateTableData(leadsData) {
    const tbody = document.querySelector('table.datatable tbody');
    if (!tbody) return;
    
    let html = '';
    for (const [stageId, stageLeads] of Object.entries(leadsData)) {
        stageLeads.forEach(lead => {
            html += `
                <tr>
                    <td>${lead.name}</td>
                    <td>${lead.email || '-'}</td>
                    <td>${lead.phone || '-'}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>${lead.assigned_user || 'Unassigned'}</td>
                    <td><span class="badge bg-info">${lead.lead_source || 'Manual'}</span></td>
                    <td><span class="badge ${lead.lead_score >= 70 ? 'bg-success' : (lead.lead_score >= 40 ? 'bg-warning' : 'bg-danger')}">${lead.lead_score || 0}</span></td>
                    <td>
                        <div class="action-btn me-2 d-inline-block">
                            <a href="/leads/${lead.id}" class="mx-3 btn btn-sm align-items-center bg-warning">
                                <i class="ti ti-eye text-white"></i>
                            </a>
                        </div>
                        <div class="action-btn me-2 d-inline-block">
                            <a href="#" class="mx-3 btn btn-sm align-items-center bg-info" data-url="/leads/${lead.id}/edit" data-ajax-popup="true">
                                <i class="ti ti-pencil text-white"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    tbody.innerHTML = html;
}

function updateStatCounts(counts) {
    document.getElementById('totalLeadsCount').textContent = counts.total || 0;
    document.getElementById('assignedLeadsCount').textContent = counts.assigned || 0;
    document.getElementById('unassignedLeadsCount').textContent = counts.unassigned || 0;
    document.getElementById('socialLeadsCount').textContent = counts.social || 0;
}

function showFilterBar(filter, count) {
    const filterBar = document.getElementById('activeFilterBar');
    const filterName = document.getElementById('activeFilterName');
    const resultCount = document.getElementById('filterResultCount');
    
    let filterText = '';
    switch(filter) {
        case 'all': filterText = 'All Leads'; break;
        case 'assigned': filterText = 'Assigned Leads'; break;
        case 'unassigned': filterText = 'Unassigned Leads'; break;
        case 'social': filterText = 'Social Media Leads'; break;
    }
    
    filterName.innerHTML = filterText;
    resultCount.innerHTML = `(${count} leads found)`;
    filterBar.style.display = 'block';
}

function showLoading() {
    let overlay = document.querySelector('.loading-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>';
        overlay.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center;';
        document.body.appendChild(overlay);
    }
    overlay.style.display = 'flex';
}

function hideLoading() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

function resetFilters() {
    currentFilter = 'all';
    
    document.querySelectorAll('.stat-card').forEach(card => {
        card.classList.remove('active');
    });
    document.querySelector('.stat-card[data-filter="all"]').classList.add('active');
    document.getElementById('activeFilterBar').style.display = 'none';
    
    window.location.href = window.location.pathname;
}

// Dropdown filters
document.getElementById('pipelineFilter')?.addEventListener('change', function() {
    const params = new URLSearchParams();
    if (this.value) params.set('pipeline_id', this.value);
    if (document.getElementById('sourceFilter').value) params.set('source', document.getElementById('sourceFilter').value);
    if (document.getElementById('assignmentFilter').value) params.set('assignment', document.getElementById('assignmentFilter').value);
    if (document.getElementById('searchLead').value) params.set('search', document.getElementById('searchLead').value);
    window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
});

document.getElementById('sourceFilter')?.addEventListener('change', function() {
    const params = new URLSearchParams();
    if (document.getElementById('pipelineFilter').value) params.set('pipeline_id', document.getElementById('pipelineFilter').value);
    if (this.value) params.set('source', this.value);
    if (document.getElementById('assignmentFilter').value) params.set('assignment', document.getElementById('assignmentFilter').value);
    if (document.getElementById('searchLead').value) params.set('search', document.getElementById('searchLead').value);
    window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
});

document.getElementById('assignmentFilter')?.addEventListener('change', function() {
    const params = new URLSearchParams();
    if (document.getElementById('pipelineFilter').value) params.set('pipeline_id', document.getElementById('pipelineFilter').value);
    if (document.getElementById('sourceFilter').value) params.set('source', document.getElementById('sourceFilter').value);
    if (this.value) params.set('assignment', this.value);
    if (document.getElementById('searchLead').value) params.set('search', document.getElementById('searchLead').value);
    window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
});

let searchTimeout;
document.getElementById('searchLead')?.addEventListener('keyup', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const params = new URLSearchParams();
        if (document.getElementById('pipelineFilter').value) params.set('pipeline_id', document.getElementById('pipelineFilter').value);
        if (document.getElementById('sourceFilter').value) params.set('source', document.getElementById('sourceFilter').value);
        if (document.getElementById('assignmentFilter').value) params.set('assignment', document.getElementById('assignmentFilter').value);
        if (this.value) params.set('search', this.value);
        window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    }, 500);
});
</script>

<!-- Import Modal Content (inline) -->
<div class="modal fade" id="commonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" id="commonModalContent">
            <!-- Dynamic content loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Make sure Bootstrap JS is loaded -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>