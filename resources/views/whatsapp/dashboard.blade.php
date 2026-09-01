@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="fab fa-whatsapp text-success"></i> WhatsApp Dashboard
            </h2>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #87CEEB 0%, #87CEEB 100%);">
                <div class="card-body text-dark">
                    <h5><i class="fab fa-whatsapp"></i> Total Conversations</h5>
                    <h2>{{ $stats['total_conversations'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #87CEEB 0%, #87CEEB 100%);">
                <div class="card-body text-dark">
                    <h5><i class="fab fa-whatsapp"></i> Active Conversations</h5>
                    <h2>{{ $stats['active_conversations'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #87CEEB 0%, #87CEEB 100%);">
                <div class="card-body text-dark">
                    <h5><i class="fas fa-envelope"></i> Total Messages</h5>
                    <h2>{{ $stats['total_messages'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #87CEEB 0%, #87CEEB 100%);">
                <div class="card-body text-dark">
                    <h5><i class="fas fa-envelope-open-text"></i> Unread Messages</h5>
                    <h2>{{ $stats['unread_messages'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('whatsapp.settings') }}" class="btn btn-success">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="{{ route('whatsapp.conversations') }}" class="btn btn-primary">
                <i class="fas fa-comments"></i> All Conversations
            </a>
        </div>
    </div>

    <!-- Recent Conversations -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Conversations</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Phone Number</th>
                                    <th>Customer Name</th>
                                    <th>Last Message</th>
                                    <th>Status</th>
                                    <th>Last Activity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentConversations as $conversation)
                                <tr>
                                    <td>{{ $conversation->phone_number }}</td>
                                    <td>{{ $conversation->customer_name ?? '-' }}</td>
                                    <td>{{ $conversation->latestMessage->body ?? 'No messages' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $conversation->status == 'active' ? 'success' : ($conversation->status == 'closed' ? 'secondary' : 'warning') }}">
                                            {{ ucfirst($conversation->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : '-' }}</td>
                                    <td>
                                        <a href="{{ route('whatsapp.conversation.show', $conversation->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No conversations yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

