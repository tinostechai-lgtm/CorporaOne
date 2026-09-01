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
    <title>Login - CorporaOne</title>

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
        <header class="main-header position-absolute w-100">
            <nav class="navbar navbar-expand-xl navbar-dark sticky-header z-10">
                <div class="container d-flex align-items-center justify-content-lg-between position-relative">
                    <a href="{{ route('dashboard.landingpage') }}" class="navbar-brand d-flex align-items-center mb-md-0 text-decoration-none">
                        <img src="{{ asset('asset/img/66.png') }}" alt="logo" class="img-fluid logo-white" style="height:75px;" />
                        <img src="{{ asset('asset/img/19.png') }}" alt="logo" class="img-fluid logo-color" />
                    </a>
                    <a class="navbar-toggler position-absolute right-0 border-0" href="#offcanvasWithBackdrop">
                        <i class="flaticon-menu" data-bs-target="#offcanvasWithBackdrop" aria-controls="offcanvasWithBackdrop"
                     data-bs-toggle="offcanvas" role="button"></i>
                    </a>
                    <div class="clearfix"></div>
                    <div class="collapse navbar-collapse justify-content-center">
                        <ul class="nav col-12 col-md-auto justify-content-center main-menu">
                            <li><a class="nav-link" href="{{ route('dashboard.landingpage') }}" aria-expanded="false">Home</a></li>
                            <li><a href="{{ route('frontend.terms_and_conditions') }}" class="nav-link">Terms and Conditions</a></li>
                            <li><a href="{{ route('frontend.privacy_policy') }}" class="nav-link">Privacy Policy</a></li>
                            <li><a href="{{ route('frontend.features') }}" class="nav-link">Features</a></li>
                            <li><a href="{{ route('frontend.showplans') }}" class="nav-link">Pricing</a></li>
                        </ul>
                    </div>
                    <div class="action-btns text-end me-5 me-lg-0 d-none d-md-block d-lg-block">
                        <a href="javascript:void(0)" class="btn btn-link p-1 tt-theme-toggle">
                            <div class="tt-theme-light" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Light"><i class="flaticon-sun-1 fs-lg"></i></div>
                            <div class="tt-theme-dark" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Dark"><i class="flaticon-moon-1 fs-lg"></i></div>
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-link text-decoration-none me-2" style="background-color: orange; color: white; border-radius: 4px; padding: 5px 10px;">Sign Up</a>
                    </div>
                </div>
            </nav>
            <!--offcanvas menu start-->
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasWithBackdrop">
                <div class="offcanvas-header d-flex align-items-center mt-4">
                    <a href="{{ route('dashboard.landingpage') }}" class="d-flex align-items-center mb-md-0 text-decoration-none">
                        <img src="{{ asset('asset/img/17.png') }}" alt="logo" class="img-fluid ps-2" />
                    </a>
                    <button type="button" class="close-btn text-danger" data-bs-dismiss="offcanvas" aria-label="Close">
                        <i class="flaticon-cancel"></i>
                    </button>
                </div>
                <div class="offcanvas-body z-10">
                    <ul class="nav col-12 col-md-auto justify-content-center main-menu">
                        <li><a class="nav-link" href="{{ route('dashboard.landingpage') }}" role="button">Home</a></li>
                        <li><a href="{{ route('frontend.terms_and_conditions') }}" class="nav-link">Terms and Conditions</a></li>
                        <li><a href="{{ route('frontend.privacy_policy') }}" class="nav-link">Privacy Policy</a></li>
                        <li><a href="{{ route('frontend.features') }}" class="nav-link">Features</a></li>
                        <li><a href="{{ route('frontend.showplans') }}" class="nav-link">Pricing</a></li>
                    </ul>
                    <div class="action-btns mt-4 ps-3">
                        <a href="{{ route('login') }}" class="btn btn-link text-decoration-none me-2" style="background-color: orange; color: white; border-radius: 4px; padding: 5px 10px;">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-link text-decoration-none me-2" style="background-color: orange; color: white; border-radius: 4px; padding: 5px 10px;">Sign Up</a>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#demoModal" style="border-radius: 4px; padding: 5px 10px;">DEMO</a>
                    </div>
                </div>
            </div>
            <!--offcanvas menu end-->
        </header>
        <!--header section end-->

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
                            <h3 class="fw-medium h4">{{ __('Welcome Back') }}</h3>

                            {{-- ========================================================= --}}
                            {{-- UPDATED LOGIN FORM – accepts email or phone number       --}}
                            {{-- ========================================================= --}}
                            {{ Form::open(['route' => 'login', 'method' => 'post', 'id' => 'loginForm2', 'class' => 'mt-4 register-form needs-validation', 'novalidate']) }}
                                @csrf

                                <div class="row">
                                    <div class="col-sm-12">
                                        <label for="login" class="mb-1">{{ __('Email or Phone Number') }} <span class="text-danger">*</span></label>
                                        <div class="input-group mb-3">
                                            {{ Form::text('login', null, ['class' => 'form-control', 'placeholder' => __('Enter your email or phone number'), 'required' => 'required', 'aria-label' => 'login', 'autofocus' => 'autofocus']) }}
                                            @error('login')
                                                <span class="text-danger small" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <label for="password" class="mb-1">{{ __('Password') }} <span class="text-danger">*</span></label>
                                        <div class="input-group mb-3">
                                            {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Password'), 'required' => 'required', 'aria-label' => 'Password']) }}
                                            @error('password')
                                                <span class="text-danger small" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="flexCheckChecked" name="remember">
                                            <label class="form-check-label small" for="flexCheckChecked">
                                                {{ __('Remember me') }}
                                                <a href="{{ route('frontend.privacy_policy') }}">{{ __('View privacy policy') }}</a>.
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        {{ Form::submit(__('Login'), ['class' => 'btn btn-primary mt-4 d-block w-100']) }}
                                    </div>

                                    <p class="font-monospace fw-medium text-center text-muted mt-3 pt-4 mb-0">
                                        {{ __('Don’t have an account?') }} <a href="{{ route('register') }}" class="text-decoration-none">{{ __('Sign up Today') }}</a>
                                        <br>
                                        <a href="{{ route('password.request') }}" class="text-decoration-none">{{ __('Forgot password') }}</a>
                                    </p>
                                </div>
                            {{ Form::close() }}
                            {{-- ========================================================= --}}
                            {{-- END UPDATED LOGIN FORM                                   --}}
                            {{-- ========================================================= --}}
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--register section end-->

        <!--footer section start-->
        <footer class="footer-section">
            <div class="footer-bottom bg-dark text-white py-4">
                <div class="container">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-7 col-lg-7">
                            <div class="copyright-text">
                                <p class="mb-lg-0 mb-md-0">&copy; 2025 CorporaOne Rights Reserved. Powered By <a href="https://tinos.co.in/" class="text-decoration-none">Tinos Software And Security Solutions LLP</a></p>
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
        </footer>
        <!--footer section end-->

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