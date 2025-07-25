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
    $lang = \App::getLocale('lang');
    if ($lang == 'ar' || $lang == 'he') {
        $SITE_RTL = 'on';
    }

    $color = !empty($setting['color']) ? $setting['color'] : '#2563eb';
    if(isset($setting['color_flag']) && $setting['color_flag'] == 'true') {
        $themeColor = 'custom-color';
    } else {
        $themeColor = $color;
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $SITE_RTL == 'on' ? 'rtl' : '' }}">
<head>
    <title>{{ $setting['title_text'] ? $setting['title_text'] : config('app.name', 'ERPGo') }}</title>
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

    <!-- Favicon -->
    <link rel="icon" href="{{ $sup_logo . '/' . $adminSettings['company_favicon'] . '?' . time() }}" type="image/x-icon" />

    <!-- Original CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}" />

    @if ($SITE_RTL == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
    @endif

    @if ($setting['cust_darklayout'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @endif

    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/landingpage/css/landing-page.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/landingpage/css/custom.css') }}" />

    <style>
        :root {
            --color-customColor: <?= $color ?>;
            --modern-primary: {{ $color }};
            --modern-primary-light: {{ $color }}15;
            --modern-primary-dark: {{ $color }}dd;
        }

        /* Modern Enhancements */
        .main-header {
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .main-header.scrolled {
            background: rgba(255, 255, 255, 0.95) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        /* Enhanced Hero Section */
        .main-banner {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            position: relative;
            overflow: hidden;
        }

        .main-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, var(--modern-primary-light) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(6, 182, 212, 0.1) 0%, transparent 50%);
            z-index: 1;
        }

        .main-banner .container-offset {
            position: relative;
            z-index: 2;
        }

        .main-banner h1 {
            background: linear-gradient(135deg, #1e293b 0%, var(--modern-primary) 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            font-size: 3.5rem;
            line-height: 1.1;
            margin-bottom: 1.5rem;
        }

        .special-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--modern-primary-light);
            color: var(--modern-primary);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            border: 1px solid var(--modern-primary-light);
        }

        .banner-btn .btn {
            padding: 1rem 2rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .banner-btn .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .dash-preview {
            transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
            transition: transform 0.3s ease;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .dash-preview:hover {
            transform: perspective(1000px) rotateY(-2deg) rotateX(2deg);
        }

        .preview-img {
            border-radius: 20px;
        }

        /* Enhanced Stats */
        .hero-stats {
            display: flex;
            gap: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
            margin-top: 2rem;
        }

        .hero-stat {
            text-align: center;
        }

        .hero-stat-number {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--modern-primary);
            display: block;
        }

        .hero-stat-label {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        /* Enhanced Features Section */
        .features-section .card {
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .features-section .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        /* Enhanced Element Section */
        .element-section .title h2 {
            background: linear-gradient(135deg, #1e293b 0%, var(--modern-primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Enhanced Discover Section */
        .discover-section .card {
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .discover-section .card:hover {
            transform: translateY(-5px);
        }

        /* Enhanced Screenshots */
        .screenshot-card {
            transition: all 0.3s ease;
        }

        .screenshot-card:hover {
            transform: translateY(-5px);
        }

        .screenshot-card .img-wrapper img {
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .screenshot-card:hover .img-wrapper img {
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        /* Enhanced Pricing */
        .subscription .table {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        /* Enhanced Testimonials */
        .testimonial .card {
            transition: all 0.3s ease;
        }

        .testimonial .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        /* Enhanced FAQ */
        .faqs .accordion-item {
            border: 1px solid #e2e8f0;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            overflow: hidden;
        }

        .faqs .accordion-button {
            font-weight: 600;
            padding: 1.25rem;
        }

        .faqs .accordion-button:not(.collapsed) {
            background: var(--modern-primary-light);
            color: var(--modern-primary);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }

        .animate-fade-in-right {
            animation: fadeInRight 0.8s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-banner h1 {
                font-size: 2.5rem;
            }
            
            .hero-stats {
                justify-content: center;
                gap: 1rem;
            }
            
            .banner-btn {
                justify-content: center;
            }
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">
</head>

@if ($setting['cust_darklayout'] == 'on')
    <body class="{{ $themeColor }} landing-dark">
@else
    <body class="{{ $themeColor }}">
@endif

<!-- [ Header ] start -->
<header class="main-header">
    @if ($settings['topbar_status'] == 'on')
        <div class="announcement bg-dark text-center p-2">
            <p class="mb-0">{!! $settings['topbar_notification_msg'] !!}</p>
        </div>
    @endif
    
    @if ($settings['menubar_status'] == 'on')
        <div class="container">
            <nav class="navbar navbar-expand-md default top-nav-collapse">
                <div class="header-left">
                    <a class="navbar-brand bg-transparent" href="#">
                        <img src="{{ $logo .'/'. $settings['site_logo'] }}" alt="logo">
                    </a>
                </div>
                <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" href="#home">{{ $settings['home_title'] }}</a>
                        </li>
                        @if ($settings['feature_status'] == 'on')
                        <li class="nav-item">
                            <a class="nav-link" href="#features">{{ $settings['feature_title'] }}</a>
                        </li>
                        @endif
                        @if ($settings['plan_status'] == 'on')
                        <li class="nav-item">
                            <a class="nav-link" href="#plan">{{ $settings['plan_title'] }}</a>
                        </li>
                        @endif
                        @if ($settings['faq_status'] == 'on')
                        <li class="nav-item">
                            <a class="nav-link" href="#faq">{{ $settings['faq_title'] }}</a>
                        </li>
                        @endif

                        @if (is_array(json_decode($settings['menubar_page'])) || is_object(json_decode($settings['menubar_page'])))
                            @foreach (json_decode($settings['menubar_page']) as $key => $value)
                                @if ($value->header == 'on' && $value->template_name == 'page_content')
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('custom.page', $value->page_slug) }}">{{ $value->menubar_page_name }}</a>
                                    </li>
                                @elseif($value->header == 'on')
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ $value->page_url }}">{{ $value->menubar_page_name }}</a>
                                    </li>
                                @endif
                            @endforeach
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
                    <button class="navbar-toggler " type="button" data-bs-toggle="collapse"
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

<!-- [ Banner ] start -->
@if ($settings['home_status'] == 'on')
    <section class="main-banner bg-primary" id="home">
        <div class="container-offset">
            <div class="row gy-3 g-0 align-items-center">
                <div class="col-xxl-4 col-md-6 animate-fade-in-up">
                    <div class="special-badge">
                        <i data-feather="zap"></i>
                        {{ $settings['home_offer_text'] }}
                    </div>
                    <h1 class="mb-3">
                        {{ $settings['home_heading'] }}
                    </h1>
                    <h6 class="mb-0">{{ $settings['home_description'] }}</h6>
                    <div class="d-flex gap-3 mt-4 banner-btn">
                        @if ($settings['home_live_demo_link'])
                            <a href="{{ $settings['home_live_demo_link'] }}" class="btn btn-outline-dark">
                                {{ __('Live Demo') }}
                            </a>
                        @endif
                        @if ($settings['home_buy_now_link'])
                            <a href="{{ $settings['home_buy_now_link'] }}" class="btn btn-outline-dark">
                                {{ __('Buy Now') }}
                            </a>
                        @endif
                    </div>

                    <!-- Enhanced Stats Section -->
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <span class="hero-stat-number">10,000+</span>
                            <span class="hero-stat-label">{{ __('Active Users') }}</span>
                        </div>
                        <div class="hero-stat">
                            <span class="hero-stat-number">99.9%</span>
                            <span class="hero-stat-label">{{ __('Uptime') }}</span>
                        </div>
                        <div class="hero-stat">
                            <span class="hero-stat-number">4.9/5</span>
                            <span class="hero-stat-label">{{ __('Rating') }}</span>
                        </div>
                        @if($settings['home_trusted_by'])
                        <div class="hero-stat">
                            <span class="hero-stat-number">{{ $settings['home_trusted_by'] }}</span>
                            <span class="hero-stat-label">{{ __('Trusted By') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col-xxl-8 col-md-6 animate-fade-in-right">
                    @if(Storage::exists('/uploads/landing_page_image/'.$settings['home_banner']))
                    <div class="{{ $settings['home_banner'] ? 'dash-preview' : '' }}">
                        <img class="img-fluid preview-img" src="{{ $logo . '/' . $settings['home_banner'] }}" alt="">
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
<!-- [ Banner ] end -->

<!-- [ features ] start -->
@if ($settings['feature_status'] == 'on')
    <section class="features-section section-gap bg-dark" id="features">
        <div class="container">
            <div class="row gy-3">
                <div class="col-xxl-4">
                    <span class="d-block mb-2 text-uppercase">{{ $settings['feature_title'] }}</span>
                    <div class="title mb-4">
                        <h2><b class="fw-bold">{!! $settings['feature_heading'] !!}</b></h2>
                    </div>
                    <p class="mb-3">{!! $settings['feature_description'] !!}</p>
                    @if ($settings['feature_buy_now_link'])
                        <a href="{{ $settings['feature_buy_now_link'] }}"
                            class="btn btn-primary rounded-pill d-inline-flex align-items-center">{{ __('Buy Now') }}
                            <i data-feather="lock" class="ms-2"></i></a>
                    @endif
                </div>
                <div class="col-xxl-8">
                    <div class="row">
                        @if (is_array(json_decode($settings['feature_of_features'], true)) ||
                                is_object(json_decode($settings['feature_of_features'], true)))
                            @foreach (json_decode($settings['feature_of_features'], true) as $key => $value)
                                <div class="col-lg-4 col-sm-6 d-flex">
                                    <div class="card {{ $key == 0 ? 'bg-primary' : '' }}">
                                        <div class="card-body">
                                            <span class="theme-avtar avtar avtar-xl mb-4">
                                                <img src="{{ $logo . '/' . $value['feature_logo'] }}" alt="">
                                            </span>
                                            <h3 class="mb-3 {{ $key == 0 ? '' : 'text-white' }}">
                                                {!! $value['feature_heading'] !!}</h3>
                                            <p class=" f-w-600 mb-0 {{ $key == 0 ? 'text-body' : '' }}">
                                                {!! $value['feature_description'] !!}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="mt-5">
                    <div class="title text-center mb-4">
                        <span class="d-block mb-2 fw-bold text-uppercase">{{ __('SCREENSHOTS') }}</span>
                        <h2 class="mb-4">{!! $settings['screenshots_heading'] !!}</h2>
                        <p>{!! $settings['screenshots_description'] !!}</p>
                    </div>
                </div>
            </div>
            <div class="row gy-4 gx-4">
                @if (is_array(json_decode($settings['screenshots'], true)) || is_object(json_decode($settings['screenshots'], true)))
                    @foreach (json_decode($settings['screenshots'], true) as $value)
                        <div class="col-md-4 col-sm-6">
                            <div class="screenshot-card">
                                <div class="img-wrapper">
                                    <img src="{{ $logo . '/' . $value['screenshots'] }}"
                                        class="img-fluid header-img mb-4 shadow-sm" alt="">
                                </div>
                                <h5 class="mb-0">{!! $value['screenshots_heading'] !!}</h5>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>
@endif
<!-- [ Screenshots ] end -->

<!-- [ subscription ] start -->
@if ($settings['plan_status'] == 'on')
    <section class="subscription bg-primary section-gap" id="plan">
        <div class="container">
            <div class="row mb-2 justify-content-center">
                <div class="col-xxl-6">
                    <div class="title text-center mb-4">
                        <span class="d-block mb-2 fw-bold text-uppercase">{{ __('PLAN') }}</span>
                        <h2 class="mb-4">{!! $settings['plan_heading'] !!}</h2>
                        <p>{!! $settings['plan_description'] !!}</p>
                    </div>
                </div>
            </div>

            @php
                $monthly_plans = \App\Models\Plan::where('price', '>', 0)->where('name', 'not like', '%(yearly)%')->where('is_disable', 1)->orderBy('price', 'ASC')->get();
                $yearly_plans = \App\Models\Plan::where('price', '>', 0)->where('name', 'like', '%(yearly)%')->where('is_disable', 1)->orderBy('price', 'ASC')->get();
                $admin_payment_setting = Utility::getAdminPaymentSetting();
                $has_yearly_plans = $yearly_plans->count() > 0;

                // Parse features for monthly plans
                $monthly_categories = [];
                foreach($monthly_plans as $plan) {
                    $lines = explode("\n", $plan->description);
                    $current_category = 'General';

                    foreach($lines as $line) {
                        $line = trim($line);
                        if(empty($line)) continue;

                        if(str_starts_with($line, '##')) {
                            $current_category = trim(str_replace('##', '', $line));
                            if(!isset($monthly_categories[$current_category])) {
                                $monthly_categories[$current_category] = [];
                            }
                        } else {
                            if(!isset($monthly_categories[$current_category])) {
                                $monthly_categories[$current_category] = [];
                            }
                            if(!in_array($line, $monthly_categories[$current_category])) {
                                $monthly_categories[$current_category][] = $line;
                            }
                        }
                    }
                }

                // Parse features for yearly plans
                $yearly_categories = [];
                foreach($yearly_plans as $plan) {
                    $lines = explode("\n", $plan->description);
                    $current_category = 'General';

                    foreach($lines as $line) {
                        $line = trim($line);
                        if(empty($line)) continue;

                        if(str_starts_with($line, '##')) {
                            $current_category = trim(str_replace('##', '', $line));
                            if(!isset($yearly_categories[$current_category])) {
                                $yearly_categories[$current_category] = [];
                            }
                        } else {
                            if(!isset($yearly_categories[$current_category])) {
                                $yearly_categories[$current_category] = [];
                            }
                            if(!in_array($line, $yearly_categories[$current_category])) {
                                $yearly_categories[$current_category][] = $line;
                            }
                        }
                    }
                }
            @endphp

            <!-- Toggle for yearly/monthly (only show if yearly plans exist) -->
            @if($has_yearly_plans)
                <div class="row justify-content-center mb-4">
                    <div class="col-auto">
                        <div class="btn-group bg-white rounded-pill p-1" role="group" style="box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <button type="button" class="btn btn-outline-dark rounded-pill px-4" id="monthly-btn">Pay monthly</button>
                            <button type="button" class="btn btn-dark rounded-pill px-4" id="yearly-btn">Pay yearly (save 25%)*</button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Monthly Plans (hidden by default) -->
            <div id="monthly-plans" class="plans-container" style="display: none;">
                @if(!empty($monthly_categories))
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <div style="position: relative;">
                                <!-- Beautiful Large Most Popular Label for Monthly Plans -->
                                @if(count($monthly_plans) > 1)
                                    <div class="position-relative" style="height: 30px; margin-bottom: -1px;">
                                        <div class="position-absolute" style="top: 0; left: {{ 40 + (60 / count($monthly_plans) * 1.5) }}%; width: {{ 60 / count($monthly_plans) }}%; transform: translateX(-50%); z-index: 1000;">
                                            <div class="text-center px-3 py-2 text-white fw-bold" style="
                                                background: linear-gradient(135deg, #ff6b35, #f7931e);
                                                border-radius: 25px 25px 0 0;
                                                position: relative;
                                                box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
                                                overflow: hidden;
                                            ">
                                                <span style="position: relative; z-index: 2; font-size: 0.9rem; letter-spacing: 0.5px;">MOST POPULAR</span>
                                                <div style="
                                                    position: absolute;
                                                    bottom: -20px;
                                                    left: -10px;
                                                    right: -10px;
                                                    height: 20px;
                                                    background: linear-gradient(135deg, #ff6b35, #f7931e);
                                                    border-radius: 0 0 25px 25px;
                                                "></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Monthly Plans Tables (truncated for brevity - keeping your existing pricing table logic) -->
                                @php $first_category = array_key_first($monthly_categories); @endphp
                                <div class="table-responsive" style="margin-bottom: 8px;">
                                    <table class="table shadow-none mb-0" style="border-radius: 15px; overflow: hidden; background-color: var(--bs-body-bg, white); border: transparent;">
                                        <thead>
                                            <tr>
                                                <th class="text-start fw-bold" style="width: 40%; background-color: #e9ecef; color: var(--bs-body-color, inherit); border: transparent;">{{ $first_category }}</th>
                                                @foreach($monthly_plans as $key => $plan)
                                                    @php
                                                        $display_name = str_replace(' (yearly)', '', $plan->name);
                                                        $monthly_price = intval($plan->price);
                                                    @endphp
                                                    <th class="text-center fw-bold" style="background-color: {{ $key == 1 ? '#f7931e' : '#e9ecef' }}; color: {{ $key == 1 ? 'white' : 'var(--bs-body-color, inherit)' }}; width: {{ 60 / count($monthly_plans) }}%; border: transparent; {{ $key == 0 ? 'border-left: 2px solid #dee2e6;' : '' }}">
                                                        <div class="plan-header">
                                                            <h5 class="mb-1" style="color: {{ $key == 1 ? 'white' : 'var(--bs-body-color, inherit)' }};">{{ $display_name }}</h5>
                                                            <h3 class="mb-0" style="color: {{ $key == 1 ? 'white' : 'var(--bs-body-color, inherit)' }};">{{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '">
                        <span class="d-block mb-2 text-uppercase">{{ $settings['feature_title'] }}</span>
                        <h2 class="mb-4">{!! $settings['highlight_feature_heading'] !!}</h2>
                        <p>{!! $settings['highlight_feature_description'] !!}</p>
                    </div>
                    @if(Storage::exists('/uploads/landing_page_image/'.$settings['highlight_feature_image']))
                    <div class="features-preview">
                        <img class="img-fluid m-auto d-block"
                            src="{{ $logo . '/' . $settings['highlight_feature_image'] }}" alt="">
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
<!-- [ features ] end -->

<!-- [ element ] start -->
@if ($settings['feature_status'] == 'on')
    <section class="element-section section-gap">
        <div class="container">
            @if (is_array(json_decode($settings['other_features'], true)) ||
                    is_object(json_decode($settings['other_features'], true)))
                @foreach (json_decode($settings['other_features'], true) as $key => $value)
                    @if ($key % 2 == 0)
                        <div class="row align-items-center justify-content-center mb-4">
                            <div class="col-lg-4 col-md-6">
                                <div class="title mb-4">
                                    <span class="d-block fw-bold mb-2 text-uppercase">{{ __('Features') }}</span>
                                    <h2>
                                        {!! $value['other_features_heading'] !!}
                                    </h2>
                                </div>
                                <p class="mb-3">{!! $value['other_featured_description'] !!}</p>
                                <a href="{{ $value['other_feature_buy_now_link'] }}"
                                    class="btn btn-primary rounded-pill d-inline-flex align-items-center">{{ __('Buy Now ') }}
                                    <i data-feather="lock" class="ms-2"></i></a>
                            </div>
                            <div class="col-lg-7 col-md-6 res-img">
                        @if(Storage::exists('/uploads/landing_page_image/'.$value['other_features_image']))
                                <div class="img-wrapper">
                                    <img src="{{ $logo . '/' . $value['other_features_image'] }}" alt=""
                                        class="img-fluid header-img">
                                </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="row align-items-center justify-content-center mb-4">
                            <div class="col-lg-7 col-md-6">
                                <div class="img-wrapper">
                                    <img src="{{ $logo . '/' . $value['other_features_image'] }}" alt=""
                                        class="img-fluid header-img">
                                </div>
                            </div>
                            <div class="col-lg-4  col-md-6">
                                <div class="title mb-4">
                                    <span class="d-block fw-bold mb-2 text-uppercase">{{ __('Features') }}</span>
                                    <h2>
                                        {!! $value['other_features_heading'] !!}
                                    </h2>
                                </div>
                                <p class="mb-3">{!! $value['other_featured_description'] !!}</p>
                                <a href="{{ $value['other_feature_buy_now_link'] }}"
                                    class="btn btn-primary rounded-pill d-inline-flex align-items-center">{{ __('Buy Now ') }}
                                    <i data-feather="lock" class="ms-2"></i></a>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </section>
@endif
<!-- [ element ] end -->

<!-- [ Discover ] start -->
@if ($settings['discover_status'] == 'on')
    <section class="bg-dark section-gap discover-section">
        <div class="container">
            <div class="row mb-2 justify-content-center">
                <div class="col-xxl-6">
                    <div class="title text-center mb-4">
                        <span class="d-block mb-2 text-uppercase">{{ __('DISCOVER') }}</span>
                        <h2 class="mb-4">{!! $settings['discover_heading'] !!}</h2>
                        <p>{!! $settings['discover_description'] !!}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                @if (is_array(json_decode($settings['discover_of_features'], true)) ||
                        is_object(json_decode($settings['discover_of_features'], true)))
                    @foreach (json_decode($settings['discover_of_features'], true) as $key => $value)
                        <div class="col-xxl-3 col-sm-6 col-lg-4 ">
                            <div class="card border {{ $key == 1 ? 'bg-primary' : 'bg-transparent' }}">
                                <div class="card-body text-center">
                                    <span class="theme-avtar avtar avtar-xl mx-auto mb-4">
                                        <img src="{{ $logo . '/' . $value['discover_logo'] }}" alt="">
                                    </span>
                                    <h3 class="mb-3 {{ $key == 1 ? '' : 'text-white' }} ">{!! $value['discover_heading'] !!}
                                    </h3>
                                    <p class="{{ $key == 1 ? 'text-body' : '' }}">
                                        {!! $value['discover_description'] !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="d-flex flex-column justify-content-center flex-sm-row gap-3 mt-3">
                @if ($settings['discover_live_demo_link'])
                    <a href="{{ $settings['discover_live_demo_link'] }}" class="btn btn-outline-light">{{ __('Live Demo') }}</a>
                @endif
                @if ($settings['discover_buy_now_link'])
                    <a href="{{ $settings['discover_buy_now_link'] }}" class="btn btn-primary">{{ __('Buy Now') }}</a>
                @endif
            </div>
        </div>
    </section>
@endif
<!-- [ Discover ] end -->

<!-- [ Screenshots ] start -->
@if ($settings['screenshots_status'] == 'on')
    <section class="screenshots section-gap">
        <div class="container">
            <div class="row mb-2 justify-content-center">
                <div class="col-xxl-6">
                    <div class="title text-center mb-4 }}{{ $monthly_price }}</h3>
                                                            <small class="{{ $key == 1 ? 'text-light' : 'text-muted' }}">/{{ $plan->duration }}</small>
                                                        </div>
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($monthly_categories[$first_category] as $feature)
                                                <tr>
                                                    <td class="text-start" style="width: 40%; color: var(--bs-body-color, inherit); border: transparent;">{{ $feature }}</td>
                                                    @foreach($monthly_plans as $key => $plan)
                                                        <td class="text-center" style="background-color: {{ $key == 1 ? '#fef5f0' : 'inherit' }}; width: {{ 60 / count($monthly_plans) }}%; border: transparent; {{ $key == 0 ? 'border-left: 2px solid #dee2e6;' : '' }}">
                                                            @if(str_contains($plan->description, $feature))
                                                                <i class="ti ti-circle-check text-success fs-4"></i>
                                                            @else
                                                                <i class="ti ti-circle-x text-muted fs-4"></i>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Yearly Plans (shown by default) -->
            @if($has_yearly_plans)
                <div id="yearly-plans" class="plans-container">
                    <!-- Similar yearly plans structure -->
                </div>
            @endif
        </div>
    </section>
@endif
<!-- [ subscription ] end -->

<!-- [ FAqs ] start -->
@if ($settings['faq_status'] == 'on')
    <section class="faqs section-gap bg-gray-100" id="faq">
        <div class="container">
            <div class="row mb-2">
                <div class="col-xxl-6">
                    <div class="title mb-4">
                        <span class="d-block mb-2 fw-bold text-uppercase">{{ $settings['faq_title'] }}</span>
                        <h2 class="mb-4">{!! $settings['faq_heading'] !!}</h2>
                        <p>{!! $settings['faq_description'] !!}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="accordion accordion-flush" id="accordionFlushExample">
                        @if (is_array(json_decode($settings['faqs'], true)) || is_object(json_decode($settings['faqs'], true)))
                            @foreach (json_decode($settings['faqs'], true) as $key => $value)
                                @if ($key % 2 == 0)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="{{ 'flush-heading' . $key }}">
                                            <button class="accordion-button collapsed fw-bold" type="button"
                                                data-bs-toggle="collapse" data-bs-target="{{ '#flush-' . $key }}"
                                                aria-expanded="false" aria-controls="{{ 'flush-collapse' . $key }}">
                                                {!! $value['faq_questions'] !!}
                                            </button>
                                        </h2>
                                        <div id="{{ 'flush-' . $key }}" class="accordion-collapse collapse"
                                            aria-labelledby="{{ 'flush-heading' . $key }}"
                                            data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body">
                                                {!! $value['faq_answer'] !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="accordion accordion-flush" id="accordionFlushExample2">
                        @if (is_array(json_decode($settings['faqs'], true)) || is_object(json_decode($settings['faqs'], true)))
                            @foreach (json_decode($settings['faqs'], true) as $key => $value)
                                @if ($key % 2 != 0)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="{{ 'flush-heading' . $key }}">
                                            <button class="accordion-button collapsed fw-bold" type="button"
                                                data-bs-toggle="collapse" data-bs-target="{{ '#flush-' . $key }}"
                                                aria-expanded="false" aria-controls="{{ 'flush-collapse' . $key }}">
                                                {!! $value['faq_questions'] !!}
                                            </button>
                                        </h2>
                                        <div id="{{ 'flush-' . $key }}" class="accordion-collapse collapse"
                                            aria-labelledby="{{ 'flush-heading' . $key }}"
                                            data-bs-parent="#accordionFlushExample2">
                                            <div class="accordion-body">
                                                {!! $value['faq_answer'] !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
<!-- [ FAqs ] end -->

<!-- [ testimonial ] start -->
@if ($settings['testimonials_status'] == 'on')
    <section class="testimonial section-gap">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4">
                    <div class="title mb-4">
                        <span class="d-block mb-2 fw-bold text-uppercase">{{ __('TESTIMONIALS') }}</span>
                        <h2 class="mb-2">{!! $settings['testimonials_heading'] !!}</h2>
                        <p>{!! $settings['testimonials_description'] !!}</p>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="row justify-content-center gy-3">
                        @if (is_array(json_decode($settings['testimonials'])) || is_object(json_decode($settings['testimonials'])))
                            @foreach (json_decode($settings['testimonials']) as $key => $value)
                                <div class="col-xxl-4 col-sm-6 col-lg-6 col-md-4">
                                    <div class="card bg-dark shadow-none mb-0">
                                        <div class="card-body p-3">
                                            <div class="d-flex mb-3 align-items-center justify-content-between">
                                                <span class="theme-avtar avtar avtar-sm bg-light-dark rounded-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="36"
                                                        height="23" viewBox="0 0 36 23" fill="none">
                                                        <path
                                                            d="M12.4728 22.6171H0.770508L10.6797 0.15625H18.2296L12.4728 22.6171ZM29.46 22.6171H17.7577L27.6669 0.15625H35.2168L29.46 22.6171Z"
                                                            fill="white" />
                                                    </svg>
                                                </span>
                                                <span>
                                                    @for ($i = 1; $i <= (int) $value->testimonials_star; $i++)
                                                        <i data-feather="star"></i>
                                                    @endfor
                                                </span>
                                            </div>
                                            <h3 class="text-white">{{ $value->testimonials_title }}</h3>
                                            <p class="hljs-comment">
                                                {{ $value->testimonials_description }}
                                            </p>
                                            <div class="d-flex  align-items-center ">
                                                <img src="{{ $logo . '/' . $value->testimonials_user_avtar }}"
                                                    class="wid-40 rounded-circle me-3" alt="">
                                                <span>
                                                    <b class="fw-bold d-block">{{ $value->testimonials_user }}</b>
                                                    {{ $value->testimonials_designation }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <p class="mb-0 f-w-600">
                        {!! $settings['testimonials_long_description'] !!}
                    </p>
                </div>
            </div>
        </div>
    </section>
@endif
<!-- [ testimonial ] end -->

<div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
    <div id="liveToast" class="toast text-white  fade" role="alert" aria-live="assertive"
        aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body"> </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- [ Footer ] start -->
<footer class="site-footer bg-gray-100">
    <div class="container">
        <div class="footer-row">
            <div class="ftr-col cmp-detail">
                <div class="footer-logo mb-3">
                    <a href="#">
                        <img src="{{ $logo . '/' . $settings['site_logo'] }}" alt="logo">
                    </a>
                </div>
                <p>
                    {!! $settings['site_description'] !!}
                </p>
            </div>
            <div class="ftr-col">
                <ul class="list-unstyled">
                    @if (is_array(json_decode($settings['menubar_page'])) || is_object(json_decode($settings['menubar_page'])))
                        @foreach (json_decode($settings['menubar_page']) as $key => $value)
                            @if ($value->footer == 'on' && $value->header == 'off' && $value->template_name == 'page_content')
                                <li><a href="{{ route('custom.page', $value->page_slug) }}">{!! $value->menubar_page_name !!}</a></li>
                            @endif
                            @if ($value->footer == 'on' && $value->header == 'on' && $value->template_name == 'page_content')
                                <li><a href="{{ route('custom.page', $value->page_slug) }}">{!! $value->menubar_page_name !!}</a></li>
                            @endif
                            @if ($value->footer == 'on' && $value->header == 'on' && $value->template_name == 'page_url')
                                <li><a href="{{ $value->page_url }}">{!! $value->menubar_page_name !!}</a></li>
                            @endif
                            @if ($value->footer == 'on' && $value->header == 'off' && $value->template_name == 'page_url')
                                <li><a href="{{ $value->page_url }}">{!! $value->menubar_page_name !!}</a></li>
                            @endif
                        @endforeach
                    @endif
                </ul>
            </div>

            @if ($settings['joinus_status'] == 'on')
                <div class="ftr-col ftr-subscribe">
                    <h2>{!! $settings['joinus_heading'] !!}</h2>
                    <p>{!! $settings['joinus_description'] !!}</p>
                    <form method="post" action="{{ route('join_us_store') }}">
                        @csrf
                        <div class="input-wrapper border border-dark">
                            <input type="email" name="email" placeholder="Type your email address...">
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
            {{ Utility::getValByName('footer_text') ? Utility::getValByName('footer_text') : config('app.name', 'ERPGo') }}
        </p>
    </div>
</footer>
<!-- [ Footer ] end -->

<!-- Required Js -->
<script src="{{ asset('assets/js/plugins/popper.min.js')}}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.min.js')}}"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js')}}"></script>

<script>
    // Start [ Menu hide/show on scroll ]
    let ost = 0;
    document.addEventListener("scroll", function() {
        let cOst = document.documentElement.scrollTop;
        const navbar = document.querySelector(".main-header");
        
        if (cOst == 0) {
            navbar.classList.remove("scrolled");
        } else if (cOst > 50) {
            navbar.classList.add("scrolled");
        }
        
        if (cOst > ost) {
            document.querySelector(".navbar").classList.add("top-nav-collapse");
            document.querySelector(".navbar").classList.remove("default");
        } else {
            document.querySelector(".navbar").classList.add("default");
            document.querySelector(".navbar").classList.remove("top-nav-collapse");
        }
        ost = cOst;
    });
    // End [ Menu hide/show on scroll ]

    var scrollSpy = new bootstrap.ScrollSpy(document.body, {
        target: "#navbar-example",
    });
    feather.replace();

    // Pricing toggle script
    document.addEventListener('DOMContentLoaded', function() {
        const monthlyBtn = document.getElementById('monthly-btn');
        const yearlyBtn = document.getElementById('yearly-btn');
        const monthlyPlans = document.getElementById('monthly-plans');
        const yearlyPlans = document.getElementById('yearly-plans');

        if (monthlyBtn && yearlyBtn && monthlyPlans && yearlyPlans) {
            monthlyBtn.addEventListener('click', function() {
                monthlyBtn.className = 'btn btn-dark rounded-pill px-4';
                yearlyBtn.className = 'btn btn-outline-dark rounded-pill px-4';
                monthlyPlans.style.display = 'block';
                yearlyPlans.style.display = 'none';
            });

            yearlyBtn.addEventListener('click', function() {
                yearlyBtn.className = 'btn btn-dark rounded-pill px-4';
                monthlyBtn.className = 'btn btn-outline-dark rounded-pill px-4';
                monthlyPlans.style.display = 'none';
                yearlyPlans.style.display = 'block';
            });
        }
    });
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

@if ($get_cookie['enable_cookie'] == 'on')
    @include('layouts.cookie_consent')
@endif

</body>
</html>
