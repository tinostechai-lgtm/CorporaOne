@extends('layouts.admin')

@section('page-title')
    {{ __('Account Transactions') }} - {{ $accountName }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('report.account.statement') }}">{{ __('Account Statement') }}</a></li>
    <li class="breadcrumb-item active">{{ $accountName }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5>{{ __('Transactions for Account: ') }} 
                        <span class="badge bg-primary">{{ $accountName }}</span>
                    </h5>
                    <small>Period: {{ \Auth::user()->dateFormat($startDate) }} to {{ \Auth::user()->dateFormat($endDate) }}</small>
                </div>
                <div>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#uploadStatementModal">
                        <i class="ti ti-upload"></i> {{ __('Upload Bank Statement') }}
                    </button>
                    <button onclick="window.print()" class="btn btn-sm btn-secondary">
                        <i class="ti ti-printer"></i> Print
                    </button>
                    <a href="{{ route('report.account.statement') }}" class="btn btn-sm btn-info">
                        <i class="ti ti-arrow-left"></i> Back to Statement
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Date Range Filter -->
                <form method="GET" action="{{ route('report.account.transactions', $accountId) }}" class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Start Date') }}</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('End Date') }}</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-filter"></i> Filter by Date
                        </button>
                    </div>
                </form>

                <!-- Comparison Summary (if available) -->
                @if(isset($comparison) && $comparison)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="ti ti-chart-bar"></i>
                                    <strong>Bank Statement Comparison Results:</strong>
                                    {{ $comparison->matched_count }} matched out of {{ $comparison->total_bank_txs }} bank transactions
                                    ({{ $comparison->match_accuracy }}% match rate)
                                </div>
                                <a href="{{ route('bank-reconciliation.compare', ['ledger_id' => $accountId, 'submission_id' => $comparison->submission_id]) }}" 
                                   class="btn btn-sm btn-primary" target="_blank">
                                    <i class="ti ti-eye"></i> View Full Comparison
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h3>{{ \Auth::user()->priceFormat($totalDebit) }}</h3>
                                <small>Total Debit</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3>{{ \Auth::user()->priceFormat($totalCredit) }}</h3>
                                <small>Total Credit</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3>{{ \Auth::user()->priceFormat($balance) }}</h3>
                                <small>Net Balance</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reconcile Button Row -->
                <div class="row mb-3">
                    <div class="col-12 text-end">
                        <a href="{{ route('bank-reconciliation.compare-with-ledger', ['ledger_id' => $accountId, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
                           class="btn btn-primary" target="_blank">
                            <i class="ti ti-balance-scale"></i> Reconcile with Bank Statement
                        </a>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="transactionsTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Date</th>
                                <th width="35%">Description</th>
                                <th width="15%">Reference</th>
                                <th width="15%" class="text-end">Debit</th>
                                <th width="15%" class="text-end">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $index => $transaction)
                            <tr>
                                <td>{{ $transactions->firstItem() + $index }}</td>
                                <td>{{ \Auth::user()->dateFormat($transaction->date) }}</td>
                                <td>
                                    <strong>{{ $transaction->description ?? $transaction->reference ?? 'Transaction' }}</strong>
                                    @if($transaction->reference)
                                        <br><small class="text-muted">Ref: {{ $transaction->reference }}</small>
                                    @endif
                                </td>
                                <td>{{ $transaction->reference ?? '-' }}</td>
                                <td class="text-end text-danger">
                                    {{ $transaction->debit > 0 ? \Auth::user()->priceFormat($transaction->debit) : '-' }}
                                </td>
                                <td class="text-end text-success">
                                    {{ $transaction->credit > 0 ? \Auth::user()->priceFormat($transaction->credit) : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="ti ti-inbox" style="font-size: 48px;"></i>
                                    <p class="mt-2">No transactions found for this account in the selected period</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="4" class="text-end fw-bold">Totals:</th>
                                <th class="text-end text-danger fw-bold">{{ \Auth::user()->priceFormat($totalDebit) }}</th>
                                <th class="text-end text-success fw-bold">{{ \Auth::user()->priceFormat($totalCredit) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="row mt-4">
                    <div class="col-12">
                        {{ $transactions->appends(request()->query())->links() }}
                    </div>
                </div>

                <!-- Quick Navigation -->
                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <a href="{{ route('report.account.statement') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left"></i> Back to Account Statement
                        </a>
                        <a href="{{ route('bank-reconciliation.compare-with-ledger', ['ledger_id' => $accountId, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
                           class="btn btn-primary" target="_blank">
                            <i class="ti ti-balance-scale"></i> Reconcile This Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Upload Bank Statement Modal --}}
<div class="modal fade" id="uploadStatementModal" tabindex="-1" aria-labelledby="uploadStatementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadStatementModalLabel">
                    <i class="ti ti-upload"></i> {{ __('Upload Bank Statement for') }} {{ $accountName }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('bank-statement.upload.direct') }}" enctype="multipart/form-data" id="uploadStatementForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle"></i>
                                Upload a bank statement to compare with <strong>{{ $accountName }}</strong> ledger entries.
                                The system will automatically match transactions based on amount and date.
                            </div>
                        </div>
                        
                        <!-- Hidden account ID - pre-selected from current page -->
                        <input type="hidden" name="bank_account_id" value="{{ $accountId }}">
                        <input type="hidden" name="ledger_id" value="{{ $accountId }}">
                        <input type="hidden" name="start_date" value="{{ $startDate }}">
                        <input type="hidden" name="end_date" value="{{ $endDate }}">
                        
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">{{ __('Statement File') }} <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">Supported formats: PDF, JPG, JPEG, PNG (Max 10MB)</small>
                            </div>
                        </div>
                        
                        <div class="col-md-12 mt-3">
                            <div class="form-group">
                                <label class="form-label">{{ __('Bank Name') }} (Optional)</label>
                                <input type="text" name="bank_name" class="form-control" placeholder="e.g., Chase Bank, Bank of America" value="{{ $account->bank_name ?? '' }}">
                                <small class="text-muted">If left blank, will use account bank name</small>
                            </div>
                        </div>
                        
                        <div class="col-md-12 mt-3">
                            <div class="alert alert-warning">
                                <i class="ti ti-alert-triangle"></i>
                                <strong>Note:</strong> The system will automatically match transactions with your ledger entries based on amount and date (±3 days tolerance).
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ti ti-x"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitUpload">
                        <i class="ti ti-upload"></i> Upload & Compare
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#transactionsTable').DataTable({
        pageLength: 25,
        responsive: true,
        ordering: true,
        order: [[1, 'desc']],
        language: {
            search: "Search transactions:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ transactions",
            emptyTable: "No transactions found"
        },
        paging: false,
        searching: true
    });
    
    // Handle form submission with loading state
    $('#uploadStatementForm').on('submit', function() {
        const submitBtn = $('#submitUpload');
        submitBtn.html('<i class="ti ti-loader fspin"></i> Uploading & Comparing...');
        submitBtn.prop('disabled', true);
    });
});

// Show success/error messages
@if(session('success'))
    toastr.success('{{ session('success') }}');
@endif

@if(session('error'))
    toastr.error('{{ session('error') }}');
@endif
</script>
@endpush

<style>
.transaction-row {
    cursor: pointer;
    transition: all 0.2s ease;
}
.transaction-row:hover {
    background-color: #f5f5f5;
}
.fspin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
@media print {
    .btn-group, .btn, form, .pagination, .modal {
        display: none !important;
    }
}
</style>
@endsection