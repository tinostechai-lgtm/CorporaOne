@extends('layouts.admin')

@section('page-title')
    {{ __('Bonvoice Call Logs') }}
@endsection

@section('title')
    {{ __('Bonvoice Call Logs') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Bonvoice Call Logs') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('Call Logs') }}</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('bonvoice.reports') }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-chart-bar"></i> {{ __('Reports') }}
                            </a>
                            <button type="button" class="btn btn-info btn-sm" id="refreshLogs">
                                <i class="ti ti-refresh"></i> {{ __('Refresh') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table dataTable" id="callLogsTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Call ID') }}</th>
                                    <th>{{ __('Source Number') }}</th>
                                    <th>{{ __('Destination Number') }}</th>
                                    <th>{{ __('Direction') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Action') }}</th>
                                 </thead>
                            <tbody>
                                @forelse($callLogs as $log)
                                    <tr>
                                        <td>
                                            <a href="{{ route('bonvoice.call_details', $log->id) }}" class="text-primary">
                                                {{ $log->call_id ?? $log->id }}
                                            </a>
                                         </td>
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
                                                <span class="badge bg-success">{{ __('Answered') }}</span>
                                            @elseif($log->status == 'missed')
                                                <span class="badge bg-danger">{{ __('Missed') }}</span>
                                            @elseif($log->status == 'initiated')
                                                <span class="badge bg-info">{{ __('Initiated') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($log->status ?? 'Unknown') }}</span>
                                            @endif
                                         </td>
                                         <td>{{ $log->start_time ? \Carbon\Carbon::parse($log->start_time)->format('Y-m-d H:i:s') : ($log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-') }}</td>
                                         <td>
                                            <a href="{{ route('bonvoice.call_details', $log->id) }}" class="btn btn-sm btn-info">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            @if($log->recording_url)
                                                <button type="button" class="btn btn-sm btn-success play-recording" data-url="{{ $log->recording_url }}">
                                                    <i class="ti ti-headphone"></i>
                                                </button>
                                            @endif
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
            </div>
        </div>
    </div>

    <!-- Audio Modal -->
    <div class="modal fade" id="audioModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Call Recording') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <audio controls style="width: 100%;">
                        <source id="audioSource" src="" type="audio/mpeg">
                        {{ __('Your browser does not support the audio element.') }}
                    </audio>
                </div>
                <div class="modal-footer">
                    <a href="#" id="downloadLink" class="btn btn-primary" download>
                        <i class="ti ti-download"></i> {{ __('Download') }}
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            if ($.fn.DataTable) {
                $('#callLogsTable').DataTable({
                    order: [[6, 'desc']],
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
            
            // Play recording
            $('.play-recording').on('click', function() {
                var url = $(this).data('url');
                $('#audioSource').attr('src', url);
                $('#downloadLink').attr('href', url);
                $('#audioModal').modal('show');
            });
            
            // Refresh logs
            $('#refreshLogs').on('click', function() {
                location.reload();
            });
        });
    </script>
@endpush