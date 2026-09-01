@extends('layouts.admin')

@section('page-title')
    {{ __('Account Statement Summary') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('report.account.statement') }}">{{ __('Account Statement') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Statement Report') }}</li>
@endsection

@push('css')
<style>
    .account-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border: 1px solid #e3e6f0;
        border-radius: 10px;
    }
    .account-card:hover {
        background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .table a {
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
    }
    .table a:hover {
        text-decoration: underline !important;
        opacity: 0.8;
    }
    .filter-badge {
        background-color: #4e73df;
        color: white;
        padding: 8px 15px;
        border-radius: 25px;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .filter-badge a {
        color: white;
        text-decoration: none;
        font-weight: bold;
    }
    .filter-badge a:hover {
        opacity: 0.8;
    }
    .clickable-link {
        cursor: pointer;
    }
    .non-clickable {
        color: #6c757d;
    }
    .active-filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
    }
    .active-filter-card .text-muted {
        color: rgba(255,255,255,0.7) !important;
    }
    .active-filter-card h6, 
    .active-filter-card h5 {
        color: white !important;
    }
</style>
@endpush

@section('content')
<div class="row">
    {{-- Filter Form --}}
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Account Statement Report') }}</h5>
                <small>View all transactions with ability to drill down into account details</small>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('report.account.statement') }}" class="row g-3" id="filterForm">
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Start Month') }}</label>
                        <input type="month" name="start_month" class="form-control" value="{{ request('start_month', now()->subMonths(5)->format('Y-m')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('End Month') }}</label>
                        <input type="month" name="end_month" class="form-control" value="{{ request('end_month', now()->format('Y-m')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Transaction Type') }}</label>
                        <select name="type" class="form-control">
                            <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>{{ __('All Transactions') }}</option>
                            <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>{{ __('Debit Only') }}</option>
                            <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>{{ __('Credit Only') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-search"></i> {{ __('Apply Filter') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Active Filter Indicator --}}
    @if(request('account_id'))
    <div class="col-12 mb-3">
        <div class="filter-badge">
            <i class="ti ti-filter"></i> 
            <strong>Currently filtering by account:</strong> {{ request('account_name', $reportData['filtered_account_name'] ?? 'Selected Account') }}
            <a href="#" onclick="clearAccountFilter()" class="ms-2">
                <i class="ti ti-x"></i> Clear Filter
            </a>
        </div>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $reportData['summary']['total_transactions'] ?? 0 }}</h3>
                        <small class="text-white-50">Total Transactions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ \Auth::user()->priceFormat($reportData['summary']['total_debit'] ?? 0) }}</h3>
                        <small class="text-white-50">Total Debit</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ \Auth::user()->priceFormat($reportData['summary']['total_credit'] ?? 0) }}</h3>
                        <small class="text-white-50">Total Credit</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ \Auth::user()->priceFormat(($reportData['summary']['net_change'] ?? 0)) }}</h3>
                        <small class="text-white-50">Net Balance</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Account Balance Cards --}}
    @if(($reportData['revenueAccounts'] ?? collect())->count() > 0 || ($reportData['paymentAccounts'] ?? collect())->count() > 0)
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h6>{{ __('Account Balances') }}</h6>
                <small class="text-muted">Click on any account card to view its transactions</small>
            </div>
            <div class="card-body">
                <div class="row">
                    @if(($reportData['revenueAccounts'] ?? collect())->count() > 0)
                        @foreach($reportData['revenueAccounts'] as $account)
                            <div class="col-xl-3 col-md-6 col-lg-3 mb-3">
                                <div class="card account-card {{ request('account_id') == $account->id ? 'active-filter-card' : '' }}" 
                                     onclick="window.location.href='{{ route('report.account.transactions', $account->id) }}?start_date={{ request('start_month', now()->subMonths(5)->format('Y-m-01')) }}&end_date={{ request('end_month', now()->format('Y-m-t')) }}'">
                                    <div class="card-body">
                                        <small class="text-muted text-uppercase {{ request('account_id') == $account->id ? 'text-white-50' : '' }}">{{ __('Revenue Account') }}</small>
                                        <h6 class="mb-1 {{ request('account_id') == $account->id ? 'text-white' : 'text-primary' }}">
                                            <i class="ti ti-building-bank"></i> 
                                            {{ $account->holder_name ?? $account->name ?? 'Account #' . $account->id }}
                                        </h6>
                                        <h5 class="mt-2 mb-0 {{ request('account_id') == $account->id ? 'text-white' : 'text-success' }}">
                                            {{ \Auth::user()->priceFormat($account->total ?? 0) }}
                                        </h5>
                                        @if(request('account_id') == $account->id)
                                            <small class="text-white-50"><i class="ti ti-check"></i> Currently viewing</small>
                                        @else
                                            <small class="text-muted"><i class="ti ti-click"></i> Click to view →</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    @if(($reportData['paymentAccounts'] ?? collect())->count() > 0)
                        @foreach($reportData['paymentAccounts'] as $account)
                            <div class="col-xl-3 col-md-6 col-lg-3 mb-3">
                                <div class="card account-card {{ request('account_id') == $account->id ? 'active-filter-card' : '' }}" 
                                     onclick="window.location.href='{{ route('report.account.transactions', $account->id) }}?start_date={{ request('start_month', now()->subMonths(5)->format('Y-m-01')) }}&end_date={{ request('end_month', now()->format('Y-m-t')) }}'">
                                    <div class="card-body">
                                        <small class="text-muted text-uppercase {{ request('account_id') == $account->id ? 'text-white-50' : '' }}">{{ __('Payment Account') }}</small>
                                        <h6 class="mb-1 {{ request('account_id') == $account->id ? 'text-white' : 'text-primary' }}">
                                            <i class="ti ti-building-bank"></i> 
                                            {{ $account->holder_name ?? $account->name ?? 'Account #' . $account->id }}
                                        </h6>
                                        <h5 class="mt-2 mb-0 {{ request('account_id') == $account->id ? 'text-white' : 'text-danger' }}">
                                            {{ \Auth::user()->priceFormat($account->total ?? 0) }}
                                        </h5>
                                        @if(request('account_id') == $account->id)
                                            <small class="text-white-50"><i class="ti ti-check"></i> Currently viewing</small>
                                        @else
                                            <small class="text-muted"><i class="ti ti-click"></i> Click to view →</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Transactions Table --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6>
                    {{ __('Transaction Details') }}
                    @if(request('account_id'))
                        <span class="badge bg-primary ms-2">
                            <i class="ti ti-building-bank"></i> 
                            Showing transactions for: {{ request('account_name', $reportData['filtered_account_name'] ?? 'Selected Account') }}
                        </span>
                    @endif
                </h6>
                <small>Click on any blue account name to view all transactions for that account</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="transactionsTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="12%">Date</th>
                                <th width="22%">Account Name</th>
                                <th width="25%">Description</th>
                                <th width="8%">Reference</th>
                                <th width="10%" class="text-end">Debit</th>
                                <th width="10%" class="text-end">Credit</th>
                                <th width="8%" class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($reportData['ledgerTransactions'] ?? []) as $index => $transaction)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Auth::user()->dateFormat($transaction->date) }}</td>
                                <td>
                                    @if($transaction->account_id && $transaction->account_id > 0)
                                        <a href="{{ route('report.account.transactions', $transaction->account_id) }}?start_date={{ date('Y-m-01', strtotime($transaction->date)) }}&end_date={{ date('Y-m-t', strtotime($transaction->date)) }}" 
                                           class="text-primary fw-bold" 
                                           title="Click to see all transactions for this account">
                                            <i class="ti ti-building-bank"></i> 
                                            {{ $transaction->account_name }}
                                        </a>
                                    @else
                                        <span class="text-muted" title="No account associated with this transaction">
                                            <i class="ti ti-info-circle"></i> 
                                            {{ $transaction->account_name }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $transaction->description ?? '-' }}</td>
                                <td>{{ $transaction->reference ?? '-' }}</td>
                                <td class="text-end text-danger">{{ $transaction->debit > 0 ? \Auth::user()->priceFormat($transaction->debit) : '-' }}</td>
                                <td class="text-end text-success">{{ $transaction->credit > 0 ? \Auth::user()->priceFormat($transaction->credit) : '-' }}</td>
                                <td class="text-end fw-bold {{ $transaction->running_balance >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ \Auth::user()->priceFormat($transaction->running_balance) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="ti ti-inbox" style="font-size: 48px;"></i>
                                    <p class="mt-2 mb-0">No transactions found for the selected period</p>
                                    @if(request('account_id'))
                                        <small class="text-muted">Try clearing the account filter or changing your date range</small>
                                    @else
                                        <small class="text-muted">Try changing your filter criteria</small>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(($reportData['ledgerTransactions'] ?? collect())->count() > 0)
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="5" class="text-end h6">Totals:</th>
                                <th class="text-end text-danger h6">{{ \Auth::user()->priceFormat($reportData['summary']['total_debit'] ?? 0) }}</th>
                                <th class="text-end text-success h6">{{ \Auth::user()->priceFormat($reportData['summary']['total_credit'] ?? 0) }}</th>
                                <th class="text-end h6">{{ \Auth::user()->priceFormat($reportData['summary']['end_balance'] ?? 0) }}</th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
