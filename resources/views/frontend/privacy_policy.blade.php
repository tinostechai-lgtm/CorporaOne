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
    <div class="main-wrapper">

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
        </header> 

        <!--support content section start-->
        <section class="support-content ptb-120">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-lg-4 col-md-4 d-none d-md-block d-lg-block">
                        <div class="support-article-sidebar sticky-sidebar">
                            <a href="javascript:history.back();" class="btn btn-primary mb-4 btn-sm"><i
                                    class="fas fa-angle-left me-2"></i> Go Back</a>
                            <div class="nav flex-column nav-pills support-article-tab bg-light-subtle rounded-custom p-5">
                                <h5>Related Content</h5>
                                <a href="#" class="text-muted text-decoration-none py-2 d-block">Information We Collect</a>
                                <a href="#" class="text-muted text-decoration-none py-2 d-block">How We Use Your Data</a>
                                <a href="#" class="text-muted text-decoration-none py-2 d-block">How We Protect Your Data</a>
                                <a href="#" class="text-muted text-decoration-none py-2 d-block">Data Sharing & Third-Party Integrations</a>
                                <a href="#" class="text-muted text-decoration-none py-2 d-block">Data Retention Policy</a>
                                <a href="#" class="text-muted text-decoration-none py-2 d-block">Cookies & Tracking Technologies</a>
                                <a href="#" class="text-muted text-decoration-none py-2 d-block">Your Rights & Choices</a>
                                <a href="#" class="text-muted text-decoration-none py-2 d-block">International Data Transfers</a>
                                <a href="#" class="text-muted text-decoration-none py-2 d-block">Policy for Minors</a>
                                <a href="#" class="text-muted text-decoration-none py-2 d-block">Changes to This Privacy Policy</a>

                            </div>
                            <div class="bg-light-subtle p-5 mt-4 rounded-custom quick-support">
                                <a href="tel:+917907358458"
                                    class="text-decoration-none text-muted d-flex align-items-center py-2">
                                    <div class="quick-support-icon rounded-circle bg-success-soft me-3">
                                        <i class="fas fa-headset text-success"></i>
                                    </div>
                                    <div class="contact-option-text"> +91 7907358458</div>
                                </a>
                                <a href="mailto:support@tinos.co.in"
                                    class="text-decoration-none text-muted d-flex align-items-center py-2">
                                    <div class="quick-support-icon rounded-circle bg-primary-soft me-3">
                                        <i class="fas fa-envelope text-primary"></i>
                                    </div>
                                    <div class="contact-option-text">support@tinos.co.in</div>
                                </a>
                                <a href="http://www.tinos.co.in" target="_blank"
                                    class="text-decoration-none text-muted d-flex align-items-center py-2">
                                    <div class="quick-support-icon rounded-circle bg-danger-soft me-3">
                                        <i class="fa-solid fa-globe"></i>
                                    </div>
                                    <div class="contact-option-text">www.tinos.co.in</div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-8 p-lg-5">
                        <div class="support-article-wrap">
                            <h1 class="display-5 mb-4 fw-bold">Privacy Policy</h1>
                            <!-- <p><strong>Effective Date:</strong> [Insert Date]</p>
                            <p><strong>Last Updated:</strong> [Insert Date]</p> -->
                            <p>Welcome to CorporaOne, an AI-powered Business Management System developed and managed by Tinos Software and Security Solutions LLP. This Privacy Policy explains how we collect, use, store, and protect your personal and business data. By using CorporaOne, you consent to the practices outlined in this Privacy Policy.</p>
        
                            <div class="job-details-info my-5">
                                <h3 class="h5">1. Information We Collect</h3>
                                <p>We collect different types of data to enhance your user experience and ensure the smooth functioning of CorporaOne.</p>
                                <h4 class="h6">1.1. Personal Information</h4>
                                <p>When you sign up or use our services, we may collect:</p>
                                <ul class="content-list list-unstyled">
                                    <li>Name</li>
                                    <li>Email address</li>
                                    <li>Phone number</li>
                                    <li>Company name</li>
                                    <li>Billing and payment details</li>
                                </ul>
                                <h4 class="h6">1.2. Business & Transactional Data</h4>
                                <p>To facilitate business operations, we may collect:</p>
                                <ul class="content-list list-unstyled">
                                    <li>Employee and HR records (attendance, payroll, etc.)</li>
                                    <li>Inventory, sales, and purchase records</li>
                                    <li>Customer relationship management (CRM) data</li>
                                    <li>Project management and business performance insights</li>
                                </ul>
                                <h4 class="h6">1.3. Usage & Technical Data</h4>
                                <p>We collect information on how you interact with our platform, including:</p>
                                <ul class="content-list list-unstyled">
                                    <li>IP address</li>
                                    <li>Device and browser information</li>
                                    <li>Login history and user activity</li>
                                    <li>System performance and error logs</li>
                                </ul>
        
                                <h3 class="h5">2. How We Use Your Data</h3>
                                <p>We use collected data for the following purposes:</p>
                                <ul class="content-list list-unstyled">
                                    <li><strong>Service Provisioning:</strong> To enable core functionalities like HR, accounting, CRM, and inventory management.</li>
                                    <li><strong>User Authentication & Security:</strong> To prevent unauthorized access, fraud, and security breaches.</li>
                                    <li><strong>Personalization & Enhancements:</strong> To improve user experience, AI-driven insights, and workflow automation.</li>
                                    <li><strong>Billing & Payments:</strong> To process subscriptions, renewals, and invoices.</li>
                                    <li><strong>Customer Support & Communication:</strong> To provide technical support, respond to inquiries, and send important updates.</li>
                                    <li><strong>Compliance & Legal Requirements:</strong> To comply with applicable regulations, tax requirements, and data protection laws.</li>
                                </ul>
        
                                <h3 class="h5">3. How We Protect Your Data</h3>
                                <p>We prioritize data security and privacy through the following measures:</p>
                                <ul class="content-list list-unstyled">
                                    <li><strong>Encryption:</strong> All sensitive data is encrypted during transmission and storage.</li>
                                    <li><strong>Access Control:</strong> Role-based access ensures that only authorized personnel can view or edit specific data.</li>
                                    <li><strong>Secure Cloud Hosting:</strong> We store your data on highly secure cloud infrastructure with regular backups.</li>
                                    <li><strong>Multi-Factor Authentication (MFA):</strong> Additional layers of security to prevent unauthorized logins.</li>
                                    <li><strong>Regular Security Audits:</strong> We continuously monitor our system for vulnerabilities and apply necessary updates.</li>
                                </ul>
        
                                <h3 class="h5">4. Data Sharing & Third-Party Integrations</h3>
                                <p>We DO NOT sell or trade user data. However, we may share necessary data with:</p>
                                <ul class="content-list list-unstyled">
                                    <li><strong>Third-Party Service Providers:</strong> CorporaOne integrates with payment gateways, communication tools (Slack, Telegram, Twilio), and cloud storage services to enhance functionality.</li>
                                    <li><strong>Legal & Compliance Authorities:</strong> If required by law, regulation, or government request, we may disclose information to comply with legal obligations.</li>
                                    <li><strong>Business Partners & Affiliates:</strong> With your consent, we may share data with trusted business partners for better service integration.</li>
                                </ul>
        
                                <h3 class="h5">5. Data Retention Policy</h3>
                                <ul class="content-list list-unstyled">
                                    <li><strong>Active Users:</strong> We retain your data as long as you are an active user of CorporaOne.</li>
                                    <li><strong>Inactive Accounts:</strong> If your account remains inactive for 12+ months, we may delete or anonymize your data.</li>
                                    <li><strong>Legal Obligations:</strong> Certain data (e.g., financial records) may be stored longer to comply with tax laws and regulatory requirements.</li>
                                </ul>
        
                                <h3 class="h5">6. Cookies & Tracking Technologies</h3>
                                <p>CorporaOne uses cookies and similar tracking technologies to:</p>
                                <ul class="content-list list-unstyled">
                                    <li>Remember user preferences for a seamless experience.</li>
                                    <li>Analyze website and system usage to enhance performance.</li>
                                    <li>Enable security measures to prevent fraud and unauthorized access.</li>
                                </ul>
                                <p>Users can disable cookies in browser settings, but this may impact certain features.</p>
        
                                <h3 class="h5">7. Your Rights & Choices</h3>
                                <p>Users have full control over their data. You can:</p>
                                <ul class="content-list list-unstyled">
                                    <li><strong>Access Your Data:</strong> Request a copy of the personal data we hold about you.</li>
                                    <li><strong>Edit or Update Information:</strong> Modify your profile details at any time.</li>
                                    <li><strong>Delete Your Account:</strong> Request deletion of your account and associated data.</li>
                                    <li><strong>Opt-Out of Marketing Communications:</strong> Unsubscribe from promotional emails or messages.</li>
                                </ul>
                                <p>To exercise these rights, contact us at <a href="mailto:support@tinos.co.in">support@tinos.co.in</a>.</p>
        
                                <h3 class="h5">8. International Data Transfers</h3>
                                <p>If you access CorporaOne from outside India, your data may be processed in India or other countries where our servers are located. By using our services, you consent to such transfers.</p>
        
                                <h3 class="h5">9. Policy for Minors</h3>
                                <p>CorporaOne is intended for business users and is not designed for individuals under 18 years old. We do not knowingly collect personal data from minors.</p>
        
                                <h3 class="h5">10. Changes to This Privacy Policy</h3>
                                <p>We may update this Privacy Policy periodically. If changes are made, we will notify users via:</p>
                                <ul class="content-list list-unstyled">
                                    <li>Email Notification</li>
                                    <li>System Alert upon Login</li>
                                </ul>
                                <p>Your continued use of CorporaOne after updates indicates acceptance of the revised policy.</p>
        
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--support content section end-->


        <!--footer section start-->
        <!--footer section start-->
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