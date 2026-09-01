
@section('page-title')
    {{ __('Register') }}
@endsection
@php
    $settings = Utility::settings();
    $logo = \App\Models\Utility::get_file('uploads/logo');
    $setting = \Modules\LandingPage\Entities\LandingPageSetting::settings();

@endphp
@push('custom-scripts')
    @if ($settings['recaptcha_module'] == 'on')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush
@if ($settings['cust_darklayout'] == 'on')
    <style>
        .g-recaptcha {
            filter: invert(1) hue-rotate(180deg) !important;
        }
    </style>
@endif
@php
    $languages = App\Models\Utility::languages();
@endphp
@section('language-bar')
    <div class="lang-dropdown-only-desk">
        <li class="dropdown dash-h-item drp-language">
           
            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                @foreach ($languages as $code => $language)
                    <a href="{{ route('register', [$ref, $code]) }}" tabindex="0" class="dropdown-item ">
                        <span>{{ Str::ucfirst($language) }}</span>
                    </a>
                @endforeach
            </div>
        </li>
    </div>
@endsection

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <!--required meta tags-->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!--twitter og-->
    <meta name="twitter:site" content="@themetags">
    <meta name="twitter:creator" content="@themetags">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="CorporaOne">
    <meta name="twitter:description" content="CorporaOne is an AI-integrated Business Management System designed to revolutionize the way businesses operate.">
    <meta name="twitter:image" content="#">

    <!--facebook og-->
    <meta property="og:url" content="#">
    <meta name="twitter:title" content="CorporaOne">
    <meta property="og:description" content="CorporaOne is an AI-integrated Business Management System designed to revolutionize the way businesses operate.">
    <meta property="og:image" content="#">
    <meta property="og:image:secure_url" content="#">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="600">

    <!--meta-->
    <meta name="description" content="CorporaOne is an AI-integrated Business Management System designed to revolutionize the way businesses operate.">
    <meta name="author" content="ThemeTags">

    <!--favicon icon-->
    <link rel="icon" href="{{ asset('asset/img/19.png') }}" type="image/png" sizes="16x16">

    <!--title-->
    <title></title>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lily+Script+One&display=swap" rel="stylesheet">
    <!-- Font -->

    <!--build:css-->
    <link rel="stylesheet" href="{{ asset('asset/css/main.css') }}">
    <!-- endbuild -->

    <!--custom css start-->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <!--custom css end-->
</head>

<body>

    <!--preloader start-->
    <div id="preloader" class="bg-light-subtle">
        <div class="preloader-wrap">
            <img src="{{ asset('asset/img/19.png') }}" alt="logo" class="img-fluid preloader-icon">
            <div class="loading-bar"></div>
        </div>
    </div>
    <!--preloader end-->
    <!--main content wrapper start-->
    <div class="main-wrapper">

        <!--header section start-->
        <!--header start-->
        <header class="main-header position-absolute w-100">
            <nav class="navbar navbar-expand-xl navbar-dark sticky-header z-10">
                <div class="container d-flex align-items-center justify-content-lg-between position-relative">
                    <a href="{{ route('dashboard.landingpage') }}" class="navbar-brand d-flex justify-content-center mb-md-0 text-decoration-none" style="flex-direction: column; align-items: center; padding-top: 20px;">
                    <img src="{{ asset('asset/img/66.png') }}" alt="logo" class="img-fluid logo-white" style="height:75px;" />
                        <img src="{{ asset('asset/img/19.png') }}" alt="logo" class="img-fluid logo-color" />
                    <a class="navbar-toggler position-absolute right-0 border-0" href="#offcanvasWithBackdrop">
                        <i class="flaticon-menu" data-bs-target="#offcanvasWithBackdrop" aria-controls="offcanvasWithBackdrop"
                     data-bs-toggle="offcanvas" role="button"></i>
                    </a>
                    <div class="clearfix"></div>
                    <div class="collapse navbar-collapse justify-content-center">
                        <ul class="nav col-12 col-md-auto justify-content-center main-menu">
                            <li >
                            <a class="nav-link" href="{{ route('dashboard.landingpage') }}" aria-expanded="false">Home</a>
                            </li>
                            <li><a href="{{ route('frontend.terms_and_conditions') }}" class="nav-link">Terms and Conditions</a></li>
                            <li><a href="{{ route('frontend.privacy_policy') }}" class="nav-link">Privacy Policy</a></li>
                        <li><a href="{{ route('frontend.features') }}" class="nav-link">Features</a></li>
                        
                        <li><a href="{{ route('frontend.showplans') }}" class="nav-link">Pricing</a></li>
                           
                            </li>
                        </ul>
                    </div>
                    <div class="action-btns text-end me-5 me-lg-0 d-none d-md-block d-lg-block">
                        <a href="javascript:void(0)" class="btn btn-link p-1 tt-theme-toggle">
                            <div class="tt-theme-light" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Light"><i class="flaticon-sun-1 fs-lg"></i></div>
                            <div class="tt-theme-dark" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Dark"><i class="flaticon-moon-1 fs-lg"></i></div>
