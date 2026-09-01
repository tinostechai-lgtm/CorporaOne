@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <i class="fab fa-whatsapp text-success"></i> WhatsApp Conversations
                    </h4>
                    <a href="{{ route('whatsapp.dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Phone Number</th>
                                    <th>Customer Name</th>
                                    <th>Customer Email</th>
                                    <th>Status</th>
                                    <th>Last Message</th>
                                    <th>Last Activity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($conversations as $conversation)
                                <tr>
                                    <td>
                                        <a href="https://wa.me/{{ $conversation->phone_number }}" target="_blank">
                                            {{ $conversation->phone_number }}
                                        </a>
                                    </td>
                                    <td>{{ $conversation->customer_name ?? '-' }}</td>
                                    <td>{{ $conversation->customer_email ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $conversation->status == 'active' ? 'success' : ($conversation->status == 'closed' ? 'secondary' : 'warning') }}">
                                            {{ ucfirst($conversation->status) }}
                                        </span>
                                    </td>
                                    <td>{{ Str::limit($conversation->latestMessage->body ?? 'No messages', 30) }}</td>
                                    <td>{{ $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : '-' }}</td>
                                    <td>
                                        <a href="{{ route('whatsapp.conversation.show', $conversation->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        @if($conversation->status == 'active')
                                        <form action="{{ route('whatsapp.conversation.close', $conversation->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Are you sure you want to close this conversation?')">
                                                <i class="fas fa-times"></i> Close
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No conversations yet. Share your WhatsApp link to start receiving messages!</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $conversations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

