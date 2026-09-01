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
        
        // Function to filter by account when clicking on account name
        function filterByAccount(accountId) {
            if (accountId) {
                var url = new URL(window.location.href);
                url.searchParams.set('account', accountId);
                window.location.href = url.toString();
            }
        }
        
        // Bank Statement Upload and Comparison Functions
        $(document).ready(function() {
            // Handle file upload
            $('#upload_form').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                
                $('#upload_btn').prop('disabled', true);
                $('#upload_btn').html('<i class="ti ti-loader ti-spin"></i> {{ __('Uploading...') }}');
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#uploadModal').modal('hide');
                            toastr.success('Bank statement uploaded successfully');
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            toastr.error(response.error || 'Upload failed');
                            $('#upload_btn').prop('disabled', false);
                            $('#upload_btn').html('<i class="ti ti-upload"></i> {{ __('Upload') }}');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Upload failed: ' + (xhr.responseJSON?.error || 'Unknown error'));
                        $('#upload_btn').prop('disabled', false);
                        $('#upload_btn').html('<i class="ti ti-upload"></i> {{ __('Upload') }}');
                    }
                });
            });
            
            // Submit form when statement changes
            $('#statement_id').on('change', function() {
                if ($(this).val()) {
                    $('#comparison_form').submit();
                }
            });
        });
        
        // Load comparison data
        function loadComparison(statementId) {
            $('#comparisonModal').modal('show');
            $('#comparison_loading').show();
            $('#comparison_content').hide();
            
            $.ajax({
                url: '{{ route("bank-reconciliation.compare-with-ledger") }}',
                type: 'GET',
                data: {
                    submission_id: statementId,
                    ledger_id: {{ request('account', 0) }},
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val()
                },
                success: function(response) {
                    $('#comparison_loading').hide();
                    $('#comparison_content').html(response).show();
                },
                error: function() {
                    $('#comparison_loading').hide();
                    $('#comparison_content').html('<div class="alert alert-danger text-center">{{ __("Failed to load comparison data") }}</div>').show();
                }
            });
        }
        
        // Show comparison modal and load statements
        function showComparisonModal() {
            $('#comparisonModal').modal('show');
            $('#comparison_loading').show();
            $('#comparison_content').hide();
            
            // Load recent statements dropdown
            $.ajax({
                url: '{{ route("bank-reconciliation.recent-submissions") }}',
                type: 'GET',
                success: function(statements) {
                    var select = $('#statement_id');
                    select.empty();
                    select.append('<option value="">{{ __("Select Statement") }}</option>');
                    $.each(statements, function(index, stmt) {
                        select.append('<option value="' + stmt.id + '">' + stmt.name + '</option>');
                    });
                    $('#comparison_loading').hide();
                    $('#statement_select_div').show();
                },
                error: function() {
                    $('#comparison_loading').hide();
                    $('#comparison_content').html('<div class="alert alert-danger text-center">{{ __("No bank statements found. Please upload a statement first.") }}</div>').show();
                }
            });
        }
    </script>
@endpush

