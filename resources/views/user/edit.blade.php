@extends('layouts.admin')

@section('page-title', __('Edit User'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('Users') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Edit User') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required></x-required>
                                    {{ Form::text('name', null, ['class' => 'form-control font-style', 'placeholder' => __('Enter User Name'), 'required' => 'required']) }}
                                    @error('name')
                                        <small class="invalid-name" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}<x-required></x-required>
                                    {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('Enter User Email'), 'required' => 'required']) }}
                                    @error('email')
                                        <small class="invalid-email" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </small>
                                    @enderror
                                </div>
                            </div>

                            <!-- ====== PHONE NUMBER FIELD ====== -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('phone', __('Phone Number'), ['class' => 'form-label']) }}
                                    {{ Form::text('phone', null, ['class' => 'form-control', 'placeholder' => __('Enter phone number (optional)')]) }}
                                    @error('phone')
                                        <small class="invalid-phone" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </small>
                                    @enderror
                                </div>
                            </div>

                            @if(\Auth::user()->type != 'super admin')
                                <div class="form-group col-md-12">
                                    {{ Form::label('role', __('User Role'), ['class' => 'form-label']) }}<x-required></x-required>
                                    {!! Form::select('role', $roles, $user->roles, ['class' => 'form-control select', 'required' => 'required']) !!}
                                    @error('role')
                                        <small class="invalid-role" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </small>
                                    @enderror
                                </div>
                            @endif

                            @if(!$customFields->isEmpty())
                                @include('customFields.formBuilder')
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        {{ Form::submit(__('Update'), ['class' => 'btn btn-primary']) }}
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <!-- If any additional CSS for select2 or others -->
@endpush

@push('js')
    <script>
        // Any existing JS for the edit form
    </script>
@endpush