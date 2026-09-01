@extends('layouts.admin')

@section('page-title')
    {{ __('Bank Statement Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('bank-statement.index') }}">{{ __('Bank Statements') }}</a></li>
    <li class="breadcrumb-item">{{ __('Details') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">{{ __('Extracted Information') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th>{{ __('Account Name') }}</th>
                                <td>{{ $submission->account_name ?? 'N/A' }}</td>
                                <td class="text-muted">Confidence: {{ ($submission->extraction_confidence['account_name'] ?? 0) * 100 }}%</td>
                            </tr>
                            <tr>
                                <th>{{ __('Account Number') }}</th>
                                <td>{{ $submission->account_number ?? 'N/A' }}</td>
                                <td class="text-muted">Confidence: {{ ($submission->extraction_confidence['account_number'] ?? 0) * 100 }}%</td>
                            </tr>
                            <tr>
                                <th>{{ __('IFSC Code') }}</th>
                                <td>{{ $submission->ifsc_code ?? 'N/A' }}</td>
                                <td class="text-muted">Confidence: {{ ($submission->extraction_confidence['ifsc_code'] ?? 0) * 100 }}%</td>
                            </tr>
                            <tr>
                                <th>{{ __('Bank Name') }}</th>
                                <td>{{ $submission->bank_name ?? 'N/A' }}</td>
                                <td class="text-muted">Confidence: {{ ($submission->extraction_confidence['bank_name'] ?? 0) * 100 }}%</td>
                            </tr>
                            <tr>
                                <th>{{ __('Branch') }}</th>
                                <td>{{ $submission->branch ?? 'N/A' }}</td>
                                <td class="text-muted">Confidence: {{ ($submission->extraction_confidence['branch'] ?? 0) * 100 }}%</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h6 class="mt-4">{{ __('Transactions') }} ({{ count($submission->transactions ?? []) }})</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('S.No') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Account Name') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Debit') }}</th>
                                <th>{{ __('Credit') }}</th>
                                <th>{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submission->transactions ?? [] as $transaction)
                            <tr>
                                <td>{{ $transaction['serial_number'] ?? '' }}</td>
                                <td>{{ $transaction['date'] ?? '' }}</td>
                                <td>{{ $transaction['account_name'] ?? '' }}</td>
                                <td>{{ $transaction['name'] ?? '' }}</td>
                                <td>{{ $transaction['type'] ?? '' }}</td>
                                <td class="text-danger">{{ $transaction['debit'] ?? '' }}</td>
                                <td class="text-success">{{ $transaction['credit'] ?? '' }}</td>
                                <td>{{ $transaction['balance'] ?? '' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">{{ __('No transactions found.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <a href="{{ route('bank-statement.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
                    <a href="{{ route('bank-statement.download', $submission->id) }}" class="btn btn-info">{{ __('Download Original File') }}</a>
                    <a href="{{ route('bank-reconciliation.compare', $submission->id) }}" class="btn btn-primary">{{ __('Reconcile with Ledger') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection