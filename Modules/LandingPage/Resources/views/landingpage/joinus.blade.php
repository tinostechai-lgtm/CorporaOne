@extends('layouts.admin')
@section('page-title')
    {{ __('Landing Page') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Landing Page') }}</li>
@endsection

@php
    $settings = \Modules\LandingPage\Entities\LandingPageSetting::settings();
    $logo = \App\Models\Utility::get_file('uploads/landing_page_image');
@endphp

@push('script-page')
    <script>
        document.getElementById('home_banner')?.onchange = function () {
            var src = URL.createObjectURL(this.files[0]);
            document.getElementById('image').src = src;
        }
        document.getElementById('home_logo')?.onchange = function () {
            var src = URL.createObjectURL(this.files[0]);
            document.getElementById('image1').src = src;
        }
    </script>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            @include('landingpage::layouts.tab')
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    {{ Form::model(null, ['route' => 'join_us.store', 'method' => 'POST', 'class' => 'needs-validation', 'novalidate']) }}
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <h5 class="mb-2">{{ __('Join Us Settings') }}</h5>
                                </div>
                                <div class="col switch-width text-end">
                                    <div class="form-group mb-0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" data-toggle="switchbutton" data-onstyle="primary" class="" name="joinus_status"
                                                id="joinus_status" {{ $settings['joinus_status'] == 'on' ? 'checked="checked"' : '' }}>
                                            <label class="custom-control-label" for="joinus_status"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('Heading', __('Heading'), ['class' => 'form-label']) }}<x-required></x-required>
                                        {{ Form::text('joinus_heading', $settings['joinus_heading'], ['class' => 'form-control', 'placeholder' => __('Enter Heading'), 'required' => 'required']) }}
                                        @error('joinus_heading')
                                            <span class="invalid-feedback text-danger" role="alert">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('Description', __('Description'), ['class' => 'form-label']) }}
                                        {{ Form::text('joinus_description', $settings['joinus_description'], ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
                                        @error('joinus_description')
                                            <span class="invalid-feedback text-danger" role="alert">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <input class="btn btn-print-invoice btn-primary m-r-10" type="submit" value="{{ __('Save Changes') }}">
                        </div>
                    </div>
                    {{ Form::close() }}

                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-lg-9 col-md-9 col-sm-9">
                                    <h5>{{ __('Join Us Users') }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Phone') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Company Name') }}</th>
                                            <th>{{ __('Joined At') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (!empty($join_us) && (is_array($join_us) || is_object($join_us)))
                                            @foreach ($join_us as $value)
                                                <tr>
                                                    <td>{{ $value->name }}</td>
                                                    <td>{{ $value->phone }}</td>
                                                    <td>{{ $value->email }}</td>
                                                    <td>{{ $value->company_name }}</td>
                                                    <td>{{ $value->created_at->format('d M Y, H:i') }}</td>
                                                    <td>
                                                        <div class="action-btn me-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['join_us.destroy', $value->id], 'id' => 'delete-form-' . $value->id]) !!}
                                                                <a href="#" class="btn btn-sm align-items-center bs-pass-para bg-danger" data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action cannot be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="document.getElementById('delete-form-{{$value->id}}').submit();">
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="6" class="text-center">{{ __('No users found.') }}</td>
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
    </div>
@endsection