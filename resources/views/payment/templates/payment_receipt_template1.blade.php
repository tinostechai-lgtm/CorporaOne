@extends('layouts.admin')
@section('page-title')
    {{__('Payment Receipt')}}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="invoice-title">
                                    <h5>{{ __('Payment Receipt') }}</h5>
                                    <small>{{ __('Receipt Date:') }} {{ Auth::user()->dateFormat($payment->date) }}</small>
                                </div>
                            </div>
                            <div class="col">
                                <small class="font-style">
                                    <strong>{{__('From Account')}} :</strong><br>
                                    @if($account)
                                        {{ $account->bank_name }} - {{ $account->holder_name }}<br>
                                    @else
                                        -
                                    @endif
                                </small>
                            </div>
                            <div class="col">
                                <div class="float-end mt-3">
                                    @if(isset($settings['payment_qr_display']) && $settings['payment_qr_display'] == 'on')
                                        {!! DNS2D::getBarcodeHTML(route('payment.pdf', \Illuminate\Support\Facades\Crypt::encrypt($payment->id)), "QRCODE",2,2) !!}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="font-bold mb-2">{{__('Payment Details')}}</div>
                                <div class="table-responsive mt-3">
                                    <table class="table mb-0 table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-dark" width="40">#</th>
                                                <th class="text-dark">{{__('Description')}}</th>
                                                <th class="text-dark">{{__('Amount')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $index => $item)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $item['name'] }}</td>
                                                    <td>{{ Auth::user()->priceFormat($item['price']) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2" class="text-end"><strong>{{ __('Total Amount') }}</strong></td>
                                                <td><strong>{{ Auth::user()->priceFormat($totalAmount) }}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>{{ __('Vendor Details') }}</strong><br>
                                                @if($vendor)
                                                    {{ $vendor->name }}<br>
                                                    @if($vendor->email){{ $vendor->email }}<br>@endif
                                                    @if($vendor->contact){{ $vendor->contact }}<br>@endif
                                                @else
                                                    -
                                                @endif
                                            </div>
                                            <div class="col-md-6 text-md-end">
                                                <strong>{{ __('Payment Reference') }}</strong><br>
                                                @if($payment->reference)
                                                    {{ $payment->reference }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($payment->description)
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <strong>{{ __('Description') }}</strong><br>
                                            {{ $payment->description }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection