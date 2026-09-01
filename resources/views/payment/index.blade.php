@extends('layouts.admin')

@section('page-title')
    {{__('Manage Payments')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Payment')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create payment')
            <a href="#" data-url="{{ route('payment.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" data-size="lg" data-title="{{__('Create New Payment')}}" title="{{__('Create')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('payment.index'),'method' => 'GET','id'=>'payment_form')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('date', __('Date'),['class'=>'form-label']) }}
                                            {{ Form::date('date', isset($_GET['date'])?$_GET['date']:'', array('class' => 'form-control month-btn','id'=>'pc-daterangepicker-1', 'placeholder'=>__('Select Date'))) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('account', __('Account'),['class'=>'form-label']) }}
                                            {{ Form::select('account',$account,isset($_GET['account'])?$_GET['account']:'', array('class' => 'form-control select','id'=>'choices-multiple')) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('vender', __('Vendor'),['class'=>'form-label']) }}
                                            {{ Form::select('vender',$vender,isset($_GET['vender'])?$_GET['vender']:'', array('class' => 'form-control select','id'=>'choices-multiple1')) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('category', __('Category'),['class'=>'form-label']) }}
                                            {{ Form::select('category',$category,isset($_GET['category'])?$_GET['category']:'', array('class' => 'form-control select','id'=>'choices-multiple2')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="#" class="btn btn-sm btn-primary me-1" onclick="document.getElementById('payment_form').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('payment.index') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="{{ __('Reset') }}">
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Amount')}}</th>
                                <th>{{__('Account')}}</th>
                                <th>{{__('Vendor')}}</th>
                                <th>{{__('Category')}}</th>
                                <th>{{__('Reference')}}</th>
                                <th>{{__('Description')}}</th>
                                <th>{{__('Payment Receipt')}}</th>
                                @if(Gate::check('edit payment') || Gate::check('delete payment'))
                                    <th>{{__('Action')}}</th>
                                @endif
                             </thead>
                            <tbody>
                            @foreach ($payments as $payment)
                                <tr class="font-style">
                                     <td>{{ Auth::user()->dateFormat($payment->date)}}</td>
                                     <td>{{ Auth::user()->priceFormat($payment->amount)}}</td>
                                     <td>{{ !empty($payment->bankAccount)?$payment->bankAccount->bank_name.' '.$payment->bankAccount->holder_name:''}}</td>
                                     <td>{{ !empty($payment->vender)?$payment->vender->name:'-'}}</td>
                                     <td>{{ !empty($payment->category)?$payment->category->name:'-'}}</td>
                                     <td>{{ !empty($payment->reference)?$payment->reference:'-'}}</td>
                                     <td>{{ !empty($payment->description)?$payment->description:'-'}}</td>
                                     <td>
                                        @if(!empty($payment->add_receipt))
                                            @php
                                                // Get file path and check existence
                                                $filePath = public_path('uploads/payment/' . $payment->add_receipt);
                                                $fileUrl = asset('uploads/payment/' . $payment->add_receipt);
                                                $fileExists = file_exists($filePath);
                                                $fileExtension = strtolower(pathinfo($payment->add_receipt, PATHINFO_EXTENSION));
                                                $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            @endphp
                                            
                                            @if($fileExists)
                                                @if($isImage)
                                                    <!-- View button opens modal -->
                                                    <a href="#" class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#receiptModal-{{ $payment->id }}" title="{{__('View Receipt')}}">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <!-- Download button -->
                                                    <a href="{{ $fileUrl }}" class="btn btn-sm btn-success" download title="{{__('Download')}}">
                                                        <i class="ti ti-download"></i>
                                                    </a>
                                                    
                                                    <!-- Modal for viewing receipt -->
                                                    <div class="modal fade" id="receiptModal-{{ $payment->id }}" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">{{ __('Payment Receipt') }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body text-center">
                                                                    <img src="{{ $fileUrl }}" alt="{{ __('Receipt') }}" class="img-fluid" style="max-height: 500px;">
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <a href="{{ $fileUrl }}" class="btn btn-primary" download>
                                                                        <i class="ti ti-download"></i> {{ __('Download') }}
                                                                    </a>
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <!-- PDF or other file -->
                                                    <a href="{{ $fileUrl }}" class="btn btn-sm btn-primary me-1" target="_blank" title="{{__('View File')}}">
                                                        <i class="ti ti-file-pdf"></i>
                                                    </a>
                                                    <a href="{{ $fileUrl }}" class="btn btn-sm btn-success" download title="{{__('Download')}}">
                                                        <i class="ti ti-download"></i>
                                                    </a>
                                                @endif
                                            @else
                                                <span class="text-danger" title="File: {{ $payment->add_receipt }}, Path: {{ $filePath }}">{{ __('File not found') }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                     </td>
                                    
                                    @if(Gate::check('edit payment') || Gate::check('delete payment'))
                                        <td class="action">
                                            @can('edit payment')
                                                <div class="action-btn me-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bg-info" data-url="{{ route('payment.edit',$payment->id) }}" data-ajax-popup="true" data-title="{{__('Edit Payment')}}" data-size="lg" data-bs-toggle="tooltip" title="{{__('Edit')}}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete payment')
                                                <div class="action-btn">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['payment.destroy', $payment->id],'id'=>'delete-form-'.$payment->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$payment->id}}').submit();">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                         </td>
                                    @endif
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