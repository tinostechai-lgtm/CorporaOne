<!-- Assign Users Modal -->
<div class="modal fade" id="assignUsersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Assign Users to Lead') }} - {{ $lead->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('leads.assign-users', $lead->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Currently Assigned Users') }}</label>
                        <div class="assigned-users-list">
                            @forelse($lead->assignedUsers as $assignedUser)
                                <div class="assigned-user-item d-flex justify-content-between align-items-center p-2 border rounded mb-2">
                                    <span>
                                        <i class="ti ti-user-check text-success"></i>
                                        {{ $assignedUser->name }}
                                        <small class="text-muted">({{ $assignedUser->email }})</small>
                                    </span>
                                    <a href="{{ route('leads.remove-user', [$lead->id, $assignedUser->id]) }}" 
                                       class="btn btn-sm btn-danger remove-user"
                                       data-user-id="{{ $assignedUser->id }}"
                                       onclick="return confirm('{{ __('Remove this user?') }}')">
                                        <i class="ti ti-trash"></i>
                                    </a>
                                </div>
                            @empty
                                <p class="text-muted">{{ __('No users assigned yet') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Add New Users') }}</label>
                        <select name="users[]" class="form-select" multiple size="5">
                            @foreach($users as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ __('Hold Ctrl to select multiple users') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Assign Users') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
$(document).ready(function() {
    $('.remove-user').click(function(e) {
        if(!confirm('{{ __("Are you sure you want to remove this user?") }}')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush