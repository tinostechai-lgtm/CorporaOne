@extends('layouts.admin')

@section('page-title')
    {{ __('Bank Statement vs Account Comparison') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('report.account.statement') }}">{{ __('Account Statement') }}</a></li>
    <li class="breadcrumb-item">{{ __('Bank Comparison') }}</li>
@endsection

@section('content')
<style>
    .edit-form {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 8px;
        margin-top: 10px;
    }
    .edit-form input, .edit-form textarea {
        margin-bottom: 8px;
    }
    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    .btn-xs {
        padding: 2px 8px;
        font-size: 11px;
    }
    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }
    .matched-row {
        background-color: #d4edda !important;
    }
    .bank-only-row {
        background-color: #fff3cd !important;
    }
    .ledger-only-row {
        background-color: #f8d7da !important;
    }
    .status-badge {
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 12px;
    }
</style>

<div class="row">
    {{-- Upload & Filter Section --}}
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0">{{ __('Upload Bank Statement & Compare') }}</h6>
                <div>
                    <a href="{{ route('bank-reconciliation.index') }}" class="btn btn-outline-info btn-sm me-2">
                        <i class="ti ti-bank"></i> {{ __('View All Statements') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('bank-statement.store') }}" enctype="multipart/form-data" class="row g-3" id="uploadForm">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Bank Account') }}</label>
                        <select name="bank_account_id" class="form-control" required>
                            <option value="">{{ __('Select Bank Account') }}</option>
                            @foreach($bankAccounts as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Statement File') }} <small class="text-danger">*</small></label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.png,.jpg,.jpeg" required>
                        <small>Max 10MB - PDF/JPG/PNG supported</small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('From Date') }}</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date', now()->subMonth()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('To Date') }}</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" id="search-btn" class="btn btn-info w-100">
                            <i class="ti ti-search"></i> {{ __('Load Account Transactions') }}
                        </button>
                        <button type="submit" class="btn btn-primary w-100 ms-1">
                            <i class="ti ti-upload"></i> {{ __('Upload & Compare') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Account Transaction Preview --}}
    <div class="col-12 mb-4" id="ledger-preview">
        <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i>
            Select a bank account and date range, then click "Load Account Transactions" to preview ledger entries. 
            Upload a bank statement to see side-by-side comparison.
        </div>
    </div>

    {{-- Comparison Results --}}
    @if(isset($comparison) && $comparison)
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">{{ __('Comparison Results') }}</h6>
                    <small class="text-muted">
                        {{ $comparison->total_bank_txs }} bank txns vs {{ $comparison->total_ledger_txs }} ledger txns 
                        | {{ $comparison->matched_count }} matched ({{ number_format(($comparison->matched_count / max($comparison->total_bank_txs, 1))*100, 1) }}%)
                    </small>
                </div>
                <div class="btn-group">
                    <button onclick="exportToExcel()" class="btn btn-success btn-sm">
                        <i class="ti ti-download"></i> Export CSV
                    </button>
                    <button onclick="window.print()" class="btn btn-info btn-sm">
                        <i class="ti ti-printer"></i> Print
                    </button>
                    <button onclick="location.reload()" class="btn btn-secondary btn-sm">
                        <i class="ti ti-refresh"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{-- Summary Cards --}}
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3>{{ $comparison->matched_count }}</h3>
                                <small>Matched</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3>{{ $comparison->unmatched_bank }}</h3>
                                <small>Bank Only</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h3>{{ $comparison->unmatched_ledger }}</h3>
                                <small>Ledger Only</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3>${{ number_format($comparison->total_variance, 2) }}</h3>
                                <small>Variance</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <h3>{{ $comparison->match_accuracy }}%</h3>
                                <small>Accuracy</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Comparison Table with Edit Actions --}}
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="comparisonTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="8%">Status</th>
                                <th width="12%">Date</th>
                                <th width="18%">Description</th>
                                <th width="10%">Bank Amount</th>
                                <th width="10%">Ledger Amount</th>
                                <th width="10%">Variance</th>
                                <th width="8%">Bank Ref</th>
                                <th width="8%">Ledger Ref</th>
                                <th width="16%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparison->results as $index => $row)
                            <tr class="{{ $row->status == 'matched' ? 'matched-row' : ($row->status == 'bank_only' ? 'bank-only-row' : 'ledger-only-row') }}" 
                                id="row-{{ $row->status }}-{{ $row->bank_txn_id ?? $row->ledger_txn_id ?? $index }}">
                                <td class="align-middle">
                                    @if($row->status == 'matched')
                                        <span class="badge bg-success status-badge">✓ Matched</span>
                                    @elseif($row->status == 'bank_only')
                                        <span class="badge bg-warning status-badge">⚠ Bank Only</span>
                                    @else
                                        <span class="badge bg-danger status-badge">✗ Ledger Only</span>
                                    @endif
                                 </td>
                                 <td class="align-middle">
                                    <span class="date-display-{{ $row->bank_txn_id ?? $row->ledger_txn_id ?? $index }}">
                                        {{ $row->date_formatted }}
                                    </span>
                                 </td>
                                 <td class="align-middle">
                                    <div class="desc-display-{{ $row->bank_txn_id ?? $row->ledger_txn_id ?? $index }}">
                                        {{ $row->description ?? '-' }}
                                    </div>
                                    @if($row->status != 'matched')
                                    <div class="edit-form-{{ $row->bank_txn_id ?? $row->ledger_txn_id ?? $index }}" style="display: none;">
                                        <form onsubmit="return updateComparisonEntry(event, '{{ $row->status }}', '{{ $row->bank_txn_id ?? '' }}', '{{ $row->ledger_txn_id ?? '' }}', {{ $index }})">
                                            @csrf
                                            <input type="date" name="date" class="form-control form-control-sm mb-1" value="{{ $row->date }}">
                                            <textarea name="description" class="form-control form-control-sm mb-1" rows="2">{{ $row->description ?? '' }}</textarea>
                                            <input type="number" name="amount" class="form-control form-control-sm mb-1" step="0.01" 
                                                   value="{{ $row->status == 'bank_only' ? $row->bank_amount : ($row->ledger_amount ?? 0) }}" 
                                                   placeholder="Amount">
                                            <input type="text" name="reference" class="form-control form-control-sm mb-1" 
                                                   value="{{ $row->status == 'bank_only' ? ($row->bank_ref ?? '') : ($row->ledger_ref ?? '') }}" 
                                                   placeholder="Reference">
                                            <div class="action-buttons">
                                                <button type="submit" class="btn btn-primary btn-xs">
                                                    <i class="ti ti-device-floppy"></i> Save
                                                </button>
                                                <button type="button" class="btn btn-secondary btn-xs" onclick="cancelEditComparison('{{ $row->bank_txn_id ?? $row->ledger_txn_id ?? $index }}')">
                                                    <i class="ti ti-x"></i> Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    @endif
                                 </td>
                                 <td class="text-end align-middle">${{ number_format($row->bank_amount, 2) }}</td>
                                 <td class="text-end align-middle">
                                    @if($row->status == 'ledger_only')
                                        <span class="ledger-amount-{{ $row->ledger_txn_id ?? $index }}">
                                            ${{ number_format($row->ledger_amount ?? 0, 2) }}
                                        </span>
                                    @else
                                        ${{ number_format($row->ledger_amount ?? 0, 2) }}
                                    @endif
                                 </td>
                                 <td class="text-end align-middle {{ $row->variance != 0 ? 'fw-bold text-danger' : '' }}">
                                    ${{ number_format($row->variance, 2) }}
                                 </td>
                                 <td class="align-middle">{{ $row->bank_ref ?? '-' }}</td>
                                 <td class="align-middle">{{ $row->ledger_ref ?? '-' }}</td>
                                 <td class="align-middle">
                                    <div class="action-buttons">
                                        @if($row->status != 'matched')
                                            <button class="btn btn-sm btn-outline-primary" onclick="showEditComparison('{{ $row->bank_txn_id ?? $row->ledger_txn_id ?? $index }}')" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="manualMatch('{{ $row->status }}', '{{ $row->bank_txn_id ?? '' }}', '{{ $row->ledger_txn_id ?? '' }}')" title="Manual Match">
                                                <i class="ti ti-link"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteEntry('{{ $row->status }}', '{{ $row->bank_txn_id ?? '' }}', '{{ $row->ledger_txn_id ?? '' }}')" title="Delete">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                                <i class="ti ti-check"></i> Matched
                                            </button>
                                        @endif
                                    </div>
                                 </td>
                             </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- Reconciliation Summary with Actions --}}
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="alert alert-{{ $comparison->total_variance == 0 ? 'success' : 'warning' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="ti ti-info-circle me-2"></i>
                                    <strong>Reconciliation Summary:</strong>
                                    {{ $comparison->matched_count }} matched, 
                                    {{ $comparison->unmatched_bank }} bank-only, 
                                    {{ $comparison->unmatched_ledger }} ledger-only transactions
                                </div>
                                <div>
                                    <button onclick="reconcileAll()" class="btn btn-sm btn-primary">
                                        <i class="ti ti-balance-scale"></i> Reconcile All
                                    </button>
                                    <button onclick="exportReconciliationReport()" class="btn btn-sm btn-success">
                                        <i class="ti ti-file-text"></i> Export Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- No Comparison Section --}}
    @if(!isset($comparison))
    <div class="col-12">
        <div class="card border-dashed">
            <div class="card-body text-center py-5">
                <i class="ti ti-balance-scale fs-1 text-muted mb-3"></i>
                <h5>{{ __('Ready to Compare Bank vs Ledger') }}</h5>
                <p class="text-muted mb-4">
                    1. Select bank account & date range → Load transactions<br>
                    2. Upload bank statement → Auto-compare with ledger<br>
                    3. Review matches, variances & export results
                </p>
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <i class="ti ti-list-numbers fs-2 text-success mb-2"></i>
                                <h6>Auto Match</h6>
                                <small>±1% amount, ±3 days tolerance</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <i class="ti ti-table fs-2 text-info mb-2"></i>
                                <h6>Side-by-Side</h6>
                                <small>Matched/unmatched view</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <i class="ti ti-edit fs-2 text-warning mb-2"></i>
                                <h6>Edit & Match</h6>
                                <small>Manual override & corrections</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <i class="ti ti-download fs-2 text-primary mb-2"></i>
                                <h6>Export</h6>
                                <small>CSV with full audit trail</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('script')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script>
// Toastr configuration
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "3000"
};

// Auto-load account transactions on account change and search button
$('select[name="bank_account_id"], #start_date, #end_date').on('change', function() {
    loadLedgerPreview();
});

$(document).ready(function() {
    $('#search-btn').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        loadLedgerPreview();
    });
    
    // Initialize DataTable if comparison exists
    @if(isset($comparison) && $comparison)
    $('#comparisonTable').DataTable({
        pageLength: 50,
        responsive: true,
        ordering: true,
        columnDefs: [
            { orderable: false, targets: -1 }
        ]
    });
    @endif
});

function loadLedgerPreview() {
    let accountId = $('select[name="bank_account_id"]').val();
    let startDate = $('#start_date').val();
    let endDate = $('#end_date').val();
    
    if (!accountId) {
        $('#ledger-preview').html('<div class="alert alert-info">Please select an account to preview transactions.</div>');
        return;
    }
    
    $('#ledger-preview').html('<div class="text-center p-4"><i class="ti ti-loader-2 fspin"></i> Loading transactions...</div>');
    
    $.ajax({
        url: '{{ route("report.ledger.preview") }}',
        method: 'GET',
        data: {
            account_id: accountId,
            start_date: startDate,
            end_date: endDate
        },
        success: function(transactions) {
            let html = '';
            if (transactions.length === 0) {
                html = '<div class="alert alert-warning"><i class="ti ti-info-circle"></i> No transactions found for this period.</div>';
            } else {
                let total = transactions.reduce((sum, t) => sum + (parseFloat(t.debit) + parseFloat(t.credit)), 0);
                html = `
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h6>Account Transaction Preview (Recent ${transactions.length})</h6>
                            <span class="badge bg-primary">$${total.toLocaleString()}</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Credit</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                
                transactions.slice(0, 10).forEach(function(t) {
                    html += `
                        <tr>
                            <td>${t.date}</td>
                            <td>${t.description.substring(0, 50)}</td>
                            <td class="text-end">$${parseFloat(t.debit || 0).toLocaleString()}</td>
                            <td class="text-end">$${parseFloat(t.credit || 0).toLocaleString()}</td>
                        </tr>`;
                });
                
                html += `
                                </tbody>
                            </table>
                            ${transactions.length > 10 ? '<p class="text-muted text-center mt-2">Showing 10 of ' + transactions.length + ' transactions</p>' : ''}
                        </div>
                    </div>`;
            }
            $('#ledger-preview').html(html);
        },
        error: function() {
            $('#ledger-preview').html('<div class="alert alert-danger">Error loading transactions. Please try again.</div>');
        }
    });
}

// Show edit form for comparison entry
function showEditComparison(id) {
    $('.desc-display-' + id).hide();
    $('.edit-form-' + id).show();
    $('.date-display-' + id).hide();
}

// Cancel edit
function cancelEditComparison(id) {
    $('.desc-display-' + id).show();
    $('.edit-form-' + id).hide();
    $('.date-display-' + id).show();
}

// Update comparison entry
function updateComparisonEntry(event, status, bankTxnId, ledgerTxnId, index) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    const data = {
        date: formData.get('date'),
        description: formData.get('description'),
        amount: parseFloat(formData.get('amount')) || 0,
        reference: formData.get('reference'),
        status: status,
        bank_txn_id: bankTxnId,
        ledger_txn_id: ledgerTxnId,
        _token: '{{ csrf_token() }}'
    };
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="ti ti-loader"></i> Saving...';
    submitBtn.disabled = true;
    
    fetch('/bank-reconciliation/update-comparison-entry', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if(result.success) {
            toastr.success('Entry updated successfully!');
            setTimeout(() => location.reload(), 1500);
        } else {
            toastr.error(result.error || 'Failed to update');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('An error occurred');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
    
    return false;
}

// Manual match
function manualMatch(status, bankTxnId, ledgerTxnId) {
    let message = '';
    if(status == 'bank_only') {
        message = 'Match this bank transaction with a ledger entry?';
    } else if(status == 'ledger_only') {
        message = 'Match this ledger entry with a bank transaction?';
    } else {
        message = 'Manually match these transactions?';
    }
    
    if(confirm(message + ' This will mark them as reconciled.')) {
        fetch('/bank-reconciliation/manual-match', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                status: status,
                bank_txn_id: bankTxnId,
                ledger_txn_id: ledgerTxnId,
                submission_id: '{{ $comparison->submission_id ?? 0 }}'
            })
        })
        .then(response => response.json())
        .then(result => {
            if(result.success) {
                toastr.success('Transactions matched successfully!');
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(result.error || 'Failed to match');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('An error occurred');
        });
    }
}

// Delete entry
function deleteEntry(status, bankTxnId, ledgerTxnId) {
    if(confirm('Are you sure you want to delete this entry? This action cannot be undone.')) {
        fetch('/bank-reconciliation/delete-comparison-entry', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                status: status,
                bank_txn_id: bankTxnId,
                ledger_txn_id: ledgerTxnId
            })
        })
        .then(response => response.json())
        .then(result => {
            if(result.success) {
                toastr.success('Entry deleted successfully!');
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(result.error || 'Failed to delete');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('An error occurred');
        });
    }
}

// Reconcile all
function reconcileAll() {
    if(confirm('Reconcile all unmatched transactions? This will mark all as reviewed.')) {
        fetch('/bank-reconciliation/reconcile-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                submission_id: '{{ $comparison->submission_id ?? 0 }}'
            })
        })
        .then(response => response.json())
        .then(result => {
            if(result.success) {
                toastr.success('All transactions reconciled!');
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(result.error || 'Failed to reconcile');
            }
        });
    }
}

// Export to Excel
function exportToExcel() {
    let table = document.getElementById('comparisonTable');
    let html = table.outerHTML;
    let url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
    let link = document.createElement('a');
    link.href = url;
    link.download = 'reconciliation_report.xls';
    link.click();
}

// Export full report
function exportReconciliationReport() {
    window.location.href = '{{ route("bank-statement-comparison.export", $comparison->submission_id ?? 0) }}';
}

// Restore values from URL and auto-load on page load
$(document).ready(function() {
    let urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('start_date')) $('#start_date').val(urlParams.get('start_date'));
    if (urlParams.has('end_date')) $('#end_date').val(urlParams.get('end_date'));
    if (urlParams.has('bank_account_id')) $('select[name="bank_account_id"]').val(urlParams.get('bank_account_id'));
    
    if (urlParams.has('bank_account_id')) {
        setTimeout(loadLedgerPreview, 500);
    }
});
</script>
@endpush

@endsection