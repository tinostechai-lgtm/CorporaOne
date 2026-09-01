@extends('layouts.admin')

@section('page-title')
    {{__('Manage Meta Leads')}}
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
@endpush

@push('script-page')
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script>
        $(document).on('click', '.view-lead', function () {
            var leadId = $(this).data('lead-id');
            
            $.ajax({
                url: '{{ route("meta.leads.details") }}',
                type: 'POST',
                data: {
                    lead_id: leadId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        var lead = response.data;
                        var html = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Lead Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>{{__('Name')}}</th>
                                            <td>${lead.name}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('Email')}}</th>
                                            <td>${lead.email || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('Phone')}}</th>
                                            <td>${lead.phone || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('Subject')}}</th>
                                            <td>${lead.subject}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('Stage')}}</th>
                                            <td>${lead.stage ? lead.stage.name : '-'}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('Pipeline')}}</th>
                                            <td>${lead.pipeline ? lead.pipeline.name : '-'}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('Created Date')}}</th>
                                            <td>${lead.date}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('Meta Lead ID')}}</th>
                                            <td>${lead.meta_leadgen_id || '-'}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5>{{__('Assigned Users')}}</h5>
                                    <div class="d-flex flex-wrap gap-2">
                                        ${lead.users.map(user => `
                                            <span class="badge bg-primary">${user.name}</span>
                                        `).join('')}
                                    </div>
                                    
                                    <h5 class="mt-4">{{__('Notes')}}</h5>
                                    <p>${lead.notes || 'No notes'}</p>
                                </div>
                            </div>
                        `;
                        
                        $('#leadDetailsContent').html(html);
                        $('#leadDetailModal').modal('show');
                    } else {
                        alert(response.message);
                    }
                },
                error: function () {
                    alert('Failed to load lead details');
                }
            });
        });

        // Sync leads button
        $(document).on('click', '#syncLeads', function () {
            var btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Syncing...');

            $.ajax({
                url: '{{ route("meta.leads.sync") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function () {
                    alert('Failed to sync leads');
                },
                complete: function () {
                    btn.prop('disabled', false).html('<i class="ti ti-refresh"></i> Sync Leads');
                }
            });
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('meta.settings')}}">{{__('Meta Settings')}}</a></li>
    <li class="breadcrumb-item">{{__('Meta Leads')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('meta.settings') }}" class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip" title="{{__('Settings')}}">
            <i class="ti ti-settings"></i>
        </a>
        <button id="syncLeads" class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip" title="{{__('Sync Leads')}}">
            <i class="ti ti-refresh"></i> {{__('Sync Leads')}}
        </button>
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- Stats Cards -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-fluid">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">{{__('Total Meta Leads')}}</h6>
                            <h3 class="mb-0">{{ $totalLeads }}</h3>
                        </div>
                        <div class="theme-avtar bg-primary">
                            <i class="ti ti-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @foreach($leadsByStage as $stageId => $count)
            @php
                $stage = \App\Models\LeadStage::find($stageId);
            @endphp
            @if($stage)
            <div class="col-xl-3 col-md-6">
                <div class="card card-fluid">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted mb-1">{{ $stage->name }}</h6>
                                <h3 class="mb-0">{{ $count }}</h3>
                            </div>
                            <div class="theme-avtar" style="background-color: {{ $stage->color ?? '#6fd943' }}">
                                <i class="ti ti-list"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach

        <!-- Leads Table -->
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Meta Leads List')}}</h5>
                </div>
                <div class="card-body table-border-style">
                    @if($totalLeads > 0)
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th>{{__('Name')}}</th>
                                        <th>{{__('Email')}}</th>
                                        <th>{{__('Phone')}}</th>
                                        <th>{{__('Subject')}}</th>
                                        <th>{{__('Stage')}}</th>
                                        <th>{{__('Created Date')}}</th>
                                        <th>{{__('Meta Lead ID')}}</th>
                                        <th>{{__('Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leads as $lead)
                                        <tr>
                                            <td>{{ $lead->name }}</td>
                                            <td>{{ $lead->email ?? '-' }}</td>
                                            <td>{{ $lead->phone ?? '-' }}</td>
                                            <td>{{ $lead->subject }}</td>
                                            <td>
                                                @if($lead->stage)
                                                    <span class="badge bg-primary" style="background-color: {{ $lead->stage->color }} !important;">
                                                        {{ $lead->stage->name }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $lead->date }}</td>
                                            <td>
                                                @if($lead->meta_leadgen_id)
                                                    <span class="badge bg-info">{{ substr($lead->meta_leadgen_id, 0, 15) }}...</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="Action">
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-primary view-lead" data-lead-id="{{ $lead->id }}">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-warning" target="_blank">
                                                        <i class="ti ti-external-link"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-users fs-5 text-muted"></i>
                            <h5 class="mt-2">{{__('No Meta Leads Found')}}</h5>
                            <p class="text-muted">{{__('Leads from Meta Ads will appear here after webhook receives data.')}}</p>
                            <a href="{{ route('meta.settings') }}" class="btn btn-primary btn-sm">
                                {{__('Configure Meta Settings')}}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Detail Modal -->
    <div class="modal fade" id="leadDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{__('Lead Details')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="leadDetailsContent">
                    <!-- Content loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Close')}}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

