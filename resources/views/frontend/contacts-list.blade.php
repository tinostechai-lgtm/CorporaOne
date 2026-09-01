@extends('layouts.admin')

@section('page-title')
    {{ __('Contact Lists') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Contact Lists') }}</li>
@endsection

@php
    $settings = \App\Models\Utility::settings(); // Assuming you have some settings utility
    $logo = \App\Models\Utility::get_file('uploads/landing_page_image');
    $contacts = \App\Models\Contact::latest()->paginate(10); // Fetch contacts
@endphp

@push('css-page')
    <link rel="stylesheet" href="{{ asset('Modules/LandingPage/Resources/assets/css/summernote/summernote-bs4.css') }}" />
@endpush

@push('script-page')
    <script src="{{ asset('Modules/LandingPage/Resources/assets/js/plugins/summernote-bs4.js') }}"></script>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            @include('landingpage::layouts.tab') <!-- Adjust this include if needed -->
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    {{-- Start for Contact Lists Settings --}}
                    <div class="card">
                        {{ Form::open(['route' => 'contact.store', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
                        @csrf
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <h5 class="mb-2">{{ __('Contact Settings') }}</h5>
                                </div>
                                <div class="col switch-width text-end">
                                    <div class="form-group mb-0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" data-toggle="switchbutton" data-onstyle="primary" class="" name="contact_status"
                                                id="contact_status" {{ $settings['contact_status'] ?? 'on' == 'on' ? 'checked="checked"' : '' }}>
                                            <label class="custom-control-label" for="contact_status"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('Title', __('Title'), ['class' => 'form-label']) }}<x-required></x-required>
                                        {{ Form::text('contact_title', $settings['contact_title'] ?? '', ['class' => 'form-control', 'placeholder' => __('Enter Title'), 'required' => 'required']) }}
                                        @error('contact_title')
                                            <span class="invalid-contact_title" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('Heading', __('Heading'), ['class' => 'form-label']) }}<x-required></x-required>
                                        {{ Form::text('contact_heading', $settings['contact_heading'] ?? '', ['class' => 'form-control', 'placeholder' => __('Enter Heading'), 'required' => 'required']) }}
                                        @error('contact_heading')
                                            <span class="invalid-contact_heading" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{ Form::label('Description', __('Description'), ['class' => 'form-label']) }}
                                        {{ Form::textarea('contact_description', $settings['contact_description'] ?? '', ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
                                        @error('contact_description')
                                            <span class="invalid-contact_description" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-print-invoice btn-primary m-r-10" type="submit">{{ __('Save Changes') }}</button>
                        </div>
                        {{ Form::close() }}
                    </div>

                    {{-- Contact Lists Table --}}
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-lg-9 col-md-9 col-sm-9">
                                    <h5>{{ __('Contact Lists') }}</h5>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-3 justify-content-end d-flex">
                                    <a href="{{ route('frontend.contact') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Add New Contact') }}">
                                        <i class="ti ti-plus text-light"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('No') }}</th>
                                            <th>{{ __('First Name') }}</th>
                                            <th>{{ __('Last Name') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Phone') }}</th>
                                            <th>{{ __('Message') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($contacts->count())
                                            @php $no = 1 @endphp
                                            @foreach ($contacts as $contact)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $contact->first_name }}</td>
                                                    <td>{{ $contact->last_name }}</td>
                                                    <td>{{ $contact->email }}</td>
                                                    <td>{{ $contact->phone }}</td>
                                                    <td>{{ Str::limit($contact->message, 50) }}</td>
                                                    <td>
                                                        <span>
                                                            <div class="action-btn me-2">
                                                                <a href="#" class="btn btn-sm align-items-center bg-info" 
                                                                   data-url="{{ route('contact.update', $contact->id) }}" 
                                                                   data-ajax-popup="true" 
                                                                   data-title="{{ __('Edit Contact') }}" 
                                                                   title="{{ __('Edit') }}" 
                                                                   data-size="lg" 
                                                                   data-bs-toggle="tooltip">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                            <div class="action-btn">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['contact.destroy', $contact->id], 'id' => 'delete-form-' . $contact->id]) !!}
                                                                    <a href="#" class="btn btn-sm align-items-center bs-pass-para bg-danger" 
                                                                       data-bs-toggle="tooltip" 
                                                                       title="{{ __('Delete') }}" 
                                                                       data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}" 
                                                                       data-confirm-yes="document.getElementById('delete-form-{{$contact->id}}').submit();">
                                                                        <i class="ti ti-trash text-white"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="7" class="text-center">{{ __('No contacts found.') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            {{ $contacts->links() }} <!-- Pagination links -->
                        </div>
                    </div>
                    {{-- End for Contact Lists --}}
                </div>
            </div>
        </div>
    </div>
@endsection