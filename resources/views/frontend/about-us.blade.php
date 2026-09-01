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
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                        <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                    </div>
                </div>
            </div>
            <!--offcanvas menu end-->
        </header> <!--header section end-->

        <!--about header section start-->
        <section class="about-header-section ptb-120 position-relative overflow-hidden bg-dark" style="background: url('asset/img/page-header-bg.svg')no-repeat center right">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-heading-wrap d-flex justify-content-between z-5 position-relative">
                            <div class="about-content-left">
                                <div class="about-info mb-5">
                                    <h1 class="fw-bold display-5">Grow your Business & Customer Satisfaction with
                                        CorporaOne</h1>
                                    <p class="lead">CorporaOne, powered by Tinos Software and Security Solutions LLP, we bring the 
                                    best of AI, automation, and business intelligence to create an all-in-one business 
                                    management solution. </p>
                                    
                                </div>
                                <img src="{{ asset('asset/img/s3.png') }}" alt="about" class="img-fluid about-img-first mt-5 rounded-custom shadow">
                            </div>
                            <div class="about-content-right">
                                <img src="{{ asset('asset/img/s1.png') }}" alt="about" class="img-fluid mb-5 rounded-custom shadow">
                                <img src="{{ asset('asset/img/s2.png') }}" alt="about" class="rounded-custom about-img-last shadow">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white position-absolute bottom-0 h-25 bottom-0 left-0 right-0 z-2 py-5">
            </div>
        </section>
        <!--about header section end-->

        
        <!--our story section end-->

        <!--feature section two start-->
        <section class="feature-section-two ptb-120 bg-light-subtle">
            <div class="container">
                <div class="row align-items-center justify-content-center">
                    <div class="col-lg-6 col-md-12">
                        <div class="section-heading">
                            <h4 class="h5 text-primary">Our Values</h4>
                            <h2> Seamless Integrations</h2>
                            <p> CorporaOne is an AI-integrated Business Management System designed to revolutionize 
                                the way businesses operate. Developed by Tinos Software and Security Solutions LLP, 
                                CorporaOne streamlines operations with cutting-edge artificial intelligence, automation, and 
                                intuitive features tailored for modern enterprises. <br><br>
                                With multi-language support, AI-powered automation, and deep integration 
                                capabilities, CorporaOne enhances productivity, reduces manual efforts, and empowers 
                                businesses with intelligent insights. </p>
                            <ul class="list-unstyled mt-5">
                                <li class="d-flex align-items-start mb-4">
                                    <div class="icon-box bg-primary rounded me-4">
                                        <i class="fas fa-bezier-curve text-white"></i>
                                    </div>
                                    <div class="icon-content">
                                        <h3 class="h5">Innovation</h3>
                                        <p> We embrace AI, automation, and emerging technologies to drive 
                                        business excellence. 
                                        </p>
                                    </div>
                                </li>
                                <li class="d-flex align-items-start mb-4">
                                    <div class="icon-box bg-danger rounded me-4">
                                        <i class="fas fa-fingerprint text-white"></i>
                                    </div>
                                    <div class="icon-content">
                                        <h3 class="h5">Collaboration & Empowerment </h3>
                                        <p>We foster teamwork, learning, and continuous 
                                        improvement, both within our organization and for our clients. 
                                        </p>
                                    </div>
                                </li>
                                <li class="d-flex align-items-start mb-4">
                                    <div class="icon-box bg-dark rounded me-4">
                                        <i class="fas fa-cog text-white"></i>
                                    </div>
                                    <div class="icon-content">
                                        <h3 class="h5">Integrity & Transparency</h3>
                                        <p> We build relationships based on trust, honesty, and 
                                        ethical business practices. 
                                        </p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-7">
                        <div class="feature-img-wrap position-relative d-flex flex-column align-items-end">
                            <ul class="img-overlay-list list-unstyled position-absolute">
                                <li class="d-flex align-items-center bg-white rounded shadow-sm p-3">
                                    <i class="fas fa-check bg-primary text-white rounded-circle"></i>
                                    <h6 class="mb-0">15+ Years of Industry Experience </h6>
                                </li>
                                <li class="d-flex align-items-center bg-white rounded shadow-sm p-3">
                                    <i class="fas fa-check bg-primary text-white rounded-circle"></i>
                                    <h6 class="mb-0">Multi-Sector Exposure</h6>
                                </li>
                                <li class="d-flex align-items-center bg-white rounded shadow-sm p-3">
                                    <i class="fas fa-check bg-primary text-white rounded-circle"></i>
                                    <h6 class="mb-0">Proven Track Record</h6>
                                </li>
                            </ul>
                            <img src="{{ asset('asset/img/feature-img3.jpg') }}" alt="feature image" class="img-fluid rounded-custom">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--feature section two end-->

        

        <!--our location address start-->
        

        <!--cat subscribe start-->
        
        <!--cat subscribe end-->

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
            <div class="footer-bottom bg-dark text-white  py-4">
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
</body>

</html>