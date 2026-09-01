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
                            <a class="nav-link dropdown-toggle" href="{{ route('dashboard.landingpage') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

        <!--page header section start-->
        <section class="page-header position-relative overflow-hidden ptb-120 bg-dark" style="background: url('asset/img/page-header-bg.svg')no-repeat bottom left">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-md-12">
                        <h1 class="display-5 fw-bold">Features Style</h1>
                        <p class="lead"> Your growth is our priority. Experience the future of business automation with 
                        CorporaOne! </p>
                    </div>
                </div>
                <div class="bg-circle rounded-circle circle-shape-3 position-absolute bg-dark-light right-5"></div>
            </div>
        </section>
        <!--page header section end-->


      

        <!--style guide block start-->
        
        <section class="ptb-60">
            <div class="container">
                
                <div class="row mb--150">
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="single-payment-step bg-white p-4 mb-4 mb-lg-0">
                            <img src="{{ asset('asset/img/pi-1.png') }}" alt="icon" />
                            <h6 class="mt-3">Double Entry Accounting</h6>
                            <p class="mb-0">
                            Ensures precise financial tracking 
                            using professional accounting principles. 
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="single-payment-step bg-white p-4 mb-4 mb-lg-0">
                            <img src="{{ asset('asset/img/pi-2.png') }}" alt="icon" />
                            <h6 class="mt-3">Banking & Transactions</h6>
                            <p class="mb-0">
                            Easily manage multiple bank 
                            accounts, transactions, and transfers. 
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="single-payment-step bg-white p-4 mb-4 mb-lg-0">
                            <img src="{{ asset('asset/img/pi-3.png') }}" alt="icon" />
                            <h6 class="mt-3">QR Code Support </h6>
                            <p class="mb-0">
                            Generate QR codes for purchase orders, 
                            proposals, invoices, and bills. 
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="single-payment-step bg-white p-4 mb-4 mb-lg-0">
                            <img src="{{ asset('asset/img/pi-4.png') }}" alt="icon" />
                            <h6 class="mt-3"> Budget Planner </h6>
                            <p class="mb-0">
                            AI-powered budget planning and real-time 
                            expense tracking for financial optimization. 
                            </p>
                        </div>
                    </div>

                    
                    
                </div>
            </div>
        </section>
<br><br>
        <section class="ptb-60">
            <div class="container">
                
                <div class="row mb--150">
                <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="single-payment-step bg-white p-4 mb-4 mb-lg-0">
                            <img src="{{ asset('asset/img/pi-4.png') }}" alt="icon" />
                            <h6 class="mt-3"> Revenue & Expense Management</h6>
                            <p class="mb-0">
                            Monitor and analyze income 
                            streams and expenditures. 
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="single-payment-step bg-white p-4 mb-4 mb-lg-0">
                            <img src="{{ asset('asset/img/pi-3.png') }}" alt="icon" />
                            <h6 class="mt-3">Profit & Loss & Balance Sheet </h6>
                            <p class="mb-0">
                            Generate detailed financial 
                            reports for business decision-making. 
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="single-payment-step bg-white p-4 mb-4 mb-lg-0">
                            <img src="{{ asset('asset/img/pi-2.png') }}" alt="icon" />
                            <h6 class="mt-3">Trial Balance & Ledger Summary</h6>
                            <p class="mb-0">
                            Maintain accurate financial 
                            records and bookkeeping.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="single-payment-step bg-white p-4 mb-4 mb-lg-0">
                            <img src="{{ asset('asset/img/pi-1.png') }}" alt="icon" />
                            <h6 class="mt-3">Tax Management </h6>
                            <p class="mb-0">
                            Automate tax calculations, ensure compliance tracking, and generate detailed reports.
                            </p>
                        </div>
                    </div>

                    
                    
                </div>
            </div>
        </section>

        <section class="dg-service-section bg-light-subtle pb-120 position-relative z-1 overflow-hidden" style="margin-top:150px;">
            <img src="{{ asset('asset/img/digital-agency/sr-line.png') }}" alt="doted line" class="position-absolute end-0 z--1 sr-line">
            <span class="sr-circle-1 dg-circle-style-1 rounded-circle position-absolute z--1"></span>
            <span class="sr-circle-2 dg-circle-style-2 rounded-circle position-absolute z--1"></span>
            <div class="container position-relative z-1" style="margin-top:-70px;">
                <div class="row justify-content-center">
                    <div class="col-xl-5">
                        <div class="section-title text-center mb-5">
                            <span class="fw-bold dg-text-primary">OUR CORE FEATURES </span>
                            <h2 class="mt-2 heading-dg-color clr-text">Customer Relationship Management </h2>
                        </div>
                    </div>
                </div>
                <div class="row g-4 justify-content-center">
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-1">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-1">
                                  <svg width="24"
                                       height="26"
                                       viewBox="0 0 24 26"
                                       fill="none"
                                       xmlns="http://www.w3.org/2000/svg">
                                      <path d="M22.6 7V19C22.6 22.6 20.8 25 16.6 25H7C2.8 25 1 22.6 1 19V7C1 3.4 2.8 1 7 1H16.6C20.8 1 22.6 3.4 22.6 7Z"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-miterlimit="10"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                      <path d="M13.3 15.4H18.4M8.20004 20.2H18.4M16 1V10.432C16 10.96 15.376 11.224 14.992 10.876L12.208 8.308C12.0976 8.20405 11.9517 8.14616 11.8 8.14616C11.6484 8.14616 11.5025 8.20405 11.392 8.308L8.60804 10.876C8.22404 11.224 7.60004 10.96 7.60004 10.432V1H16Z"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-miterlimit="10"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                  </svg>
                              </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color">
                                Lead & Deal Management </h5>
                            </a>
                            <p class="mb-3 text-dg-color"> Track leads, nurture prospects, and close deals 
                            effectively.  </p>
                            <span class="number-count position-relative color-1 fw-semibold">01</span>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-2">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-2">
                                  <svg width="26"
                                       height="26"
                                       viewBox="0 0 26 26"
                                       fill="none"
                                       xmlns="http://www.w3.org/2000/svg">
                                      <path d="M10.12 22.6H15.88C20.68 22.6 22.6 20.68 22.6 15.88V10.12C22.6 5.32002 20.68 3.40002 15.88 3.40002H10.12C5.31999 3.40002 3.39999 5.32002 3.39999 10.12V15.88C3.39999 20.68 5.31999 22.6 10.12 22.6Z"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                      <path d="M8.212 3.4V1M13 3.4V1M17.8 3.4V1M22.6 8.2H25M22.6 13H25M22.6 17.8H25M17.8 22.6V25M13.012 22.6V25M8.212 22.6V25M1 8.2H3.4M1 13H3.4M1 17.8H3.4M11.2 19H14.8C17.8 19 19 17.8 19 14.8V11.2C19 8.2 17.8 7 14.8 7H11.2C8.2 7 7 8.2 7 11.2V14.8C7 17.8 8.2 19 11.2 19Z"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                      <path d="M13 10.24L11.872 12.208C11.62 12.64 11.824 13 12.328 13H13.672C14.176 13 14.38 13.36 14.128 13.792L13 15.76"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                  </svg>
                              </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color">Customer Communication Module</h5>
                            </a>
                            <p class="mb-3 text-dg-color">  Manage customer interactions and maintain 
                            records.  </p>
                            <span class="number-count position-relative color-2 fw-semibold">02</span>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-3">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-3">
                                  <svg width="24"
                                       height="26"
                                       viewBox="0 0 24 26"
                                       fill="none"
                                       xmlns="http://www.w3.org/2000/svg">
                                      <path d="M20.2 13V5.8C20.2 3.148 20.2 1 15.4 1H8.2C3.4 1 3.4 3.148 3.4 5.8V13M5.8 13C1 13 1 15.148 1 17.8V19C1 22.312 1 25 7 25H16.6C21.4 25 22.6 22.312 22.6 19V17.8C22.6 15.148 22.6 13 17.8 13C16.6 13 16.264 13.252 15.64 13.72L14.416 15.016C14.0795 15.3739 13.6732 15.6592 13.2223 15.8542C12.7714 16.0492 12.2853 16.1497 11.794 16.1497C11.3027 16.1497 10.8166 16.0492 10.3657 15.8542C9.91475 15.6592 9.50852 15.3739 9.172 15.016L7.96 13.72C7.336 13.252 7 13 5.8 13Z"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-miterlimit="10"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                      <path d="M10.06 9.67593H14.056M9.06396 6.07593H15.064"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                  </svg>
                              </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color">Client Database </h5>
                            </a>
                            <p class="mb-3 text-dg-color"> Centralized storage of customer information, purchase history, 
                            and communication logs. </p>
                            <span class="number-count position-relative color-3 fw-semibold">03</span>
                        </div>
                    </div>
                    
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-4">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-4">
                                  <svg width="26"
                                       height="26"
                                       viewBox="0 0 26 26"
                                       fill="none"
                                       xmlns="http://www.w3.org/2000/svg">
                                      <path d="M5.7904 4.58979V6.991M10.5928 4.58979V6.991M5.7904 18.997V21.3982M10.5928 18.997V21.3982M15.3952 5.7904H20.1976M15.3952 20.1976H20.1976M21.7824 10.5928H4.21761C2.44072 10.5928 1 9.14007 1 7.37519V4.21761C1 2.44072 2.45273 1 4.21761 1H21.7824C23.5593 1 25 2.45273 25 4.21761V7.37519C25 9.14007 23.5473 10.5928 21.7824 10.5928ZM21.7824 25H4.21761C2.44072 25 1 23.5473 1 21.7824V18.6248C1 16.8479 2.45273 15.4072 4.21761 15.4072H21.7824C23.5593 15.4072 25 16.8599 25 18.6248V21.7824C25 23.5473 23.5473 25 21.7824 25Z"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                  </svg>
                              </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color">
                                Automated Follow-Ups & Reminders</h5>
                            </a>
                            <p class="mb-3 text-dg-color">AI-driven reminders for sales follow-ups and 
                            meetings. </p>
                            <span class="number-count position-relative color-4 fw-semibold">04</span>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-4">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-4">
                                  <svg width="26"
                                       height="26"
                                       viewBox="0 0 26 26"
                                       fill="none"
                                       xmlns="http://www.w3.org/2000/svg">
                                      <path d="M5.7904 4.58979V6.991M10.5928 4.58979V6.991M5.7904 18.997V21.3982M10.5928 18.997V21.3982M15.3952 5.7904H20.1976M15.3952 20.1976H20.1976M21.7824 10.5928H4.21761C2.44072 10.5928 1 9.14007 1 7.37519V4.21761C1 2.44072 2.45273 1 4.21761 1H21.7824C23.5593 1 25 2.45273 25 4.21761V7.37519C25 9.14007 23.5473 10.5928 21.7824 10.5928ZM21.7824 25H4.21761C2.44072 25 1 23.5473 1 21.7824V18.6248C1 16.8479 2.45273 15.4072 4.21761 15.4072H21.7824C23.5593 15.4072 25 16.8599 25 18.6248V21.7824C25 23.5473 23.5473 25 21.7824 25Z"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                  </svg>
                              </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color">POS (Point of Sale) Module</h5>
                            </a>
                            <p class="mb-3 text-dg-color"> Handles retail transactions, invoice generation, and sales tracking. </p>
                            <span class="number-count position-relative color-4 fw-semibold">05</span>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-3">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-3">
                                  <svg width="24"
                                       height="26"
                                       viewBox="0 0 24 26"
                                       fill="none"
                                       xmlns="http://www.w3.org/2000/svg">
                                      <path d="M20.2 13V5.8C20.2 3.148 20.2 1 15.4 1H8.2C3.4 1 3.4 3.148 3.4 5.8V13M5.8 13C1 13 1 15.148 1 17.8V19C1 22.312 1 25 7 25H16.6C21.4 25 22.6 22.312 22.6 19V17.8C22.6 15.148 22.6 13 17.8 13C16.6 13 16.264 13.252 15.64 13.72L14.416 15.016C14.0795 15.3739 13.6732 15.6592 13.2223 15.8542C12.7714 16.0492 12.2853 16.1497 11.794 16.1497C11.3027 16.1497 10.8166 16.0492 10.3657 15.8542C9.91475 15.6592 9.50852 15.3739 9.172 15.016L7.96 13.72C7.336 13.252 7 13 5.8 13Z"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-miterlimit="10"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                      <path d="M10.06 9.67593H14.056M9.06396 6.07593H15.064"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                  </svg>
                              </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color"> Quotation & Invoice Generation</h5>
                            </a>
                            <p class="mb-3 text-dg-color">Quickly create and send professional quotations 
                            and invoices.</p>
                            <span class="number-count position-relative color-3 fw-semibold">06</span>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-2">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-2">
                                  <svg width="26"
                                       height="26"
                                       viewBox="0 0 26 26"
                                       fill="none"
                                       xmlns="http://www.w3.org/2000/svg">
                                      <path d="M10.12 22.6H15.88C20.68 22.6 22.6 20.68 22.6 15.88V10.12C22.6 5.32002 20.68 3.40002 15.88 3.40002H10.12C5.31999 3.40002 3.39999 5.32002 3.39999 10.12V15.88C3.39999 20.68 5.31999 22.6 10.12 22.6Z"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                      <path d="M8.212 3.4V1M13 3.4V1M17.8 3.4V1M22.6 8.2H25M22.6 13H25M22.6 17.8H25M17.8 22.6V25M13.012 22.6V25M8.212 22.6V25M1 8.2H3.4M1 13H3.4M1 17.8H3.4M11.2 19H14.8C17.8 19 19 17.8 19 14.8V11.2C19 8.2 17.8 7 14.8 7H11.2C8.2 7 7 8.2 7 11.2V14.8C7 17.8 8.2 19 11.2 19Z"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                      <path d="M13 10.24L11.872 12.208C11.62 12.64 11.824 13 12.328 13H13.672C14.176 13 14.38 13.36 14.128 13.792L13 15.76"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                  </svg>
                              </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color">Sales & Payment Processing  </h5>
                            </a>
                            <p class="mb-3 text-dg-color"> Integrated multi-payment processing with receipts and reconciliation.</p>
                            <span class="number-count position-relative color-2 fw-semibold">07</span>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="dg-service-item bg-white rounded">
                            <div class="icon-wrapper rounded-circle shadow-1">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle w-100 h-100 bg-color-1">
                                  <svg width="24"
                                       height="26"
                                       viewBox="0 0 24 26"
                                       fill="none"
                                       xmlns="http://www.w3.org/2000/svg">
                                      <path d="M22.6 7V19C22.6 22.6 20.8 25 16.6 25H7C2.8 25 1 22.6 1 19V7C1 3.4 2.8 1 7 1H16.6C20.8 1 22.6 3.4 22.6 7Z"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-miterlimit="10"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                      <path d="M13.3 15.4H18.4M8.20004 20.2H18.4M16 1V10.432C16 10.96 15.376 11.224 14.992 10.876L12.208 8.308C12.0976 8.20405 11.9517 8.14616 11.8 8.14616C11.6484 8.14616 11.5025 8.20405 11.392 8.308L8.60804 10.876C8.22404 11.224 7.60004 10.96 7.60004 10.432V1H16Z"
                                            stroke="white"
                                            stroke-width="1.5"
                                            stroke-miterlimit="10"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />
                                  </svg>
                              </span>
                            </div>
                            <a href="service-single.html">
                                <h5 class="mt-4 mb-2 heading-dg-color">Customer Statement Reports</h5>
                            </a>
                            <p class="mb-3 text-dg-color">Generate financial summaries, reports, and insights for clients with one click.</p>
                            <span class="number-count position-relative color-1 fw-semibold">08</span>
                        </div>
                    </div>

                </div>

                
               
            </div>
        </section> 

        <div class="aih-info-card-lg-area bg-light-subtle ptb-60">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        <div class="text-center">
                            <h2 class="aih-title aih-color-two fs-48 fw-600 mb-20">Project,Task Management And  Employee,Business Management Tools</h2>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-4 col-md-6">
                        <div class="aih-info-card-item bgc-white aih-shadow-hover p-4 ptb-30 rounded-10 mt-20">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset('asset/img/ai_home/info_icon_3.svg') }}" alt="">
                                <h5 class="aih-color-two fs-24 fw-600"> Project Management System</h5>
                            </div>
                            <p class="ca-two-body-clr mt-20 mb-0"> Plan, execute, and monitor projects with Gantt 
                            charts and work breakdown structures. </p>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="aih-info-card-item bgc-white aih-shadow-hover p-4 ptb-30 rounded-10 mt-20">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset('asset/img/ai_home/info_icon_4.svg') }}" alt="">
                                <h5 class="aih-color-two fs-24 fw-600"> Task Assignment & Tracking</h5>
                            </div>
                            <p class="ca-two-body-clr mt-20 mb-0"> Create, assign, and track tasks with real-time 
                            progress updates.</p>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="aih-info-card-item bgc-white aih-shadow-hover p-4 ptb-30 rounded-10 mt-20">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset('asset/img/ai_home/info_icon_5.svg') }}" alt="">
                                <h5 class="aih-color-two fs-24 fw-600">Timesheets </h5>
                            </div>
                            <p class="ca-two-body-clr mt-20 mb-0">Capture project hours to improve time management and budget 
                            planning. </p>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="aih-info-card-item bgc-white aih-shadow-hover p-4 ptb-30 rounded-10 mt-20">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset('asset/img/ai_home/info_icon_6.svg') }}" alt="">
                                <h5 class="aih-color-two fs-24 fw-600">
                                Bug Tracking</h5>
                            </div>
                            <p class="ca-two-body-clr mt-20 mb-0"> Identify, prioritize, and resolve software bugs efficiently. </p>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="aih-info-card-item bgc-white aih-shadow-hover p-4 ptb-30 rounded-10 mt-20">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset('asset/img/ai_home/info_icon_7.svg') }}" alt="">
                                <h5 class="aih-color-two fs-24 fw-600">Task Calendar & Tracker</h5>
                            </div>
                            <p class="ca-two-body-clr mt-20 mb-0"> Visualize timelines, deadlines, and dependencies in an 
                            interactive calendar.</p>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="aih-info-card-item bgc-white aih-shadow-hover p-4 ptb-30 rounded-10 mt-20">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset('asset/img/ai_home/info_icon_8.svg') }}" alt="">
                                <h5 class="aih-color-two fs-24 fw-600">Goal & KPI Tracking </h5>
                            </div>
                            <p class="ca-two-body-clr mt-20 mb-0">Set and monitor business objectives with AI-powered 
                            performance tracking. </p>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="aih-info-card-item bgc-white aih-shadow-hover p-4 ptb-30 rounded-10 mt-20">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset('asset/img/ai_home/info_icon_5.svg') }}" alt="">
                                <h5 class="aih-color-two fs-24 fw-600">Event & Notice Board</h5>
                            </div>
                            <p class="ca-two-body-clr mt-20 mb-0">Publish announcements, company events, and internal 
                            news. </p>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="aih-info-card-item bgc-white aih-shadow-hover p-4 ptb-30 rounded-10 mt-20">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset('asset/img/ai_home/info_icon_4.svg') }}" alt="">
                                <h5 class="aih-color-two fs-24 fw-600">HR Admin Tools</h5>
                            </div>
                            <p class="ca-two-body-clr mt-20 mb-0">  Manage employee transfers, promotions, grievances, and 
                            disciplinary actions.</p>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="aih-info-card-item bgc-white aih-shadow-hover p-4 ptb-30 rounded-10 mt-20">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset('asset/img/ai_home/info_icon_3.svg') }}" alt="">
                                <h5 class="aih-color-two fs-24 fw-600"> User Access & Permissions</h5>
                            </div>
                            <p class="ca-two-body-clr mt-20 mb-0">Role-based user management for security and 
                            streamlined access.  </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        


        <!--style guide block start-->
        <div class="style-guide">
            

            <!--feature section start-->
            <section class="feature-section ptb-120">
                <div class="container">
                    <div class="feature-color bg-primary-soft px-5 rounded-custom position-relative">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-lg-5 col-md-12">
                                <div class="feature-content-wrap pe-lg-4 ptb-60 p-lg-0 mb-5 mb-lg-0">
                                    <h5 class="text-primary h6 fw-bold">Features</h5>
                                    <h2>Best Features that Help you Build Quality for Your Business</h2>
                                    <p> CorporaOne integrates multiple AI models to automate 
                                        workflows, provide predictive insights, and enhance decision-making.Our system connects effortlessly with Slack, Telegram, 
                                        Twilio, Zoom, Google Calendar, and over 20+ payment gateways, ensuring smooth 
                                        business operations.With end-to-end encryption, customizable role-based access, 
                                        and cloud storage, our system ensures high security and scalability for businesses of all 
                                        sizes.
                                    </p>
                                    
                                </div>
                            </div>
                            <div class="col-lg-7 col-md-12">
                                <div class="row align-items-center justify-content-center position-relative mt--100 z-2">
                                    <div class="col-md-6">
                                        <div class="cta-card rounded-custom text-center shadow p-5 bg-white my-4">
                                            <div class="feature-icon d-inline-block bg-dark rounded mb-4">
                                                <i class="fas fa-bezier-curve text-white fa-2x"></i>
                                            </div>
                                            <h3 class="h5 fw-bold">Customer Relationship Management </h3>
                                            <p class="mb-0">Track leads, nurture prospects, and close deals 
                                            effectively. Manage customer interactions and maintain 
                                            records.  </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="cta-card rounded-custom text-center shadow p-5 bg-white my-4">
                                            <div class="feature-icon d-inline-block bg-danger rounded mb-4">
                                                <i class="fas fa-comment text-white fa-2x"></i>
                                            </div>
                                            <h3 class="h5 fw-bold">AI Integration</h3>
                                            <p class="mb-0"> Uses multiple OpenAI models to enhance automation and business 
                                            intelligence.  </p>
                                        </div>
                                        <div class="cta-card rounded-custom text-center shadow p-5 bg-white my-4">
                                            <div class="feature-icon d-inline-block bg-success rounded mb-4">
                                                <i class="fas fa-eye text-white fa-2x"></i>
                                            </div>
                                            <h3 class="h5 fw-bold">Cloud-Powered</h3>
                                            <p class="mb-0">Secure data storage, easy access, and real-time updates for 
                                            businesses on the go. </p>
                                        </div>
                                    </div>
                                    <!--animated shape start-->
                                    <ul class="position-absolute animate-element parallax-element z--1">
                                        <li class="layer" data-depth="0.06">
                                            <img src="{{ asset('asset/img/shape/shape-bg-3.svg') }}" alt="shape" class="img-fluid">
                                        </li>
                                    </ul>
                                    <!--animated shape end-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!--feature section end-->

        </div>
        <!--style guide block end-->


        
        <div class="style-guide">
            

            
            <section class="feature-section two-bg-dark-light ptb-120">
                <div class="container">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-lg-6 col-md-6">
                            <div class="image-wrap">
                                <img src="{{ asset('asset/img/dashboard-img1.png') }}" alt="feature img" class="img-fluid">
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-6">
                            <div class="feature-content-wrap">
                                <h5 class="h6 text-primary">Advanced Features</h5>
                                <h2>How We Put Customers First</h2>
                                <p>We understand that every business is unique. Our 
                                    customizable dashboards, role-based access, and modular features ensure a perfect fit 
                                    for your operations.Designed with intuitive navigation, real-time insights, 
and automation, CorporaOne simplifies complex business processes, saving time and 
effort.</p>
                                <ul class="list-unstyled mt-5">
                                    <li class="d-flex align-items-start mb-4">
                                        <div class="icon-box bg-primary rounded me-4">
                                            <i class="fas fa-bezier-curve text-white"></i>
                                        </div>
                                        <div class="icon-content">
                                            <h3 class="h5"> Data-Driven Efficiency</h3>
                                            <p>Advanced analytics, reporting, and forecasting tools help 
                                            businesses optimize processes and maximize profitability. 
                                            </p>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-4">
                                        <div class="icon-box bg-danger rounded me-4">
                                            <i class="fas fa-fingerprint text-white"></i>
                                        </div>
                                        <div class="icon-content">
                                            <h3 class="h5">Multi-Platform Accessibility </h3>
                                            <p>Access CorporaOne anytime, anywhere with its 
                                            cloud-based infrastructure, offering real-time updates and a seamless user experience. 
                                            </p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
           

        </div>
        


        
        <div class="style-guide">
            

            
             <section class="feature-section ptb-120">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10">
                            <div class="section-heading text-center">
                                <h5 class="h6 text-primary">Features</h5>
                                <h2>With all The Features you Need</h2>
                                <p>CorporaOne, powered by Tinos Software and Security Solutions LLP, we bring the 
best of AI, automation, and business intelligence to create an all-in-one business 
management solution. </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="feature-grid mt-5">
                                <div class="feature-card border border-light border-2 bg-white highlight-card rounded-custom p-5">
                                    <div class="feature-icon rounded bg-primary mb-32">
                                        <i class="fas fa-chart-simple fa-2x text-white"></i>
                                    </div>
                                    <div class="feature-content">
                                        <h3 class="h5">Why CorporaOne is Built for Growth</h3>
                                        <p>To revolutionize business management with AI-driven, scalable, and intelligent solutions, 
                                        empowering organizations worldwide to automate, optimize, and grow efficiently. </p>
                                        
                                        <h6 class="mt-4">Included with...</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li class="py-1"><i
                                                class="fas fa-check-circle me-2 text-primary"></i>Adaptable to Businesses of All Sizes
                                            </li>
                                            <li class="py-1"><i class="fas fa-check-circle me-2 text-primary"></i>Modular & Customizable
                                            </li>
                                            <li class="py-1"><i class="fas fa-check-circle me-2 text-primary"></i>Cloud-Based & Multi-User Support 
                                            </li>
                                            <li class="py-1"><i class="fas fa-check-circle me-2 text-primary"></i> Multi-Language & Multi-Business Support 
                                            </li>
                                            <li class="py-1"><i class="fas fa-check-circle me-2 text-primary"></i>
                                            AI-Powered Automation 
                                            </li>
                                            <li class="py-1"><i class="fas fa-check-circle me-2 text-primary"></i>Easy Integration with Third-Party Tools
                                            </li>
                                            <li class="py-1"><i class="fas fa-check-circle me-2 text-primary"></i>Future-Ready & Scalable Infrastructure
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="feature-card border border-light border-2 bg-white rounded-custom p-5">
                                    <div class="feature-icon rounded bg-success mb-32">
                                        <i class="fas fa-file-lines fa-2x text-white"></i>
                                    </div>
                                    <div class="feature-content">
                                        <h3 class="h5"> Business & Financial Management</h3>
                                        <p class="mb-0">Ensures precise financial tracking 
                                        using professional accounting principles and Easily manage multiple bank 
                                        accounts, transactions, and transfers.  </p>
                                    </div>
                                </div>
                                <div class="feature-card border border-light border-2 bg-white rounded-custom p-5">
                                    <div class="feature-icon rounded bg-danger mb-32">
                                        <i class="fas fa-user-friends fa-2x text-white"></i>
                                    </div>
                                    <div class="feature-content">
                                        <h3 class="h5">HR & Payroll Management</h3>
                                        <p class="mb-0"> Handles recruitment, onboarding, payroll, and employee 
                                        engagement and Streamlines salary calculations, tax deductions, and direct 
                                        deposits. </p>
                                    </div>
                                </div>
                                <div class="feature-card border border-light border-2 bg-white rounded-custom p-5">
                                    <div class="feature-icon rounded bg-dark mb-32">
                                        <i class="fas fa-spell-check fa-2x text-white"></i>
                                    </div>
                                    <div class="feature-content">
                                        <h3 class="h5"> Project & Task Management</h3>
                                        <p class="mb-0">Plan, execute, and monitor projects with Gantt 
                                        charts and work breakdown structures. Create, assign, and track tasks with real-time progress updates. </p>
                                    </div>
                                </div>
                                <div class="feature-card border border-light border-2 bg-white rounded-custom p-5">
                                    <div class="feature-icon rounded bg-warning mb-32">
                                        <i class="fas fa-cog fa-2x text-white"></i>
                                    </div>
                                    <div class="feature-content">
                                        <h3 class="h5"> Inventory & Warehouse Management </h3>
                                        <p class="mb-0">Real-time stock tracking, order fulfillment, and predictive 
                                        demand forecasting.Organize and categorize all products/services for 
                                        smooth operations. </p>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </section> 
           

            <!--features grid section end-->

        </div>
        <!--style guide block end-->


        <!--style guide block start-->
        <div class="style-guide">
            

            
            <section class="feature-section ptb-120 bg-light-subtle">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10">
                            <div class="section-heading text-center">
                                <h5 class="h6 text-primary">Features</h5>
                                <h2>With all The Features you Need</h2>
                                <p>Your growth is our priority. Experience the future of business automation with 
                                CorporaOne! </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="feature-grid mt-5">
                                
                                @if(!empty($feature_of_features) && is_iterable($feature_of_features))
                                    @foreach($feature_of_features as $feature)
                                        <div class="feature-card border border-light border-2 bg-white rounded-custom p-5 text-center">
                                            <div class="rounded mb-4">
                                                <i class="fas fa-cog fa-2x text-primary"></i>
                                            </div>
                                            <h3 class="h5">{{ $feature['feature_heading'] }}</h3>
                                            <p>{{ $feature['feature_description'] }}</p>
                                        </div>
                                    @endforeach
                                @else
                                    <p>No features available.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!--features grid section end-->

        </div>
        <!--style guide block end-->


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