@extends('layouts.auth')
@php
    use App\Models\Utility;
    $logo = \App\Models\Utility::get_file('uploads/logo');
    $settings = Utility::settings();
    $company_logo = $settings['company_logo'] ?? '';
    $adminSettings = $settings;
    $setting = \App\Models\Utility::colorset();
    $SITE_RTL = $adminSettings['SITE_RTL'] ? $adminSettings['SITE_RTL'] : '';
    $lang = \App::getLocale('lang');
    if ($lang == 'ar' || $lang == 'he') {
        $SITE_RTL = 'on';
    }
    $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';
    if(isset($setting['color_flag']) && $setting['color_flag'] == 'true') {
        $themeColor = 'custom-color';
    } else {
        $themeColor = $color;
    }
@endphp

@push('custom-scripts')
    @if ($settings['recaptcha_module'] == 'on')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush

@section('page-title')
    {{ __('Login') }}
@endsection

@if ($settings['cust_darklayout'] == 'on')
    <style>
        .g-recaptcha {
            filter: invert(1) hue-rotate(180deg) !important;
        }
    </style>
@endif

@php
    $languages = App\Models\Utility::languages();
@endphp

@section('language-bar')
    <div class="lang-dropdown-only-desk">
        <li class="dropdown dash-h-item drp-language">
            <a class="dash-head-link dropdown-toggle btn" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ $languages[$lang] }}
                </span>
            </a>
            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                @foreach ($languages as $code => $language)
                    <a href="{{ route('login', $code) }}" class="dropdown-item @if ($lang == $code) text-primary @endif">
                        <span>{{ Str::upper($language) }}</span>
                    </a>
                @endforeach
            </div>
        </li>
    </div>
@endsection

