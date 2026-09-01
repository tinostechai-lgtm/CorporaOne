@extends('layouts.admin')

@section('page-title')
    {{ __('Upload Bank Statement') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('bank-statement.index') }}">{{ __('Bank Statements') }}</a></li>
    <li class="breadcrumb-item">{{ __('Upload') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                {{ Form::open(['route' => 'bank-statement.store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                <div class="row">
                    <div class="form-group col-md-12">
                        {{ Form::label('file', __('Bank Statement File'), ['class' => 'form-label']) }}
                        {{ Form::file('file', ['class' => 'form-control', 'required' => true, 'accept' => '.pdf,.png,.jpg,.jpeg']) }}
                        <small class="text-muted">{{ __('Supported formats: PDF, PNG, JPG, JPEG. Max size: 10MB') }}</small>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i>
                    {{ __('The system will automatically extract bank details including account number, IFSC code, bank name, and transaction history from the uploaded statement.') }}
                </div>

                <div class="modal-footer">
                    <a href="{{ route('bank-statement.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    {{ Form::submit(__('Upload & Process'), ['class' => 'btn btn-primary']) }}
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection