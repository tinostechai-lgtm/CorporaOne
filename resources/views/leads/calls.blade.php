<div>
    <button class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#addCallModal">
        <i class="ti ti-phone-plus"></i> {{ __('Add Call') }}
    </button>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Subject') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Duration') }}</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Result') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($calls as $call)
                <tr>
                    <td>{{ $call->created_at->format('M d, H:i') }}</td>
                    <td>{{ $call->subject }}</td>
                    <td>
                        @if($call->call_type == 'incoming')
                            <span class="badge bg-info">{{ __('Incoming') }}</span>
                        @else
                            <span class="badge bg-primary">{{ __('Outgoing') }}</span>
                        @endif
                    </td>
                    <td>{{ $call->duration ? $call->duration . ' min' : '-' }}</td>
                    <td>{{ $call->user->name ?? '-' }}</td>
                    <td>
                        @if($call->call_result == 'completed')
                            <span class="badge bg-success">{{ __('Completed') }}</span>
                        @elseif($call->call_result == 'missed')
                            <span class="badge bg-danger">{{ __('Missed') }}</span>
                        @else
                            <span class="badge bg-secondary">{{ $call->call_result ?? '-' }}</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm btn-light" onclick="editCall({{ $call->id }})">
                            <i class="ti ti-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCall({{ $call->id }})">
                            <i class="ti ti-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">
                        <p class="text-muted">{{ __('No calls recorded.') }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function editCall(callId) {
    // Implement edit call logic
    console.log('Edit call:', callId);
}

function deleteCall(callId) {
    if(confirm('{{ __("Are you sure?") }}')) {
        $.ajax({
            url: '{{ route("leads.calls.destroy", [$lead->id, ""]) }}/' + callId,
            type: 'DELETE',
            data: {_token: '{{ csrf_token() }}'},
            success: function() {
                location.reload();
            }
        });
    }
}
</script>