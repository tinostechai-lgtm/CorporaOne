@php
    use App\Models\Utility;
    $settings = \Modules\LandingPage\Entities\LandingPageSetting::settings();
    $logo = Utility::get_file('uploads/landing_page_image');
    $sup_logo = Utility::get_file('uploads/logo');

    $metatitle = isset($adminSettings['meta_title']) ? $adminSettings['meta_title'] : '';
    $metsdesc = isset($adminSettings['meta_desc']) ? $adminSettings['meta_desc'] : '';
    $meta_image = \App\Models\Utility::get_file('uploads/meta/');
    $meta_logo = isset($adminSettings['meta_image']) ? $adminSettings['meta_image'] : '';
    $get_cookie = \App\Models\Utility::getCookieSetting();

    $setting = \App\Models\Utility::colorset();

    $SITE_RTL = $adminSettings['SITE_RTL'] ? $adminSettings['SITE_RTL'] : '';

    $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';

    if(isset($setting['color_flag']) && $setting['color_flag'] == 'true')
    {
        $themeColor = 'custom-color';
    }
    else {
        $themeColor = $color;
    }
@endphp

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
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
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
                        <i class="flaticon-menu" data-bs-toggle="offcanvas" data-bs-target="#offcanvasWithBackdrop" aria-controls="offcanvasWithBackdrop"></i>
                    </a>
                    <div class="clearfix"></div>
                    <div class="collapse navbar-collapse justify-content-center">
                        <ul class="nav col-12 col-md-auto justify-content-center main-menu">
                            <li class="nav-item dropdown">
                                <a class="nav-link" href="{{ route('dashboard.landingpage') }}" aria-expanded="false">Home</a>
                            </li>
                            <li><a href="{{ route('frontend.features') }}" class="nav-link">Features</a></li>
                            <li><a href="{{ route('frontend.about') }}" class="nav-link">About Us</a></li>
                            <li><a href="{{ route('frontend.showplans') }}" class="nav-link">Pricing</a></li>
                            <li><a href="{{ route('frontend.faq') }}" class="nav-link">FAQ</a></li>
                            <li><a href="{{ route('frontend.contact') }}" class="nav-link">Contact Us</a></li>
                        </ul>
                    </div>

                    <div class="action-btns text-end me-5 me-lg-0 d-none d-md-block d-lg-block">
    <a href="javascript:void(0)" class="btn btn-link p-1 tt-theme-toggle" style="display: inline-flex; align-items: center;">
        <div class="tt-theme-light" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Light" style="display: inline-flex; align-items: center;">
            <i class="flaticon-sun-1 fs-lg" style="font-size: 1.5rem; color: #fff;"></i>
        </div>
        <div class="tt-theme-dark" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Dark" style="display: none; align-items: center;">
            <i class="flaticon-moon-1 fs-lg" style="font-size: 1.5rem; color: #fff;"></i>
        </div>
    </a>
    <a href="{{ route('login') }}" class="btn btn-link text-decoration-none me-2" style="
        background: linear-gradient(135deg, #ff7e00, #ff4500); 
        color: #fff; 
        padding: 10px 20px; 
        border-radius: 8px; 
        font-weight: 600; 
        font-size: 16px; 
        box-shadow: 0 4px 15px rgba(255, 126, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2); 
        transform: translateY(0); 
        transition: all 0.3s ease; 
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2); 
        display: inline-block;"
        onmouseover="this.style.transform='translateY(-2px) scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(255, 126, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.2)';"
        onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 4px 15px rgba(255, 126, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2)';">
        Sign In
    </a>
    <a href="{{ route('register') }}" class="btn btn-link text-decoration-none me-2" style="
        background: linear-gradient(135deg, #ff7e00, #ff4500); 
        color: #fff; 
        padding: 10px 20px; 
        border-radius: 8px; 
        font-weight: 600; 
        font-size: 16px; 
        box-shadow: 0 4px 15px rgba(255, 126, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2); 
        transform: translateY(0); 
        transition: all 0.3s ease; 
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2); 
        display: inline-block;"
        onmouseover="this.style.transform='translateY(-2px) scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(255, 126, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.2)';"
        onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 4px 15px rgba(255, 126, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2)';">
        Sign Up
    </a>
    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#demoModal"  style="
        background: linear-gradient(135deg, #00c4ff, #007bff); 
        color: #fff; 
        padding: 10px 20px; 
        border-radius: 8px; 
        font-weight: 600; 
        font-size: 16px; 
        box-shadow: 0 4px 15px rgba(0, 196, 255, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2); 
        transform: translateY(0); 
        transition: all 0.3s ease; 
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2); 
        display: inline-block;"
        onmouseover="this.style.transform='translateY(-2px) scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(0, 196, 255, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.2)';"
        onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 4px 15px rgba(0, 196, 255, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2)';">
        DEMO
    </a>
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
                            <a class="nav-link dropdown-toggle" href="{{ route('dashboard.landingpage') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Home
                            </a>
                        </li>
                        <li><a href="{{ route('frontend.features') }}" class="nav-link">Features</a></li>
                        <li><a href="{{ route('frontend.about') }}" class="nav-link">About Us</a></li>
                        <li><a href="{{ route('frontend.showplans') }}" class="nav-link">Pricing</a></li>
                        <li><a href="{{ route('frontend.faq') }}" class="nav-link">FAQ</a></li>
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
        <!--header section end-->

        <!--hero section start-->
        <section class="mk-hero-section bg-white position-relative overflow-hidden" style="background-image: url('asset/img/shape/mk-hero-curve.svg');">
            <span class="mk-hero-rectangle-shape position-absolute"></span>
            <img src="{{ asset('asset/img/shape/mk-hero-circle-line.png') }}" alt="circle line" class="position-absolute start-0 w-100 mk-hero-circle-line">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xl-7">
                        <div class="mk-title">
                            <h1 class="display-4 fw-bold mk-title text-white">AI
                                <mark class="bg-transparent p-0"> Integrated Business Management </mark>System!</h1>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="mk-hero-content">
                            <p class="mb-4 text-white">Make your work easier with an integrated ecosystem that lets all departments work properly together.</p>
                            <div class="d-flex align-items-center mk-btn-group flex-wrap">
                                <!-- <a href="" class="ins-btn mk-white-btn">Get Started</a> -->
                                <!-- <a href="http://www.youtube.com/watch?v=hAP2QF--2Dg" class="mk-hero-play fw-bold popup-youtube"><span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white me-2"><i class="fas fa-play"></i></span> How it Work</a> -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="mk-hero-dashboard text-md-center position-relative mt-60 position-relative">
                            <span class="mk-gradient-hero-shape position-absolute rounded-circle"></span>
                            <span class="mk-secondary-gradient-shape position-absolute rounded-circle"></span>
                            <img src="{{ asset('asset/img/shape/mk-doted.png') }}" alt="doted" class="mk-hero-doted position-absolute">
                            <img src="{{ asset('asset/img/s2.png') }}" alt="not found" class="img-fluid">
                            <img src="{{ asset('asset/img/dashboard-sm.png') }}" alt="dashboard" class="dashboard-sm d-none d-sm-block">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--hero section end-->

        <!--feature section start-->
        <section class="dg-service-section bg-light-subtle pb-120 position-relative z-1 overflow-hidden">
            <img src="{{ asset('asset/img/digital-agency/sr-line.png') }}" alt="doted line" class="position-absolute end-0 z--1 sr-line">
            <span class="sr-circle-1 dg-circle-style-1 rounded-circle position-absolute z--1"></span>
            <span class="sr-circle-2 dg-circle-style-2 rounded-circle position-absolute z--1"></span>
            <div class="container position-relative z-1">
                <div class="row justify-content-center">
                    <div class="col-xl-5">
                        <div class="section-title text-center mb-5">
                            <span class="fw-bold dg-text-primary">OUR CORE FEATURES </span>
                            <h2 class="mt-2 heading-dg-color clr-text">Human Resources& Payroll Management </h2>
                        </div>
                    </div>
                </div>
                <div class="row g-4 justify-content-center">
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-1">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-1">
                                    <svg width="24" height="26" viewBox="0 0 24 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M22.6 7V19C22.6 22.6 20.8 25 16.6 25H7C2.8 25 1 22.6 1 19V7C1 3.4 2.8 1 7 1H16.6C20.8 1 22.6 3.4 22.6 7Z" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M13.3 15.4H18.4M8.20004 20.2H18.4M16 1V10.432C16 10.96 15.376 11.224 14.992 10.876L12.208 8.308C12.0976 8.20405 11.9517 8.14616 11.8 8.14616C11.6484 8.14616 11.5025 8.20405 11.392 8.308L8.60804 10.876C8.22404 11.224 7.60004 10.96 7.60004 10.432V1H16Z" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color">HR Management </h5>
                            </a>
                            <p class="mb-3 text-dg-color"> Handles recruitment, onboarding, payroll, and employee engagement. </p>
                            <span class="number-count position-relative color-1 fw-semibold">01</span>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-2">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-2">
                                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10.12 22.6H15.88C20.68 22.6 22.6 20.68 22.6 15.88V10.12C22.6 5.32002 20.68 3.40002 15.88 3.40002H10.12C5.31999 3.40002 3.39999 5.32002 3.39999 10.12V15.88C3.39999 20.68 5.31999 22.6 10.12 22.6Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M8.212 3.4V1M13 3.4V1M17.8 3.4V1M22.6 8.2H25M22.6 13H25M22.6 17.8H25M17.8 22.6V25M13.012 22.6V25M8.212 22.6V25M1 8.2H3.4M1 13H3.4M1 17.8H3.4M11.2 19H14.8C17.8 19 19 17.8 19 14.8V11.2C19 8.2 17.8 7 14.8 7H11.2C8.2 7 7 8.2 7 11.2V14.8C7 17.8 8.2 19 11.2 19Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M13 10.24L11.872 12.208C11.62 12.64 11.824 13 12.328 13H13.672C14.176 13 14.38 13.36 14.128 13.792L13 15.76" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color">Payroll Automation</h5>
                            </a>
                            <p class="mb-3 text-dg-color"> Streamlines salary calculations, tax deductions, and direct deposits.  </p>
                            <span class="number-count position-relative color-2 fw-semibold">02</span>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-3">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-3">
                                    <svg width="24" height="26" viewBox="0 0 24 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20.2 13V5.8C20.2 3.148 20.2 1 15.4 1H8.2C3.4 1 3.4 3.148 3.4 5.8V13M5.8 13C1 13 1 15.148 1 17.8V19C1 22.312 1 25 7 25H16.6C21.4 25 22.6 22.312 22.6 19V17.8C22.6 15.148 22.6 13 17.8 13C16.6 13 16.264 13.252 15.64 13.72L14.416 15.016C14.0795 15.3739 13.6732 15.6592 13.2223 15.8542C12.7714 16.0492 12.2853 16.1497 11.794 16.1497C11.3027 16.1497 10.8166 16.0492 10.3657 15.8542C9.91475 15.6592 9.50852 15.3739 9.172 15.016L7.96 13.72C7.336 13.252 7 13 5.8 13Z" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M10.06 9.67593H14.056M9.06396 6.07593H15.064" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color"> Attendance Tracking</h5>
                            </a>
                            <p class="mb-3 text-dg-color"> Real-time employee attendance monitoring for workforce management. </p>
                            <span class="number-count position-relative color-3 fw-semibold">03</span>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-4">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-4">
                                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5.7904 4.58979V6.991M10.5928 4.58979V6.991M5.7904 18.997V21.3982M10.5928 18.997V21.3982M15.3952 5.7904H20.1976M15.3952 20.1976H20.1976M21.7824 10.5928H4.21761C2.44072 10.5928 1 9.14007 1 7.37519V4.21761C1 2.44072 2.45273 1 4.21761 1H21.7824C23.5593 1 25 2.45273 25 4.21761V7.37519C25 9.14007 23.5473 10.5928 21.7824 10.5928ZM21.7824 25H4.21761C2.44072 25 1 23.5473 1 21.7824V18.6248C1 16.8479 2.45273 15.4072 4.21761 15.4072H21.7824C23.5593 15.4072 25 16.8599 25 18.6248V21.7824C25 23.5473 23.5473 25 21.7824 25Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color">Leave Management </h5>
                            </a>
                            <p class="mb-3 text-dg-color">Automates leave requests, approvals, tracking, and management.</p>
                            <span class="number-count position-relative color-4 fw-semibold">04</span>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-4">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-4">
                                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5.7904 4.58979V6.991M10.5928 4.58979V6.991M5.7904 18.997V21.3982M10.5928 18.997V21.3982M15.3952 5.7904H20.1976M15.3952 20.1976H20.1976M21.7824 10.5928H4.21761C2.44072 10.5928 1 9.14007 1 7.37519V4.21761C1 2.44072 2.45273 1 4.21761 1H21.7824C23.5593 1 25 2.45273 25 4.21761V7.37519C25 9.14007 23.5473 10.5928 21.7824 10.5928ZM21.7824 25H4.21761C2.44072 25 1 23.5473 1 21.7824V18.6248C1 16.8479 2.45273 15.4072 4.21761 15.4072H21.7824C23.5593 15.4072 25 16.8599 25 18.6248V21.7824C25 23.5473 23.5473 25 21.7824 25Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color">Performance Monitoring </h5>
                            </a>
                            <p class="mb-3 text-dg-color">Uses AI-powered insights for performance tracking, KPI analysis, productivity monitoring, and reporting. </p>
                            <span class="number-count position-relative color-4 fw-semibold">05</span>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-3">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-3">
                                    <svg width="24" height="26" viewBox="0 0 24 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20.2 13V5.8C20.2 3.148 20.2 1 15.4 1H8.2C3.4 1 3.4 3.148 3.4 5.8V13M5.8 13C1 13 1 15.148 1 17.8V19C1 22.312 1 25 7 25H16.6C21.4 25 22.6 22.312 22.6 19V17.8C22.6 15.148 22.6 13 17.8 13C16.6 13 16.264 13.252 15.64 13.72L14.416 15.016C14.0795 15.3739 13.6732 15.6592 13.2223 15.8542C12.7714 16.0492 12.2853 16.1497 11.794 16.1497C11.3027 16.1497 10.8166 16.0492 10.3657 15.8542C9.91475 15.6592 9.50852 15.3739 9.172 15.016L7.96 13.72C7.336 13.252 7 13 5.8 13Z" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M10.06 9.67593H14.056M9.06396 6.07593H15.064" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color"> Training & Development</h5>
                            </a>
                            <p class="mb-3 text-dg-color">Manages training schedules, tracks trainer profiles, monitors progress, and enhances employee upskilling. </p>
                            <span class="number-count position-relative color-3 fw-semibold">06</span>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-2">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-2">
                                    <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10.12 22.6H15.88C20.68 22.6 22.6 20.68 22.6 15.88V10.12C22.6 5.32002 20.68 3.40002 15.88 3.40002H10.12C5.31999 3.40002 3.39999 5.32002 3.39999 10.12V15.88C3.39999 20.68 5.31999 22.6 10.12 22.6Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M8.212 3.4V1M13 3.4V1M17.8 3.4V1M22.6 8.2H25M22.6 13H25M22.6 17.8H25M17.8 22.6V25M13.012 22.6V25M8.212 22.6V25M1 8.2H3.4M1 13H3.4M1 17.8H3.4M11.2 19H14.8C17.8 19 19 17.8 19 14.8V11.2C19 8.2 17.8 7 14.8 7H11.2C8.2 7 7 8.2 7 11.2V14.8C7 17.8 8.2 19 11.2 19Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M13 10.24L11.872 12.208C11.62 12.64 11.824 13 12.328 13H13.672C14.176 13 14.38 13.36 14.128 13.792L13 15.76" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color">Job & Recruitment Management </h5>
                            </a>
                            <p class="mb-3 text-dg-color"> Supports job postings, application tracking, interview scheduling, and onboarding. </p>
                            <span class="number-count position-relative color-2 fw-semibold">07</span>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-1">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-1">
                                    <svg width="24" height="26" viewBox="0 0 24 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M22.6 7V19C22.6 22.6 20.8 25 16.6 25H7C2.8 25 1 22.6 1 19V7C1 3.4 2.8 1 7 1H16.6C20.8 1 22.6 3.4 22.6 7Z" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M13.3 15.4H18.4M8.20004 20.2H18.4M16 1V10.432C16 10.96 15.376 11.224 14.992 10.876L12.208 8.308C12.0976 8.20405 11.9517 8.14616 11.8 8.14616C11.6484 8.14616 11.5025 8.20405 11.392 8.308L8.60804 10.876C8.22404 11.224 7.60004 10.96 7.60004 10.432V1H16Z" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color"> Employee Recognition & Awards</h5>
                            </a>
                            <p class="mb-3 text-dg-color">System for recognizing employee achievements, performance, and engagement. </p>
                            <span class="number-count position-relative color-1 fw-semibold">08</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-center mt-4">
                    <a href="{{ route('frontend.features') }}" class="btn dg-primary-btn rounded-pill">Explore More</a>
                </div>
            </div>
        </section>

        <!--portfolio section start-->
        <section class="crm-about-section ptb-120" style="margin-top: -80px;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-5 col-lg-6">
                        <div class="crm-title text-center">
                            <span class="crm-subtitle">Why Choose Us <img src="{{ asset('asset/img/shape/arrow-red.png') }}" alt="arrow"></span>
                            <h2 class="mt-1 clr-text">Why Us ERP</h2>
                        </div>
                    </div>
                </div>
                <div class="mt-5">
                    <div class="row justify-content-center g-4">
                        <div class="col-xl-6">
                            <div class="crm-about-content-box crm-bg-light rounded overflow-hidden">
                                <div class="crm-content-top">
                                    <h4> AI-Driven Efficiency </h4>
                                    <p class="mb-4">Automate workflows, optimize operations, and leverage OpenAI model integrations for smarter decision-making. Enhance productivity by streamlining complex tasks with AI-driven insights and automation. Drive innovation and efficiency by utilizing advanced machine learning models for data-driven strategies.</p>
                                    <a href="{{ route('frontend.features') }}" class="read-more-link">Explore More <i class="fa-solid fa-arrow-right-long ms-1"></i></a>
                                </div>
                                <div class="text-center mt-4 position-relative z-1">
                                    <span class="circle-shape position-absolute rounded-circle z--1"></span>
                                    <img src="{{ asset('asset/img/vector-1.png') }}" alt="vector" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="row g-4 justify-content-center">
                                <div class="col-xl-12">
                                    <div class="crm-about-content-box crm-bg-yellow-light rounded position-relative z-1 overflow-hidden">
                                        <div class="crm-content-wrapper">
                                            <h4> User-Centric Design</h4>
                                            <p class="mb-4"> Personalized dashboards, role-based access control, and real-time reporting for seamless user experience. </p>
                                            <a href="{{ route('frontend.features') }}" class="read-more-link">Explore More <i class="fa-solid fa-arrow-right-long ms-1"></i></a>
                                        </div>
                                        <img src="{{ asset('asset/img/vector-2.png') }}" alt="vector" class="crm-vector-img">
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="crm-about-content-box crm-bg-light-green rounded position-relative z-1 overflow-hidden">
                                        <div class="crm-content-wrapper">
                                            <h4>Comprehensive Business Modules</h4>
                                            <p class="mb-4">HR, Finance, Payroll, Sales, Inventory, Project Management, and more, all in one unified platform. </p>
                                            <a href="{{ route('frontend.features') }}" class="read-more-link">Explore More <i class="fa-solid fa-arrow-right-long ms-1"></i></a>
                                        </div>
                                        <img src="{{ asset('asset/img/vector-3.png') }}" alt="vector" class="crm-vector-img">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--about section end-->

        <!-- Pricing Section Start -->
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
        <!-- Pricing Section End -->

        <!--business section start-->
        <section class="mk-business bg-white">
            <div class="container">
                <div class="row justify-content-between align-items-center">
                    <div class="col-xl-7">
                        <div class="mk-business-pr position-relative">
                            <img src="{{ asset('asset/img/shape/mk-doted-lg.png') }}" alt="not found" class="position-absolute mk-doted-lg">
                            <img src="{{ asset('asset/img/laptop.png') }}" alt="laptop" class="img-fluid">
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="mk-business-content">
                            <h3 class="mk-heading mb-3">Why Choose CorporaOne? <br class="d-none d-sm-block"> </h3>
                            <p class="mb-30">At Tinos Software and Security Solutions LLP, we are committed to delivering innovative, cost-effective, and scalable solutions that empower businesses of all sizes. With CorporaOne, We bring the future of AI-powered business management to your fingertips.</p>
                            <ul class="mk-business-reports p-0">
                                <li class="d-flex align-items-start mk-bg-secondary">
                                    <span class="icon-wrapper d-inline-flex align-items-center justify-content-center rounded flex-shrink-0">
                                        <svg width="24" height="32" viewBox="0 0 24 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M16 0V8H24L16 0Z" fill="#FF724B"/>
                                            <path d="M16 10C14.898 10 14 9.102 14 8V0H2C0.898 0 0 0.898 0 2V30C0 31.104 0.898 32 2 32H22C23.104 32 24 31.104 24 30V10H16ZM8 28H4V22H8V28ZM14 28H10V18H14V28ZM20 28H16V14H20V28Z" fill="#FF724B"/>
                                        </svg>
                                    </span>
                                    <div class="ms-4">
                                        <h6 class="mk-heading mb-2">Customizable & Scalable</h6>
                                        <p class="mb-0">Tailored settings for branding, color themes, layout preferences, and multi-business support. </p>
                                    </div>
                                </li>
                                <li class="d-flex align-items-start mk-bg-primary">
                                    <span class="icon-wrapper d-inline-flex align-items-center justify-content-center rounded flex-shrink-0 bg-mk-primary">
                                        <svg width="20" height="33" viewBox="0 0 20 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.5296 0H4.07697C1.81611 0 0 1.81611 0 4.07697V28.5388C0 30.7997 1.85317 32.6158 4.07697 32.6158H15.5296C17.7904 32.6158 19.6065 30.7997 19.6065 28.5388V4.07697C19.6065 1.81611 17.7904 0 15.5296 0ZM9.8218 29.9843C8.78403 29.9843 7.96863 29.1689 7.96863 28.1311C7.96863 27.0934 8.78403 26.278 9.8218 26.278C10.8596 26.278 11.675 27.0934 11.675 28.1311C11.675 29.1689 10.8596 29.9843 9.8218 29.9843ZM17.7534 24.3136H1.85317V5.44832H17.7534V24.3136Z" fill="#5F2CF2"/>
                                        </svg>
                                    </span>
                                    <div class="ms-4">
                                        <h6 class="mk-heading mb-2">Cloud-Powered</h6>
                                        <p class="mb-0">Secure data storage, easy access, and real-time updates for businesses on the go. </p>
                                    </div>
                                </li>
                                <li class="d-flex align-items-start mk-bg-secondary">
                                    <span class="icon-wrapper d-inline-flex align-items-center justify-content-center rounded flex-shrink-0">
                                        <svg width="24" height="32" viewBox="0 0 24 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M16 0V8H24L16 0Z" fill="#FF724B"/>
                                            <path d="M16 10C14.898 10 14 9.102 14 8V0H2C0.898 0 0 0.898 0 2V30C0 31.104 0.898 32 2 32H22C23.104 32 24 31.104 24 30V10H16ZM8 28H4V22H8V28ZM14 28H10V18H14V28ZM20 28H16V14H20V28Z" fill="#FF724B"/>
                                        </svg>
                                    </span>
                                    <div class="ms-4">
                                        <h6 class="mk-heading mb-2">Enhanced Decision-Making </h6>
                                        <p class="mb-0">AI-driven analytics, sales forecasts, and intelligent reporting to drive business growth.  </p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--business section end-->

        <!--about section start-->
        <section class="mk-about-section bg-white pt-60 pb-120">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-6 col-lg-7 col-md-8">
                        <div class="mk-title text-center">
                            <span class="fw-bold mk-subtitle">Why Choose Us</span>
                            <h2 class="mt-3 mb-0 mk-heading">Core System Features</h2>
                        </div>
                    </div>
                </div>
                <div class="mt-5 position-relative mk-sf-bottom">
                    <div class="row justify-content-center g-4">
                        <div class="col-xl-4 col-lg-6">
                            <div class="mk-sf-item bg-white">
                                <span class="icon-wrapper d-inline-flex align-items-center justify-content-center rounded">
                                    <img src="{{ asset('asset/img/icons/mk-1.svg') }}" alt="icon" class="img-fluid">
                                </span>
                                <h4 class="mk-heading mb-3 mt-4">Individual Employee Accounts</h4>
                                <p class="mb-0"> Each employee gets a dedicated online account for personalized access, ensuring a seamless and secure experience. </p>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-6">
                            <div class="mk-sf-item bg-white">
                                <span class="icon-wrapper d-inline-flex align-items-center justify-content-center rounded warning-bg">
                                    <img src="{{ asset('asset/img/icons/mk-2.svg') }}" alt="icon" class="img-fluid">
                                </span>
                                <h4 class="mk-heading mb-3 mt-4">Custom Branding</h4>
                                <p class="mb-0">Feature your own logo, company name, and branding elements in the system to create a personalized, professional, and consistent brand identity experience.</p>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-6">
                            <div class="mk-sf-item bg-white">
                                <span class="icon-wrapper d-inline-flex align-items-center justify-content-center rounded mk-primary">
                                    <img src="{{ asset('asset/img/icons/mk-3.svg') }}" alt="icon" class="img-fluid">
                                </span>
                                <h4 class="mk-heading mb-3 mt-4"> Multi-Language Support </h4>
                                <p class="mb-0">Supports multiple languages for global accessibility, ensuring seamless communication, user convenience,and a personalized experience worldwide. </p>
                            </div>
                        </div>
                    </div>
                    <img src="{{ asset('asset/img/shape/mk-wave.png') }}" alt="wave" class="position-absolute mk-wave">
                </div>
            </div>
        </section>
        <!--about section end-->

        <!--integration section start-->
        <div class="ail-work-area pt-120 pb-60" style="margin-top:-100px;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-9">
                        <div class="text-center mb-30">
                            <p class="ail-sub-title text-black fw-500 ah-input-bg d-inline-flex align-items-center gap-2 rounded-5 mb-20">
                                <span class="ail-gd-bg"></span> How it works
                            </p>
                            <h2 class="ail-title text-black fs-48 fw-600">Transform your business with
                                <span class="ail-highlighted-text">CorporaOne </span>where AI meets efficiency!
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="ail-step-sub-title d-flex align-items-center gap-3 mb-20">
                            <span class="bg-black"></span>
                            <p class="ca-two-body-clr ff-poppins fw-600 mb-0">01</p>
                        </div>
                        <h3 class="text-black fs-36">Select writing template Content Creation at Scale</h3>
                        <p class="mb-20">Marve has the answer to every request and is using the latest Google Data for accurate responses.</p>
                        <ul class="ail-work-list list-unstyled">
                            <li class="d-flex gap-3">
                                <svg class="mt-5-ov" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.3" d="M8.9998 1.80005C5.0308 1.80005 1.7998 5.03105 1.7998 9.00005C1.7998 12.969 5.0308 16.2 8.9998 16.2C12.9688 16.2 16.1998 12.969 16.1998 9.00005C16.1998 5.03105 12.9688 1.80005 8.9998 1.80005ZM7.1998 13.5L3.5998 9.90005L4.8688 8.63105L7.1998 10.953L13.1308 5.02205L14.3998 6.30005L7.1998 13.5Z" fill="#6672FB" />
                                    <path d="M9 0C4.032 0 0 4.032 0 9C0 13.968 4.032 18 9 18C13.968 18 18 13.968 18 9C18 4.032 13.968 0 9 0ZM9 16.2C5.031 16.2 1.8 12.969 1.8 9C1.8 5.031 5.031 1.8 9 1.8C12.969 1.8 16.2 5.031 16.2 9C16.2 12.969 12.969 16.2 9 16.2ZM13.131 5.022L7.2 10.953L4.869 8.631L3.6 9.9L7.2 13.5L14.4 6.3L13.131 5.022Z" fill="#476EFB" />
                                </svg>
                                <div class="ail-work__content">
                                    <p class="text-black fw-700 mb-0"> Admin Module</p>
                                    <p class="fch-40 mb-0">Enables admins to view employee details, manage attendance, and perform administrative tasks. </p>
                                </div>
                            </li>
                            <li class="d-flex gap-3 mt-20">
                                <svg class="mt-5-ov" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.3" d="M8.9998 1.80005C5.0308 1.80005 1.7998 5.03105 1.7998 9.00005C1.7998 12.969 5.0308 16.2 8.9998 16.2C12.9688 16.2 16.1998 12.969 16.1998 9.00005C16.1998 5.03105 12.9688 1.80005 8.9998 1.80005ZM7.1998 13.5L3.5998 9.90005L4.8688 8.63105L7.1998 10.953L13.1308 5.02205L14.3998 6.30005L7.1998 13.5Z" fill="#6672FB" />
                                    <path d="M9 0C4.032 0 0 4.032 0 9C0 13.968 4.032 18 9 18C13.968 18 18 13.968 18 9C18 4.032 13.968 0 9 0ZM9 16.2C5.031 16.2 1.8 12.969 1.8 9C1.8 5.031 5.031 1.8 9 1.8C12.969 1.8 16.2 5.031 16.2 9C16.2 12.969 12.969 16.2 9 16.2ZM13.131 5.022L7.2 10.953L4.869 8.631L3.6 9.9L7.2 13.5L14.4 6.3L13.131 5.022Z" fill="#476EFB" />
                                </svg>
                                <div class="ail-work__content">
                                    <p class="text-black fw-700 mb-0">Multi-Language Support </p>
                                    <p class="fch-40 mb-0">Supports multiple languages for global accessibility and user convenience.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-6">
                        <div class="ail-gd-bg-2 p-4 pt-40 pb-40 rounded-16">
                            <img src="{{ asset('asset/img/ab-1.png') }}" alt="" class="w-100 img-fluid">
                        </div>
                    </div>
                </div>
                <div class="mt-50">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <div class="ail-gd-bg-3 p-4 pt-40 pb-40 rounded-16">
                                <img src="{{ asset('asset/img/ab-2.png') }}" alt="" class="w-100 img-fluid rounded-16">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="pl-40">
                                <div class="ail-step-sub-title d-flex align-items-center gap-3 mb-20">
                                    <span class="bg-black"></span>
                                    <p class="ca-two-body-clr ff-poppins fw-600 mb-0"> 02</p>
                                </div>
                                <h3 class="text-black fs-36">Customer Relationship Management (CRM) </h3>
                                <p class="mb-20">Lead & Deal Management – Track leads, nurture prospects, and close deals effectively. </p>
                                <h6 class="text-black fs-18">AI-Powered Automation </h6>
                                <p class="mb-20">As your business expands, CorporaOne automates repetitive tasks, optimizes workflows, and enhances efficiency without additional workload.</p>
                                <ul class="ail-work-list list-unstyled d-flex align-items-center gap-4 flex-wrap">
                                    <li class="d-flex gap-3">
                                        <svg class="mt-5-ov" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path opacity="0.3" d="M8.9998 1.80005C5.0308 1.80005 1.7998 5.03105 1.7998 9.00005C1.7998 12.969 5.0308 16.2 8.9998 16.2C12.9688 16.2 16.1998 12.969 16.1998 9.00005C16.1998 5.03105 12.9688 1.80005 8.9998 1.80005ZM7.1998 13.5L3.5998 9.90005L4.8688 8.63105L7.1998 10.953L13.1308 5.02205L14.3998 6.30005L7.1998 13.5Z" fill="#6672FB" />
                                            <path d="M9 0C4.032 0 0 4.032 0 9C0 13.968 4.032 18 9 18C13.968 18 18 13.968 18 9C18 4.032 13.968 0 9 0ZM9 16.2C5.031 16.2 1.8 12.969 1.8 9C1.8 5.031 5.031 1.8 9 1.8C12.969 1.8 16.2 5.031 16.2 9C16.2 12.969 12.969 16.2 9 16.2ZM13.131 5.022L7.2 10.953L4.869 8.631L3.6 9.9L7.2 13.5L14.4 6.3L13.131 5.022Z" fill="#476EFB" />
                                        </svg>
                                        <div class="ail-work__content">
                                            <p class="text-black fw-700 mb-0"> Custom UI Settings </p>
                                        </div>
                                    </li>
                                    <li class="d-flex gap-3">
                                        <svg class="mt-5-ov" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path opacity="0.3" d="M8.9998 1.80005C5.0308 1.80005 1.7998 5.03105 1.7998 9.00005C1.7998 12.969 5.0308 16.2 8.9998 16.2C12.9688 16.2 16.1998 12.969 16.1998 9.00005C16.1998 5.03105 12.9688 1.80005 8.9998 1.80005ZM7.1998 13.5L3.5998 9.90005L4.8688 8.63105L7.1998 10.953L13.1308 5.02205L14.3998 6.30005L7.1998 13.5Z" fill="#6672FB" />
                                            <path d="M9 0C4.032 0 0 4.032 0 9C0 13.968 4.032 18 9 18C13.968 18 18 13.968 18 9C18 4.032 13.968 0 9 0ZM9 16.2C5.031 16.2 1.8 12.969 1.8 9C1.8 5.031 5.031 1.8 9 1.8C12.969 1.8 16.2 5.031 16.2 9C16.2 12.969 12.969 16.2 9 16.2ZM13.131 5.022L7.2 10.953L4.869 8.631L3.6 9.9L7.2 13.5L14.4 6.3L13.131 5.022Z" fill="#476EFB" />
                                        </svg>
                                        <div class="ail-work__content">
                                            <p class="text-black fw-700 mb-0">Custom Branding </p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-40">
                    <div class="ail-main-bg p-5 rounded-16">
                        <div class="row align-items-center">
                            <div class="col-lg-6">
                                <div class="ail-step-sub-title d-flex align-items-center gap-3 mb-20">
                                    <span class="bg-black"></span>
                                    <p class="ca-two-body-clr ff-poppins fw-600 mb-0">At Tinos Software and Security Solutions LLP</p>
                                </div>
                                <h3 class="text-black fs-36">We bring decades of expertise in business automation</h3>
                                <p class="mb-20">We stand by our clients with unmatched support and commitment, ensuring that CorporaOne remains a reliable, high-performance, and future-ready business management solution. </p>
                                <h6 class="text-black fs-18"> Enterprise Software Development </h6>
                                <ul class="ail-work-list list-unstyled">
                                    <li class="d-flex gap-3">
                                        <svg class="mt-5-ov" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path opacity="0.3" d="M8.9998 1.80005C5.0308 1.80005 1.7998 5.03105 1.7998 9.00005C1.7998 12.969 5.0308 16.2 8.9998 16.2C12.9688 16.2 16.1998 12.969 16.1998 9.00005C16.1998 5.03105 12.9688 1.80005 8.9998 1.80005ZM7.1998 13.5L3.5998 9.90005L4.8688 8.63105L7.1998 10.953L13.1308 5.02205L14.3998 6.30005L7.1998 13.5Z" fill="#6672FB"></path>
                                            <path d="M9 0C4.032 0 0 4.032 0 9C0 13.968 4.032 18 9 18C13.968 18 18 13.968 18 9C18 4.032 13.968 0 9 0ZM9 16.2C5.031 16.2 1.8 12.969 1.8 9C1.8 5.031 5.031 1.8 9 1.8C12.969 1.8 16.2 5.031 16.2 9C16.2 12.969 12.969 16.2 9 16.2ZM13.131 5.022L7.2 10.953L4.869 8.631L3.6 9.9L7.2 13.5L14.4 6.3L13.131 5.022Z" fill="#476EFB"></path>
                                        </svg>
                                        <div class="ail-work__content">
                                            <p class="fch-40 mb-0"> Building scalable, secure, and customizable solutions tailored to business needs. </p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-lg-6">
                                <div class="pl-15">
                                    <div class="bg-sky-blue p-4 rounded-16">
                                        <img src="{{ asset('asset/img/ab-3.png') }}" alt="" class="w-100 img-fluid">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Work -->
        <section class="mk-faq bg-white pt-60 pb-60 overflow-hidden">
            <div class="container">
                <div class="row align-items-center justify-content-between g-5">
                    <div class="col-xl-7">
                        <div class="mk-faq-feature">
                            <img src="{{ asset('asset/img/faq.png') }}" alt="not found" class="img-fluid">
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="mk-faq-content">
                            <div class="mk-title mb-30">
                                <span class="mk-subtitle fw-bold mb-3">Frequently Asked Question</span>
                                <h2 class="mk-heading mb-3">Freely Asked Questions</h2>
                                <p class="mb-0">Globally whiteboard global web-readiness rather than holistic action items. Uniquely communicate synergistic markets.</p>
                            </div>
                            <div class="mk-accordion accordion" id="mk-accordion">
                                @if(!empty($faqs))
                                    @foreach($faqs as $key => $faq)
                                        <div class="accordion-item {{ $key == 0 ? 'active' : '' }}">
                                            <div class="accordion-header">
                                                <a href="#acc_{{ $key }}" class="accordion-button {{ $key == 0 ? '' : 'collapsed' }}" data-bs-toggle="collapse">
                                                    {{ $faq['faq_questions'] }}
                                                </a>
                                            </div>
                                            <div class="accordion-collapse collapse {{ $key == 0 ? 'show' : '' }}" id="acc_{{ $key }}" data-bs-parent="#mk-accordion">
                                                <div class="accordion-body">
                                                    <p class="mb-0">{{ $faq['faq_answer'] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p>No FAQs available at the moment.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!--testimonial section start-->
        <section class="testimonial-section ptb-120 ">
            <div class="container">
                <div class="row justify-content-center align-content-center">
                    <div class="col-md-10 col-lg-6">
                        <div class="section-heading text-center">
                            <h4 class="h5 text-primary">Testimonial</h4>
                            <h2>What They Say About Us</h2>
                            <p>Uniquely promote adaptive quality vectors rather than stand-alone e-markets pontificate alternative architectures with accurate schemas.</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="position-relative w-100">
                            <div class="swiper testimonialSwiper">
                                <div class="swiper-wrapper">
                                    @if(!empty($testimonials) && is_array($testimonials))
                                        @foreach($testimonials as $testimonial)
                                            @php
                                                $avatar = !empty($testimonial['testimonials_user_avtar']) 
                                                    ? asset('uploads/landing_page_image/' . $testimonial['testimonials_user_avtar']) 
                                                    : asset('uploads/default-avatar.png');
                                                $user = $testimonial['testimonials_user'] ?? 'Anonymous';
                                                $designation = $testimonial['testimonials_designation'] ?? 'User';
                                                $description = $testimonial['testimonials_description'] ?? 'No feedback.';
                                                    $stars = $testimonial['testimonials_star'] ?? 5;
                                                @endphp
                                                <div class="swiper-slide">
                                                    <div class="border border-2 p-5 rounded-custom position-relative">
                                                        <img src="{{ asset('asset/img/testimonial/quotes-dot.svg') }}" alt="quotes" width="100" class="img-fluid position-absolute left-0 top-0 z--1 p-3">
                                                        <div class="d-flex mb-32 align-items-center">
                                                            <img src="{{ $avatar }}" class="img-fluid me-3 rounded" width="60" alt="user">
                                                            <div class="author-info">
                                                                <h6 class="mb-0">{{ $user }}</h6>
                                                                <small>{{ $designation }}</small>
                                                            </div>
                                                        </div>
                                                        <blockquote>
                                                            <h6>Testimonial</h6>
                                                            "{{ $description }}"
                                                        </blockquote>
                                                        <ul class="review-rate mb-0 mt-2 list-unstyled list-inline">
                                                            @for ($i = 0; $i < $stars; $i++)
                                                                <li class="list-inline-item"><i class="fas fa-star text-warning"></i></li>
                                                            @endfor
                                                        </ul>
                                                        <img src="{{ asset('asset/img/testimonial/quotes.svg') }}" alt="quotes" class="position-absolute right-0 bottom-0 z--1 pe-4 pb-4">
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                                <div class="swiper-nav-control">
                                    <span class="swiper-button-next"></span>
                                    <span class="swiper-button-prev"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section> <!--testimonial section end-->

        <!-- [ Demo Section ] Start -->
      
<!-- Demo Modal -->
<div class="modal fade" id="demoModal" tabindex="-1" aria-labelledby="demoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-animated">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="demoModalLabel">Request a Demo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mk-box-content">
                    <div class="mk-title">
                        <span class="mk-subtitle mb-3">Let's Try!</span>
                        <h2 class="mk-heading mb-3">For Demo</h2>
                    </div>

                    <!-- Error Messages (for non-AJAX fallback) -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Main Form (AJAX Submission) -->
                    <form action="{{ route('join_us.user.store') }}" method="POST" class="mk-sb-form mt-40" id="demoForm">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" name="name" id="name" placeholder="Enter your Name" class="form-control" style="outline: 2px solid #B0B0B0;" value="{{ old('name') }}" required>
                            <span class="text-danger error-text name_error"></span>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" name="phone" id="phone" placeholder="Enter your Phone Number" class="form-control" style="outline: 2px solid #B0B0B0;" value="{{ old('phone') }}" required>
                            <span class="text-danger error-text phone_error"></span>
                        </div>

                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" name="company_name" id="company_name" placeholder="Enter your Company Name" class="form-control" style="outline: 2px solid #B0B0B0;" value="{{ old('company_name') }}" required>
                            <span class="text-danger error-text company_name_error"></span>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" placeholder="Enter your Email" class="form-control formTextbox" style="outline: 2px solid #B0B0B0;" value="{{ old('email') }}" required>
                            <span class="text-danger error-text email_error"></span>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="mk-submit-btn btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert JS (Add this at the end of your HTML, before </body>) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- AJAX Form Submission Script -->
<script>
    document.getElementById('demoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;

        // Clear previous errors
        document.querySelectorAll('.error-text').forEach(el => el.textContent = '');

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('demoModal'));
                if (modal) modal.hide();

                // Show success popup
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    confirmButtonText: 'OK'
                });

                // Reset form
                form.reset();
            } else if (data.errors) {
                // Display validation errors
                for (const field in data.errors) {
                    const errorElement = document.querySelector(`.${field}_error`);
                    if (errorElement) {
                        errorElement.textContent = data.errors[field][0];
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong. Please try again.',
            });
        });
    });
</script>

<!-- Success Popup Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-animated">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if (session('success'))
                    <p>{{ session('success') }}</p>
                @else
                    <p>Your registration was successful!</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Show success modal if session('success') is present
        @if (session('success'))
            document.addEventListener('DOMContentLoaded', function () {
                var successModal = new bootstrap.Modal(document.getElementById('successModal'), {
                    keyboard: false
                });
                successModal.show();
            });
        @endif
    </script>
@endpush

<!-- [ Demo Section ] End -->
        <!--faq section start-->
        


        <!--trusted partners start-->
        


        <footer class="footer-section">
            <!--footer top start-->
            <!--for light footer add .footer-light class and for dark footer add .bg-dark .text-white class-->
            <div class="footer-top bg-dark text-white  ptb-120" style="background: url('asset/img/page-header-bg.svg')no-repeat bottom right">
                <div class="container">
                    <div class="row justify-content-between">
                        <div class="col-md-8 col-lg-4 mb-md-4 mb-lg-0">
                            <div class="footer-single-col">
                               
                                <p>CorporaOne is an AI-integrated Business Management System designed to revolutionize 
                                the way businesses operate. Developed by Tinos Software and Security Solutions LLP, 
                                CorporaOne streamlines operations with cutting-edge artificial intelligence, automation, and 
                                intuitive features tailored for modern enterprises. </p>

                                
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-7 mt-4 mt-md-0 mt-lg-0">
                            <div class="row">
                                <div class="col-md-4 col-lg-4 mt-4 mt-md-0 mt-lg-0">
                                    <div class="footer-single-col">
                                        <h3>Primary Pages</h3>
                                        <ul class="list-unstyled footer-nav-list mb-lg-0">
                                            <li><a href="{{ route('dashboard.landingpage') }}" class="text-decoration-none">Home</a></li>
                                            <li><a href="{{ route('frontend.about') }}" class="text-decoration-none">About Us</a></li>
                                            <li><a href="{{ route('frontend.features') }}" class="text-decoration-none">Features</a></li>
                                            
                                            </li>
                                            
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 mt-4 mt-md-0 mt-lg-0">
                                    <div class="footer-single-col">
                                        <h3>Pages</h3>
                                        <ul class="list-unstyled footer-nav-list mb-lg-0">
                                            <li><a href="{{ route('frontend.showplans') }}" class="text-decoration-none">Pricing</a></li>
                                            <li><a href="{{ route('frontend.faq') }}" class="text-decoration-none">FAQ</a></li>
                                            <li><a href="{{ route('login') }}" class="text-decoration-none">Sign In</a></li>
                                            
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 mt-4 mt-md-0 mt-lg-0">
                                    <div class="footer-single-col">
                                        <h3>Template</h3>
                                        <ul class="list-unstyled footer-nav-list mb-lg-0">
                                            <li><a href="{{ route('frontend.terms_and_conditions') }}" class="text-decoration-none">Terms And Conditions</a></li>
                                            <li><a href="{{ route('frontend.privacy_policy') }}" class="text-decoration-none">Privacy Policy</a></li>
                                            <li><a href="{{ route('register') }}" class="text-decoration-none">Sign Up</a></li>
                                            
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!--footer top end-->

            <!--footer bottom start-->
            <div class="footer-bottom bg-dark text-white  py-4">l
                <div class="container">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-7 col-lg-7">
                            <div class="copyright-text">
                                <p class="mb-lg-0 mb-md-0">&copy; 2025 CorporaOne Rights Reserved.  <a href="https://tinos.co.in/" class="text-decoration-none">Powerd By Tinos Software And Security Solutions LLP</a></p>
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

    <script>
$(document).ready(function() {
    $('a[href="#demo-section"]').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: $('#demo-section').offset().top - 100 // Adjust offset for header height
        }, 800); // Smooth scroll duration in milliseconds
    });
});
</script>
</body>

</html>