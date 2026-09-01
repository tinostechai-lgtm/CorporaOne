@extends('layouts.app')

@section('page-title', __('Edit Lead'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">{{ __('Leads') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('leads.show', $lead->id) }}">{{ $lead->name }}</a></li>
    <li class="breadcrumb-item active">{{ __('Edit') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Edit Lead') }}</h5>
                <small class="text-muted">{{ __('Update lead information') }}</small>
            </div>
            <div class="card-body">
                <form action="{{ route('leads.update', $lead->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $lead->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $lead->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Phone') }}</label>
                                <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $lead->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Subject') }} <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" 
                                       value="{{ old('subject', $lead->subject) }}" required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Pipeline & Stage -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Pipeline') }} <span class="text-danger">*</span></label>
                                <select name="pipeline_id" class="form-select @error('pipeline_id') is-invalid @enderror" id="pipelineSelect" required>
                                    <option value="">{{ __('Select Pipeline') }}</option>
                                    @foreach($pipelines as $id => $name)
                                        <option value="{{ $id }}" {{ $lead->pipeline_id == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pipeline_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Stage') }} <span class="text-danger">*</span></label>
                                <select name="stage_id" class="form-select @error('stage_id') is-invalid @enderror" id="stageSelect" required>
                                    <option value="">{{ __('Select Stage') }}</option>
                                    @foreach($stages as $id => $name)
                                        <option value="{{ $id }}" {{ $lead->stage_id == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('stage_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Assignment -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Assigned To') }}</label>
                                <select name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                    <option value="">{{ __('Select User') }}</option>
                                    @foreach($users as $id => $name)
                                        <option value="{{ $id }}" {{ $lead->user_id == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-select">
                                    <option value="active" {{ $lead->is_active ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="inactive" {{ !$lead->is_active ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Sources -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Sources') }}</label>
                                <select name="sources[]" class="form-select" multiple size="4">
                                    @foreach($sources as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, $lead->sources) ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">{{ __('Hold Ctrl to select multiple') }}</small>
                            </div>
                        </div>
                        
                        <!-- Products -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Products') }}</label>
                                <select name="products[]" class="form-select" multiple size="4">
                                    @foreach($products as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, $lead->products) ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">{{ __('Hold Ctrl to select multiple') }}</small>
                            </div>
                        </div>
                        
                        <!-- Notes -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Notes') }}</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="5">{{ old('notes', $lead->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left"></i> {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-save"></i> {{ __('Update Lead') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Danger Zone -->
        @can('delete lead')
        <div class="card border-danger mt-3">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0">{{ __('Danger Zone') }}</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ __('Delete Lead') }}</strong>
                        <p class="text-muted mb-0">{{ __('Once deleted, all data associated with this lead will be permanently removed.') }}</p>
                    </div>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteLeadModal">
                        <i class="ti ti-trash"></i> {{ __('Delete Lead') }}
                    </button>
                </div>
            </div>
        </div>
        @endcan
    </div>
</div>

<!-- Delete Confirmation Modal -->
@can('delete lead')
<div class="modal fade" id="deleteLeadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Lead') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="ti ti-alert-triangle"></i>
                    <strong>{{ __('Warning!') }}</strong>
                    <p class="mb-0">{{ __('Are you sure you want to delete this lead? This action cannot be undone.') }}</p>
                </div>
                <p>{{ __('This will also delete:') }}</p>
                <ul>
                    <li>{{ __('All discussions/comments') }}</li>
                    <li>{{ __('All uploaded files') }}</li>
                    <li>{{ __('Call history') }}</li>
                    <li>{{ __('Email history') }}</li>
                    <li>{{ __('Activity logs') }}</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('Confirm Delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan

@push('js')
<script>
$(document).ready(function() {
    // Pipeline change - update stages
    $('#pipelineSelect').change(function() {
        var pipelineId = $(this).val();
        if(pipelineId) {
            $.ajax({
                url: '{{ route("leads.stages.json") }}',
                data: { pipeline_id: pipelineId },
                success: function(data) {
                    $('#stageSelect').empty();
                    $('#stageSelect').append('<option value="">{{ __("Select Stage") }}</option>');
                    $.each(data, function(id, name) {
                        $('#stageSelect').append('<option value="' + id + '">' + name + '</option>');
                    });
                }
            });
        }
    });
});
</script>
@endpush