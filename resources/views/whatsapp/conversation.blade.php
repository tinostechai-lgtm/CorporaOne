@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <i class="fab fa-whatsapp text-success"></i> 
                        Conversation with {{ $conversation->phone_number }}
                    </h4>
                    <div>
                        <a href="{{ route('whatsapp.conversations') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <a href="https://wa.me/{{ $conversation->phone_number }}" target="_blank" class="btn btn-success">
                            <i class="fab fa-whatsapp"></i> Open in WhatsApp
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Customer Info -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5>Customer Information</h5>
                                    <form action="{{ route('whatsapp.conversation.update', $conversation->id) }}" method="POST" class="row g-3">
                                        @csrf
                                        <div class="col-md-4">
                                            <label class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" value="{{ $conversation->phone_number }}" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Customer Name</label>
                                            <input type="text" name="customer_name" class="form-control" value="{{ $conversation->customer_name ?? '' }}" placeholder="Enter name">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Customer Email</label>
                                            <input type="email" name="customer_email" class="form-control" value="{{ $conversation->customer_email ?? '' }}" placeholder="Enter email">
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">Update Info</button>
                                            @if($conversation->status == 'active')
                                            <button type="button" class="btn btn-secondary" onclick="closeConversation({{ $conversation->id }})">Close Conversation</button>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="chat-messages" style="max-height: 400px; overflow-y: auto;">
                        @forelse($conversation->messages as $message)
                        <div class="message mb-3 {{ $message->direction == 'inbound' ? 'text-start' : 'text-end' }}">
                            <div class="d-inline-block p-3 rounded {{ $message->direction == 'inbound' ? 'bg-light' : 'bg-primary text-white' }}" style="max-width: 70%;">
                                <p class="mb-1">{{ $message->body }}</p>
                                <small class="{{ $message->direction == 'inbound' ? 'text-muted' : 'text-light' }}">
                                    {{ $message->sent_at ? $message->sent_at->format('M d, h:i A') : '' }}
                                    @if($message->direction == 'outbound')
                                        @if($message->is_read)
                                            <i class="fas fa-check-double"></i>
                                        @elseif($message->is_delivered)
                                            <i class="fas fa-check"></i>
                                        @endif
                                    @endif
                                </small>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-muted">No messages yet</p>
                        @endforelse
                    </div>

                    <!-- Reply Form -->
                    @if($conversation->status == 'active')
                    <div class="mt-4">
                        <form action="{{ route('whatsapp.send.message') }}" method="POST" id="replyForm">
                            @csrf
                            <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
                            <div class="input-group">
                                <input type="text" name="message" class="form-control" placeholder="Type your reply..." required>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                            </div>
                        </form>
                    </div>
                    @else
                    <div class="alert alert-warning mt-4">
                        This conversation is closed. <a href="{{ route('whatsapp.conversation.reopen', $conversation->id) }}">Reopen</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Close conversation function
function closeConversation(id) {
    if (!confirm('Close this conversation?')) return;
    
    fetch('/whatsapp/conversation/' + id + '/close', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to close conversation');
        }
    })
    .catch(() => {
        alert('Error closing conversation');
    });
}

document.getElementById('replyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(new FormData(form))
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert('Failed to send: ' + data.error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send';
        }
    })
    .catch(() => {
        alert('Error sending message');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send';
    });
});

// Auto-scroll to bottom of messages
const messagesDiv = document.querySelector('.chat-messages');
messagesDiv.scrollTop = messagesDiv.scrollHeight;
</script>
@endsection

