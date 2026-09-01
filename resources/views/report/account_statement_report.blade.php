{{-- resources/views/report/account-statement-report.blade.php --}}

@extends('layouts.admin')

@section('page-title')
    {{ __('Account Statement Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Account Statement') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Account Statement Report') }}</h5>
                <small>View all transactions with ability to drill down into account details</small>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('report.account.statement') }}" class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Start Month') }}</label>
                        <input type="month" name="start_month" class="form-control" value="{{ request('start_month', $startMonth ?? now()->subMonths(5)->format('Y-m')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('End Month') }}</label>
                        <input type="month" name="end_month" class="form-control" value="{{ request('end_month', $endMonth ?? now()->format('Y-m')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Transaction Type') }}</label>
                        <select name="type" class="form-control">
                            <option value="all" {{ request('type', $type ?? 'all') == 'all' ? 'selected' : '' }}>{{ __('All Transactions') }}</option>
                            <option value="debit" {{ request('type', $type ?? 'all') == 'debit' ? 'selected' : '' }}>{{ __('Debit Only') }}</option>
                            <option value="credit" {{ request('type', $type ?? 'all') == 'credit' ? 'selected' : '' }}>{{ __('Credit Only') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-search"></i> {{ __('Filter') }}
                        </button>
                    </div>
                </form>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3>{{ $transactions->total() }}</h3>
                                <small>Total Transactions</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h3>${{ number_format($totalDebit, 2) }}</h3>
                                <small>Total Debit</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3>${{ number_format($totalCredit, 2) }}</h3>
                                <small>Total Credit</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3>${{ number_format($balance, 2) }}</h3>
                                <small>Net Balance</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transactions Table with Clickable Account Names -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="transactionsTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="12%">
                                    <a href="?sort_by=date&sort_order={{ request('sort_order') == 'asc' ? 'desc' : 'asc' }}" class="text-white text-decoration-none">
                                        Date
                                        @if(request('sort_by') == 'date')
                                            <i class="ti ti-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="25%">Description</th>
                                <th width="15%">
                                    <a href="?sort_by=account&sort_order={{ request('sort_order') == 'asc' ? 'desc' : 'asc' }}" class="text-white text-decoration-none">
                                        Account
                                        @if(request('sort_by') == 'account')
                                            <i class="ti ti-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="10%">
                                    <a href="?sort_by=debit&sort_order={{ request('sort_order') == 'asc' ? 'desc' : 'asc' }}" class="text-white text-decoration-none">
                                        Debit
                                        @if(request('sort_by') == 'debit')
                                            <i class="ti ti-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="10%">
                                    <a href="?sort_by=credit&sort_order={{ request('sort_order') == 'asc' ? 'desc' : 'asc' }}" class="text-white text-decoration-none">
                                        Credit
                                        @if(request('sort_by') == 'credit')
                                            <i class="ti ti-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="10%">
                                    <a href="?sort_by=amount&sort_order={{ request('sort_order') == 'asc' ? 'desc' : 'asc' }}" class="text-white text-decoration-none">
                                        Amount
                                        @if(request('sort_by') == 'amount')
                                            <i class="ti ti-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="13%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $index => $transaction)
                            <tr class="transaction-row">
                                <td>{{ $transactions->firstItem() + $index }}</td>
                                <td>{{ \Auth::user()->dateFormat($transaction->date) }}</td>
                                <td>
                                    <strong>{{ $transaction->description }}</strong>
                                    @if($transaction->reference)
                                        <br><small class="text-muted">Ref: {{ $transaction->reference }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->account)
                                        <a href="{{ route('account.transactions', $transaction->account_id) }}?start_date={{ date('Y-m-01', strtotime($transaction->date)) }}&end_date={{ date('Y-m-t', strtotime($transaction->date)) }}" 
                                           class="text-primary text-decoration-none fw-bold"
                                           target="_blank">
                                            <i class="ti ti-building-bank"></i> 
                                            {{ $transaction->account->holder_name ?? $transaction->account->name ?? 'N/A' }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                 </td>
                                <td class="text-end text-danger">
                                    @if($transaction->debit > 0)
                                        ${{ number_format($transaction->debit, 2) }}
                                    @else
                                        -
                                    @endif
                                 </td>
                                <td class="text-end text-success">
                                    @if($transaction->credit > 0)
                                        ${{ number_format($transaction->credit, 2) }}
                                    @else
                                        -
                                    @endif
                                 </td>
                                <td class="text-end fw-bold">
                                    ${{ number_format($transaction->debit + $transaction->credit, 2) }}
                                 </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('transaction.show', $transaction->id) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="View Details">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <a href="{{ route('bank-reconciliation.compare-with-ledger', ['ledger_id' => $transaction->account_id, 'start_date' => date('Y-m-01', strtotime($transaction->date)), 'end_date' => date('Y-m-t', strtotime($transaction->date))]) }}" 
                                           class="btn btn-sm btn-success"
                                           title="Reconcile"
                                           target="_blank">
                                            <i class="ti ti-balance-scale"></i>
                                        </a>
                                    </div>
                                 </td>
                             </tr>
                            @empty
                             <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="ti ti-inbox" style="font-size: 48px;"></i>
                                    <p class="mt-2">No transactions found for the selected period</p>
                                 </td>
                             </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="row mt-4">
                    <div class="col-12">
                        {{ $transactions->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable with sorting options
    $('#transactionsTable').DataTable({
        pageLength: 25,
        responsive: true,
        ordering: true,
        order: [[1, 'desc']], // Sort by date descending
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            emptyTable: "No transactions found"
        },
        columnDefs: [
            { orderable: false, targets: [7] } // Disable sorting on actions column
        ]
    });
});
</script>
@endpush

@endsection