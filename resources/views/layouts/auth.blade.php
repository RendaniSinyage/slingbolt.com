<!DOCTYPE html>
@php
    use App\Models\Utility;

    $setting = Utility::settings();
    $company_logo = $setting['company_logo_dark'] ?? '';
    $company_logos = $setting['company_logo_light'] ?? '';
    $company_favicon = $setting['company_favicon'] ?? '';

    $logo = \App\Models\Utility::get_file('uploads/logo/');

    $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';

    if(isset($setting['color_flag']) && $setting['color_flag'] == 'true')
    {
        $themeColor = 'custom-color';
    }
    else {
        $themeColor = $color;
    }

    $company_logo = \App\Models\Utility::GetLogo();
    $SITE_RTL = isset($setting['SITE_RTL']) ? $setting['SITE_RTL'] : 'off';

    $lang = \App::getLocale('lang');
    if ($lang == 'ar' || $lang == 'he') {
        $SITE_RTL = 'on';
    }
    elseif($SITE_RTL == 'on')
    {
        $SITE_RTL = 'on';
    }
    else {
        $SITE_RTL = 'off';
    }

    $metatitle = isset($setting['meta_title']) ? $setting['meta_title'] : '';
    $metsdesc = isset($setting['meta_desc']) ? $setting['meta_desc'] : '';
    $meta_image = \App\Models\Utility::get_file('uploads/meta/');
    $meta_logo = isset($setting['meta_image']) ? $setting['meta_image'] : '';
    $get_cookie = isset($setting['enable_cookie']) ? $setting['enable_cookie'] : '';

@endphp

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $SITE_RTL == 'on' ? 'rtl' : '' }}">

<head>
    <title>
        {{ Utility::getValByName('title_text') ? Utility::getValByName('title_text') : config('app.name', 'ERPGO') }}
        - @yield('page-title')
    </title>

    <meta name="title" content="{{ $metatitle }}">
    <meta name="description" content="{{ $metsdesc }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title" content="{{ $metatitle }}">
    <meta property="og:description" content="{{ $metsdesc }}">
    <meta property="og:image" content="{{ $meta_image . $meta_logo }}">
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title" content="{{ $metatitle }}">
    <meta property="twitter:description" content="{{ $metsdesc }}">
    <meta property="twitter:image" content="{{ $meta_image . $meta_logo }}">

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link rel="icon" href="{{ $logo . '/' . (isset($company_favicon) && !empty($company_favicon) ? $company_favicon : 'favicon.png')  . '?' . time() }}" type="image/x-icon" />

    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">

    @if ($SITE_RTL == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
    @endif

    @if ($setting['cust_darklayout'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @endif

    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/landingpage/css/landing-page.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/landingpage/css/custom.css') }}">

    <style>
        :root {
            --color-customColor: <?= $color ?>;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: var(--color-customColor, #667eea);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .auth-gradient {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 100vh;
            background: var(--color-customColor, #667eea);
            z-index: -1;
        }

        .auth-header {
            position: relative;
            z-index: 10;
            padding: 1rem 0;
        }

        .auth-main {
            min-height: calc(100vh - 120px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            z-index: 1;
        }

        .auth-footer {
            position: relative;
            z-index: 10;
            padding: 1rem 0;
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            background: transparent;
        }

        .auth-footer .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Modern header styling same as landing page */
        .main-header {
            background: transparent;
        }

        .navbar {
            background: transparent !important;
        }

        .navbar-brand img {
            filter: brightness(0) invert(1);
        }

        .nav-link {
            color: white !important;
            font-weight: 500;
        }

        .nav-link:hover {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        /* Card styling */
        .card {
            background: transparent;
            border: none;
            box-shadow: none;
        }

        /* Remove old auth styling */
        .custom-login,
        .login-bg-img,
        .bg-login,
        .custom-login-inner,
        .custom-wrapper,
        .custom-row {
            all: unset;
            display: block;
        }

        /* Language dropdown styling */
        .lang-dropdown-only-desk .dropdown-toggle {
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            background: rgba(255, 255, 255, 0.1) !important;
        }

        .lang-dropdown-only-desk .dropdown-toggle:hover {
            background: rgba(255, 255, 255, 0.2) !important;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>

@if ($setting['cust_darklayout'] == 'on')
    <body class="{{ $themeColor }} landing-dark">
@else
    <body class="{{ $themeColor }}">
@endif

    <div class="auth-gradient"></div>

    <!-- Modern Header same as landing page -->
    <header class="main-header position-relative z-10 auth-header">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-3">
            <div class="container d-flex align-items-center justify-content-between">
                <a class="navbar-brand" href="/">
                    @if ($setting['cust_darklayout'] == 'on')
                        <img src="{{ $logo . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-light.png') . '?' . time() }}" alt="logo" height="40">
                    @else
                        <img src="{{ $logo . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png') . '?' . time() }}" alt="logo" height="40">
                    @endif
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAuth" aria-controls="navbarAuth" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarAuth">
                    @php
                        // Get landing page settings for menu items
                        $landingSettings = \Modules\LandingPage\Entities\LandingPageSetting::settings();
                        // Determine logo type and set appropriate text color
                        $logo_filename = $landingSettings['site_logo'] ?? '';
                        $is_light_logo = str_contains(strtolower($logo_filename), 'light') || 
                                        str_contains(strtolower($logo_filename), 'white') ||
                                        str_contains(strtolower($logo_filename), 'inverse');
                        $nav_text_color = $is_light_logo ? 'text-white' : 'text-white';
                    @endphp

                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                        <!-- Features - only show if feature section is enabled -->
                        @if (isset($landingSettings['feature_status']) && $landingSettings['feature_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link {{ $nav_text_color }}" href="/#features">Features</a>
                            </li>
                        @endif

                        <!-- Pricing - only show if plan section is enabled -->
                        @if (isset($landingSettings['plan_status']) && $landingSettings['plan_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link {{ $nav_text_color }}" href="/#plan">Pricing</a>
                            </li>
                        @endif

                        <!-- Discover - only show if discover section is enabled -->
                        @if (isset($landingSettings['discover_status']) && $landingSettings['discover_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link {{ $nav_text_color }}" href="/#discover">Discover</a>
                            </li>
                        @endif

                        <!-- Screenshots - only show if screenshots section is enabled -->
                        @if (isset($landingSettings['screenshots_status']) && $landingSettings['screenshots_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link {{ $nav_text_color }}" href="/#screenshots">Screenshots</a>
                            </li>
                        @endif

                        <!-- FAQ - only show if FAQ section is enabled -->
                        @if (isset($landingSettings['faq_status']) && $landingSettings['faq_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link {{ $nav_text_color }}" href="/#faq">FAQ</a>
                            </li>
                        @endif

                        <!-- Testimonials - only show if testimonials section is enabled -->
                        @if (isset($landingSettings['testimonials_status']) && $landingSettings['testimonials_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link {{ $nav_text_color }}" href="/#testimonials">Testimonials</a>
                            </li>
                        @endif
                    </ul>

                    <!-- Auth buttons and language -->
                    <div class="d-flex gap-2 align-items-center">
                        @yield('language-bar')
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="auth-main">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="card">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="auth-footer">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <span>&copy; {{ date('Y') }}
                        {{ App\Models\Utility::getValByName('footer_text') ? App\Models\Utility::getValByName('footer_text') : config('app.name', 'Storego Saas') }}
                    </span>
                </div>
            </div>
        </div>
    </footer>

    @if ($get_cookie == 'on')
        @include('layouts.cookie_consent')
    @endif

    <script src="{{ asset('assets/js/vendor-all.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>

    <script>
        feather.replace();
    </script>

    @if (\App\Models\Utility::getValByName('cust_darklayout') == 'on')
        <style>
            .g-recaptcha {
                filter: invert(1) hue-rotate(180deg) !important;
            }
        </style>
    @endif

    <script>
        feather.replace();
        var pctoggle = document.querySelector("#pct-toggler");
        if (pctoggle) {
            pctoggle.addEventListener("click", function() {
                if (!document.querySelector(".pct-customizer").classList.contains("active")) {
                    document.querySelector(".pct-customizer").classList.add("active");
                } else {
                    document.querySelector(".pct-customizer").classList.remove("active");
                }
            });
        }

        var themescolors = document.querySelectorAll(".themes-color > a");
        for (var h = 0; h < themescolors.length; h++) {
            var c = themescolors[h];
            c.addEventListener("click", function(event) {
                var targetElement = event.target;
                if (targetElement.tagName == "SPAN") {
                    targetElement = targetElement.parentNode;
                }
                var temp = targetElement.getAttribute("data-value");
                removeClassByPrefix(document.querySelector("body"), "theme-");
                document.querySelector("body").classList.add(temp);
            });
        }
        
        function removeClassByPrefix(node, prefix) {
            for (let i = 0; i < node.classList.length; i++) {
                let value = node.classList[i];
                if (value.startsWith(prefix)) {
                    node.classList.remove(value);
                }
            }
        }
    </script>
    
    @stack('custom-scripts')

</body>
</html>