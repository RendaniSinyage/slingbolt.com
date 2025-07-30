@extends('layouts.auth')
@php
    use App\Models\Utility;
    $logo = \App\Models\Utility::get_file('uploads/logo');
    $settings = Utility::settings();
    $company_logo = $settings['company_logo'] ?? '';
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
<style>
    body {
        margin: 0;
        padding: 0;
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .auth-wrapper {
        min-height: 100vh;
        display: flex;
    }

    .auth-left {
        width: 50%;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: 2rem;
        background: white;
    }

    .auth-right {
        width: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }

    .auth-right::before {
        display: none;
    }

    .login-card {
        background: white;
        border-radius: 0;
        padding: 3rem;
        box-shadow: none;
        border: none;
        width: 100%;
        max-width: 420px;
        margin-left: 2rem;
    }

    .welcome-content {
        text-align: center;
        color: white;
        z-index: 2;
        position: relative;
        max-width: 500px;
        padding: 2rem;
    }

    .welcome-content h2 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        line-height: 1.2;
    }

    .welcome-content p {
        font-size: 1.25rem;
        opacity: 0.9;
        margin-bottom: 2rem;
    }

    .feature-list {
        text-align: left;
    }

    .feature-item {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }

    .feature-item i {
        margin-right: 1rem;
        opacity: 0.8;
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

    @media (max-width: 768px) {
        .auth-wrapper {
            flex-direction: column;
        }
        
        .auth-left {
            order: 2;
        }
        
        .auth-right {
            order: 1;
            min-height: 60vh;
        }
        
        .login-card {
            padding: 2rem 1.5rem;
            border-radius: 20px;
            margin: 1rem;
        }
        
        .auth-title h1 {
            font-size: 1.75rem;
        }
        
        .welcome-content h2 {
            font-size: 2rem;
        }
    }
</style>

<div class="auth-wrapper">
    <div class="auth-left">
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

    <div class="auth-right">
        <div class="welcome-content">
            <h2>Welcome to Our Platform</h2>
            <p>Join thousands of users who trust our platform for their business needs.</p>
            <div class="feature-list">
                <div class="feature-item">
                    <i class="ti ti-check-circle"></i>
                    <span>Secure and reliable platform</span>
                </div>
                <div class="feature-item">
                    <i class="ti ti-check-circle"></i>
                    <span>24/7 customer support</span>
                </div>
                <div class="feature-item">
                    <i class="ti ti-check-circle"></i>
                    <span>Easy to use interface</span>
                </div>
            </div>
        </div>
    </div>
</div>

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
@endsection