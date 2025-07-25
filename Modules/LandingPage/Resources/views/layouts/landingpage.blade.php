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

    $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';

    if(isset($setting['color_flag']) && $setting['color_flag'] == 'true')
    {
        $themeColor = 'custom-color';
    }
    else {
        $themeColor = $color;
    }

    // Make variables available to all included views
    View::share('logo', $logo);
    View::share('sup_logo', $sup_logo);
    View::share('settings', $settings);
    View::share('adminSettings', $adminSettings);
    View::share('setting', $setting);
    View::share('SITE_RTL', $SITE_RTL);
    View::share('color', $color);
    View::share('themeColor', $themeColor);
    View::share('metatitle', $metatitle);
    View::share('metsdesc', $metsdesc);
    View::share('meta_image', $meta_image);
    View::share('meta_logo', $meta_logo);
    View::share('get_cookie', $get_cookie);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $SITE_RTL == 'on' ? 'rtl' : '' }}">

<head>
    @include('landingpage::partials.head')
    
    <!-- Add the gradient CSS -->
    <style>
        /* WP ERP Header + Hero Gradient Background */
        .header-hero-gradient {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 100vh;
            z-index: -1;
            pointer-events: none;
            /* Use your actual backend primary color */
            @if(isset($setting['theme_color']) && !empty($setting['theme_color']))
                background: linear-gradient(
                    135deg,
                    {{ $setting['theme_color'] }} 0%,
                    {{ $setting['theme_color'] }}e6 25%,
                    {{ $setting['theme_color'] }}b3 50%,
                    {{ $setting['theme_color'] }}4d 75%,
                    transparent 100%
                );
            @else
                /* Fallback to CSS custom property if theme_color not set */
                background: linear-gradient(
                    135deg,
                    var(--primary) 0%,
                    rgba(var(--primary-rgb), 0.9) 25%,
                    rgba(var(--primary-rgb), 0.7) 50%,
                    rgba(var(--primary-rgb), 0.3) 75%,
                    transparent 100%
                );
            @endif
        }

        /* Ensure proper z-index layering */
        .landing-header, .hero-section {
            position: relative;
            z-index: 1;
        }
        .mockup-wrapper {
            position: relative;
            z-index: 2;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header-hero-gradient {
                height: 120vh;
                background: linear-gradient(135deg, var(--bs-primary) 0%, rgba(var(--bs-primary-rgb), 0.9) 20%, rgba(var(--bs-primary-rgb), 0.7) 40%, rgba(var(--bs-primary-rgb), 0.4) 60%, rgba(var(--bs-primary-rgb), 0.2) 80%, transparent 100%);
            }
        }

        /* RTL support */
        [dir="rtl"] .header-hero-gradient {
            background: linear-gradient(-135deg, var(--bs-primary) 0%, rgba(var(--bs-primary-rgb), 0.9) 25%, rgba(var(--bs-primary-rgb), 0.7) 50%, rgba(var(--bs-primary-rgb), 0.3) 75%, transparent 100%);
        }

        /* Dark mode adjustments */
        .landing-dark .header-hero-gradient {
            background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.8) 0%, rgba(var(--bs-primary-rgb), 0.6) 25%, rgba(var(--bs-primary-rgb), 0.4) 50%, rgba(var(--bs-primary-rgb), 0.2) 75%, transparent 100%);
        }
    </style>
</head>

@if ($setting['cust_darklayout'] == 'on')
    <body class="{{ $themeColor }} landing-dark">
@else
    <body class="{{ $themeColor }}">
@endif

    <!-- Gradient Background Overlay -->
    <div class="header-hero-gradient"></div>

    @include('landingpage::partials.header')

    @include('landingpage::sections.hero')
    
    @include('landingpage::sections.features')
    
    @include('landingpage::sections.discover-screenshots')
    
    @include('landingpage::sections.pricing-faq')
    
    @include('landingpage::sections.testimonials')

    @include('landingpage::partials.footer-scripts')

</body>
</html>