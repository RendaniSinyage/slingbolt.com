<title>{{ $setting['title_text'] ? $setting['title_text'] : config('app.name', 'SLINGBOLT') }}</title>
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
<link rel="icon" href="{{ $sup_logo . '/' . $adminSettings['company_favicon'] . '?' . time() }}" type="image/x-icon" />

<!-- font css -->
<link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}" />

<!-- vendor css -->
@if ($SITE_RTL == 'on')
    <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
@endif

@if ($setting['cust_darklayout'] == 'on')
    <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
@else
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
@endif

<link rel="stylesheet" href=" {{ asset('assets/css/customizer.css') }}" />
<link rel="stylesheet" href=" {{ asset('assets/landingpage/css/landing-page.css') }}" />
<link rel="stylesheet" href=" {{ asset('assets/landingpage/css/custom.css') }}" />

<style>
    :root {
        --color-customColor: <?= $color ?>;
    }
.module-tab {
    padding: 0.5rem 1.2rem;
    border: 1px solid #ccc;
    border-radius: 30px;
    background: transparent;
    color: #333;
    font-weight: 500;
    transition: all 0.2s ease;
}

.module-tab:hover {
    border-color: #0d6efd;
    color: #0d6efd;
}

.module-tab.active-tab {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

</style>

<link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">