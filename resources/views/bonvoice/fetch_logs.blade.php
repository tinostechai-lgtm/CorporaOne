@extends('layouts.admin')

@section('page-title')
    {{ __('Fetch Bonvoice Call Logs') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('bonvoice.call_logs') }}">{{ __('Call Logs') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Fetch Logs') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Fetch Call Logs from Bonvoice API') }}</h5>
                <small class="text-muted">Enter an Event ID to fetch call details from Bonvoice API</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="form-label">Event ID</label>
                            <input type="text" id="event_id" class="form-control" placeholder="Enter Event ID (e.g., EVT_xxxxx)">
                            <small class="text-muted">You can find Event IDs from your call responses or from Bonvoice dashboard</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" id="fetch-log-btn" class="btn btn-primary w-100">
                                <i class="ti ti-search"></i> Fetch Call Log
                            </button>
                        </div>
                    </div>
                </div>
                
                <div id="fetch-result" style="display: none;" class="mt-4">
                    <div class="alert" id="result-alert"></div>
                    <div id="result-data" class="mt-3"></div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>{{ __('How to Get Call Logs') }}</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="ti ti-info-circle"></i>
                    <strong>Note:</strong> Bonvoice API requires an Event ID to fetch call details:
                    <ol class="mt-2 mb-0">
                        <li>When you make a call via the API, you receive an <code>event_id</code> in the response</li>
                        <li>Use that <code>event_id</code> to fetch the call details here</li>
                        <li>You can also configure the webhook to receive call logs automatically</li>
                    </ol>
                </div>
                
                <h6 class="mt-3">Webhook URL for Automatic Call Logs:</h6>
                <div class="input-group">
                    <input type="text" id="webhook-url" class="form-control" value="{{ url('/bonvoice/webhook') }}" readonly>
                    <button class="btn btn-secondary" onclick="copyWebhookUrl()">
                        <i class="ti ti-copy"></i> Copy
                    </button>
                </div>
                <small class="text-muted">Configure this URL in your Bonvoice settings to receive call logs automatically</small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
    // Store the route base URL as a variable to avoid Blade compilation issues
    var fetchCallLogUrl = '{{ url("/bonvoice/fetch-call-log") }}';
    
    function copyWebhookUrl() {
        var copyText = document.getElementById("webhook-url");
        copyText.select();
        document.execCommand("copy");
        alert("Webhook URL copied: " + copyText.value);
    }
    
    $('#fetch-log-btn').on('click', function() {
        var eventId = $('#event_id').val();
        
        if (!eventId) {
            alert('Please enter an Event ID');
            return;
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Fetching...');
        
        // Build the URL correctly by concatenating
        var url = fetchCallLogUrl + '/' + encodeURIComponent(eventId);
        
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#result-alert').removeClass('alert-danger').addClass('alert-success')
                        .html('<i class="ti ti-check-circle"></i> Call log fetched and stored successfully!');
                    
                    var data = response.data?.data?.outbound || {};
                    $('#result-data').html(`
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Value</th>
                                    </thead>
                                <tbody>
                                    <tr><th>Call ID</th><td>${data.callID || '-'}</td></tr>
                                    <tr><th>Caller Number</th><td>${data.SourceNumber || '-'}</td></tr>
                                    <tr><th>Called Number</th><td>${data.DestinationNumber || '-'}</td></tr>
                                    <tr><th>Status</th><td>${data.Status || '-'}</td></tr>
                                    <tr><th>Duration</th><td>${data.CallDuration || 0} sec</td></tr>
                                    <tr><th>Start Time</th><td>${data.StartTime || '-'}</td></tr>
                                    <tr><th>Event ID</th><td>${data.eventID || '-'}</td></tr>
                                    <tr><th>Direction</th><td>${data.Direction || '-'}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    `);
                } else {
                    $('#result-alert').removeClass('alert-success').addClass('alert-danger')
                        .html('<i class="ti ti-alert-circle"></i> ' + (response.message || 'Failed to fetch call log'));
                    $('#result-data').html('');
                }
                $('#fetch-result').show();
            },
            error: function(xhr) {
                var errorMsg = xhr.responseJSON?.message || 'Failed to fetch call log';
                $('#result-alert').removeClass('alert-success').addClass('alert-danger')
                    .html('<i class="ti ti-alert-circle"></i> ' + errorMsg);
                $('#result-data').html('');
                $('#fetch-result').show();
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="ti ti-search"></i> Fetch Call Log');
            }
        });
    });
</script>
@endpush