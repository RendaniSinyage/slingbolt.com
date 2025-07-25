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
</head>

@if ($setting['cust_darklayout'] == 'on')
    <body class="{{ $themeColor }} landing-dark">
@else
    <body class="{{ $themeColor }}">
@endif

    @include('landingpage::partials.header')

    @include('landingpage::sections.hero')
    
    @include('landingpage::sections.features')
    
    @include('landingpage::sections.discover-screenshots')
    
    @include('landingpage::sections.pricing-faq')
    
    @include('landingpage::sections.testimonials')

    @include('landingpage::partials.footer-scripts')

</body>
</html>