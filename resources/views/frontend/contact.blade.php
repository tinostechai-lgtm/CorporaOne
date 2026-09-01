<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Quiety creative Saas, software technology, Saas agency & business Bootstrap 5 Html template.">
    <meta name="author" content="ThemeTags">
    <link rel="icon" href="{{ asset('asset/img/favicon.png') }}" type="image/png" sizes="16x16">
    <title>Contact Us - Software & IT Solutions HTML Template</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lily+Script+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('asset/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/custom.css') }}">
</head>
<body>
    <div id="preloader" class="bg-light-subtle">
        <div class="preloader-wrap">
            <img src="{{ asset('asset/img/favicon.png') }}" alt="logo" class="img-fluid preloader-icon">
            <div class="loading-bar"></div>
        </div>
    </div>
    <div class="main-wrapper">
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
                    <div class="collapse navbar-collapse justify-content-center">
                        <ul class="nav col-12 col-md-auto justify-content-center main-menu">
                            <li><a href="{{ route('dashboard.landingpage') }}" class="nav-link">Home</a></li>
                            <li><a href="{{ route('frontend.features') }}" class="nav-link">Features</a></li>
                            <li><a href="{{ route('frontend.about') }}" class="nav-link">About Us</a></li>
                            <li><a href="{{ route('frontend.showplans') }}" class="nav-link">Pricing</a></li>
                            <li><a href="{{ route('frontend.faq') }}" class="nav-link">FAQ</a></li>
                            <li><a href="{{ route('frontend.contact') }}" class="nav-link">Contact Us</a></li>
                        </ul>
                    </div>
                    <div class="action-btns text-end me-5 me-lg-0 d-none d-md-block d-lg-block">
                        <a href="{{ route('login') }}" class="btn btn-link text-decoration-none me-2">Login</a>
                        <a href="{{ route('dashboard.landingpage') }}#demo-section" class="btn btn-primary">DEMO</a>
                    </div>
                </div>
            </nav>
        </header>

        <section class="page-header position-relative overflow-hidden ptb-120 bg-dark" style="background: url('{{ asset('asset/img/page-header-bg.svg') }}')no-repeat bottom left">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <h1 class="display-5 fw-bold">Contact Us</h1>
                        <p class="lead">Seamlessly actualize client-based users after out-of-the-box value data through frictionless expertise.</p>
                    </div>
                </div>
                <div class="bg-circle rounded-circle circle-shape-3 position-absolute bg-dark-light right-5"></div>
            </div>
        </section>

        <section class="contact-promo ptb-120">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-4 col-md-6 mt-4 mt-lg-0">
                        <div class="contact-us-promo p-5 bg-white rounded-custom custom-shadow text-center d-flex flex-column h-100">
                            <span class="fas fa-comment fa-3x text-primary"></span>
                            <div class="contact-promo-info mb-4">
                                <h5>Chat with us</h5>
                                <p>We've got live Social Experts waiting to help you <strong>monday to friday</strong> from <strong>9am to 5pm EST.</strong></p>
                            </div>
                            <a href="mailto:hellothemetags@gmail.com" class="btn btn-link mt-auto">Chat with us</a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mt-4 mt-lg-0">
                        <div class="contact-us-promo p-5 bg-white rounded-custom custom-shadow text-center d-flex flex-column h-100">
                            <span class="fas fa-envelope fa-3x text-primary"></span>
                            <div class="contact-promo-info mb-4">
                                <h5>Email Us</h5>
                                <p>Simple drop us an email at <strong>hellothemetags@gmail.com</strong> and you'll receive a reply within 24 hours</p>
                            </div>
                            <a href="mailto:hellothemetags@gmail.com" class="btn btn-primary mt-auto">Email Us</a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mt-4 mt-lg-0">
                        <div class="contact-us-promo p-5 bg-white rounded-custom custom-shadow text-center d-flex flex-column h-100">
                            <span class="fas fa-phone fa-3x text-primary"></span>
                            <div class="contact-promo-info mb-4">
                                <h5>Give us a call</h5>
                                <p>Give us a ring. Our Experts are standing by <strong>monday to friday</strong> from <strong>9am to 5pm EST.</strong></p>
                            </div>
                            <a href="tel:00-976-561-008" class="btn btn-link mt-auto">00-976-561-008</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="contact-us-form pt-60 pb-120" style="background: url('{{ asset('asset/img/shape/contact-us-bg.svg') }}')no-repeat center bottom">
            <div class="container">
                <div class="row justify-content-lg-between align-items-center">
                    <div class="col-lg-6 col-md-8">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        <div class="section-heading">
                            <h2>Talk to Our Sales & Marketing Department Team</h2>
                            <p>Collaboratively promote client-focused convergence vis-a-vis customer directed alignments via standardized infrastructures.</p>
                        </div>
                        <form action="{{ route('contact.store') }}" method="POST" class="register-form">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="first_name" class="mb-1">First name <span class="text-danger">*</span></label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                            id="first_name" name="first_name" required 
                                            placeholder="First name" value="{{ old('first_name') }}">
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="last_name" class="mb-1">Last name</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                            id="last_name" name="last_name" 
                                            placeholder="Last name" value="{{ old('last_name') }}">
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="phone" class="mb-1">Phone <span class="text-danger">*</span></label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                            id="phone" name="phone" required 
                                            placeholder="Phone" value="{{ old('phone') }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="email" class="mb-1">Email <span class="text-danger">*</span></label>
                                    <div class="input-group mb-3">
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                            id="email" name="email" required 
                                            placeholder="Email" value="{{ old('email') }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="mb-1">Message <span class="text-danger">*</span></label>
                                    <div class="input-group mb-3">
                                        <textarea class="form-control @error('message') is-invalid @enderror" 
                                            id="message" name="message" required 
                                            placeholder="How can we help you?" 
                                            style="height: 120px">{{ old('message') }}</textarea>
                                        @error('message')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-4">Get in Touch</button>
                        </form>
                    </div>
                    <div class="col-lg-5 col-md-10">
                        <div class="contact-us-img">
                            <img src="{{ asset('asset/img/contact-us-img-2.svg') }}" alt="contact us" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="footer-section">
            <div class="footer-top bg-dark text-white ptb-120" style="background: url('{{ asset('asset/img/page-header-bg.svg') }}')no-repeat bottom right">
                <div class="container">
                    <div class="row justify-content-between">
                        <div class="col-md-8 col-lg-4 mb-md-4 mb-lg-0">
                            <div class="footer-single-col">
                                <div class="footer-single-col mb-4">
                                    <img src="{{ asset('asset/img/logo-white.png') }}" alt="logo" class="img-fluid logo-white">
                                    <img src="{{ asset('asset/img/logo-color.png') }}" alt="logo" class="img-fluid logo-color">
                                </div>
                                <p>Our latest news, articles, and resources, we will sent to your inbox weekly.</p>
                                <form class="newsletter-form position-relative d-block d-lg-flex d-md-flex">
                                    <input type="text" class="input-newsletter form-control me-2" placeholder="Enter your email" name="email" required="" autocomplete="off">
                                    <input type="submit" value="Subscribe" data-wait="Please wait..." class="btn btn-primary mt-3 mt-lg-0 mt-md-0">
                                </form>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom bg-dark text-white py-4">
                <div class="container">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-7 col-lg-7">
                            <div class="copyright-text">
                                <p class="mb-lg-0 mb-md-0">© 2021 Quiety Rights Reserved. Designed By <a href="https://themetags.com/" class="text-decoration-none">ThemeTags</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="{{ asset('asset/js/vendors/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('asset/js/vendors/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('asset/js/vendors/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('asset/js/vendors/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('asset/js/vendors/parallax.min.js') }}"></script>
    <script src="{{ asset('asset/js/vendors/aos.js') }}"></script>
    <script src="{{ asset('asset/js/vendors/massonry.min.js') }}"></script>
    <script src="{{ asset('asset/js/app.js') }}"></script>
</body>
</html>