@extends('layouts.admin')

@section('page-title', __('Bank Statements'))

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6>{{ __('Uploaded Bank Statements') }}</h6>
                    <a href="{{ route('bank-statement.create') }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-upload"></i> {{ __('Upload New Statement') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('File Name') }}</th>
                                <th>{{ __('Bank Name') }}</th>
                                <th>{{ __('Account No.') }}</th>
                                <th>{{ __('Transactions') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Uploaded') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $submission)
                                <tr>
                                    <td>{{ $submission->id }}</td>
                                    <td>{{ Str::limit($submission->original_file_name, 40) }}</td>
                                    <td>{{ $submission->bank_name ?? 'N/A' }}</td>
                                    <td>{{ $submission->account_number ?? 'N/A' }}</td>
                                    <td><span class="badge bg-info">{{ count($submission->transactions ?? []) }}</span></td>
                                    <td>
                                        @if($submission->status == 'completed')
                                            <span class="badge bg-success">{{ __('Completed') }}</span>
                                        @elseif($submission->status == 'processing')
                                            <span class="badge bg-warning">{{ __('Processing') }}</span>
                                        @elseif($submission->status == 'failed')
                                            <span class="badge bg-danger">{{ __('Failed') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Pending') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $submission->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('bank-statement.show', $submission->id) }}" class="btn btn-sm btn-info">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <a href="{{ route('bank-statement.compare', $submission->id) }}" class="btn btn-sm btn-primary">
                                            <i class="ti ti-balance-scale"></i>
                                        </a>
                                        <a href="{{ route('bank-statement.download', $submission->id) }}" class="btn btn-sm btn-secondary">
                                            <i class="ti ti-download"></i>
                                        </a>
                                        <form action="{{ route('bank-statement.destroy', $submission->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure?') }}')">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        {{ __('No bank statements uploaded yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection