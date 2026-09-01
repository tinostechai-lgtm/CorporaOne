@extends('layouts.admin')

@section('page-title')
    {{ __('Bank Reconciliation') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('report.ledger') }}">{{ __('Ledger Summary') }}</a></li>
    <li class="breadcrumb-item">{{ __('Bank Reconciliation') }}</li>
@endsection

@push('css-page')
<style>
    .matched-row { background-color: #d4edda !important; }
    .unmatched-bank-row { background-color: #f8d7da !important; }
    .unmatched-ledger-row { background-color: #fff3cd !important; }
    .match-exact { background: #28a745; color: white; }
    .match-amount { background: #17a2b8; color: white; }
    .match-date { background: #ffc107; color: #333; }
    .reconciliation-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 25px;
    }
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        color: #333;
    }
    .stat-card .stat-value {
        font-size: 28px;
        font-weight: bold;
    }
    .stat-card .stat-value.positive { color: #28a745; }
    .stat-card .stat-value.negative { color: #dc3545; }
    .table-responsive {
        overflow-x: auto;
    }
    .badge {
        font-size: 12px;
        padding: 4px 8px;
    }
    .fs-2xs {
        font-size: 10px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                {{ Form::open(['route' => 'report.bank-reconciliation', 'method' => 'GET', 'id' => 'reconciliation_form']) }}
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('statement_id', __('Bank Statement'), ['class' => 'form-label']) }}
                            {{ Form::select('statement_id', $bankStatements->pluck('bank_name', 'id')->prepend('Select Statement', ''), $selectedStatement?->id, ['class' => 'form-control select', 'required' => 'required']) }}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                            {{ Form::date('start_date', $filter['startDateRange'] ?? date('Y-m-01'), ['class' => 'form-control']) }}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                            {{ Form::date('end_date', $filter['endDateRange'] ?? date('Y-m-t'), ['class' => 'form-control']) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{ Form::label('account_id', __('Ledger Account'), ['class' => 'form-label']) }}
                            <select name="account_id" class="form-control select">
                                <option value="">{{ __('All Accounts') }}</option>
                                @foreach($accountsForFilter ?? [] as $acc)
                                    <option value="{{ $acc['id'] }}" {{ ($accountId ?? '') == $acc['id'] ? 'selected' : '' }}>
                                        {{ $acc['code'] }} - {{ $acc['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-reload"></i> {{ __('Reconcile') }}
                        </button>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

@if($selectedStatement)
<div class="row">
    <div class="col-12">
        <!-- Summary Cards -->
        <div class="reconciliation-summary">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-title">{{ __('Bank Statement') }}</div>
                        <div class="stat-value">{{ $selectedStatement->bank_name ?? 'N/A' }}</div>
                        <small>{{ $selectedStatement->account_number ?? '' }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-title">{{ __('Statement Date') }}</div>
                        <div class="stat-value">{{ $selectedStatement->created_at->format('d-m-Y') }}</div>
                        <small>{{ __('Uploaded') }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-title">{{ __('Total Bank Amount') }}</div>
                        <div class="stat-value positive">{{ \Auth::user()->priceFormat($matchedTransactions['total_bank_amount'] ?? 0) }}</div>
                        <small>{{ count($bankTransactions) }} {{ __('transactions') }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-title">{{ __('Total Ledger Amount') }}</div>
                        <div class="stat-value">{{ \Auth::user()->priceFormat($matchedTransactions['total_ledger_amount'] ?? 0) }}</div>
                        <small>{{ count($ledgerTransactions) }} {{ __('entries') }}</small>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-title">{{ __('Difference') }}</div>
                        <div class="stat-value {{ ($matchedTransactions['difference'] ?? 0) == 0 ? 'positive' : 'negative' }}">
                            {{ \Auth::user()->priceFormat(abs($matchedTransactions['difference'] ?? 0)) }}
                        </div>
                        @if(($matchedTransactions['difference'] ?? 0) > 0)
                            <small>{{ __('Bank Higher') }}</small>
                        @elseif(($matchedTransactions['difference'] ?? 0) < 0)
                            <small>{{ __('Ledger Higher') }}</small>
                        @else
                            <small>{{ __('Balanced') }}</small>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-title">{{ __('Matched Amount') }}</div>
                        <div class="stat-value positive">{{ \Auth::user()->priceFormat($matchedTransactions['matched_amount'] ?? 0) }}</div>
                        <small>{{ number_format($matchedTransactions['match_rate'] ?? 0, 2) }}% {{ __('match rate') }}</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-title">{{ __('Transaction Status') }}</div>
                        <div class="stat-value">
                            <span class="badge bg-success">{{ count($matchedTransactions['matched'] ?? []) }} {{ __('Matched') }}</span>
                            <span class="badge bg-danger">{{ count($unmatchedBank) }} {{ __('Unmatched Bank') }}</span>
                            <span class="badge bg-warning text-dark">{{ count($unmatchedLedger) }} {{ __('Unmatched Ledger') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matched Transactions -->
        @if(count($matchedTransactions['matched'] ?? []) > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="ti ti-checkbox text-success me-2"></i>
                    {{ __('Matched Transactions') }} ({{ count($matchedTransactions['matched']) }})
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Bank Description') }}</th>
                                <th>{{ __('Bank Amount') }}</th>
                                <th>{{ __('Ledger Date') }}</th>
                                <th>{{ __('Ledger Description') }}</th>
                                <th>{{ __('Ledger Amount') }}</th>
                                <th>{{ __('Reference') }}</th>
                                <th>{{ __('Match Score') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($matchedTransactions['matched'] as $match)
                            <tr class="matched-row">
                                <td>
                                    {{ \Carbon\Carbon::parse($match['bank_transaction']['date'] ?? now())->format('d-m-Y') }}
                                </td>
                                <td>
                                    {{ $match['bank_transaction']['description'] ?? $match['bank_transaction']['purpose'] ?? $match['bank_transaction']['account_name'] ?? 'N/A' }}
                                    @if($match['bank_transaction']['reference'] ?? false)
                                        <br><small class="text-muted">Ref: {{ $match['bank_transaction']['reference'] }}</small>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-success">
                                    {{ \Auth::user()->priceFormat($match['bank_amount']) }}
                                </td>
                                <td>
                                    {{ $match['ledger_transaction'] ? \Carbon\Carbon::parse($match['ledger_transaction']->date)->format('d-m-Y') : 'N/A' }}
                                </td>
                                <td>
                                    @if($match['ledger_transaction'])
                                        {{ $match['ledger_transaction']->description ?? $match['ledger_transaction']->reference ?? 'Transaction' }}
                                        <br><small class="text-muted">{{ $match['ledger_transaction']->account_name ?? '' }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    @if($match['ledger_transaction'])
                                        {{ \Auth::user()->priceFormat($match['ledger_amount']) }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($match['ledger_transaction'])
                                        <span class="badge bg-info">{{ $match['ledger_transaction']->reference ?? 'N/A' }}</span>
                                        @if($match['ledger_transaction']->reference_id)
                                            <small class="d-block">#{{ $match['ledger_transaction']->reference_id }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $match['match_score'] >= 90 ? 'bg-success' : ($match['match_score'] >= 70 ? 'bg-warning' : 'bg-secondary') }}" style="font-size: 14px;">
                                        {{ $match['match_score'] }}%
                                    </span>
                                    @if($match['match_type'] == 'exact')
                                        <span class="badge bg-success mt-1">Exact</span>
                                    @elseif($match['match_type'] == 'amount_only')
                                        <span class="badge bg-info mt-1">Amount Only</span>
                                    @else
                                        <span class="badge bg-warning mt-1">Date Proximity</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Unmatched Transactions -->
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">
                            <i class="ti ti-alert-triangle me-2"></i>
                            {{ __('Unmatched Bank Transactions') }} ({{ count($unmatchedBank) }})
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(count($unmatchedBank) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Reference') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($unmatchedBank as $tx)
                                        <tr class="unmatched-bank-row">
                                            <td>{{ \Carbon\Carbon::parse($tx['date'] ?? now())->format('d-m-Y') }}</td>
                                            <td>{{ $tx['description'] ?? $tx['purpose'] ?? $tx['account_name'] ?? 'N/A' }}</td>
                                            <td class="text-end text-danger fw-bold">
                                                {{ \Auth::user()->priceFormat(abs(($tx['debit'] ?? 0) - ($tx['credit'] ?? 0))) }}
                                            </td>
                                            <td><small>{{ $tx['reference'] ?? 'N/A' }}</small></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="ti ti-check-circle text-success display-4"></i>
                                <p class="mt-2 text-muted">{{ __('No unmatched bank transactions') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="ti ti-file-text me-2"></i>
                            {{ __('Unmatched Ledger Transactions') }} ({{ count($unmatchedLedger) }})
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(count($unmatchedLedger) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Account') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Reference') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($unmatchedLedger as $tx)
                                        <tr class="unmatched-ledger-row">
                                            <td>{{ \Carbon\Carbon::parse($tx->date)->format('d-m-Y') }}</td>
                                            <td>
                                                <span class="fw-bold">{{ $tx->account_name ?? 'N/A' }}</span>
                                                @if($tx->account_code)
                                                    <br><small class="text-muted">({{ $tx->account_code }})</small>
                                                @endif
                                            </td>
                                            <td>{{ $tx->description ?? $tx->reference ?? 'Transaction' }}</td>
                                            <td class="text-end text-warning fw-bold">
                                                {{ \Auth::user()->priceFormat(abs(($tx->debit ?? 0) - ($tx->credit ?? 0))) }}
                                            </td>
                                            <td><small>{{ $tx->reference ?? '#' }}{{ $tx->reference_id ?? '' }}</small></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="ti ti-check-circle text-success display-4"></i>
                                <p class="mt-2 text-muted">{{ __('No unmatched ledger transactions') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- No Data Message -->
        @if(count($matchedTransactions['matched'] ?? []) == 0 && count($unmatchedBank) == 0 && count($unmatchedLedger) == 0)
        <div class="card mt-3">
            <div class="card-body text-center py-5">
                <i class="ti ti-receipt-off display-1 text-muted"></i>
                <h4 class="mt-3">{{ __('No transactions to reconcile') }}</h4>
                <p class="text-muted">
                    {{ __('No bank transactions found in the selected statement or no ledger transactions in the date range.') }}
                </p>
                <div class="row justify-content-center mt-3">
                    <div class="col-md-3">
                        <a href="{{ route('report.bank.statement.reconciliation') }}" class="btn btn-outline-primary w-100">
                            <i class="ti ti-upload me-1"></i> {{ __('Upload Statement') }}
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('report.ledger') }}" class="btn btn-outline-success w-100">
                            <i class="ti ti-book me-1"></i> {{ __('View Ledger') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Export Button -->
<div class="row mt-3">
    <div class="col-12 text-end">
        <a href="{{ route('report.bank-reconciliation.export', ['statement_id' => $selectedStatement->id, 'start_date' => $filter['startDateRange'] ?? '', 'end_date' => $filter['endDateRange'] ?? '', 'account_id' => $accountId ?? '']) }}" 
           class="btn btn-success">
            <i class="ti ti-file-export"></i> {{ __('Export Report') }}
        </a>
        <a href="{{ route('report.bank-reconciliation') }}" class="btn btn-secondary">
            <i class="ti ti-refresh"></i> {{ __('Reset') }}
        </a>
    </div>
</div>
@else
<!-- No Statement Selected -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="ti ti-file-invoice display-1 text-muted"></i>
                <h4 class="mt-3">{{ __('Select a Bank Statement') }}</h4>
                <p class="text-muted">
                    {{ __('Please select a bank statement from the dropdown above to start reconciliation.') }}
                </p>
                @if($bankStatements->count() == 0)
                    <a href="{{ route('report.bank.statement.reconciliation') }}" class="btn btn-primary mt-3">
                        <i class="ti ti-upload me-1"></i> {{ __('Upload Bank Statement') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('script-page')
<script>
$(document).ready(function() {
    // Submit form when statement changes
    $('#statement_id').on('change', function() {
        if ($(this).val()) {
            $('#reconciliation_form').submit();
        }
    });
    
    // Auto-submit when account filter changes
    $('select[name="account_id"]').on('change', function() {
        $('#reconciliation_form').submit();
    });
    
    // Highlight rows on hover
    $('.table tbody tr').hover(
        function() { $(this).addClass('table-active'); },
        function() { $(this).removeClass('table-active'); }
    );
});
</script>
@endpush