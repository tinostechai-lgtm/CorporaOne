@extends('layouts.admin')

@section('page-title')
    {{ __('Bonvoice Settings') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Bonvoice Settings') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Bonvoice API Settings') }}</h5>
                    <small class="text-muted">{{ __('Configure your Bonvoice integration for call management') }}</small>
                </div>
                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ti ti-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ti ti-alert-circle"></i>
                            <ul class="mb-0 mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('bonvoice.settings.save') }}" method="POST" id="bonvoiceForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('API Username') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="bonvoice_api_username" class="form-control"
                                           value="{{ old('bonvoice_api_username', $settings['bonvoice_api_username'] ?? '') }}" 
                                           placeholder="Enter your Bonvoice username" required>
                                    <small class="text-muted">{{ __('The username for Bonvoice API authentication') }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('API Password') }} <span class="text-danger">*</span></label>
                                    <input type="password" name="bonvoice_api_password" class="form-control"
                                           value="{{ old('bonvoice_api_password', $settings['bonvoice_api_password'] ?? '') }}" 
                                           placeholder="Enter your Bonvoice password" required>
                                    <small class="text-muted">{{ __('The password for Bonvoice API authentication') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Base URL') }} <span class="text-danger">*</span></label>
                                    <input type="url" name="bonvoice_base_url" class="form-control"
                                           value="{{ old('bonvoice_base_url', $settings['bonvoice_base_url'] ?? 'https://backend.pbx.bonvoice.com') }}"
                                           placeholder="https://backend.pbx.bonvoice.com" required>
                                    <small class="text-muted">{{ __('The base URL for Bonvoice API (usually https://backend.pbx.bonvoice.com)') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle"></i>
                                    <strong>{{ __('Authentication Endpoint:') }}</strong> {{ rtrim($settings['bonvoice_base_url'] ?? 'https://backend.pbx.bonvoice.com', '/') }}/usermanagement/external-auth/
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

<!-- Test Connection Modal -->
<div class="modal fade" id="testConnectionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Bonvoice Connection Test') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="testModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0">{{ __('Testing connection...') }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
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
        
        // Disable button and show loading
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> {{ __("Testing...") }}');
        
        // Get form data
        var username = $('input[name="bonvoice_api_username"]').val();
        var password = $('input[name="bonvoice_api_password"]').val();
        var baseUrl = $('input[name="bonvoice_base_url"]').val();
        
        if (!username || !password) {
            alert('Please enter both username and password before testing.');
            $btn.prop('disabled', false).html(originalText);
            return;
        }
        
        // Show modal
        $('#testConnectionModal').modal('show');
        
        $.ajax({
            url: '{{ route("bonvoice.test.connection") }}',
            type: 'POST',
            data: {
                bonvoice_api_username: username,
                bonvoice_api_password: password,
                bonvoice_base_url: baseUrl,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                console.log('Test response:', response);
                if (response.success) {
                    $('#testModalBody').html(`
                        <div class="text-center py-3">
                            <i class="ti ti-check-circle fs-1 text-success mb-3 d-block"></i>
                            <h5 class="text-success">✓ Connection Successful!</h5>
                            <p class="mb-2">${response.message || 'Authentication worked successfully.'}</p>
                            <hr>
                            <div class="text-start bg-light p-3 rounded">
                                <small class="text-muted">Endpoint: ${baseUrl}/usermanagement/external-auth/</small><br>
                                <small class="text-muted">Username: ${username}</small><br>
                                ${response.token ? `<small class="text-muted">Token: ${response.token.substring(0, 20)}...</small>` : ''}
                            </div>
                        </div>
                    `);
                } else {
                    $('#testModalBody').html(`
                        <div class="text-center py-3">
                            <i class="ti ti-alert-circle fs-1 text-danger mb-3 d-block"></i>
                            <h5 class="text-danger">✗ Connection Failed!</h5>
                            <p class="mb-2">${response.message || 'Authentication failed. Please check your credentials.'}</p>
                            <hr>
                            <div class="text-start bg-light p-3 rounded">
                                <small class="text-muted">Please verify:</small>
                                <ul class="mt-2 mb-0">
                                    <li>Username and password are correct</li>
                                    <li>Base URL is accessible</li>
                                    <li>Your account has API access enabled</li>
                                </ul>
                            </div>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Test error:', xhr);
                
                var errorMsg = 'Connection failed!';
                var statusText = '';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 401 || xhr.status === 403) {
                    errorMsg = 'Authentication failed. Please check your username and password.';
                    statusText = 'Invalid credentials';
                } else if (xhr.status === 0) {
                    errorMsg = 'Network error. Please check your connection and base URL.';
                    statusText = 'Network unreachable';
                } else if (xhr.status === 404) {
                    errorMsg = 'API endpoint not found. Please check your base URL.';
                    statusText = 'Endpoint not found';
                } else if (xhr.status === 500) {
                    errorMsg = 'Server error. Please check the Bonvoice service status.';
                    statusText = 'Internal server error';
                }
                
                $('#testModalBody').html(`
                    <div class="text-center py-3">
                        <i class="ti ti-alert-circle fs-1 text-danger mb-3 d-block"></i>
                        <h5 class="text-danger">✗ Connection Failed!</h5>
                        <p class="mb-2">${errorMsg}</p>
                        ${statusText ? `<small class="text-muted d-block mb-2">Status: ${statusText}</small>` : ''}
                        <hr>
                        <div class="text-start bg-light p-3 rounded">
                            <small class="text-muted">Error details:</small><br>
                            <small class="text-muted">Status: ${xhr.status} ${xhr.statusText}</small><br>
                            <small class="text-muted">URL: ${baseUrl}/usermanagement/external-auth/</small>
                        </div>
                    </div>
                `);
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endpush