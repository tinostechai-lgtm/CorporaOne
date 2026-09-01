@extends('layouts.admin')

@section('page-title', __('Bank Statement vs Ledger Comparison'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('bank-statement.index') }}">{{ __('Bank Statements') }}</a></li>
    <li class="breadcrumb-item">{{ __('Compare with Ledger') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        {{-- Filter Form --}}
        <div class="card mb-4">
            <div class="card-body">
                {{ Form::open(['route' => ['bank-statement.compare', $submission->id], 'method' => 'GET']) }}
                <div class="row">
                    <div class="col-md-3">
                        {{ Form::label('ledger_id', __('Ledger Account'), ['class' => 'form-label']) }}
                        {{ Form::select('ledger_id', $accounts->pluck('name', 'id'), $ledgerId, ['class' => 'form-control', 'placeholder' => __('Select Account')]) }}
                    </div>
                    <div class="col-md-3">
                        {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                        {{ Form::date('start_date', $startDate, ['class' => 'form-control']) }}
                    </div>
                    <div class="col-md-3">
                        {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                        {{ Form::date('end_date', $endDate, ['class' => 'form-control']) }}
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        {{ Form::submit(__('Filter & Compare'), ['class' => 'btn btn-primary w-100']) }}
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>

        @if($comparison)
        {{-- Results --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6>{{ __('Comparison Results') }}</h6>
                    <small class="text-muted">{{ $submission->bank_name }} - {{ $submission->account_number }}</small>
                </div>
                <div>
                    <a href="{{ route('bank-statement.exportComparison', $submission->id, ['ledger_id' => $ledgerId, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="btn btn-success btn-sm">
                        <i class="ti ti-file-export"></i> {{ __('Export CSV') }}
                    </a>
                    @if(isset($ledgerTransactions) && $ledgerTransactions->count() > 0)
                    <a href="{{ route('bank-statement.reconcile', $submission->id) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-checks"></i> {{ __('Reconcile') }}
                    </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                {{-- Summary Stats --}}
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5 class="mb-0">{{ $comparison['total_matched'] }}</h5>
                                <small>{{ __('Matched') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h5 class="mb-0">{{ count($comparison['unmatched_bank']) }}</h5>
                                <small>{{ __('Unmatched Bank') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5 class="mb-0">{{ count($comparison['unmatched_ledger']) }}</h5>
                                <small>{{ __('Unmatched Ledger') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5 class="mb-0">{{ $comparison['match_rate'] }}%</h5>
                                <small>{{ __('Match Rate') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Matched Transactions --}}
                @if(count($comparison['matched']) > 0)
                <h6>{{ __('Matched Transactions') }} ({{ count($comparison['matched']) }})</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Bank Date') }}</th>
                                <th>{{ __('Bank Description') }}</th>
                                <th class="text-end">{{ __('Bank Amount') }}</th>
                                <th>{{ __('Ledger Date') }}</th>
                                <th>{{ __('Ledger Description') }}</th>
                                <th class="text-end">{{ __('Ledger Amount') }}</th>
                                <th>{{ __('Score') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparison['matched'] as $match)
                            <tr class="table-{{ $match['match_type'] == 'exact' ? 'success' : ($match['match_type'] == 'high' ? 'info' : 'warning') }}">
                                <td><strong>{{ $match['bank_transaction']['date'] ?? 'N/A' }}</strong></td>
                                <td>{{ Str::limit($match['bank_transaction']['description'] ?? 'N/A', 40) }}</td>
                                <td class="text-end text-danger">
                                    {{ number_format(($match['bank_transaction']['debit'] ?? 0) + ($match['bank_transaction']['credit'] ?? 0), 2) }}
                                </td>
                                <td>{{ $match['ledger_transaction']->date ?? 'N/A' }}</td>
                                <td>{{ Str::limit($match['ledger_transaction']->description ?? $match['ledger_transaction']->reference ?? 'N/A', 40) }}</td>
                                <td class="text-end text-success">
                                    {{ number_format(($match['ledger_transaction']->debit ?? 0) + ($match['ledger_transaction']->credit ?? 0), 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $match['match_type'] == 'exact' ? 'success' : ($match['match_type'] == 'high' ? 'primary' : 'warning') }}">
                                        {{ $match['match_score'] }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- Unmatched Bank Transactions --}}
                @if(count($comparison['unmatched_bank']) > 0)
                <h6>{{ __('Unmatched Bank Transactions') }} ({{ count($comparison['unmatched_bank']) }})</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm table-danger">
                        <thead class="table-danger">
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th class="text-end">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparison['unmatched_bank'] as $tx)
                            <tr>
                                <td>{{ $tx['date'] ?? 'N/A' }}</td>
                                <td>{{ Str::limit($tx['description'] ?? 'N/A', 50) }}</td>
                                <td class="text-end">{{ number_format(($tx['debit'] ?? 0) + ($tx['credit'] ?? 0), 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- Unmatched Ledger Transactions --}}
                @if(count($comparison['unmatched_ledger']) > 0)
                <h6>{{ __('Unmatched Ledger Transactions') }} ({{ count($comparison['unmatched_ledger']) }})</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm table-warning">
                        <thead class="table-warning">
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th class="text-end">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparison['unmatched_ledger'] as $tx)
                            <tr>
                                <td>{{ $tx->date ?? 'N/A' }}</td>
                                <td>{{ Str::limit($tx->description ?? $tx->reference ?? 'N/A', 50) }}</td>
                                <td class="text-end">{{ number_format(($tx->debit ?? 0) + ($tx->credit ?? 0), 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