@section('content')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $SITE_RTL == 'on' ? 'rtl' : '' }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <title>{{ $setting['title_text'] ? $setting['title_text'] : config('app.name', 'SLINGBOLT') }} - Login</title>
    
    <link rel="icon" href="{{ $logo . '/' . $adminSettings['company_favicon'] . '?' . time() }}" type="image/x-icon" />
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
    
    <link rel="stylesheet" href=" {{ asset('assets/css/customizer.css') }}" />
    <link rel="stylesheet" href=" {{ asset('assets/landingpage/css/landing-page.css') }}" />
    <link rel="stylesheet" href=" {{ asset('assets/landingpage/css/custom.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">
    
    <style>
        :root {
            --color-customColor: <?= $color ?>;
        }
        
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .auth-gradient {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            z-index: -1;
        }

        .auth-header {
            position: relative;
            z-index: 10;
            padding: 1rem 0;
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.18);
            width: 100%;
            max-width: 480px;
        }

        .auth-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .auth-logo img {
            height: 48px;
            filter: brightness(0) invert(1);
        }

        .auth-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-title h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }

        .auth-title p {
            color: #64748b;
            font-size: 1.1rem;
        }

        .form-floating {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-control-modern {
            background: rgba(248, 249, 250, 0.8);
            border: 2px solid rgba(148, 163, 184, 0.2);
            border-radius: 16px;
            padding: 1.25rem 1rem;
            font-size: 1rem;
            line-height: 1.5;
            transition: all 0.3s ease;
            width: 100%;
            min-height: 60px;
        }

        .form-control-modern:focus {
            background: rgba(255, 255, 255, 1);
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .form-floating label {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            font-size: 1rem;
            color: #64748b;
            transition: all 0.3s ease;
            pointer-events: none;
            background: transparent;
            padding: 0 0.25rem;
        }

        .form-floating .form-control-modern:focus ~ label,
        .form-floating .form-control-modern:not(:placeholder-shown) ~ label {
            top: 0;
            font-size: 0.875rem;
            color: #667eea;
            background: rgba(255, 255, 255, 0.9);
            padding: 0 0.5rem;
            border-radius: 8px;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-check input {
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            margin: 0;
        }

        .form-check input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .forgot-link:hover {
            color: #5a6fd8;
        }

        .login-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .auth-footer {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(148, 163, 184, 0.2);
        }

        .register-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link:hover {
            color: #5a6fd8;
        }

        .alert-modern {
            background: rgba(248, 215, 218, 0.9);
            border: 1px solid rgba(245, 198, 203, 0.5);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #721c24;
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(248, 215, 218, 0.1);
            border-radius: 8px;
            border-left: 4px solid #dc3545;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 2rem 1.5rem;
                border-radius: 20px;
                margin: 1rem;
            }
            
            .auth-title h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>

@if ($setting['cust_darklayout'] == 'on')
    <body class="{{ $themeColor }} landing-dark">
@else
    <body class="{{ $themeColor }}">
@endif

    <div class="auth-gradient"></div>

    <!-- Header same as landing page -->
    <header class="main-header position-relative z-10 auth-header">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-3">
            <div class="container d-flex align-items-center justify-content-between">
                <a class="navbar-brand" href="/">
                    <img src="{{ $logo . '/' . $company_logo }}" alt="logo" height="40">
                </a>
                <div class="d-flex gap-2">
                    <a href="{{ route('register', ['0', $lang]) }}" class="btn rounded-pill px-4" style="background-color: white; color: #333; border: 1px solid rgba(255,255,255,0.3);">
                        {{ __('Get Started') }}
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <div class="auth-container">
        <div class="login-card">
            <div class="auth-title">
                <h1>{{ __('Welcome Back') }}</h1>
                <p>{{ __('Sign in to your account') }}</p>
            </div>

            {{ Form::open(['route' => 'login', 'method' => 'post', 'id' => 'loginForm', 'class' => 'needs-validation', 'novalidate']) }}
            
            @if (session('status'))
                <div class="alert-modern">
                    {{ session('status') }}
                </div>
            @endif

            <div class="form-floating">
                {{ Form::email('email', null, [
                    'class' => 'form-control form-control-modern', 
                    'id' => 'email',
                    'placeholder' => 'Email', 
                    'required' => 'required'
                ]) }}
                <label for="email">{{ __('Email Address') }}</label>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-floating">
                {{ Form::password('password', [
                    'class' => 'form-control form-control-modern', 
                    'id' => 'password',
                    'placeholder' => 'Password', 
                    'required' => 'required'
                ]) }}
                <label for="password">{{ __('Password') }}</label>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-options">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        {{ __('Remember me') }}
                    </label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request', $lang) }}" class="forgot-link">
                        {{ __('Forgot Password?') }}
                    </a>
                @endif
            </div>

            @if ($settings['recaptcha_module'] == 'on')
                @if (isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox')
                    <div class="mb-3 d-flex justify-content-center">
                        {!! NoCaptcha::display() !!}
                        @error('g-recaptcha-response')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @else
                    <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
                    @error('g-recaptcha-response')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                @endif
            @endif

            {{ Form::submit(__('Sign In'), ['class' => 'login-btn', 'id' => 'saveBtn']) }}

            @if ($settings['enable_signup'] == 'on')
                <div class="auth-footer">
                    <p class="mb-0 text-muted">{{ __("Don't have an account?") }} 
                        <a href="{{ route('register', ['0',$lang]) }}" class="register-link">{{ __('Create Account') }}</a>
                    </p>
                </div>
            @endif

            {{ Form::close() }}
        </div>
    </div>

    <script src="{{ asset('assets/js/plugins/popper.min.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js')}}"></script>
    <script src="{{ asset('js/jquery.min.js') }}"></script>

    @if (isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'on')
        @if (isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox')
            {!! NoCaptcha::renderJs() !!}
        @else
            <script src="https://www.google.com/recaptcha/api.js?render={{ $settings['google_recaptcha_key'] }}"></script>
            <script>
                $(document).ready(function() {
                    grecaptcha.ready(function() {
                        grecaptcha.execute('{{ $settings['google_recaptcha_key'] }}', {
                            action: 'submit'
                        }).then(function(token) {
                            $('#g-recaptcha-response').val(token);
                        });
                    });
                });
            </script>
        @endif
    @endif

</body>
</html>
@endsection