@section('action-btn')
    <div class="float-end">
        <button type="button" class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="ti ti-upload"></i> {{ __('Upload Statement') }}
        </button>
        @if(request('account'))
            <button type="button" class="btn btn-sm btn-info me-1" onclick="showComparisonModal()">
                <i class="ti ti-compare"></i> {{ __('Compare with Statement') }}
            </button>
        @endif
        <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()" data-bs-toggle="tooltip" title="{{ __('Download') }}">
            <i class="ti ti-download"></i>
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
                            <div class="col-xl-9">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'month-btn form-control', 'id' => 'start_date']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'month-btn form-control', 'id' => 'end_date']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('account', __('Account'), ['class' => 'form-label']) }}
                                            <select name="account" class="form-control select" onchange="this.form.submit()">
                                                <option value="">{{ __('All Accounts') }}</option>
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
                                        <a href="#" class="btn btn-sm btn-primary me-1" onclick="document.getElementById('report_ledger').submit(); return false;" data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                            <i class="ti ti-search"></i>
                                        </a>
                                        <a href="{{ route('report.ledger') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="{{ __('Reset') }}">
                                            <i class="ti ti-refresh"></i>
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
                                            {{-- Account Header with Clickable Link --}}
                                            <tr class="table-info fw-bold fs-5 border-bottom border-3">
                                                <td colspan="7">
                                                    <a href="#" onclick="filterByAccount('{{ $account->id }}')" 
                                                       style="color: #000; text-decoration: none; cursor: pointer;"
                                                       class="account-link"
                                                       data-account-id="{{ $account->id }}">
                                                        <i class="ti ti-building-bank me-2"></i>
                                                        {{ $account->code }} - {{ $account->name }}
                                                        <i class="ti ti-click ms-2" style="font-size: 12px; opacity: 0.7;"></i>
                                                    </a>
                                                    <span class="badge bg-primary ms-2" style="font-size: 10px;">
                                                        <i class="ti ti-filter"></i> {{ __('Click to filter') }}
                                                    </span>
                                                </td>
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
                                                <td colspan="4" class="text-end">{{ __('Account Totals') }} 
                                                    <a href="#" onclick="filterByAccount('{{ $account->id }}')" class="ms-2 text-primary" style="font-size: 12px;">
                                                        <i class="ti ti-filter"></i> {{ __('View Only This Account') }}
                                                    </a>
                                                </td>
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

    {{-- If only one account is selected, show a message and a link to view all --}}
    @if(request('account') && request('account') != '')
        <div class="row mt-3">
            <div class="col-12 text-center">
                <div class="alert alert-info d-inline-block">
                    <i class="ti ti-filter"></i> 
                    {{ __('Showing filtered results for selected account.') }}
                    <a href="{{ route('report.ledger') }}" class="ms-2 text-dark fw-bold">
                        <i class="ti ti-eye"></i> {{ __('View All Accounts') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Upload Modal (Simplified - No Account Fields) --}}
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">{{ __('Upload Bank Statement') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                {{ Form::open(['route' => 'bank-statement.upload.direct', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'upload_form']) }}
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::label('file', __('Bank Statement File'), ['class' => 'form-label']) }}
                                {{ Form::file('file', ['class' => 'form-control', 'required' => 'required', 'accept' => '.pdf,.jpg,.jpeg,.png']) }}
                                <small class="text-muted">{{ __('Upload PDF, JPG, or PNG (Max 10MB)') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="upload_btn">
                        <i class="ti ti-upload"></i> {{ __('Upload') }}
                    </button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    {{-- Comparison Modal --}}
    <div class="modal fade" id="comparisonModal" tabindex="-1" aria-labelledby="comparisonModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="comparisonModalLabel">{{ __('Compare with Bank Statement') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3" id="statement_select_div" style="display: none;">
                        <div class="col-md-12">
                            {{ Form::open(['route' => 'bank-reconciliation.compare-with-ledger', 'method' => 'GET', 'id' => 'comparison_form']) }}
                            <div class="row">
                                <div class="col-md-10">
                                    <div class="form-group">
                                        {{ Form::label('submission_id', __('Select Bank Statement'), ['class' => 'form-label']) }}
                                        <select name="submission_id" id="statement_id" class="form-control select" required>
                                            <option value="">{{ __('Select Statement') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary form-control">
                                            <i class="ti ti-compare"></i> {{ __('Compare') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="ledger_id" value="{{ request('account', 0) }}">
                            <input type="hidden" name="start_date" id="modal_start_date" value="{{ $filter['startDateRange'] ?? date('Y-m-01') }}">
                            <input type="hidden" name="end_date" id="modal_end_date" value="{{ $filter['endDateRange'] ?? date('Y-m-t') }}">
                            {{ Form::close() }}
                        </div>
                    </div>
                    <div id="comparison_loading" class="text-center py-5" style="display: none;">
                        <i class="ti ti-loader ti-spin fa-3x"></i>
                        <p class="mt-2">{{ __('Loading comparison data...') }}</p>
                    </div>
                    <div id="comparison_content"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css-page')
<style>
    .account-link {
        transition: all 0.3s ease;
        display: inline-block;
    }
    .account-link:hover {
        color: #007bff !important;
        text-decoration: underline !important;
        transform: translateX(5px);
    }
    .account-link:hover i {
        opacity: 1;
    }
    .table-info {
        cursor: pointer;
    }
    .table-info:hover {
        background-color: #e0e7ff !important;
    }
    .ti-spin {
        animation: ti-spin 2s infinite linear;
    }
    @keyframes ti-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush