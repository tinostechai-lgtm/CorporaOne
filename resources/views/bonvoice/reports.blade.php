@extends('layouts.admin')

@section('page-title')
    {{ __('Bonvoice Reports') }}
@endsection

@section('title')
    {{ __('Bonvoice Reports') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('bonvoice.call_logs') }}">{{ __('Call Logs') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Reports') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h6 class="text-muted mb-1">{{ __('Total Calls') }}</h6>
                                    <h3 class="mb-0">{{ $totalCalls ?? 0 }}</h3>
                                </div>
                                <div class="col-auto">
                                    <div class="icon-icon bg-primary icon-icon-style">
                                        <i class="ti ti-phone-call"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h6 class="text-muted mb-1">{{ __('Completed Calls') }}</h6>
                                    <h3 class="mb-0">{{ $completedCalls ?? 0 }}</h3>
                                </div>
                                <div class="col-auto">
                                    <div class="icon-icon bg-success icon-icon-style">
                                        <i class="ti ti-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h6 class="text-muted mb-1">{{ __('Failed Calls') }}</h6>
                                    <h3 class="mb-0">{{ $failedCalls ?? 0 }}</h3>
                                </div>
                                <div class="col-auto">
                                    <div class="icon-icon bg-danger icon-icon-style">
                                        <i class="ti ti-alert-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chart Section -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Call Statistics') }}</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="callStatsChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Calls Table -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Recent Call Logs') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table dataTable" id="recentCallsTable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Call ID') }}</th>
                                            <th>{{ __('Source Number') }}</th>
                                            <th>{{ __('Destination') }}</th>
                                            <th>{{ __('Direction') }}</th>
                                            <th>{{ __('Duration') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Action') }}</th>
                                         </thead>
                                    <tbody>
                                        @forelse($reports as $log)
                                             <tr>
                                                <td>{{ $log->call_id ?? $log->id }}</td>
                                                <td>{{ $log->source_number ?? '-' }}</td>
                                                <td>{{ $log->destination_number ?? '-' }}</td>
                                                <td>
                                                    @if($log->direction == 'inbound')
                                                        <span class="badge bg-info">{{ __('Inbound') }}</span>
                                                    @elseif($log->direction == 'outbound')
                                                        <span class="badge bg-warning">{{ __('Outbound') }}</span>
                                                    @else
                                                        {{ ucfirst($log->direction ?? '-') }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($log->duration)
                                                        {{ gmdate('H:i:s', $log->duration) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($log->status == 'answered' || $log->status == 'completed')
                                                        <span class="badge bg-success">{{ __('Completed') }}</span>
                                                    @elseif($log->status == 'missed')
                                                        <span class="badge bg-danger">{{ __('Missed') }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ ucfirst($log->status ?? 'Unknown') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $log->start_time ? \Carbon\Carbon::parse($log->start_time)->format('Y-m-d H:i:s') : ($log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-') }}</td>
                                                <td>
                                                    <a href="{{ route('bonvoice.call_details', $log->id) }}" class="btn btn-sm btn-info">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                </td>
                                             </tr>
                                        @empty
                                             <tr>
                                                <td colspan="8" class="text-center">{{ __('No call logs found') }}</td>
                                             </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="{{ route('bonvoice.call_logs') }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-arrow-left"></i> {{ __('View All Call Logs') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            if ($.fn.DataTable) {
                $('#recentCallsTable').DataTable({
                    order: [[6, 'desc']],
                    pageLength: 10,
                    language: {
                        emptyTable: "{{ __('No call logs available') }}",
                        info: "{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}",
                        infoEmpty: "{{ __('Showing 0 to 0 of 0 entries') }}",
                        infoFiltered: "{{ __('(filtered from _MAX_ total entries)') }}",
                        lengthMenu: "{{ __('Show _MENU_ entries') }}",
                        loadingRecords: "{{ __('Loading...') }}",
                        processing: "{{ __('Processing...') }}",
                        search: "{{ __('Search:') }}",
                        zeroRecords: "{{ __('No matching records found') }}",
                        paginate: {
                            first: "{{ __('First') }}",
                            last: "{{ __('Last') }}",
                            next: "{{ __('Next') }}",
                            previous: "{{ __('Previous') }}"
                        }
                    }
                });
            }
            
            // Create chart
            var ctx = document.getElementById('callStatsChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Total Calls', 'Completed', 'Failed'],
                    datasets: [{
                        label: 'Call Statistics',
                        data: [{{ $totalCalls ?? 0 }}, {{ $completedCalls ?? 0 }}, {{ $failedCalls ?? 0 }}],
                        backgroundColor: ['#4f46e5', '#22c55e', '#ef4444'],
                        borderColor: ['#4f46e5', '#22c55e', '#ef4444'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Calls'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Call Status'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
@endpush