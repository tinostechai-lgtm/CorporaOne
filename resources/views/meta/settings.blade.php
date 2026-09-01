@extends('layouts.admin')

@section('page-title')
    {{ __('Meta Lead Settings') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Meta Lead Settings') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Meta Lead Settings') }}</h5>
                </div>
                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('meta.settings.save') }}" method="POST" id="metaSettingsForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Meta App ID') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="meta_app_id" class="form-control"
                                           value="{{ old('meta_app_id', $settings['meta_app_id'] ?? '') }}" required>
                                    <small class="text-muted">{{ __('Your Facebook App ID from Meta for Developers') }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Meta App Secret') }} <span class="text-danger">*</span></label>
                                    <input type="password" name="meta_app_secret" class="form-control"
                                           value="{{ old('meta_app_secret', $settings['meta_app_secret'] ?? '') }}" required>
                                    <small class="text-muted">{{ __('Your Facebook App Secret from Meta for Developers') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Meta Access Token') }} <span class="text-danger">*</span></label>
                                    <input type="password" name="meta_access_token" class="form-control"
                                           value="{{ old('meta_access_token', $settings['meta_access_token'] ?? '') }}" required>
                                    <small class="text-muted">{{ __('Page Access Token from Meta for Developers') }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Meta Verify Token') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="meta_verify_token" class="form-control"
                                           value="{{ old('meta_verify_token', $settings['meta_verify_token'] ?? '') }}" required>
                                    <small class="text-muted">{{ __('Verify Token for webhook verification') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Meta Page ID') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="meta_page_id" class="form-control"
                                           value="{{ old('meta_page_id', $settings['meta_page_id'] ?? '') }}" required>
                                    <small class="text-muted">{{ __('Facebook Page ID for lead generation') }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Meta Webhook URL') }}</label>
                                    <input type="url" name="meta_webhook_url" class="form-control"
                                           value="{{ old('meta_webhook_url', $settings['meta_webhook_url'] ?? url('/api/leads/meta-webhook')) }}" readonly>
                                    <small class="text-muted">{{ __('Webhook URL for receiving lead data (auto-generated)') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy"></i> {{ __('Save Settings') }}
                                    </button>
                                    <button type="button" id="testConnection" class="btn btn-info">
                                        <i class="ti ti-plug"></i> {{ __('Test Connection') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
$(document).ready(function() {
    $('#testConnection').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> {{ __("Testing...") }}');
        
        // Get form data
        var formData = {
            meta_access_token: $('input[name="meta_access_token"]').val(),
            meta_page_id: $('input[name="meta_page_id"]').val(),
            _token: '{{ csrf_token() }}'
        };
        
        console.log('Testing connection with:', { page_id: formData.meta_page_id });
        
        $.ajax({
            url: '{{ route("meta.test.connection") }}',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert('✓ ' + (response.message || 'Connection successful!'));
                    if (response.data && response.data.name) {
                        alert('Page Name: ' + response.data.name + '\nPage ID: ' + response.data.id);
                    }
                } else {
                    alert('✗ ' + (response.message || 'Connection failed'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Test error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                var errorMsg = 'Connection test failed.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 0) {
                    errorMsg = 'Network error. Please check your connection.';
                } else if (xhr.status === 419) {
                    errorMsg = 'Session expired. Please refresh the page.';
                } else if (xhr.status === 500) {
                    errorMsg = 'Server error. Please check logs.';
                }
                alert('✗ ' + errorMsg);
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endpush