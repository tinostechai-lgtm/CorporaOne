<form action="{{ route('leads.discussions.store', $lead->id) }}" method="POST">
    @csrf
    <div class="mb-3">
        <textarea name="comment" class="form-control" rows="3" placeholder="{{ __('Add a comment...') }}" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">{{ __('Post Comment') }}</button>
</form>

<hr>

<div class="discussion-list mt-3">
    @foreach($discussions as $discussion)
    <div class="discussion-item mb-3 p-3 border rounded">
        <div class="d-flex justify-content-between">
            <strong>{{ $discussion->creator->name ?? 'System' }}</strong>
            <small class="text-muted">{{ $discussion->created_at->diffForHumans() }}</small>
        </div>
        <p class="mt-2 mb-0">{{ $discussion->comment }}</p>
    </div>
    @endforeach
    
    @if($discussions->isEmpty())
    <p class="text-center text-muted">{{ __('No discussions yet') }}</p>
    @endif
</div>