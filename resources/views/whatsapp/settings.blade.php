@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fab fa-whatsapp text-success"></i>
                        WhatsApp Business API Settings
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Setup Instructions</h5>
                        <ol class="mb-0">
                            <li>Go to <a href="https://developers.facebook.com/" target="_blank">Meta Developers</a></li>
                            <li>Create a new app (select "Other" > "Business")</li>
                            <li>Add "WhatsApp" product to your app</li>
                            <li>Get your Phone Number ID and Access Token from the WhatsApp > API Setup</li>
                            <li>Configure your webhook URL: <code>{{ url('/api/whatsapp/webhook') }}</code></li>
                            <li>Subscribe to messages and status events</li>
                        </ol>
                    </div>

                    <form action="{{ route('whatsapp.settings.save') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone Number ID <span class="text-danger">*</span></label>
                                    <input type="text" name="phone_number_id" class="form-control"
                                           value="{{ old('phone_number_id', $settings['phone_number_id'] ?? '') }}" required>
                                    <small class="form-text text-muted">From Meta Developers > WhatsApp > API Setup</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Access Token <span class="text-danger">*</span></label>
                                    <input type="password" name="access_token" class="form-control"
                                           value="{{ old('access_token', $settings['access_token'] ?? '') }}" required>
                                    <small class="form-text text-muted">Temporary token from Meta (needs refresh)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Verify Token <span class="text-danger">*</span></label>
                                    <input type="text" name="verify_token" class="form-control"
                                           value="{{ old('verify_token', $settings['verify_token'] ?? 'whatsapp_verify_token') }}" required>
                                    <small class="form-text text-muted">Your custom token for webhook verification</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Business Account ID</label>
                                    <input type="text" name="business_account_id" class="form-control"
                                           value="{{ old('business_account_id', $settings['business_account_id'] ?? '') }}">
                                    <small class="form-text text-muted">From WhatsApp > API Setup > Business Account ID</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5>Frontend Settings</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Default WhatsApp Number <span class="text-danger">*</span></label>
                                    <input type="text" name="default_number" class="form-control"
                                           value="{{ old('default_number', $settings['default_number'] ?? '') }}" placeholder="+1234567890" required>
                                    <small class="form-text text-muted">Phone number with country code (for click-to-chat)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Default Message</label>
                                    <input type="text" name="default_message" class="form-control"
                                           value="{{ old('default_message', $settings['default_message'] ?? 'Hello, I need help!') }}">
                                    <small class="form-text text-muted">Pre-filled message when clicking WhatsApp button</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Floating Button Position</label>
                                    <select name="floating_position" class="form-select">
                                        <option value="bottom-right" {{ (old('floating_position', $settings['floating_position'] ?? '') == 'bottom-right') ? 'selected' : '' }}>Bottom Right</option>
                                        <option value="bottom-left" {{ (old('floating_position', $settings['floating_position'] ?? '') == 'bottom-left') ? 'selected' : '' }}>Bottom Left</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" name="enabled" class="form-check-input" id="enabled"
                                               value="1" {{ (old('enabled', $settings['enabled'] ?? '1') == '1' || old('enabled', $settings['enabled'] ?? '') == true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enabled">Enable WhatsApp Integration</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" name="show_floating_button" class="form-check-input" id="show_floating_button"
                                               value="1" {{ (old('show_floating_button', $settings['show_floating_button'] ?? '1') == '1' || old('show_floating_button', $settings['show_floating_button'] ?? '') == true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_floating_button">Show Floating Button</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                            <button type="button" id="testConnection" class="btn btn-primary">
                                <i class="fab fa-whatsapp"></i> Test Connection
                            </button>
                            <a href="{{ route('whatsapp.dashboard') }}" class="btn btn-info">
                                <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Webhook URL</h5>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ url('/api/whatsapp/webhook') }}" readonly id="webhookUrl">
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <small class="text-muted">Use this URL in your Meta app's webhook configuration</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyToClipboard() {
    var copyText = document.getElementById("webhookUrl");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    alert("Webhook URL copied to clipboard!");
}

document.getElementById('testConnection').addEventListener('click', function () {
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Testing...';

    fetch("{{ route('whatsapp.test.connection') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Connection successful! Phone Number ID: ' + (data.data?.phone_number_id || 'Connected'));
        } else {
            alert('Connection failed: ' + data.error);
        }
    })
    .catch(() => {
        alert('Connection test failed. Please try again.');
    })
    .finally(() => {
        this.disabled = false;
        this.innerHTML = '<i class="fab fa-whatsapp"></i> Test Connection';
    });
});
</script>
@endsection

