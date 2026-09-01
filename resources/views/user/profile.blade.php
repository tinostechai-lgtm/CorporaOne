@extends('layouts.admin')
@php
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
@endphp
@section('page-title')
    {{__('Profile Account')}}
@endsection
@push('script-page')
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300,
        });
        $(".list-group-item").click(function(){
            $('.list-group-item').filter(function(){
                return this.href == id;
            }).parent().removeClass('text-primary');
        });

        // Enhanced image upload handling with loading state and error handling
        document.getElementById('avatar').onchange = function () {
            // Check if file is selected
            if (!this.files || !this.files[0]) {
                return;
            }

            // Validate file type
            if (!this.files[0].type.match('image.*')) {
                alert('{{ __("Please select a valid image file (JPEG, PNG, JPG, GIF)") }}');
                this.value = ''; // Clear the invalid file
                return;
            }

            // Validate file size (client-side additional check)
            if (this.files[0].size > 2097152) { // 2MB in bytes
                alert('{{ __("Image size must be less than 2MB") }}');
                this.value = '';
                return;
            }

            // Create and show loading indicator
            var loader = document.createElement('div');
            loader.className = 'image-loading-spinner';
            loader.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
            var imageContainer = document.getElementById('image').parentNode;
            imageContainer.appendChild(loader);

            // Process the image
            var img = document.getElementById('image');
            var reader = new FileReader();

            reader.onload = function(e) {
                img.src = e.target.result;
                
                // Remove loader when image is loaded
                img.onload = function() {
                    loader.remove();
                };
                
                // Handle potential image loading errors
                img.onerror = function() {
                    loader.remove();
                    alert('{{ __("Error loading image. Please try another file.") }}');
                    document.getElementById('avatar').value = '';
                };
            };

            reader.onerror = function() {
                loader.remove();
                alert('{{ __("Error reading file. Please try again.") }}');
                document.getElementById('avatar').value = '';
            };

            reader.readAsDataURL(this.files[0]);
        };
    </script>

    <style>
        .image-loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
        }
        .theme-avtar-logo {
            position: relative;
        }
        .big-logo {
            max-width: 150px;
            max-height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }
        .profile_update {
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 4px;
            display: inline-block;
        }
    </style>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Profile')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-3">
            <div class="card sticky-top" style="top:30px">
                <div class="list-group list-group-flush" id="useradd-sidenav">
                    <a href="#personal_info" class="list-group-item list-group-item-action border-0">{{__('Personal Info')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                    <a href="#change_password" class="list-group-item list-group-item-action border-0">{{__('Change Password')}}<div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                </div>
            </div>
        </div>
        <div class="col-xl-9">
            <div id="personal_info" class="card">
                <div class="card-header">
                    <h5>{{__('Personal Info')}}</h5>
                </div>
                <div class="card-body">
                    {{Form::model($userDetail,array('route' => array('update.account'), 'method' => 'post', 'enctype' => "multipart/form-data"))}}
                        @csrf
                        <div class="row">
                            <div class="col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <label class="col-form-label text-dark">{{__('Name')}}</label>
                                    <input class="form-control" name="name" type="text" id="name" placeholder="{{ __('Enter Your Name') }}" value="{{ $userDetail->name }}" required autocomplete="name">
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <label for="email" class="col-form-label text-dark">{{__('Email')}}</label>
                                    <input class="form-control" name="email" type="email" id="email" placeholder="{{ __('Enter Your Email Address') }}" value="{{ $userDetail->email }}" required autocomplete="email">
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6">
                                <div class="form-group">
                                    <div class="theme-avtar-logo mt-4">
                                        {{-- ✅ FIXED: Use direct asset path for profile image --}}
                                        <img id="image" 
                                             src="{{ asset('uploads/avatar/' . ($userDetail->avatar ?? 'avatar.png')) }}" 
                                             class="big-logo">
                                    </div>
                                    <div class="choose-files mt-3">
                                        <label for="avatar">
                                            <div class="bg-primary profile_update"> <i class="ti ti-upload px-1"></i>{{__('Choose file here')}}</div>
                                            <input type="file" class="form-control file file-validate" name="profile" id="avatar" data-filename="profile_update" accept="image/jpeg,image/png,image/jpg,image/gif">
                                            <p id="file-error" class="file-error text-danger d-none"></p>
                                        </label>
                                    </div>
                                    <span class="text-xs text-muted">{{ __('Please upload a valid image file (JPEG, PNG, JPG, GIF) with size less than 2MB.')}}</span>
                                </div>
                            </div>
                            <div class="col-lg-12 text-end">
                                <input type="submit" value="{{__('Save Changes')}}" class="btn btn-print-invoice btn-primary m-r-10">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div id="change_password" class="card">
                <div class="card-header">
                    <h5>{{__('Change Password')}}</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="{{route('update.password')}}">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6 col-sm-6 form-group">
                                <label for="old_password" class="col-form-label text-dark">{{ __('Old Password') }}</label>
                                <input class="form-control" name="old_password" type="password" id="old_password" required autocomplete="old_password" placeholder="{{ __('Enter Old Password') }}">
                            </div>
                            <div class="col-lg-6 col-sm-6 form-group">
                                <label for="password" class="col-form-label text-dark">{{ __('New Password') }}</label>
                                <input class="form-control" name="password" type="password" required autocomplete="new-password" id="password" placeholder="{{ __('Enter Your New Password') }}">
                            </div>
                            <div class="col-lg-6 col-sm-6 form-group">
                                <label for="password_confirmation" class="col-form-label text-dark">{{ __('New Confirm Password') }}</label>
                                <input class="form-control" name="password_confirmation" type="password" required autocomplete="new-password" id="password_confirmation" placeholder="{{ __('Enter Your Confirm Password') }}">
                            </div>
                            <div class="col-lg-12 text-end">
                                <input type="submit" value="{{__('Change Password')}}" class="btn btn-print-invoice btn-primary m-r-10">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection