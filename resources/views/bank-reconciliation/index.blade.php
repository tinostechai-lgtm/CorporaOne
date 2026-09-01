@extends('layouts.admin')

@section('page-title')
    {{ __('Bank Reconciliation') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Bank Reconciliation') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h6>{{ __('Ledger Accounts - Click to View Ledger Summary') }}</h6>
            </div>
            <div class="card-body">
                @if(isset($error))
                    <div class="alert alert-danger">{{ $error }}</div>
                @endif
                
                @if($accounts->isEmpty())
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle"></i> No accounts found. Please create chart of accounts first.
                    </div>
                @endif
                
                {{-- Account Cards Grid --}}
                <div class="row">
                    @foreach($accounts as $account)
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('bank-reconciliation.ledger-report', ['accountId' => $account['id']]) }}" 
                           class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 position-relative hover-shadow transition-all" 
                                 style="cursor: pointer; transition: transform 0.2s;"
                                 onmouseover="this.style.transform='translateY(-5px)'"
                                 onmouseout="this.style.transform='translateY(0)'">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                            <i class="ti ti-building-bank fs-4 text-primary"></i>
                                        </div>
                                        <span class="badge bg-secondary">{{ $account['type'] ?? 'Account' }}</span>
                                    </div>
                                    @if($account['code'])
                                        <small class="text-muted d-block">{{ $account['code'] }}</small>
                                    @endif
                                    <h6 class="card-title mb-2 text-truncate">{{ $account['name'] }}</h6>
                                    <div class="mt-2">
                                        <small class="text-success">
                                            <i class="ti ti-chart-line"></i> Click to view ledger
                                        </small>
                                    </div>
                                    <div class="mt-2 pt-2 border-top">
                                        <small class="text-muted">Balance: </small>
                                        <strong class="{{ $account['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($account['balance'], 2) }}
                                        </strong>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0 pt-0">
                                    <div class="text-primary small">
                                        <i class="ti ti-eye"></i> View Transactions →
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('Recent Bank Statements') }}</h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#quickUploadModal">
                            <i class="ti ti-upload"></i> {{ __('Upload Statement') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Bank') }}</th>
                                <th>{{ __('Account') }}</th>
                                <th>{{ __('Txns') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions->take(10) as $submission)
                                <tr>
                                    <td>{{ $submission->id }}</td>
                                    <td>{{ $submission->bank_name ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($submission->account_number ?? 'N/A', 15) }}</td>
{{ count(json_decode($submission->transactions ?? '[]', true) ?? []) }}
                                    <td>
                                        @if($submission->reconciliation_status == 'completed')
                                            <span class="badge bg-success">{{ __('Complete') }}</span>
                                        @elseif($submission->reconciliation_status == 'partial')
                                            <span class="badge bg-warning">{{ __('Partial') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Pending') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('bank-reconciliation.compare', ['ledger_id' => 1, 'submission_id' => $submission->id]) }}" class="btn btn-sm btn-info">
                                            <i class="ti ti-balance-scale"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted p-4">
                                        <i class="ti ti-file-upload fs-1 d-block mb-2 opacity-50"></i>
                                        <div>{{ __('No statements yet. Upload one to start reconciliation.') }}</div>
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

{{-- Quick Upload Modal --}}
<div class="modal fade" id="quickUploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Quick Upload Bank Statement') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('bank-statement.store') }}" method="POST" enctype="multipart/form-data" class="modal-body">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('Bank Statement File') }}</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.png,.jpg,.jpeg" required>
                    <small class="text-muted">{{ __('Supported: PDF/JPG/PNG. Max 10MB') }}</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Bank Name') }}</label>
                    <input type="text" name="bank_name" class="form-control" placeholder="e.g., Chase Bank">
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">{{ __('Upload & Process') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.transition-all {
    transition: all 0.3s ease;
}
.card {
    overflow: hidden;
}
a.text-decoration-none:hover {
    text-decoration: none;
}
</style>
@endsection