@php
    use App\Models\Utility;
    $settings = \Modules\LandingPage\Entities\LandingPageSetting::settings();
    $logo = Utility::get_file('uploads/landing_page_image');
    $sup_logo = Utility::get_file('uploads/logo');

    $metatitle = $adminSettings['meta_title'] ?? '';
    $metsdesc = $adminSettings['meta_desc'] ?? '';
    $meta_image = \App\Models\Utility::get_file('uploads/meta/');
    $meta_logo = $adminSettings['meta_image'] ?? '';
    $get_cookie = \App\Models\Utility::getCookieSetting();

    $setting = \App\Models\Utility::colorset();
    $SITE_RTL = $adminSettings['SITE_RTL'] ?? '';
    $lang = \App::getLocale('lang');
    if ($lang == 'ar' || $lang == 'he') {
        $SITE_RTL = 'on';
    }

    $color = $setting['color'] ?? 'theme-3';
    if(($setting['color_flag'] ?? '') == 'true') {
        $themeColor = 'custom-color';
    } else {
        $themeColor = $color;
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $SITE_RTL == 'on' ? 'rtl' : '' }}">

<head>
    <title>{{ $setting['title_text'] ?? config('app.name', 'ERPGo') }}</title>
    <!-- Meta -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />

    <meta name="title" content="{{ $metatitle }}">
    <meta name="description" content="{{ $metsdesc }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title" content="{{ $metatitle }}">
    <meta property="og:description" content="{{ $metsdesc }}">
    <meta property="og:image" content="{{ $meta_image . $meta_logo }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title" content="{{ $metatitle }}">
    <meta property="twitter:description" content="{{ $metsdesc }}">
    <meta property="twitter:image" content="{{ $meta_image . $meta_logo }}">

    <!-- Favicon icon -->
    <link rel="icon" href="{{ $sup_logo . '/' . ($adminSettings['company_favicon'] ?? 'favicon.ico') . '?' . time() }}" type="image/x-icon" />

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}" />

    <!-- vendor css -->
    @if ($SITE_RTL == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
    @endif

    @if (($setting['cust_darklayout'] ?? '') == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @endif

    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/landingpage/css/landing-page.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/landingpage/css/custom.css') }}" />

    <style>
        :root {
            --color-customColor: {{ $color }};
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        /* Enhanced Navigation */
        .main-header .navbar {
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        /* Glass cards */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" style="stop-color:%23ffffff;stop-opacity:0.1"/><stop offset="100%" style="stop-color:%23ffffff;stop-opacity:0"/></radialGradient></defs><circle cx="20%" cy="20%" r="200" fill="url(%23a)"/><circle cx="80%" cy="80%" r="300" fill="url(%23a)"/></svg>');
            opacity: 0.5;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            background: linear-gradient(45deg, #ffffff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            font-weight: 300;
        }

        /* Modern Buttons */
        .btn-modern {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
        }

        .btn-primary-modern {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary-modern {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary-modern:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }

        /* Hero Stats */
        .hero-stats {
            margin-top: 3rem;
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            color: white;
            min-width: 150px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            display: block;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Hero Image */
        .hero-image {
            perspective: 1000px;
        }

        .hero-image img {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transform: rotateY(-10deg) rotateX(5deg);
            transition: transform 0.3s ease;
        }

        .hero-image:hover img {
            transform: rotateY(-5deg) rotateX(2deg);
        }

        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: linear-gradient(45deg, #f8fafc, #e2e8f0);
        }

        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-stats {
                justify-content: center;
                gap: 1rem;
            }
            
            .stat-card {
                min-width: 120px;
            }
        }

        /* Animations */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">
</head>

@if (($setting['cust_darklayout'] ?? '') == 'on')
    <body class="{{ $themeColor }} landing-dark">
@else
    <body class="{{ $themeColor }}">
@endif

<!-- [ Header ] start -->
<header class="main-header">
    @if (($settings['topbar_status'] ?? '') == 'on')
        <div class="announcement bg-dark text-center p-2">
            <p class="mb-0">{!! $settings['topbar_notification_msg'] ?? '' !!}</p>
        </div>
    @endif
    @if (($settings['menubar_status'] ?? '') == 'on')
        <div class="container">
            <nav class="navbar navbar-expand-md default top-nav-collapse">
                <div class="header-left">
                    <a class="navbar-brand bg-transparent" href="#">
                        <img src="{{ $logo . '/' . ($settings['site_logo'] ?? 'logo.png') }}" alt="logo">
                    </a>
                </div>
                <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" href="#home">{{ $settings['home_title'] ?? 'Home' }}</a>
                        </li>
                        @if (($settings['feature_status'] ?? '') == 'on')
                        <li class="nav-item">
                            <a class="nav-link" href="#features">{{ $settings['feature_title'] ?? 'Features' }}</a>
                        </li>
                        @endif
                        @if (($settings['plan_status'] ?? '') == 'on')
                        <li class="nav-item">
                            <a class="nav-link" href="#plan">{{ $settings['plan_title'] ?? 'Plans' }}</a>
                        </li>
                        @endif
                        @if (($settings['faq_status'] ?? '') == 'on')
                        <li class="nav-item">
                            <a class="nav-link" href="#faq">{{ $settings['faq_title'] ?? 'FAQ' }}</a>
                        </li>
                        @endif
                    </ul>
                    <button class="navbar-toggler bg-primary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
                <div class="ms-auto d-flex justify-content-end gap-2">
                    <a href="{{ route('login') }}" class="btn btn-outline-dark rounded">
                        <span class="hide-mob me-2">{{ __('Login') }}</span> 
                        <i data-feather="log-in"></i>
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-dark rounded">
                        <span class="hide-mob me-2">{{ __('Register') }}</span> 
                        <i data-feather="user-check"></i>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </nav>
        </div>
    @endif
</header>
<!-- [ Header ] End -->

<!-- [ Hero Section ] start -->
@if (($settings['home_status'] ?? '') == 'on')
    <section class="hero-section" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <div class="animate-on-scroll">
                        <div class="badge bg-light text-primary rounded-pill px-3 py-2 mb-4">
                            <i class="fas fa-star me-2"></i>
                            {{ $settings['home_offer_text'] ?? '✨ New Features Available' }}
                        </div>
                        
                        <h1 class="hero-title">
                            {{ $settings['home_heading'] ?? 'Build Something Amazing Today' }}
                        </h1>
                        
                        <p class="hero-subtitle">
                            {{ $settings['home_description'] ?? 'Transform your business with our cutting-edge platform. Designed for modern teams who demand excellence.' }}
                        </p>
                        
                        <div class="d-flex flex-wrap gap-3 mb-4">
                            @if (!empty($settings['home_buy_now_link']))
                                <a href="{{ $settings['home_buy_now_link'] }}" class="btn btn-modern btn-primary-modern">
                                    <i class="fas fa-rocket me-2"></i>
                                    {{ __('Start Free Trial') }}
                                </a>
                            @endif
                            @if (!empty($settings['home_live_demo_link']))
                                <a href="{{ $settings['home_live_demo_link'] }}" class="btn btn-modern btn-secondary-modern">
                                    <i class="fas fa-play me-2"></i>
                                    {{ __('Watch Demo') }}
                                </a>
                            @endif
                        </div>
                        
                        <!-- Hero Stats -->
                        <div class="hero-stats">
                            <div class="stat-card">
                                <span class="stat-number">50K+</span>
                                <span class="stat-label">Happy Customers</span>
                            </div>
                            <div class="stat-card">
                                <span class="stat-number">99.9%</span>
                                <span class="stat-label">Uptime</span>
                            </div>
                            <div class="stat-card">
                                <span class="stat-number">4.9★</span>
                                <span class="stat-label">Rating</span>
                            </div>
                            @if (!empty($settings['home_trusted_by']))
                            <div class="stat-card">
                                <span class="stat-number">{{ $settings['home_trusted_by'] }}</span>
                                <span class="stat-label">Companies</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="hero-image animate-on-scroll">
                        @if (!empty($settings['home_banner']))
                            <img src="{{ $logo . '/' . $settings['home_banner'] }}" alt="Hero Image" class="img-fluid">
                        @else
                            <div class="placeholder-image bg-light rounded d-flex align-items-center justify-content-center" style="height: 400px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
<!-- [ Hero Section ] end -->

<!-- [ Features Section ] start -->
@if (($settings['feature_status'] ?? '') == 'on')
    <section class="features-section" id="features">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">{{ $settings['feature_title'] ?? 'Powerful Features' }}</h2>
                    <p class="lead text-muted">{!! $settings['feature_description'] ?? 'Everything you need to succeed, built with modern technology and designed for scale.' !!}</p>
                </div>
            </div>
            
            <div class="row g-4">
                @php
                    $features = json_decode($settings['feature_of_features'] ?? '[]', true) ?: [
                        ['feature_heading' => 'Fast & Secure', 'feature_description' => 'Lightning-fast performance with enterprise-grade security', 'feature_logo' => 'shield-check'],
                        ['feature_heading' => 'Easy Integration', 'feature_description' => 'Seamlessly integrate with your existing tools and workflows', 'feature_logo' => 'puzzle'],
                        ['feature_heading' => '24/7 Support', 'feature_description' => 'Round-the-clock support from our expert team', 'feature_logo' => 'headphones'],
                        ['feature_heading' => 'Analytics', 'feature_description' => 'Detailed insights and analytics to drive your decisions', 'feature_logo' => 'chart-line'],
                        ['feature_heading' => 'Scalable', 'feature_description' => 'Grows with your business from startup to enterprise', 'feature_logo' => 'trending-up'],
                        ['feature_heading' => 'Mobile Ready', 'feature_description' => 'Fully responsive design that works on any device', 'feature_logo' => 'smartphone']
                    ];
                @endphp
                
                @foreach (array_slice($features, 0, 6) as $key => $feature)
                    <div class="col-lg-4 col-md-6">
                        <div class="feature-card animate-on-scroll">
                            <div class="feature-icon">
                                <i class="fas fa-{{ $feature['feature_logo'] ?? 'star' }}"></i>
                            </div>
                            <h4 class="fw-bold mb-3">{{ $feature['feature_heading'] ?? 'Feature' }}</h4>
                            <p class="text-muted">{{ $feature['feature_description'] ?? 'Feature description goes here.' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
<!-- [ Features Section ] end -->

<!-- [ Simple Pricing Section ] start -->
@if (($settings['plan_status'] ?? '') == 'on')
    <section class="subscription bg-primary section-gap" id="plan">
        <div class="container">
            <div class="row mb-5 justify-content-center">
                <div class="col-xxl-6">
                    <div class="title text-center mb-4">
                        <span class="d-block mb-2 fw-bold text-uppercase text-white">{{ __('PLAN') }}</span>
                        <h2 class="mb-4 text-white">{!! $settings['plan_heading'] ?? 'Choose Your Plan' !!}</h2>
                        <p class="text-white-50">{!! $settings['plan_description'] ?? 'Select the perfect plan for your needs.' !!}</p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                @php
                    try {
                        $all_plans = \App\Models\Plan::where('price', '>', 0)->where('is_disable', 1)->orderBy('price')->get();
                        $admin_payment_setting = Utility::getAdminPaymentSetting();
                    } catch (\Exception $e) {
                        $all_plans = collect([]);
                        $admin_payment_setting = ['currency_symbol' => '$'];
                    }
                @endphp
                
                @forelse($all_plans->take(3) as $key => $plan)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 {{ $key == 1 ? 'border-warning' : '' }}" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 15px;">
                            @if($key == 1)
                                <div class="card-header bg-warning text-dark text-center fw-bold">
                                    {{ __('Most Popular') }}
                                </div>
                            @endif
                            <div class="card-body text-center text-white">
                                <h5 class="card-title">{{ $plan->name }}</h5>
                                <h3 class="text-warning">
                                    {{ ($admin_payment_setting['currency_symbol'] ?? '$') . $plan->price }}
                                    <small class="text-white-50">/ {{ $plan->duration }}</small>
                                </h3>
                                <div class="plan-features mt-3">
                                    @if($plan->description)
                                        @foreach(explode("\n", $plan->description) as $feature)
                                            @if(trim($feature) && !str_starts_with(trim($feature), '##'))
                                                <p class="mb-1 text-white-50">
                                                    <i class="fas fa-check text-success me-2"></i>
                                                    {{ trim($feature) }}
                                                </p>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer bg-transparent text-center">
                                <a href="{{ Auth::check() ? route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                   class="btn {{ $key == 1 ? 'btn-warning' : 'btn-outline-light' }} w-100 rounded-pill">
                                   {{ __('Get Started') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <p class="text-white">No pricing plans available at the moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endif
<!-- [ Simple Pricing Section ] end -->

<!-- [ Footer ] start -->
<footer class="site-footer bg-gray-100">
    <div class="container">
        <div class="footer-row">
            <div class="ftr-col cmp-detail">
                <div class="footer-logo mb-3">
                    <a href="#">
                        <img src="{{ $logo . '/' . ($settings['site_logo'] ?? 'logo.png') }}" alt="logo">
                    </a>
                </div>
                <p>{!! $settings['site_description'] ?? 'Your trusted platform for all your needs.' !!}</p>
            </div>
            
            <div class="ftr-col">
                <ul class="list-unstyled">
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>

            @if (($settings['joinus_status'] ?? '') == 'on')
            <div class="ftr-col ftr-subscribe">
                <h2>{!! $settings['joinus_heading'] ?? 'Join Our Newsletter' !!}</h2>
                <p>{!! $settings['joinus_description'] ?? 'Stay updated with our latest news and offers.' !!}</p>
                <form method="post" action="{{ route('join_us_store') }}">
                    @csrf
                    <div class="input-wrapper border border-dark">
                        <input type="email" name="email" placeholder="Type your email address..." required>
                        <button type="submit" class="btn btn-dark rounded-pill">{{ __('Join Us') }}!</button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>
    <div class="border-top border-dark text-center p-2">
        <p class="mb-0"> &copy;
            {{ date('Y') }}
            {{ Utility::getValByName('footer_text') ?: config('app.name', 'ERPGo') }}
        </p>
    </div>
</footer>
<!-- [ Footer ] end -->

<!-- Required Js -->
<script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>

<script>
    // Start [ Menu hide/show on scroll ]
    let ost = 0;
    document.addEventListener("scroll", function() {
        let cOst = document.documentElement.scrollTop;
        if (cOst == 0) {
            document.querySelector(".navbar").classList.add("top-nav-collapse");
        } else if (cOst > ost) {
            document.querySelector(".navbar").classList.add("top-nav-collapse");
            document.querySelector(".navbar").classList.remove("default");
        } else {
            document.querySelector(".navbar").classList.add("default");
            document
                .querySelector(".navbar")
                .classList.remove("top-nav-collapse");
        }
        ost = cOst;
    });

    // Animate on Scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });

    // Initialize Feather Icons
    feather.replace();
</script>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/custom.js') }}"></script>

@if ($message = Session::get('success'))
<script>
    show_toastr('success', '{!! $message !!}');
</script>
@endif
@if ($message = Session::get('error'))
<script>
    show_toastr('error', '{!! $message !!}');
</script>
@endif

@if (($get_cookie['enable_cookie'] ?? '') == 'on')
    @include('layouts.cookie_consent')
@endif

</body>
</html>