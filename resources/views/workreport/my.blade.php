@extends('layouts.admin')

@section('page-title', 'My Work Reports')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hrm.dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('My Work Reports') }}</li>
@endsection

@push('css')
<style>
    .work-report-table .badge-pending {
        background: #ffc107;
        color: #212529;
    }
    .work-report-table .badge-approved {
        background: #28a745;
        color: white;
    }
    .work-report-table .badge-rejected {
        background: #dc3545;
        color: white;
    }
    .work-report-table .badge {
        padding: 5px 12px;
        font-weight: 600;
        font-size: 11px;
        border-radius: 20px;
    }
    .work-report-table .table td {
        vertical-align: middle;
    }
    .work-report-table .btn-sm {
        padding: 2px 8px;
        font-size: 12px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fa fa-file-text me-2 text-primary"></i>
                        {{ __('My Work Reports') }}
                    </h5>
                    <a href="{{ route('workreport.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus me-1"></i> {{ __('Submit New Report') }}
                    </a>
                </div>
                <div class="card-body">
                    @if($reports->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover work-report-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Hours') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Submitted') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reports as $index => $report)
                                        @php
                                            // ============================================
                                            // SAFE DATE FORMATTING
                                            // ============================================
                                            $formattedDate = '--';
                                            if ($report->date) {
                                                try {
                                                    if ($report->date instanceof \DateTime || $report->date instanceof \Carbon\Carbon) {
                                                        $formattedDate = $report->date->format('d M Y');
                                                    } elseif (is_string($report->date)) {
                                                        $formattedDate = date('d M Y', strtotime($report->date));
                                                    }
                                                } catch (\Exception $e) {
                                                    $formattedDate = '--';
                                                }
                                            }
                                            
                                            // ============================================
                                            // SAFE HOURS FORMATTING
                                            // ============================================
                                            $hours = $report->hours_project ?? 0;
                                            $hoursDisplay = number_format((float)$hours, 1);
                                            
                                            // ============================================
                                            // STATUS
                                            // ============================================
                                            $status = $report->review_status ?? 'pending';
                                            $statusLower = strtolower($status);
                                            $statusText = ucfirst($status);
                                            
                                            $statusClass = 'badge-pending';
                                            if ($statusLower == 'approved') {
                                                $statusClass = 'badge-approved';
                                            } elseif ($statusLower == 'rejected') {
                                                $statusClass = 'badge-rejected';
                                            }
                                            
                                            // ============================================
                                            // SAFE SUBMITTED DATE
                                            // ============================================
                                            $formattedSubmitted = '--';
                                            if ($report->created_at) {
                                                try {
                                                    if ($report->created_at instanceof \DateTime || $report->created_at instanceof \Carbon\Carbon) {
                                                        $formattedSubmitted = $report->created_at->format('d M Y h:i A');
                                                    } elseif (is_string($report->created_at)) {
                                                        $formattedSubmitted = date('d M Y h:i A', strtotime($report->created_at));
                                                    }
                                                } catch (\Exception $e) {
                                                    $formattedSubmitted = '--';
                                                }
                                            }
                                            
                                            // ============================================
                                            // DESCRIPTION
                                            // ============================================
                                            $description = $report->work_description ?? 'N/A';
                                            $descriptionLimit = Str::limit($description, 50);
                                            
                                            // ============================================
                                            // IS EDITABLE
                                            // ============================================
                                            $isPending = $statusLower == 'pending';
                                        @endphp
                                        <tr>
                                            <td>{{ $reports->firstItem() + $index }}</td>
                                            <td>{{ $formattedDate }}</td>
                                            <td>{{ $descriptionLimit }}</td>
                                            <td>{{ $hoursDisplay }}</td>
                                            <td>
                                                <span class="badge {{ $statusClass }}">
                                                    {{ $statusText }}
                                                </span>
                                            </td>
                                            <td>{{ $formattedSubmitted }}</td>
                                            <td>
                                                <a href="{{ route('workreport.show', $report->id) }}" class="btn btn-sm btn-info" title="View">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                @if($isPending)
                                                    <a href="{{ route('workreport.edit', $report->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('workreport.destroy', $report->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this report?')">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $reports->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa fa-file-text-o display-4 text-muted"></i>
                            <p class="mt-3 text-muted">{{ __('No work reports found.') }}</p>
                            <a href="{{ route('workreport.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus me-1"></i> {{ __('Submit Your First Report') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection