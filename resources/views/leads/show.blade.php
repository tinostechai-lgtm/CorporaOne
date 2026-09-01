@extends('layouts.app')

@section('page-title', $lead->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">{{ __('Leads') }}</a></li>
    <li class="breadcrumb-item active">{{ $lead->name }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Lead Info Card -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Lead Information') }}</h5>
                <div class="card-header-actions">
                    @can('edit lead')
                    <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-sm btn-warning">
                        <i class="ti ti-edit"></i> {{ __('Edit') }}
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar avatar-xl">
                        <div class="avatar-initial bg-primary rounded-circle" style="font-size: 2rem; width: 80px; height: 80px; line-height: 80px; text-align: center; display: inline-block;">
                            {{ strtoupper(substr($lead->name, 0, 1)) }}
                        </div>
                    </div>
                </div>
                
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">{{ __('Name') }}:</th>
                        <td>{{ $lead->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Email') }}:</th>
                        <td>{{ $lead->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Phone') }}:</th>
                        <td>{{ $lead->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Subject') }}:</th>
                        <td>{{ $lead->subject }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Stage') }}:</th>
                        <td>
                            <div class="progress mb-2" style="height: 5px;">
                                <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span>{{ $lead->stage->name ?? '-' }} ({{ $percentage }}%)</span>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('Assigned To') }}:</th>
                        <td>
                            @foreach($lead->users as $user)
                                <span class="badge bg-info">{{ $user->name }}</span>
                            @endforeach
                            @if($lead->users->count() == 0)
                                <span class="badge bg-warning">{{ __('Unassigned') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('Lead Source') }}:</th>
                        <td>
                            @if($lead->lead_source)
                                <span class="badge bg-secondary">{{ ucfirst($lead->lead_source) }}</span>
                            @else
                                <span class="text-muted">{{ __('Manual') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('Created') }}:</th>
                        <td>{{ $lead->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>

                @if(!$lead->is_converted)
                <a href="{{ route('leads.convert', $lead->id) }}" class="btn btn-success w-100">
                    <i class="ti ti-arrows-right-left"></i> {{ __('Convert to Deal') }}
                </a>
                @else
                <a href="{{ route('deals.show', $deal->id) }}" class="btn btn-info w-100">
                    <i class="ti ti-arrow-right"></i> {{ __('View Deal') }}
                </a>
                @endif
            </div>
        </div>

        <!-- Labels Card -->
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Labels') }}</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#labelsModal">
                    <i class="ti ti-tag"></i> {{ __('Manage') }}
                </button>
            </div>
            <div class="card-body">
                @php $labelsList = $lead->labels(); @endphp
                @if($labelsList && count($labelsList) > 0)
                    @foreach($labelsList as $label)
                        <span class="badge" style="background: {{ $label->color ?? '#6c5ce7' }}; color: #fff; margin: 2px;">
                            {{ $label->name }}
                        </span>
                    @endforeach
                @else
                    <p class="text-muted">{{ __('No labels assigned') }}</p>
                @endif
            </div>
        </div>

        <!-- Sources Card -->
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Sources') }}</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sourcesModal">
                    <i class="ti ti-source"></i> {{ __('Manage') }}
                </button>
            </div>
            <div class="card-body">
                @php $sourcesList = $lead->sources(); @endphp
                @if($sourcesList && count($sourcesList) > 0)
                    @foreach($sourcesList as $source)
                        <span class="badge bg-secondary">{{ $source->name }}</span>
                    @endforeach
                @else
                    <p class="text-muted">{{ __('No sources selected') }}</p>
                @endif
            </div>
        </div>

        <!-- Products Card -->
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Products') }}</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#productsModal">
                    <i class="ti ti-package"></i> {{ __('Manage') }}
                </button>
            </div>
            <div class="card-body">
                @php $productsList = $lead->products(); @endphp
                @if($productsList && count($productsList) > 0)
                    @foreach($productsList as $product)
                        <span class="badge bg-info">{{ $product->name }}</span>
                    @endforeach
                @else
                    <p class="text-muted">{{ __('No products added') }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column - Tabs -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="leadTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#activity">
                            <i class="ti ti-activity"></i> {{ __('Activity') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#calls">
                            <i class="ti ti-phone"></i> {{ __('Calls') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#emails">
                            <i class="ti ti-mail"></i> {{ __('Emails') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#discussions">
                            <i class="ti ti-message"></i> {{ __('Discussions') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#files">
                            <i class="ti ti-file"></i> {{ __('Files') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#notes">
                            <i class="ti ti-notes"></i> {{ __('Notes') }}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Activity Tab -->
                    <div class="tab-pane fade show active" id="activity">
                        @include('leads.partials.activity-log')
                    </div>

                    <!-- Calls Tab -->
                    <div class="tab-pane fade" id="calls">
                        @include('leads.calls')
                    </div>

                    <!-- Emails Tab -->
                    <div class="tab-pane fade" id="emails">
                        @include('leads.emails')
                    </div>

                    <!-- Discussions Tab -->
                    <div class="tab-pane fade" id="discussions">
                        @include('leads.discussions')
                    </div>

                    <!-- Files Tab -->
                    <div class="tab-pane fade" id="files">
                        <form action="{{ route('leads.files.upload', $lead->id) }}" method="POST" enctype="multipart/form-data" id="fileUploadForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">{{ __('Upload File') }}</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                        </form>
                        <hr>
                        <div id="fileList">
                            @foreach($files as $file)
                            <div class="file-item d-flex justify-content-between align-items-center mb-2">
                                <span>
                                    <i class="ti ti-file"></i> {{ $file->file_name }}
                                    <small class="text-muted">{{ $file->created_at->diffForHumans() }}</small>
                                </span>
                                <div>
                                    <a href="{{ route('leads.file.download', [$lead->id, $file->id]) }}" class="btn btn-sm btn-success">
                                        <i class="ti ti-download"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-file" data-id="{{ $file->id }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Notes Tab -->
                    <div class="tab-pane fade" id="notes">
                        <form id="notesForm">
                            @csrf
                            <textarea name="notes" class="form-control" rows="10">{{ $lead->notes }}</textarea>
                            <button type="submit" class="btn btn-primary mt-3">{{ __('Save Notes') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('leads.labels')
@include('leads.sources')
@include('leads.products')
@include('leads.assign-users')
@include('leads.calls')
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Save notes
    $('#notesForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route("leads.notes.save", $lead->id) }}',
            type: 'POST',
            data: {
                notes: $('textarea[name=notes]').val(),
                _token: '{{ csrf_token() }}'
            },
           