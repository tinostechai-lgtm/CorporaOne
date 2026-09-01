@extends('layouts.admin')

@section('page-title')
    {{ __('Statement Comparison') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6>{{ __('Comparison Results') }}</h6>
                <small>{{ $statement->bank_name }} - {{ $statement->account_number }}</small>
            </div>
            <div class="card-body">
                {{-- Filter Form --}}
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label>{{ __('Start Date') }}</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-3">
                            <label>{{ __('End Date') }}</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-3">
                            <label>{{ __('Ledger Account') }}</label>
                            <select name="account_id" class="form-control">
                                <option value="">{{ __('All Accounts') }}</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ $accountId == $account->id ? 'selected' : '' }}>
                                        {{ $account->code }} - {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">{{ __('Compare') }}</button>
                        </div>
                    </div>
                </form>

                {{-- Summary Stats --}}
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="mb-0">{{ $comparison['total_matched'] }}</h5>
                                <small>{{ __('Matched Transactions') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="mb-0">{{ $comparison['total_unmatched_bank'] ?? count($comparison['unmatched_bank']) }}</h5>
                                <small>{{ __('Unmatched Bank') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="mb-0">{{ $comparison['total_unmatched_ledger'] ?? count($comparison['unmatched_ledger']) }}</h5>
                                <small>{{ __('Unmatched Ledger') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="mb-0">{{ $comparison['match_rate'] }}%</h5>
                                <small>{{ __('Match Rate') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Matched Transactions --}}
                <h6>{{ __('Matched Transactions') }}</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Bank Date') }}</th>
                                <th>{{ __('Bank Description') }}</th>
                                <th>{{ __('Bank Amount') }}</th>
                                <th>{{ __('Ledger Date') }}</th>
                                <th>{{ __('Ledger Description') }}</th>
                                <th>{{ __('Ledger Amount') }}</th>
                                <th>{{ __('Match Score') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparison['matched'] as $match)
                                <tr>
                                    <td>{{ $match['bank']['date'] ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($match['bank']['description'] ?? 'N/A', 50) }}</td>
                                    <td class="text-end">{{ number_format(($match['bank']['debit'] ?? 0) + ($match['bank']['credit'] ?? 0), 2) }}</td>
                                    <td>{{ $match['ledger']->date ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($match['ledger']->description ?? 'N/A', 50) }}</td>
                                    <td class="text-end">{{ number_format(($match['ledger']->debit ?? 0) + ($match['ledger']->credit ?? 0), 2) }}</td>
                                    <td><span class="badge bg-success">{{ $match['score'] }}%</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection