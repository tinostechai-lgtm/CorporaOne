@extends('layouts.admin')

@section('page-title')
    {{ __('Call Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('bonvoice.call_logs') }}">{{ __('Call Logs') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Call Details') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Call Details') }}</h5>
                    <div>
                        @if($callLog->recording_url)
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#recordingModal">
                                <i class="ti ti-headphone"></i> {{ __('Play Recording') }}
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="35%">{{ __('Call ID') }}</th>
                                            <td>{{ $callLog->call_id ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Source Number') }}</th>
                                            <td>{{ $callLog->source_number ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Destination Number') }}</th>
                                            <td>{{ $callLog->destination_number ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Display Number') }}</th>
                                            <td>{{ $callLog->display_number ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Direction') }}</th>
                                            <td>
                                                @if($callLog->direction == 'inbound')
                                                    <span class="badge bg-info">{{ __('Inbound') }}</span>
                                                @elseif($callLog->direction == 'outbound')
                                                    <span class="badge bg-warning">{{ __('Outbound') }}</span>
                                                @else
                                                    {{ ucfirst($callLog->direction ?? '-') }}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Call Type') }}</th>
                                            <td>
                                                @if($callLog->call_type == '0')
                                                    {{ __('Initiated') }}
                                                @elseif($callLog->call_type == '1')
                                                    {{ __('Answered') }}
                                                @elseif($callLog->call_type == '2')
                                                    {{ __('Hangup') }}
                                                @else
                                                    {{ $callLog->call_type ?? '-' }}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Status') }}</th>
                                            <td>
                                                @if($callLog->status == 'answered')
                                                    <span class="badge bg-success">{{ __('Answered') }}</span>
                                                @elseif($callLog->status == 'completed')
                                                    <span class="badge bg-success">{{ __('Completed') }}</span>
                                                @elseif($callLog->status == 'missed')
                                                    <span class="badge bg-danger">{{ __('Missed') }}</span>
                                                @elseif($callLog->status == 'initiated')
                                                    <span class="badge bg-info">{{ __('Initiated') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($callLog->status ?? 'Unknown') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Agent Status') }}</th>
                                            <td>{{ $callLog->agent_status ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Leg') }}</th>
                                            <td>
                                                @if($callLog->leg == 'A')
                                                    {{ __('Caller (Leg A)') }}
                                                @elseif($callLog->leg == 'B')
                                                    {{ __('Callee (Leg B)') }}
                                                @else
                                                    {{ $callLog->leg ?? '-' }}
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="35%">{{ __('Start Time') }}</th>
                                            <td>{{ $callLog->start_time ? \Carbon\Carbon::parse($callLog->start_time)->format('Y-m-d H:i:s') : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('End Time') }}</th>
                                            <td>{{ $callLog->end_time ? \Carbon\Carbon::parse($callLog->end_time)->format('Y-m-d H:i:s') : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Duration') }}</th>
                                            <td>
                                                @if($callLog->duration)
                                                    <span class="badge bg-dark">{{ gmdate('H:i:s', $callLog->duration) }}</span>
                                                    <small class="text-muted ms-2">({{ $callLog->duration }} seconds)</small>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('DTMF') }}</th>
                                            <td>
                                                @if($callLog->dtmf)
                                                    <code>{{ $callLog->dtmf }}</code>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Event ID') }}</th>
                                            <td>{{ $callLog->event_id ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Account ID') }}</th>
                                            <td>{{ $callLog->account_id ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Data Source') }}</th>
                                            <td>{{ $callLog->data_source ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Call Back Parent ID') }}</th>
                                            <td>{{ $callLog->call_back_parent_id ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Created At') }}</th>
                                            <td>{{ $callLog->created_at ? \Carbon\Carbon::parse($callLog->created_at)->format('Y-m-d H:i:s') : '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    @if($callLog->call_back_params)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">{{ __('Call Back Parameters') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <pre class="bg-light p-3 rounded">{{ json_encode($callLog->call_back_params, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if($callLog->raw_payload)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">{{ __('Raw Payload') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">{{ json_encode($callLog->raw_payload, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('bonvoice.call_logs') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left"></i> {{ __('Back to Call Logs') }}
                        </a>
                        
                        @if($callLog->call_id)
                            <button type="button" class="btn btn-info" id="refreshCallDetails">
                                <i class="ti ti-refresh"></i> {{ __('Refresh from API') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recording Modal -->
    @if($callLog->recording_url)
    <div class="modal fade" id="recordingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Call Recording') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <audio controls style="width: 100%;">
                        <source src="{{ $callLog->recording_url }}" type="audio/mpeg">
                        {{ __('Your browser does not support the audio element.') }}
                    </audio>
                    <div class="mt-3">
                        <a href="{{ $callLog->recording_url }}" class="btn btn-sm btn-primary" download>
                            <i class="ti ti-download"></i> {{ __('Download Recording') }}
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            $('#refreshCallDetails').on('click', function() {
                var $btn = $(this);
                var originalText = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Loading...');
                
                $.ajax({
                    url: '{{ route("bonvoice.call.record", $callLog->call_id) }}',
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            // Update the page with new data
                            var data = response.data;
                            location.reload();
                        } else {
                            showToast('error', response.message || 'Failed to refresh call details');
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'Failed to refresh call details';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showToast('error', errorMsg);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
        
        function showToast(type, message) {
            var toast = $('#toast');
            if (toast.length) {
                toast.removeClass('bg-success bg-danger bg-info bg-warning');
                toast.addClass(type === 'success' ? 'bg-success' : 'bg-danger');
                toast.find('.toast-body').text(message);
                new bootstrap.Toast(toast).show();
            } else {
                alert(message);
            }
        }
    </script>
@endpush