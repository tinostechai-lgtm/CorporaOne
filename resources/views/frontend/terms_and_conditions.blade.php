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
    <link rel="icon" href="{{ asset('asset/img/corpo.svg') }}" type="image/png" sizes="16x16">

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
            <img src="{{ asset('asset/img/corpo.svg') }}" alt="logo" class="img-fluid preloader-icon">
            <div class="loading-bar"></div>
        </div>
    </div>
    <!--preloader end-->
    <!--main content wrapper start-->
    <div class="main-wrapper bg-soft-blue">
        <!--header section start-->
        <header class="main-header w-100 z-10">
            <nav class="navbar navbar-expand-xl navbar-light sticky-header">
                <div class="container d-flex align-items-center justify-content-lg-between position-relative">
                    <a href="{{ route('dashboard.landingpage') }}" class="navbar-brand d-flex align-items-center mb-md-0 text-decoration-none">
                        <img src="{{ asset('asset/img/corpo.svg') }}" alt="logo" class="img-fluid logo-white" />
                        <img src="{{ asset('asset/img/corpo.svg') }}" alt="logo" class="img-fluid logo-color" />
                    </a>

                    <a class="navbar-toggler position-absolute right-0 border-0" href="#offcanvasWithBackdrop" role="button">
                        <i class="flaticon-menu"
                         data-bs-toggle="offcanvas"
                         data-bs-target="#offcanvasWithBackdrop"
                         aria-controls="offcanvasWithBackdrop"></i>
                    </a>
                    <div class="clearfix"></div>
                    <div class="collapse navbar-collapse justify-content-center">
                        <ul class="nav col-12 col-md-auto justify-content-center main-menu">
                            <li class="nav-item dropdown">
                                <a class="nav-link " href="{{ route('dashboard.landingpage') }}"   aria-expanded="false">Home</a>     
                            </li>
                            <li><a href="{{ route('frontend.features') }}" class="nav-link">Features</a></li>
                            <li><a href="{{ route('frontend.about') }}" class="nav-link">About Us</a></li>
                            <li><a href="{{ route('frontend.showplans') }}" class="nav-link">Pricing</a></li>
                            <li><a href="{{ route('frontend.faq') }}" class="nav-link">FAQ</a></li>
                            
                            
                        </ul>
                    </div>

                    <div class="action-btns text-end me-5 me-lg-0 d-none d-md-block d-lg-block">
                        <a href="javascript:void(0)" class="btn btn-link p-1 tt-theme-toggle">
                            <div class="tt-theme-light" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Light"><i class="flaticon-sun-1 fs-lg"></i></div>
                            <div class="tt-theme-dark" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Dark"><i class="flaticon-moon-1 fs-lg"></i></div>
                            <a href="{{ route('login') }}" class="btn btn-link text-decoration-none me-2" style="background-color: orange; color: white; border-radius: 4px; padding: 5px 10px;">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-link text-decoration-none me-2" style="background-color: orange; color: white; border-radius: 4px; padding: 5px 10px;">SignUp</a>
                        
                        
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#demoModal" style="  border-radius: 4px; padding: 5px 10px;">DEMO</a>
                    </div>
                </div>
            </nav>
            <!--offcanvas menu start-->
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasWithBackdrop">
                <div class="offcanvas-header d-flex align-items-center mt-4">
                    <a href="{{ route('dashboard.landingpage') }}" class="d-flex align-items-center mb-md-0 text-decoration-none">
                        <img src="{{ asset('asset/img/corpo.svg') }}" alt="logo" class="img-fluid ps-2" />
                    </a>
                    <button type="button" class="close-btn text-danger" data-bs-dismiss="offcanvas" aria-label="Close">
                        <i class="flaticon-cancel"></i>
                    </button>
                </div>
                <div class="offcanvas-body">
                    <ul class="nav col-12 col-md-auto justify-content-center main-menu">
                        <li class="nav-item dropdown">
                            <a class="nav-link " href="{{ route('dashboard.landingpage') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Home
                            </a>
                            
                        </li>
                        <li><a href="{{ route('frontend.features') }}" class="nav-link">Features</a></li>
                        </li>
                        <li><a href="{{ route('frontend.about') }}" class="nav-link">About Us</a></li>
                        
                        </li>
                        <li><a href="{{ route('frontend.showplans') }}" class="nav-link">Pricing</a></li>
                        
                        </li>
                        <li><a href="{{ route('frontend.faq') }}" class="nav-link">FAQ</a></li>
                        
                        </li>
                        
                    </ul>
                    <div class="action-btns mt-4 ps-3">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Sign In</a>
                        <a href="{{ route('dashboard.landingpage') }}#demo-section" class="btn btn-primary">DEMO</a>
                    </div>
                </div>
            </div>
            <!--offcanvas menu end-->
        </header> <!--header section end--> <!--header section end-->

        <!--page header section start-->
        <section class="page-header position-relative overflow-hidden ptb-120 bg-dark" style="background: url('asset/img/page-header-bg.svg')no-repeat bottom left">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-md-12">
                        <h1 class="display-5 fw-bold">Price Suit to Your Business</h1>
                        <p class="lead">Seamlessly actualize client-based users after out-of-the-box value. Globally embrace strategic data through frictionless expertise.</p>
                    </div>
                </div>
                <div class="bg-circle rounded-circle circle-shape-3 position-absolute bg-dark-light right-5"></div>
            </div>
        </section>
        <!--page header section end-->

        <!--pricing section start-->
        <!-- <section class="pricing-section ptb-120 position-relative z-2">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="position-relative single-pricing-wrap rounded-custom bg-white custom-shadow p-5 mb-4 mb-lg-0">
                            <div class="pricing-header mb-32">
                                <h3 class="package-name text-primary d-block">Stater</h3>
                                <h4 class="display-6 fw-semi-bold">$25<span>/month</span></h4>
                            </div>
                            <div class="pricing-info mb-4">
                                <ul class="pricing-feature-list list-unstyled">
                                    <li><i class="fas fa-circle fa-2xs text-primary me-2"></i>Users</li>
                                    <li><i class="fas fa-circle fa-2xs text-primary me-2"></i>Customers</li>
                                    <li><i class="fas fa-circle fa-2xs text-primary me-2"></i> Vendors</li>
                                    <li><i class="fas fa-circle fa-2xs text-primary me-2"></i> Clients</li>
                                    <li><i class="fas fa-circle fa-2xs text-primary me-2"></i> 1024 MB Storage</li>
                                    <li><i class="fas fa-circle fa-2xs text-primary me-2"></i> Enable Account</li>
                                    <li><i class="fas fa-circle fa-2xs text-primary me-2"></i> Enable CRM</li>
                                    <li><i class="fas fa-circle fa-2xs text-primary me-2"></i> Enable HRM</li>
                                    <li><i class="fas fa-circle fa-2xs text-primary me-2"></i>Enable Project</li>
                                    <li><i class="fas fa-circle fa-2xs text-primary me-2"></i>Enable POS</li>
                                    <li><i class="fas fa-circle fa-2xs text-primary me-2"></i>Enable Chat GPT</li>
                                </ul>
                            </div>
                            <a href="" class="btn btn-outline-primary mt-2">Buy Now</a>

                            
                            <div class="dot-shape-bg position-absolute z--1 left--40 bottom--40">
                                <img src="{{ asset('asset/img/shape/dot-big-square.svg') }}" alt="shape">
                            </div>
                            
                        </div>
                    </div>
                    
                    
                </div>
            </div>
        </section>  -->

        <section class="mk-pricing-section ptb-120 bg-light-subtle">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-xxl-4 col-lg-6">
                        <div class="mk-title text-center text-lg-start">
                            <span class="mk-subtitle fw-bold">Pricing Plans</span>
                            <h2 class="mk-heading mb-0 mt-3">Get Started for Free. Add a Plan Later.</h2>
                        </div>
                    </div>
                    <div class="col-xl-5 col-lg-7">
                        <div class="mk-pricing-desc mt-4 mt-xl-0 text-center text-lg-start">
                            <p class="mb-3">Make your work easier with an integrated ecosystem that lets all departments work properly together.</p>
                            <p class="text-center fw-bold mk-offer-text">Get 30% off</p>
                            <div class="mk-pricing-control-wrapper d-flex align-items-center justify-content-center justify-content-lg-start">
                                <ul class="mk-pricing-control list-unstyled p-0 m-0 d-flex align-items-center">
                                    <li><a href="#" class="active mk_monthly_switch">Monthly</a></li>
                                    <li><a href="#" class="mk_yearly_switch">Yearly</a></li>
                                </ul>
                                <img src="{{ asset('asset/img/shape/arrow-shape.png') }}" alt="arrow" class="mk-arrow-shape">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container">
                    <div class="row">
                        @php
                            $collection = \App\Models\Plan::orderBy('price', 'ASC')->get();
                            $admin_payment_setting = Utility::getAdminPaymentSetting();
                        @endphp
                        @foreach ($collection as $key => $value)
                            @if($value->is_disable == 1)
                                <div class="col-lg-4 col-md-6">
                                    <div class="position-relative single-pricing-wrap rounded-custom {{ $key == 1 ? 'bg-dark text-white' : 'bg-white custom-shadow' }} p-5 mb-4 mb-lg-0">
                                        <div class="pricing-header mb-32">
                                            <h3 class="package-name {{ $key == 1 ? 'text-warning' : 'text-primary' }} d-block">{{ $value->name }}</h3>
                                            <h4 class="display-6 fw-semi-bold">
                                                {{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}{{ intval($value->price) }}
                                                <span>/{{ $value->duration }}</span>
                                            </h4>
                                        </div>
                                        <div class="pricing-info mb-4">
                                            <ul class="pricing-feature-list list-unstyled">
                                                <li><i class="fas fa-circle fa-2xs {{ $key == 1 ? 'text-warning' : 'text-primary' }} me-2"></i> {{ $value->max_users == -1 ? 'Unlimited' : $value->max_users }} {{ __('User') }}</li>
                                                <li><i class="fas fa-circle fa-2xs {{ $key == 1 ? 'text-warning' : 'text-primary' }} me-2"></i> {{ $value->max_customers == -1 ? 'Unlimited' : $value->max_customers }} {{ __('Customer') }}</li>
                                                <li><i class="fas fa-circle fa-2xs {{ $key == 1 ? 'text-warning' : 'text-primary' }} me-2"></i> {{ $value->max_venders == -1 ? 'Unlimited' : $value->max_venders }} {{ __('Vendors') }}</li>
                                                <li><i class="fas fa-circle fa-2xs {{ $key == 1 ? 'text-warning' : 'text-primary' }} me-2"></i> {{ $value->max_clients == -1 ? 'Unlimited' : $value->max_clients }} {{ __('Clients') }}</li>
                                                <li><i class="fas fa-circle fa-2xs {{ $key == 1 ? 'text-warning' : 'text-primary' }} me-2"></i> {{ $value->account == 1 ? 'Enable' : 'Disable' }} {{ __('Account') }}</li>
                                                <li><i class="fas fa-circle fa-2xs {{ $key == 1 ? 'text-warning' : 'text-primary' }} me-2"></i> {{ $value->crm == 1 ? 'Enable' : 'Disable' }} {{ __('CRM') }}</li>
                                                <li><i class="fas fa-circle fa-2xs {{ $key == 1 ? 'text-warning' : 'text-primary' }} me-2"></i> {{ $value->hrm == 1 ? 'Enable' : 'Disable' }} {{ __('HRM') }}</li>
                                                <li><i class="fas fa-circle fa-2xs {{ $key == 1 ? 'text-warning' : 'text-primary' }} me-2"></i> {{ $value->project == 1 ? 'Enable' : 'Disable' }} {{ __('Project') }}</li>
                                                <li><i class="fas fa-circle fa-2xs {{ $key == 1 ? 'text-warning' : 'text-primary' }} me-2"></i> {{ $value->pos == 1 ? 'Enable' : 'Disable' }} {{ __('POS') }}</li>
                                                <li><i class="fas fa-circle fa-2xs {{ $key == 1 ? 'text-warning' : 'text-primary' }} me-2"></i> {{ $value->chatgpt == 1 ? 'Enable' : 'Disable' }} {{ __('ChatGPT') }}</li>
                                            </ul>
                                        </div>
                                        <a href="{{ Auth::check() ? route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($value->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($value->id)]) }}"
                                           class="btn {{ $key == 1 ? 'btn-primary' : 'btn-outline-primary' }} mt-2">
                                            {{ __('Buy Now') }}
                                        </a>

                                        <!-- Pattern Start -->
                                        @if($key != 1)
                                            <div class="dot-shape-bg position-absolute z--1 {{ $key == 0 ? 'left--40 bottom--40' : 'right--40 top--40' }}">
                                                <img src="{{ asset('asset/img/shape/dot-big-square.svg') }}" alt="shape">
                                            </div>
                                        @endif
                                        <!-- Pattern End -->
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
    </section>

        


    

        
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