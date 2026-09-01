@extends('layouts.admin')

@section('page-title')
    {{ __('Call Logs') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Call Logs') }}</li>
@endsection

@section('content')
<style>
    .call-log-table th {
        background: #f8f9fa;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .call-log-table td {
        padding: 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .call-log-table tr:hover {
        background: #fafbfc;
    }
    
    .badge-call-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        display: inline-block;
    }
    
    .badge-completed {
        background: #d1fae5;
        color: #065f46;
    }
    
    .badge-answered {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .badge-missed {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .badge-initiated {
        background: #fef3c7;
        color: #b45309;
    }
    
    .badge-outbound {
        background: #e0e7ff;
        color: #3730a3;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 10px;
    }
    
    .badge-inbound {
        background: #fae8ff;
        color: #86198f;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 10px;
    }
    
    .call-uuid {
        font-family: monospace;
        font-size: 11px;
        color: #6b7280;
        background: #f3f4f6;
        padding: 4px 8px;
        border-radius: 12px;
        display: inline-block;
    }
    
    .stats-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.2s;
    }
    
    .stats-card:hover {
        transform: translateY(-2px);
    }
    
    .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="row">
    <div class="col-12">
        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted text-uppercase small">Total Calls</span>
                                <h2 class="mb-0 fw-bold mt-1">{{ $callLogs->count() }}</h2>
                            </div>
                            <div class="stats-icon bg-primary bg-opacity-10">
                                <i class="ti ti-phone-call fs-4 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted text-uppercase small">Completed</span>
                                <h2 class="mb-0 fw-bold mt-1 text-success">{{ $callLogs->where('status', 'completed')->count() }}</h2>
                            </div>
                            <div class="stats-icon bg-success bg-opacity-10">
                                <i class="ti ti-check fs-4 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted text-uppercase small">Answered</span>
                                <h2 class="mb-0 fw-bold mt-1 text-info">{{ $callLogs->where('status', 'answered')->count() }}</h2>
                            </div>
                            <div class="stats-icon bg-info bg-opacity-10">
                                <i class="ti ti-phone fs-4 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted text-uppercase small">Missed</span>
                                <h2 class="mb-0 fw-bold mt-1 text-danger">{{ $callLogs->where('status', 'missed')->count() }}</h2>
                            </div>
                            <div class="stats-icon bg-danger bg-opacity-10">
                                <i class="ti ti-alert-circle fs-4 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Call Logs Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-semibold">{{ __('Call Logs') }}</h5>
                    <small class="text-muted">Call history from Bonvoice integration</small>
                </div>
                <span class="badge bg-primary">{{ $callLogs->count() }} Total</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="call-log-table table mb-0">
                        <thead>
                             <div class="col-4">
                                <th style="width: 5%">#</th>
                                <th style="width: 15%">Caller</th>
                                <th style="width: 15%">Called</th>
                                <th style="width: 8%">Direction</th>
                                <th style="width: 10%">Status</th>
                                <th style="width: 8%">Duration</th>
                                <th style="width: 15%">Date/Time</th>
                                <th style="width: 12%">Call UUID</th>
                                <th style="width: 12%">Event ID</th>
                             </div>
                        </thead>
                        <tbody>
                            @forelse($callLogs as $log)
                            <tr>
                                <td><span class="fw-semibold">{{ $loop->iteration }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-phone-call me-2 text-muted"></i>
                                        <span class="fw-medium">{{ $log->caller_number ?: '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-phone me-2 text-muted"></i>
                                        <span>{{ $log->called_number ?: '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($log->direction == 'outbound')
                                        <span class="badge-outbound">Outbound</span>
                                    @else
                                        <span class="badge-inbound">Inbound</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->status == 'completed')
                                        <span class="badge-call-status badge-completed">Completed</span>
                                    @elseif($log->status == 'answered')
                                        <span class="badge-call-status badge-answered">Answered</span>
                                    @elseif($log->status == 'missed')
                                        <span class="badge-call-status badge-missed">Missed</span>
                                    @else
                                        <span class="badge-call-status badge-initiated">{{ ucfirst($log->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->duration)
                                        <span class="fw-medium">{{ $log->duration }} sec</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small">
                                        {{ $log->start_time ? \Carbon\Carbon::parse($log->start_time)->format('Y-m-d H:i:s') : $log->created_at->format('Y-m-d H:i:s') }}
                                    </div>
                                </td>
                                <td>
                                    <span class="call-uuid">{{ substr($log->call_uuid, 0, 15) }}...</span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ substr($log->event_id, 0, 15) ?: '-' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="ti ti-phone-off fs-1 text-muted d-block mb-3"></i>
                                    <p class="text-muted mb-0">No call logs found</p>
                                    <small class="text-muted">Make a call or receive a webhook to see logs here</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('.call-log-table').DataTable({
                order: [[6, 'desc']],
                pageLength: 15,
                language: {
                    emptyTable: "No call logs found",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    lengthMenu: "Show _MENU_ entries",
                    loadingRecords: "Loading...",
                    processing: "Processing...",
                    search: "Search:",
                    zeroRecords: "No matching records found",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        }
    });
</script>
@endpush