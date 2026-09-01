<form action="{{ route('leads.emails.store', $lead->id) }}" method="POST">
    @csrf
    <div class="mb-3">
        <label class="form-label">{{ __('To') }} <span class="text-danger">*</span></label>
        <input type="email" name="to" class="form-control" value="{{ $lead->email }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ __('Subject') }} <span class="text-danger">*</span></label>
        <input type="text" name="subject" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ __('Message') }} <span class="text-danger">*</span></label>
        <textarea name="description" class="form-control" rows="5" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">{{ __('Send Email') }}</button>
</form>

<hr>

<div class="email-list">
    <h6>{{ __('Email History') }}</h6>
    @foreach($emails as $email)
    <div class="email-item mb-3 p-3 border rounded">
        <div class="d-flex justify-content-between">
            <strong>{{ __('To:') }} {{ $email->to }}</strong>
            <small class="text-muted">{{ $email->created_at->format('M d, Y H:i') }}</small>
        </div>
        <div class="mt-2">
            <strong>{{ __('Subject:') }}</strong> {{ $email->subject }}
        </div>
        <p class="mt-2 mb-0">{{ \Str::limit($email->description, 200) }}</p>
    </div>
    @endforeach
    
    @if($emails->isEmpty())
    <p class="text-center text-muted">{{ __('No emails sent yet') }}</p>
    @endif
</div>