<a href="{{ route('login') }}" class="btn btn-link text-decoration-none me-2" style="background-color: orange; color: white; border-radius: 4px; padding: 5px 10px; float: right;">Sign In</a>
                        <!-- <a href="request-demo.html" class="btn btn-primary">Get Started</a> -->
                    </div>
                </div>
            </nav>
            <!--offcanvas menu start-->
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasWithBackdrop">
                <div class="offcanvas-header d-flex align-items-center mt-4">
                    <a href="index.html" class="d-flex align-items-center mb-md-0 text-decoration-none">
                        <img src="{{ asset('asset/img/17.png') }}" alt="logo" class="img-fluid ps-2" />
                    </a>
                    <button type="button" class="close-btn text-danger" data-bs-dismiss="offcanvas" aria-label="Close">
                        <i class="flaticon-cancel"></i>
                    </button>
                </div>
                <div class="offcanvas-body z-10">
                    <ul class="nav col-12 col-md-auto justify-content-center main-menu">
                        <li >
                            <a class="nav-link" href="{{ route('dashboard.landingpage') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Home
                            </a>
                            
                        </li>
                        <li><a href="{{ route('frontend.terms_and_conditions') }}" class="nav-link">Terms and Conditions</a></li>
                        <li><a href="{{ route('frontend.privacy_policy') }}" class="nav-link">Privacy Policy</a></li>
                        
                        <li><a href="pricing.html" class="nav-link">Pricing</a></li>
                        
                    </ul>
                    <div class="action-btns mt-4 ps-3">
                    <a href="{{ route('login') }}" class="btn btn-link text-decoration-none me-2" style="background-color: orange; color: white; border-radius: 4px; padding: 5px 10px;">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-link text-decoration-none me-2" style="background-color: orange; color: white; border-radius: 4px; padding: 5px 10px;">SignUp</a>
                        
                        
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#demoModal" style="  border-radius: 4px; padding: 5px 10px;">DEMO</a>
                    </div>
                </div>
            </div>
            <!--offcanvas menu end-->
        </header>
        <!--header end--> <!--header section end-->

        <!--register section start-->
        <section class="sign-up-in-section bg-dark ptb-120" style="background: url('assets/img/page-header-bg.svg')no-repeat bottom right">
            <div class="container">
                <div class="row align-items-center justify-content-between">
                    <div class="col-xl-5 col-lg-5 col-md-12 order-1 order-lg-0">
                        <div class="testimonial-tab-slider-wrap mt-5">
                            <h1 class="fw-bold text-white display-5">Start Your Project with Us</h1>
                            <p>CorporaOne is an AI-integrated Business Management System designed to revolutionize 
                                    the way businesses operate. Developed by Tinos Software and Security Solutions LLP, 
                                    CorporaOne streamlines operations with cutting-edge artificial intelligence, automation, and 
                                    intuitive features tailored for modern enterprises.</p>
                            <hr>
                           
                            
                        </div>
                    </div>
                    <div class="col-xl-5 col-lg-7 col-md-12 order-0 order-lg-1">
                    <div class="register-wrap p-5 bg-white shadow rounded-custom mt-5 mt-lg-5 mt-xl-5">

                    
                            <h3 class="fw-medium h4">{{ __('Join the Community – Sign Up Now') }}</h3>

                            <form method="POST" action="{{ route('register.store', ['plan' => $plan]) }}" class="mt-4 register-form">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label for="name" class="mb-1">{{ __('Name') }} <span class="text-danger">*</span></label>
                                        <div class="input-group mb-3">
                                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('Name') }}" value="{{ old('name') }}" required>
                                            @error('name')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <label for="email" class="mb-1">{{ __('Email') }} <span class="text-danger">*</span></label>
                                        <div class="input-group mb-3">
                                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="{{ __('Email') }}" value="{{ old('email') }}" required>
                                            @error('email')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>


                                    <div class="col-sm-12">
                                        <label for="password" class="mb-1">{{ __('Password') }} <span class="text-danger">*</span></label>
                                        <div class="input-group mb-3">
                                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ __('Password') }}" required>
                                            @error('password')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>


                                    <div class="col-sm-12">
                                        <label for="password_confirmation" class="mb-1">{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
                                        <div class="input-group mb-3">
                                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="{{ __('Confirm Password') }}" required>
                                            @error('password_confirmation')
                                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="termsCheckbox" name="terms" required>
                                            <label class="form-check-label small" for="termsCheckbox">
                                                {{ __('I Agree to the ') }}
                                                <a href="{{ route('frontend.terms_and_conditions') }}" target="_blank">Terms and Conditions</a>
                                                {{ __(' and the ') }}
                                                <a href="{{ route('frontend.privacy_policy') }}" target="_blank">Privacy Policy</a>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Google reCAPTCHA -->
                                    @if ($settings['recaptcha_module'] == 'on')
                                        @if (isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox')
                                            <div class="form-group col-lg-12 col-md-12 mt-3">
                                                {!! NoCaptcha::display() !!}
                                                @error('g-recaptcha-response')
                                                    <span class="small text-danger"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </div>
                                        @else
                                            <div class="form-group col-lg-12 col-md-12 mt-3">
                                                <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
                                                @error('g-recaptcha-response')
                                                    <span class="small text-danger"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </div>
                                        @endif
                                    @endif

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary mt-4 d-block w-100">{{ __('Sign Up') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if (isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'on')
            @if (isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox')
                {!! NoCaptcha::renderJs() !!}
            @else
                <script src="https://www.google.com/recaptcha/api.js?render={{ $settings['google_recaptcha_key'] }}"></script>
                <script>
                    $(document).ready(function() {
                        grecaptcha.ready(function() {
                            grecaptcha.execute("{{ $settings['google_recaptcha_key'] }}", {
                                action: "submit"
                            }).then(function(token) {
                                $("#g-recaptcha-response").val(token);
                            });
                        });
                    });
                </script>
            @endif
        @endif

        <!--register section end-->

        <!--footer section start-->
        <!--footer section start-->
        <footer class="footer-section">
            <!--footer bottom start-->
            <div class="footer-bottom bg-dark text-white py-4">
                <div class="container">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-7 col-lg-7">
                            <div class="copyright-text">
                                <p class="mb-lg-0 mb-md-0">&copy; 2025 CorporaOne Rights Reserved. Powerd By <a href="https://tinos.co.in/" class="text-decoration-none">Tinos Software And Security Solutions LLP</a></p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="footer-single-col text-start text-lg-end text-md-end">
                                <ul class="list-unstyled list-inline footer-social-list mb-0">
                                        <li class="list-inline-item"><a href="https://www.facebook.com/tinoscyberlabz/"><i class="fab fa-facebook-f"></i></a></li>
                                        <li class="list-inline-item"><a href="https://www.instagram.com/tinossoftware/?utm_source=qr&igshid=YzU1NGVlODEzOA%3D%3D"><i class="fab fa-instagram"></i></a></li>
                                        <li class="list-inline-item"><a href="https://in.linkedin.com/company/tinossoftware"><i class="fab fa-linkedin"></i></a></li>
                                    
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--footer bottom end-->
        </footer>
        <!--footer section end--> <!--footer section end-->

    </div>

    <!--build:js-->
    <script src="{{ asset('asset/js/vendors/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('asset/js/vendors/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('asset/js/vendors/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('asset/js/vendors/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('asset/js/vendors/parallax.min.js') }}"></script>
    <script src="{{ asset('asset/js/vendors/aos.js') }}"></script>
    <script src="{{ asset('asset/js/vendors/massonry.min.js') }}"></script>
    <script src="{{ asset('asset/js/app.js') }}"></script>
    <!--endbuild-->
</body>

</html>

