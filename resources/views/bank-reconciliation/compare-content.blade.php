@extends('layouts.admin')

@section('page-title')
    {{ __('Bank Statement Comparison') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Selection Form -->
        <div class="card">
            <div class="card-header">
                <h4>{{ __('Select Ledger Account for Comparison') }}</h4>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('bank-reconciliation.compare-with-ledger') }}" id="comparison-form">
                    <input type="hidden" name="submission_id" value="{{ request()->get('submission_id', $submissionId ?? '') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('Ledger Account') }}</label>
                                @if(isset($accounts) && count($accounts) > 0)
                                    <select name="ledger_id" class="form-control select2" required>
                                        <option value="">{{ __('Select Account') }}</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}" {{ ($ledgerId ?? '') == $account->id ? 'selected' : '' }}>
                                                {{ $account->code }} - {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <select name="ledger_id" class="form-control" disabled>
                                        <option value="">{{ __('No accounts available') }}</option>
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Start Date') }}</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate ?? date('Y-m-01') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('End Date') }}</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate ?? date('Y-m-t') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fa fa-search"></i> {{ __('Compare') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Comparison Results -->
        @if(isset($comparison) && isset($comparison->results) && count($comparison->results) > 0)
        <div class="card">
            <div class="card-header">
                <h4>{{ __('Comparison Result') }}</h4>
                <div class="card-header-action">
                    <button type="button" class="btn btn-sm btn-success" onclick="exportCSV()">
                        <i class="fa fa-download"></i> {{ __('Export CSV') }}
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="alert alert-success text-center">
                            <h5 class="mb-0">✓ {{ $comparison->matched_count ?? 0 }}</h5>
                            <small>{{ __('Fully Matched') }}</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-warning text-center">
                            <h5 class="mb-0">⚠ {{ $comparison->mismatched_count ?? 0 }}</h5>
                            <small>{{ __('Field Mismatch') }}</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-info text-center">
                            <h5 class="mb-0">📄 {{ $comparison->unmatched_bank ?? 0 }}</h5>
                            <small>{{ __('PDF Only') }}</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-danger text-center">
                            <h5 class="mb-0">💻 {{ $comparison->unmatched_ledger ?? 0 }}</h5>
                            <small>{{ __('Ledger Only') }}</small>
                        </div>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>{{ __('Match Accuracy') }}:</strong> 
                            <span class="badge badge-success">{{ number_format($comparison->match_accuracy ?? 0, 2) }}%</span>
                        </div>
                        <div class="col-md-3">
                            <strong>{{ __('Total Bank Amount') }}:</strong> 
                            {{ number_format($comparison->total_bank_amount ?? 0, 2) }}
                        </div>
                        <div class="col-md-3">
                            <strong>{{ __('Total Ledger Amount') }}:</strong> 
                            {{ number_format($comparison->total_ledger_amount ?? 0, 2) }}
                        </div>
                        <div class="col-md-3">
                            <strong>{{ __('Difference') }}:</strong> 
                            <span class="{{ ($comparison->total_variance ?? 0) != 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($comparison->total_variance ?? 0, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Comparison Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="comparison-table">
                        <thead>
                            <tr>
                                <th rowspan="2" class="text-center align-middle" style="width: 5%">#</th>
                                <th colspan="4" class="text-center bg-primary text-white">UPLOADED PDF (BANK STATEMENT)</th>
                                <th colspan="4" class="text-center bg-success text-white">SYSTEM LEDGER (RAW DATA)</th>
                                <th rowspan="2" class="text-center align-middle" style="width: 12%">Status</th>
                            </tr>
                            <tr>
                                <th class="bg-primary text-white">Date</th>
                                <th class="bg-primary text-white">Description/Account</th>
                                <th class="bg-primary text-white">Amount</th>
                                <th class="bg-primary text-white">Reference</th>
                                <th class="bg-success text-white">Date</th>
                                <th class="bg-success text-white">Description/Account</th>
                                <th class="bg-success text-white">Amount</th>
                                <th class="bg-success text-white">Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparison->results as $index => $result)
                            <tr class="comparison-row" data-status="{{ $result->status }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                
                                <!-- PDF Fields -->
                                <td class="@if(($result->status == 'bank_only' || $result->status == 'mismatched') && ($result->pdf_date_mismatch || !$result->date_formatted)) table-warning mismatch-cell @endif">
                                    {{ $result->date_formatted ?? '-' }}
                                    @if($result->status == 'bank_only')
                                        <small class="d-block text-danger">
                                            <i class="fa fa-exclamation-triangle"></i> Missing in Ledger
                                        </small>
                                    @endif
                                    @if($result->status == 'mismatched' && $result->pdf_date_mismatch && $result->ledger_date)
                                        <small class="d-block text-danger">
                                            <i class="fa fa-exclamation-triangle"></i> Expected: {{ \Carbon\Carbon::parse($result->ledger_date)->format('M d, Y') }}
                                        </small>
                                    @endif
                                </td>
                                
                                <td class="@if(($result->status == 'bank_only' || $result->status == 'mismatched') && ($result->pdf_account_mismatch || !$result->description)) table-warning mismatch-cell @endif">
                                    {{ $result->description ?? '-' }}
                                    @if($result->status == 'bank_only')
                                        <small class="d-block text-danger">
                                            <i class="fa fa-exclamation-triangle"></i> Missing in Ledger
                                        </small>
                                    @endif
                                    @if($result->status == 'mismatched' && $result->pdf_account_mismatch && $result->ledger_description)
                                        <small class="d-block text-danger">
                                            <i class="fa fa-exclamation-triangle"></i> Expected: {{ $result->ledger_description }}
                                        </small>
                                    @endif
                                </td>
                                
                                <td class="@if(($result->status == 'bank_only' || $result->status == 'mismatched') && ($result->pdf_amount_mismatch || $result->bank_amount == 0)) table-warning mismatch-cell font-weight-bold @endif">
                                    @if($result->bank_amount > 0)
                                        {{ number_format($result->bank_amount, 2) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                    @if($result->status == 'bank_only')
                                        <small class="d-block text-danger">
                                            <i class="fa fa-exclamation-triangle"></i> Missing in Ledger
                                        </small>
                                    @endif
                                    @if($result->status == 'mismatched' && $result->pdf_amount_mismatch && $result->ledger_amount > 0)
                                        <small class="d-block text-danger">
                                            <i class="fa fa-exclamation-triangle"></i> Expected: {{ number_format($result->ledger_amount, 2) }}
                                        </small>
                                    @endif
                                </td>
                                
                                <td>
                                    {{ $result->pdf_reference ?? '-' }}
                                </td>
                                
                                <!-- Ledger Fields -->
                                <td class="@if(($result->status == 'ledger_only' || $result->status == 'mismatched') && ($result->ledger_date_mismatch || !$result->ledger_date)) table-warning mismatch-cell @endif">
                                    {{ $result->ledger_date ? \Carbon\Carbon::parse($result->ledger_date)->format('M d, Y') : '-' }}
                                    @if($result->status == 'ledger_only')
                                        <small class="d-block text-danger">
                                            <i class="fa fa-exclamation-triangle"></i> Missing in PDF
                                        </small>
                                    @endif
                                    @if($result->status == 'mismatched' && $result->ledger_date_mismatch && $result->date_formatted)
                                        <small class="d-block text-danger">
                                            <i class="fa fa-exclamation-triangle"></i> Expected: {{ $result->date_formatted }}
                                        </small>
                                    @endif
                                </td>
                                
                                <td class="@if(($result->status == 'ledger_only' || $result->status == 'mismatched') && ($result->ledger_account_mismatch || !$result->ledger_description)) table-warning mismatch-cell @endif">
                                    {{ $result->ledger_description ?? '-' }}
                                    @if($result->status == 'ledger_only')
                                        <small class="d-block text-danger">
                                            <i class="fa fa-exclamation-triangle"></i> Missing in PDF
                                        </small>
                                    @endif
                                    @if($result->status == 'mismatched' && $result->ledger_account_mismatch && $result->description)
                                        <small class="d-block text-danger">
                                            <i class="fa fa-exclamation-triangle"></i> Expected: {{ $result->description }}
                                        </small>
                                    @endif
                                </td>
                                
                                <td class="@if(($result->status == 'ledger_only' || $result->status == 'mismatched') && ($result->ledger_amount_mismatch || $result->ledger_amount == 0)) table-warning mismatch-cell font-weight-bold @endif">
                                    @if($result->ledger_amount > 0)
                                        {{ number_format($result->ledger_amount, 2) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                    @if($result->status == 'ledger_only')
                                        <small class="d-block text-danger">
                                            <i class="fa fa-exclamation-triangle"></i> Missing in PDF
                                        </small>
                                    @endif
                                    @if($result->status == 'mismatched' && $result->ledger_amount_mismatch && $result->bank_amount > 0)
                                        <small class="d-block text-danger">
                                            <i class="fa fa-exclamation-triangle"></i> Expected: {{ number_format($result->bank_amount, 2) }}
                                        </small>
                                    @endif
                                </td>
                                
                                <td>
                                    {{ $result->ledger_reference ?? '-' }}
                                </td>
                                
                                <!-- Status -->
                                <td class="text-center">
                                    @if($result->status == 'matched')
                                        <span class="badge badge-success px-3 py-2">
                                            <i class="fa fa-check-circle"></i> Fully Matched
                                        </span>
                                    @elseif($result->status == 'mismatched')
                                        <span class="badge badge-warning px-3 py-2">
                                            <i class="fa fa-exclamation-triangle"></i> Fields Mismatch
                                        </span>
                                        @if(isset($result->match_score) && $result->match_score > 0)
                                            <small class="d-block text-muted">Match: {{ $result->match_score }}%</small>
                                        @endif
                                    @elseif($result->status == 'bank_only')
                                        <span class="badge badge-info px-3 py-2">
                                            <i class="fa fa-file-pdf-o"></i> PDF Only
                                        </span>
                                    @elseif($result->status == 'ledger_only')
                                        <span class="badge badge-danger px-3 py-2">
                                            <i class="fa fa-database"></i> Ledger Only
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Filter Buttons -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary filter-btn active" data-filter="all">
                                <i class="fa fa-list"></i> All ({{ count($comparison->results) }})
                            </button>
                            <button type="button" class="btn btn-success filter-btn" data-filter="matched">
                                <i class="fa fa-check-circle"></i> Matched ({{ $comparison->matched_count ?? 0 }})
                            </button>
                            <button type="button" class="btn btn-warning filter-btn" data-filter="mismatched">
                                <i class="fa fa-exclamation-triangle"></i> Mismatched ({{ $comparison->mismatched_count ?? 0 }})
                            </button>
                            <button type="button" class="btn btn-info filter-btn" data-filter="bank_only">
                                <i class="fa fa-file-pdf-o"></i> PDF Only ({{ $comparison->unmatched_bank ?? 0 }})
                            </button>
                            <button type="button" class="btn btn-danger filter-btn" data-filter="ledger_only">
                                <i class="fa fa-database"></i> Ledger Only ({{ $comparison->unmatched_ledger ?? 0 }})
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('Legend / How to Read') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-2">
                                            <span class="badge badge-success">✓ Fully Matched</span>
                                            <small class="text-muted ml-2">- All fields match between PDF and Ledger</small>
                                        </div>
                                        <div class="mb-2">
                                            <span class="badge badge-warning">⚠ Fields Mismatch</span>
                                            <small class="text-muted ml-2">- Transaction exists but some fields don't match</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-2">
                                            <span class="badge badge-info">📄 PDF Only</span>
                                            <small class="text-muted ml-2">- Transaction exists only in PDF (missing from Ledger)</small>
                                        </div>
                                        <div class="mb-2">
                                            <span class="badge badge-danger">💻 Ledger Only</span>
                                            <small class="text-muted ml-2">- Transaction exists only in Ledger (missing from PDF)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-2">
                                            <div class="bg-warning p-1 rounded d-inline-block px-2">
                                                <small><strong>⚠ Yellow Highlight</strong></small>
                                            </div>
                                            <small class="text-muted ml-2">- Field has mismatch or is missing</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @elseif(isset($ledgerId) && $ledgerId)
        <div class="card">
            <div class="card-body">
                <div class="alert alert-warning text-center mb-0">
                    <i class="fa fa-info-circle"></i> 
                    {{ __('No comparison data available for the selected criteria.') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    .table-warning {
        background-color: #fff3cd !important;
    }
    
    .mismatch-cell {
        background-color: #ffeb9c !important;
        position: relative;
    }
    
    .badge {
        font-size: 12px;
        font-weight: 500;
    }
    
    .badge-success {
        background-color: #28a745;
        color: white;
    }
    
    .badge-warning {
        background-color: #ffc107;
        color: #856404;
    }
    
    .badge-danger {
        background-color: #dc3545;
        color: white;
    }
    
    .badge-info {
        background-color: #17a2b8;
        color: white;
    }
    
    .bg-primary {
        background-color: #007bff !important;
    }
    
    .bg-success {
        background-color: #28a745 !important;
    }
    
    .font-weight-bold {
        font-weight: bold;
    }
    
    .table th {
        vertical-align: middle;
        text-align: center;
        font-size: 13px;
    }
    
    .table td {
        vertical-align: middle;
        font-size: 13px;
    }
    
    small.text-danger {
        font-size: 10px;
        margin-top: 3px;
    }
    
    .mismatch-cell:hover {
        background-color: #ffe69c !important;
        cursor: help;
    }
    
    @keyframes highlightBlink {
        0% { background-color: #fff3cd; }
        50% { background-color: #ffe69c; }
        100% { background-color: #fff3cd; }
    }
    
    .mismatch-cell {
        animation: highlightBlink 1s ease-in-out;
    }
    
    .filter-btn.active {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.5);
    }
    
    .select2-container .select2-selection--single {
        height: 38px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>

<script>
    function exportCSV() {
        let csv = [];
        let rows = document.querySelectorAll('#comparison-table tbody tr');
        
        csv.push(['Status', 'PDF Date', 'PDF Description', 'PDF Amount', 'PDF Reference', 'Ledger Date', 'Ledger Description', 'Ledger Amount', 'Ledger Reference'].join(','));
        
        rows.forEach(row => {
            let rowData = [];
            let status = row.querySelector('.badge')?.innerText.trim() || '';
            rowData.push(status);
            
            let pdfCells = row.querySelectorAll('td:nth-child(2), td:nth-child(3), td:nth-child(4), td:nth-child(5)');
            pdfCells.forEach(cell => {
                let text = cell.innerText.split('Expected:')[0].trim();
                rowData.push(text);
            });
            
            let ledgerCells = row.querySelectorAll('td:nth-child(6), td:nth-child(7), td:nth-child(8), td:nth-child(9)');
            ledgerCells.forEach(cell => {
                let text = cell.innerText.split('Expected:')[0].trim();
                rowData.push(text);
            });
            
            csv.push(rowData.join(','));
        });
        
        let blob = new Blob([csv.join('\n')], { type: 'text/csv' });
        let url = window.URL.createObjectURL(blob);
        let a = document.createElement('a');
        a.href = url;
        a.download = 'comparison_report.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }
    
    document.querySelectorAll('.filter-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            const filterValue = this.getAttribute('data-filter');
            const rows = document.querySelectorAll('#comparison-table tbody tr');
            
            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                if (filterValue === 'all' || status === filterValue) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    
    document.querySelector('select[name="ledger_id"]')?.addEventListener('change', function() {
        if(this.value) {
            document.getElementById('comparison-form').submit();
        }
    });
</script>
@endsection