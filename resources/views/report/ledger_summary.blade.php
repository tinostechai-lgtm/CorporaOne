@extends('layouts.admin')
@section('page-title')
    {{ __('Ledger Summary') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Ledger Summary') }}</li>
@endsection

@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A2'
                }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
@endpush

@section('action-btn')
    <div class="float-end">
        <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()" data-bs-toggle="tooltip" title="{{ __('Download') }}" data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['report.ledger'], 'method' => 'GET', 'id' => 'report_ledger']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box"></div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'month-btn form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'month-btn form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('account', __('Account'), ['class' => 'form-label']) }}
                                            <select name="account" class="form-control select" required>
                                                @foreach($accountsForFilter as $account)
                                                    <option value="{{ $account['id'] }}" {{ (request('account') == $account['id']) ? 'selected' : '' }}>
                                                        {{ $account['code'] ? $account['code'].' - ' : '' }}{{ $account['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary me-1" onclick="document.getElementById('report_ledger').submit(); return false;" data-bs-toggle="tooltip" title="{{ __('Apply') }}" data-original-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('report.ledger') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="{{ __('Reset') }}" data-original-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-refresh text-white-off"></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="printableArea">
        <input type="hidden" value="{{ __('Ledger') . ' ' . 'Report of' . ' ' . $filter['startDateRange'] . ' to ' . $filter['endDateRange'] }}" id="filename">
        
        <div class="row mb-4">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>{{ __('Account Name') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Transaction Type') }}</th>
                                        <th>{{ __('Transaction Date') }}</th>
                                        <th class="text-end">{{ __('Debit') }}</th>
                                        <th class="text-end">{{ __('Credit') }}</th>
                                        <th class="text-end">{{ __('Balance') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($accountData as $accountDataItem)
                                        @php
                                            $account = $accountDataItem['account'];
                                            $transactions = collect($accountDataItem['transactions']);
                                        @endphp

                                        @if($transactions->count() > 0 || $accountDataItem['totals']['debit'] > 0 || $accountDataItem['totals']['credit'] > 0)
                                            {{-- Account Header --}}
                                            <tr class="table-info fw-bold fs-5 border-bottom border-3">
                                                <td colspan="7">{{ $account->code }} - {{ $account->name }}</td>
                                            </tr>

                                            {{-- Running Balance --}}
                                            @php
                                                $runningBalance = 0;
                                            @endphp

                                            @foreach($transactions as $transaction)
                                                @php
                                                    $debit = $transaction->debit ?? 0;
                                                    $credit = $transaction->credit ?? 0;
                                                    $net = $debit - $credit;
                                                    $runningBalance += $net;
                                                    $refType = $transaction->reference ?? 'Direct';
                                                    $refNumber = $transaction->ids ?? __('Manual Entry');
                                                    $userName = $transaction->user_name ?? __('System');
                                                @endphp

                                                <tr>
                                                    <td>&nbsp;</td>
                                                    <td>{{ $userName }}</td>
                                                    <td>{{ $refType }} #{{ $refNumber }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($transaction->date) }}</td>
                                                    <td class="text-end">{{ \Auth::user()->priceFormat($debit) }}</td>
                                                    <td class="text-end">{{ \Auth::user()->priceFormat($credit) }}</td>
                                                    <td class="text-end {{ $runningBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $runningBalance >= 0 ? \Auth::user()->priceFormat($runningBalance) : \Auth::user()->priceFormat(abs($runningBalance)) . ' (Dr)' }}
                                                    </td>
                                                </tr>
                                            @endforeach

                                            {{-- Account Totals --}}
                                            <tr class="table-warning fw-bold border-top border-2">
                                                <td colspan="4" class="text-end">{{ __('Account Totals') }}</td>
                                                <td class="text-end">{{ \Auth::user()->priceFormat($accountDataItem['totals']['debit']) }}</td>
                                                <td class="text-end">{{ \Auth::user()->priceFormat($accountDataItem['totals']['credit']) }}</td>
                                                <td class="text-end {{ $accountDataItem['totals']['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $accountDataItem['totals']['balance'] >= 0 ? \Auth::user()->priceFormat($accountDataItem['totals']['balance']) : \Auth::user()->priceFormat(abs($accountDataItem['totals']['balance'])) . ' (Dr)' }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach

                                    {{-- Grand Totals --}}
                                    @if(!empty($filter['totals']))
                                    <tr class="table-danger fw-bold fs-5 border-top border-3">
                                        <td colspan="4" class="text-end">{{ __('Grand Totals') }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($filter['totals']['debit']) }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($filter['totals']['credit']) }}</td>
                                        <td class="text-end {{ $filter['totals']['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $filter['totals']['balance'] >= 0 ? \Auth::user()->priceFormat($filter['totals']['balance']) : \Auth::user()->priceFormat(abs($filter['totals']['balance'])) . ' (Dr)' }}
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
