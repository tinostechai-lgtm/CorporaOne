<!-- Sources Modal -->
<div class="modal fade" id="sourcesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Manage Sources') }} - {{ $lead->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('leads.sources.update', $lead->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Select Sources') }}</label>
                        <select name="sources[]" class="form-select" multiple size="5">
                            @foreach($sources as $source)
                                <option value="{{ $source->id }}" {{ isset($selected[$source->id]) ? 'selected' : '' }}>
                                    {{ $source->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ __('Hold Ctrl to select multiple sources') }}</small>
                    </div>
                    
                    @if($sources->isEmpty())
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle"></i>
                            {{ __('No sources available. Please create sources first.') }}
                            <a href="{{ route('sources.index') }}" class="btn btn-sm btn-link">{{ __('Create Source') }}</a>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save Sources') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create New Source Form (Optional) -->
<div class="card mt-3">
    <div class="card-header">
        <h6>{{ __('Quick Add Source') }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('sources.store') }}" method="POST" class="row">
            @csrf
            <div class="col-md-8">
                <input type="text" name="name" class="form-control" placeholder="{{ __('Enter source name') }}" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">{{ __('Add Source') }}</button>
            </div>
        </form>
    </div>
</div>