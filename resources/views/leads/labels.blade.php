<!-- Labels Modal -->
<div class="modal fade" id="labelsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Manage Labels') }} - {{ $lead->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('leads.labels.update', $lead->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Select Labels') }}</label>
                        <div class="labels-list">
                            @foreach($labels as $label)
                                <div class="form-check mb-2">
                                    <input type="checkbox" 
                                           name="labels[]" 
                                           value="{{ $label->id }}" 
                                           id="label_{{ $label->id }}" 
                                           class="form-check-input"
                                           {{ isset($selected[$label->id]) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="label_{{ $label->id }}">
                                        <span class="badge" style="background: {{ $label->color ?? '#6c5ce7' }}; color: #fff;">
                                            {{ $label->name }}
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($labels->isEmpty())
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle"></i>
                            {{ __('No labels available. Please create labels first.') }}
                            <a href="{{ route('labels.index') }}" class="btn btn-sm btn-link">{{ __('Create Label') }}</a>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save Labels') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>