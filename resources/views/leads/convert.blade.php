@extends('layouts.app')

@section('page-title', __('Convert Lead to Deal'))

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Convert Lead to Deal') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('leads.convert.store', $lead->id) }}" method="POST">
                    @csrf
                    
                    <!-- Lead Info -->
                    <div class="alert alert-info">
                        <strong>{{ __('Lead:') }}</strong> {{ $lead->name }}<br>
                        <strong>{{ __('Email:') }}</strong> {{ $lead->email ?? 'N/A' }}
                    </div>

                    <!-- Deal Name -->
                    <div class="mb-3">
                        <label class="form-label">{{ __('Deal Name') }} *</label>
                        <input type="text" name="name" class="form-control" value="{{ $lead->name }}" required>
                    </div>

                    <!-- Deal Price -->
                    <div class="mb-3">
                        <label class="form-label">{{ __('Deal Value') }}</label>
                        <input type="number" name="price" class="form-control" step="0.01" value="0">
                    </div>

                    <!-- Client Selection -->
                    <div class="mb-3">
                        <label class="form-label">{{ __('Client') }}</label>
                        <select name="clients" class="form-select" id="clientSelect">
                            <option value="">{{ __('-- Select Existing Client --') }}</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Or Create New -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="create_new_client" value="1" id="createNewClient" class="form-check-input">
                            <label class="form-check-label" for="createNewClient">
                                {{ __('Create new client') }}
                            </label>
                        </div>
                    </div>

                    <div id="newClientFields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Client Name') }}</label>
                                    <input type="text" name="client_name" class="form-control" value="{{ $lead->name }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Client Email') }}</label>
                                    <input type="email" name="client_email" class="form-control" value="{{ $lead->email }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Password') }}</label>
                                    <input type="password" name="client_password" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transfer Options -->
                    <div class="mb-3">
                        <label class="form-label">{{ __('Transfer to Deal') }}</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" name="is_transfer[]" value="sources" class="form-check-input" checked>
                                    <label class="form-check-label">{{ __('Sources') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" name="is_transfer[]" value="products" class="form-check-input" checked>
                                    <label class="form-check-label">{{ __('Products') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" name="is_transfer[]" value="notes" class="form-check-input" checked>
                                    <label class="form-check-label">{{ __('Notes') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" name="is_transfer[]" value="discussion" class="form-check-input" checked>
                                    <label class="form-check-label">{{ __('Discussions') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" name="is_transfer[]" value="files" class="form-check-input" checked>
                                    <label class="form-check-label">{{ __('Files') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" name="is_transfer[]" value="calls" class="form-check-input" checked>
                                    <label class="form-check-label">{{ __('Calls') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success">Convert to Deal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$('#createNewClient').change(function() {
    if($(this).is(':checked')) {
        $('#newClientFields').show();
        $('#clientSelect').prop('disabled', true);
    } else {
        $('#newClientFields').hide();
        $('#clientSelect').prop('disabled', false);
    }
});
</script>
@endpush