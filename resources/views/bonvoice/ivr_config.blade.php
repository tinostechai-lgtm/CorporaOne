@extends('layouts.admin')

@section('page-title')
    {{ __('Bonvoice IVR Configuration') }}
@endsection

@section('title')
    {{ __('Bonvoice IVR Configuration') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Bonvoice IVR Configuration') }}</li>
@endsection

@section('content')
    <?php $settings = Utility::settings(); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('IVR Configuration') }}</h5>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => 'bonvoice.ivr_config.save', 'method' => 'post', 'id' => 'ivrForm']) }}
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('bonvoice_ivr_enabled', __('Enable IVR'), ['class' => 'form-label']) }}
                                <div class="form-check form-switch">
                                    {{ Form::checkbox('bonvoice_ivr_enabled', 'on', ($settings['bonvoice_ivr_enabled'] ?? '') == 'on', ['class' => 'form-check-input', 'id' => 'bonvoice_ivr_enabled']) }}
                                    <label class="form-check-label" for="bonvoice_ivr_enabled">{{ __('Enable IVR System') }}</label>
                                </div>
                                <small class="text-muted">{{ __('Enable IVR for automated call routing and menu options') }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('bonvoice_ivr_flow_id', __('IVR Flow ID'), ['class' => 'form-label']) }}
                                {{ Form::text('bonvoice_ivr_flow_id', $settings['bonvoice_ivr_flow_id'] ?? '', ['class' => 'form-control', 'placeholder' => __('Enter IVR Flow ID')]) }}
                                <small class="text-muted">{{ __('The flow ID from Bonvoice for IVR routing') }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::label('bonvoice_default_greeting', __('Default Greeting'), ['class' => 'form-label']) }}
                                {{ Form::textarea('bonvoice_default_greeting', $settings['bonvoice_default_greeting'] ?? '', ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter greeting message')]) }}
                                <small class="text-muted">{{ __('Welcome message played when caller reaches IVR') }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('bonvoice_timeout', __('Timeout (seconds)'), ['class' => 'form-label']) }}
                                {{ Form::number('bonvoice_timeout', $settings['bonvoice_timeout'] ?? 30, ['class' => 'form-control', 'min' => 5, 'max' => 120]) }}
                                <small class="text-muted">{{ __('Seconds to wait for user input before repeating') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('bonvoice_max_attempts', __('Max Attempts'), ['class' => 'form-label']) }}
                                {{ Form::number('bonvoice_max_attempts', $settings['bonvoice_max_attempts'] ?? 3, ['class' => 'form-control', 'min' => 1, 'max' => 5]) }}
                                <small class="text-muted">{{ __('Number of attempts before disconnecting') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('bonvoice_invalid_option', __('Invalid Option Message'), ['class' => 'form-label']) }}
                                {{ Form::text('bonvoice_invalid_option', $settings['bonvoice_invalid_option'] ?? __('Invalid option. Please try again.'), ['class' => 'form-control', 'placeholder' => __('Invalid option message')]) }}
                                <small class="text-muted">{{ __('Message played when user selects invalid option') }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::label('bonvoice_webhook_url', __('Webhook URL'), ['class' => 'form-label']) }}
                                {{ Form::text('bonvoice_webhook_url', $settings['bonvoice_webhook_url'] ?? '', ['class' => 'form-control', 'placeholder' => __('Enter webhook URL for call events')]) }}
                                <small class="text-muted">{{ __('URL where Bonvoice will send call event notifications') }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">{{ __('IVR Menu Options') }}</label>
                                <div id="ivrOptions">
                                    <?php
                                    $ivrOptions = isset($settings['bonvoice_ivr_options']) ? json_decode($settings['bonvoice_ivr_options'], true) : [];
                                    ?>
                                    @foreach($ivrOptions as $index => $option)
                                        <div class="row mb-2 ivr-option-row" data-index="{{ $index }}">
                                            <div class="col-md-2">
                                                <input type="text" name="ivr_options[{{ $index }}][digit]" class="form-control" placeholder="{{ __('Digit') }}" value="{{ $option['digit'] ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" name="ivr_options[{{ $index }}][action]" class="form-control" placeholder="{{ __('Action (e.g., Transfer, Voicemail, Repeat)') }}" value="{{ $option['action'] ?? '' }}">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" name="ivr_options[{{ $index }}][target]" class="form-control" placeholder="{{ __('Target (Extension or URL)') }}" value="{{ $option['target'] ?? '' }}">
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-danger remove-option"><i class="ti ti-trash"></i></button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm btn-primary mt-2" id="addIvrOption">
                                    <i class="ti ti-plus"></i> {{ __('Add Option') }}
                                </button>
                                <small class="text-muted d-block mt-2">{{ __('Define IVR menu options (e.g., 1 for Sales, 2 for Support)') }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy"></i> {{ __('Save Configuration') }}
                        </button>
                        <button type="button" class="btn btn-secondary" id="testIvr">
                            <i class="ti ti-headphone"></i> {{ __('Test IVR') }}
                        </button>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            var optionIndex = {{ count($ivrOptions ?? []) }};
            
            // Add IVR Option
            $('#addIvrOption').on('click', function() {
                var row = `
                    <div class="row mb-2 ivr-option-row" data-index="${optionIndex}">
                        <div class="col-md-2">
                            <input type="text" name="ivr_options[${optionIndex}][digit]" class="form-control" placeholder="{{ __('Digit') }}">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="ivr_options[${optionIndex}][action]" class="form-control" placeholder="{{ __('Action (e.g., Transfer, Voicemail, Repeat)') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="ivr_options[${optionIndex}][target]" class="form-control" placeholder="{{ __('Target (Extension or URL)') }}">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger remove-option"><i class="ti ti-trash"></i></button>
                        </div>
                    </div>
                `;
                $('#ivrOptions').append(row);
                optionIndex++;
            });
            
            // Remove IVR Option
            $(document).on('click', '.remove-option', function() {
                $(this).closest('.ivr-option-row').remove();
            });
            
            // Test IVR
            $('#testIvr').on('click', function() {
                var $btn = $(this);
                var originalText = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>{{ __("Testing...") }}');
                
                // Gather form data
                var formData = $('#ivrForm').serialize();
                
                $.ajax({
                    url: '{{ route("bonvoice.test.ivr") }}',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('IVR test successful!\n' + (response.message || 'Configuration is valid.'));
                        } else {
                            alert('IVR test failed: ' + (response.message || 'Unknown error'));
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'IVR test failed';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        alert(errorMsg);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
@endpush