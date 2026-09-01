<div class="timeline">
    @foreach($activities as $activity)
    <div class="timeline-item mb-3">
        <div class="d-flex">
            <div class="timeline-icon me-3">
                @if($activity->log_type == 'Create Lead')
                    <i class="ti ti-plus-circle text-success"></i>
                @elseif($activity->log_type == 'Move Lead')
                    <i class="ti ti-arrows-right-left text-warning"></i>
                @elseif($activity->log_type == 'Add Product')
                    <i class="ti ti-package text-info"></i>
                @elseif($activity->log_type == 'Upload File')
                    <i class="ti ti-file text-primary"></i>
                @elseif($activity->log_type == 'Send Email')
                    <i class="ti ti-mail text-primary"></i>
                @elseif($activity->log_type == 'Create Lead Call')
                    <i class="ti ti-phone text-success"></i>
                @else
                    <i class="ti ti-activity"></i>
                @endif
            </div>
            <div class="timeline-content flex-grow-1">
                <div class="d-flex justify-content-between">
                    <strong>{{ $activity->user->name ?? 'System' }}</strong>
                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                </div>
                <div class="mt-1">
                    @php 
                        $remark = is_string($activity->remark) ? json_decode($activity->remark, true) : $activity->remark; 
                    @endphp
                    @if($activity->log_type == 'Move Lead')
                        {{ __('Moved lead from') }} <strong>{{ $remark['old_status'] ?? '' }}</strong> 
                        {{ __('to') }} <strong>{{ $remark['new_status'] ?? '' }}</strong>
                    @elseif($activity->log_type == 'Upload File')
                        {{ __('Uploaded file:') }} <strong>{{ $remark['file_name'] ?? '' }}</strong>
                    @elseif($activity->log_type == 'Add Product')
                        {{ __('Added products:') }} <strong>{{ $remark['title'] ?? '' }}</strong>
                    @else
                        {{ __($activity->log_type) }}
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
    
    @if($activities->isEmpty())
    <p class="text-center text-muted">{{ __('No activities recorded yet') }}</p>
    @endif
</div>

@push('css')
<style>
.timeline-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 50%;
}
.timeline-content {
    flex: 1;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}
</style>
@endpush