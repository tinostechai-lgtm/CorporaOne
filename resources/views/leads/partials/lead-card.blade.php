<div class="lead-card" data-lead-id="{{ $lead->id }}" data-stage-id="{{ $lead->stage_id }}">
    <div class="lead-title">
        <a href="{{ route('leads.show', $lead->id) }}" class="text-dark">
            {{ $lead->name }}
        </a>
    </div>
    <div class="lead-email">
        <i class="ti ti-mail"></i> {{ $lead->email ?? 'No email' }}
    </div>
    @if($lead->phone)
    <div class="lead-email">
        <i class="ti ti-phone"></i> {{ $lead->phone }}
    </div>
    @endif
    <div class="lead-meta">
        <span class="badge-assigned badge">
            <i class="ti ti-user"></i> 
            {{ $lead->users->first()->name ?? 'Unassigned' }}
        </span>
        <small>{{ $lead->created_at->diffForHumans() }}</small>
    </div>
    
    <!-- FIXED: Call labels() as a method -->
    @php $leadLabels = $lead->labels(); @endphp
    @if($leadLabels && count($leadLabels) > 0)
    <div class="mt-1">
        @foreach($leadLabels as $label)
            <span class="badge" style="background: {{ $label->color ?? '#6c5ce7' }}; color: #fff; font-size: 0.6rem;">
                {{ $label->name }}
            </span>
        @endforeach
    </div>
    @endif
</div>