function clearAccountFilter() {
    var currentUrl = new URL(window.location.href);
    var params = new URLSearchParams(currentUrl.search);
    
    // Remove account filters
    params.delete('account_id');
    params.delete('account_name');
    
    // Keep other filters
    var startMonth = $('#filterForm input[name="start_month"]').val();
    var endMonth = $('#filterForm input[name="end_month"]').val();
    var type = $('#filterForm select[name="type"]').val();
    
    var newParams = new URLSearchParams();
    if (startMonth) newParams.set('start_month', startMonth);
    if (endMonth) newParams.set('end_month', endMonth);
    if (type && type !== 'all') newParams.set('type', type);
    
    // Build new URL
    var newUrl = window.location.pathname;
    if (newParams.toString()) {
        newUrl = window.location.pathname + '?' + newParams.toString();
    }
    
    window.location.href = newUrl;
}

$(document).ready(function() {
    // Initialize DataTable
    $('#transactionsTable').DataTable({
        pageLength: 25,
        responsive: true,
        order: [[1, 'desc']],
        language: {
            search: "<i class='ti ti-search'></i> Search:",
            searchPlaceholder: "Search transactions...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            emptyTable: "No transactions found"
        },
        paging: true,
        searching: true,
        ordering: true
    });
});
</script>
@endpush
@endsection