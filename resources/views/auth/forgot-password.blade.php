
@section('page-title')
    {{ __('Forgot Password') }}
@endsection

@php
      $settings = Utility::settings();
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
    <link rel="stylesheet" href="{{ asset('asset/css/custom.css') }}">
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
                    <a href="{{ route('dashboard.landingpage') }}" class="navbar-brand d-flex align-items-center mb-md-0 text-decoration-none">
                        <img src="{{ asset('asset/img/66.png') }}" alt="logo" class="img-fluid logo-white" style="height:110px;width:110px;" />
                        <img src="{{ asset('asset/img/19.png') }}" alt="logo" class="img-fluid logo-color" />
                    </a>
                    <a class="navbar-toggler position-absolute right-0 border-0" href="#offcanvasWithBackdrop">
                        <i class="flaticon-menu" data-bs-target="#offcanvasWithBackdrop" aria-controls="offcanvasWithBackdrop"
                     data-bs-toggle="offcanvas" role="button"></i>
                    </a>
                    <div class="clearfix"></div>
                    <div class="collapse navbar-collapse justify-content-center">
                        <ul class="nav col-12 col-md-auto justify-content-center main-menu">
                            <li >
                                <a class="nav-link " href="{{ route('dashboard.landingpage') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">Home</a>
                                
                            </li>
                            <li><a href="{{ route('frontend.terms_and_conditions') }}" class="nav-link">Terms and Conditions</a></li>
                            <li><a href="{{ route('frontend.privacy_policy') }}" class="nav-link">Privacy Policy</a></li>
                            <li><a href="{{ route('frontend.features') }}" class="nav-link">Features</a></li>
                            
                            <li><a href="" class="nav-link">Pricing</a></li>
                           
                            </li>
                        </ul>
                    </div>
                    <div class="action-btns text-end me-5 me-lg-0 d-none d-md-block d-lg-block">
                        <a href="javascript:void(0)" class="btn btn-link p-1 tt-theme-toggle">
                            <div class="tt-theme-light" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Light"><i class="flaticon-sun-1 fs-lg"></i></div>
                            <div class="tt-theme-dark" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Dark"><i class="flaticon-moon-1 fs-lg"></i></div>
                        </a> <a href="{{ route('register') }}" class="btn btn-link text-decoration-none me-2">Sign Up</a>
                        <!-- <a href="request-demo.html" class="btn btn-primary">Get Started</a> -->
                    </div>
                </div>
            </nav>
            <!--offcanvas menu start-->
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasWithBackdrop">
                <div class="offcanvas-header d-flex align-items-center mt-4">
                    <a href="{{ route('dashboard.landingpage') }}" class="d-flex align-items-center mb-md-0 text-decoration-none">
                        <img src="{{ asset('asset/img/logo-color.png') }}" alt="logo" class="img-fluid ps-2" />
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
                        <li><a href="{{ route('frontend.features') }}" class="nav-link">Features</a></li>
                        
                        <li><a href="" class="nav-link">Pricing</a></li>
                        
                    </ul>
                    <div class="action-btns mt-4 ps-3">
                        <a href="{{ route('register') }}" class="btn btn-outline-primary me-2">Sign Up</a>
                        <!-- <a href="request-demo.html" class="btn btn-primary">Get Started</a> -->
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
                            <h1 class="fw-bold text-white display-5">Transform your business with CorporaOne—where AI meets efficiency!</h1>
                            <p>Multi-language support, AI-powered automation, and deep integration 
                                capabilities, CorporaOne enhances productivity, reduces manual efforts, and empowers 
                                businesses with intelligent insights. </p>
                            <hr>
                            
                            
                        </div>
                    </div>
                    <div class="col-xl-5 col-lg-7 col-md-12 order-0 order-lg-1">
                        <div class="register-wrap p-5 bg-white shadow rounded-custom mt-5 mt-lg-0 mt-xl-0">
                            <h3 class="fw-medium h4">{{ __('Forgot Password') }}</h3>

                            @if (session('status'))
                                <div class="alert alert-primary">
                                    {{ session('status') }}
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('password.email') }}" class="mt-4 register-form needs-validation" novalidate>
                                @csrf
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label for="email" class="mb-1">{{ __('Email') }} <span class="text-danger">*</span></label>
                                        <div class="input-group mb-3">
                                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                                name="email" value="{{ old('email') }}" required autocomplete="email" autofocus 
                                                placeholder="{{ __('Enter Email') }}">
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <small>{{ $message }}</small>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    @if ($settings['recaptcha_module'] == 'on')
                                        @if (isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox')
                                            <div class="form-group mt-3">
                                                {!! NoCaptcha::display() !!}
                                                @error('g-recaptcha-response')
                                                    <span class="small text-danger" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        @else
                                            <div class="form-group mt-3">
                                                <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
                                                @error('g-recaptcha-response')
                                                    <span class="error small text-danger" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        @endif
                                    @endif

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary mt-4 d-block w-100">{{ __('Send Password Reset Link') }}</button>
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
                        grecaptcha.execute('{{ $settings['google_recaptcha_key'] }}', {
                            action: 'submit'
                        }).then(function(token) {
                            $('#g-recaptcha-response').val(token);
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
                                <p class="mb-lg-0 mb-md-0">&copy; 2025 CorporaOne Rights Reserved. Designed By <a href="https://tinos.co.in/" class="text-decoration-none">Tinos Software And Security Solutions LLP</a></p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="footer-single-col text-start text-lg-end text-md-end">
                                <ul class="list-unstyled list-inline footer-social-list mb-0">
                                    <li class="list-inline-item"><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                    <li class="list-inline-item"><a href="#"><i class="fab fa-instagram"></i></a></li>
                                    <li class="list-inline-item"><a href="#"><i class="fab fa-dribbble"></i></a></li>
                                    <li class="list-inline-item"><a href="#"><i class="fab fa-github"></i></a></li>
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