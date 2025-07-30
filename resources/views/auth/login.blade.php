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
    <div class="login-container">
        <!-- Welcome Back Section -->
        <div class="login-header text-center mb-5">
            <div class="welcome-icon mb-3">
                <div class="icon-circle">
                    <i class="ti ti-login-2 fs-1"></i>
                </div>
            </div>
            <h1 class="display-6 fw-bold mb-2">{{ __('Welcome Back') }}</h1>
            <p class="text-muted fs-5">{{ __('Sign in to continue to your account') }}</p>
        </div>

        <!-- Login Form -->
        <div class="login-form-wrapper">
            {{ Form::open(['route' => 'login', 'method' => 'post', 'id' => 'loginForm', 'class' => 'login-form needs-validation', 'novalidate']) }}
            
            @if (session('status'))
                <div class="alert alert-danger alert-modern mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-alert-triangle me-2 fs-5"></i>
                        <div>{{ session('status') }}</div>
                    </div>
                </div>
            @endif

            <div class="form-floating mb-4">
                {{ Form::email('email', null, [
                    'class' => 'form-control form-control-modern', 
                    'id' => 'email',
                    'placeholder' => __('Enter Your Email'), 
                    'required' => 'required'
                ]) }}
                <label for="email" class="form-label-modern">
                    <i class="ti ti-mail me-2"></i>{{ __('Email Address') }}
                </label>
                @error('email')
                    <div class="invalid-feedback-modern">
                        <i class="ti ti-alert-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-floating mb-4">
                <div class="position-relative">
                    {{ Form::password('password', [
                        'class' => 'form-control form-control-modern', 
                        'id' => 'password',
                        'placeholder' => __('Enter Your Password'), 
                        'required' => 'required'
                    ]) }}
                    <label for="password" class="form-label-modern">
                        <i class="ti ti-lock me-2"></i>{{ __('Password') }}
                    </label>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="ti ti-eye" id="toggleIcon"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback-modern">
                        <i class="ti ti-alert-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check custom-checkbox-modern">
                    <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                    <label class="form-check-label" for="rememberMe">
                        {{ __('Remember me') }}
                    </label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request', $lang) }}" class="forgot-password-link">
                        {{ __('Forgot Password?') }}
                    </a>
                @endif
            </div>

            @if ($settings['recaptcha_module'] == 'on')
                @if (isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox')
                    <div class="form-group mb-4">
                        <div class="recaptcha-wrapper">
                            {!! NoCaptcha::display() !!}
                        </div>
                        @error('g-recaptcha-response')
                            <div class="invalid-feedback-modern">
                                <i class="ti ti-alert-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>
                @else
                    <div class="form-group mb-4">
                        <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" class="form-control">
                        @error('g-recaptcha-response')
                            <div class="invalid-feedback-modern">
                                <i class="ti ti-alert-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>
                @endif
            @endif

            <div class="d-grid mb-4">
                {{ Form::submit(__('Sign In'), ['class' => 'btn btn-primary btn-modern btn-lg', 'id' => 'saveBtn']) }}
            </div>

            @if ($settings['enable_signup'] == 'on')
                <div class="text-center">
                    <p class="text-muted mb-0">{{ __("Don't have an account?") }}</p>
                    <a href="{{ route('register', ['0',$lang]) }}" class="register-link">
                        {{ __('Create Account') }} <i class="ti ti-arrow-right ms-1"></i>
                    </a>
                </div>
            @endif

            {{ Form::close() }}
        </div>
    </div>

    <style>
        .login-container {
            max-width: 420px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        .login-header .welcome-icon {
            position: relative;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .icon-circle::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .login-form-wrapper {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .form-control-modern {
            background: rgba(248, 249, 250, 0.8);
            border: 2px solid rgba(108, 117, 125, 0.15);
            border-radius: 16px;
            padding: 1rem 1rem 1rem 3rem;
            font-size: 1rem;
            line-height: 1.5;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 60px;
        }

        .form-control-modern:focus {
            background: rgba(255, 255, 255, 1);
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .form-floating .form-label-modern {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            background: transparent;
            padding: 0;
            font-size: 1rem;
            color: #6c757d;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
            z-index: 2;
        }

        .form-floating .form-control-modern:focus ~ .form-label-modern,
        .form-floating .form-control-modern:not(:placeholder-shown) ~ .form-label-modern {
            top: 0.5rem;
            font-size: 0.875rem;
            color: #667eea;
            background: rgba(255, 255, 255, 0.9);
            padding: 0 0.5rem;
            border-radius: 8px;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            font-size: 1.25rem;
            cursor: pointer;
            z-index: 3;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .password-toggle:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .custom-checkbox-modern .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .custom-checkbox-modern .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .forgot-password-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            position: relative;
        }

        .forgot-password-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #667eea;
            transition: width 0.3s ease;
        }

        .forgot-password-link:hover {
            color: #5a6fd8;
        }

        .forgot-password-link:hover::after {
            width: 100%;
        }

        .btn-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-modern:active {
            transform: translateY(0);
        }

        .register-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
        }

        .register-link:hover {
            color: #5a6fd8;
            transform: translateX(4px);
        }

        .alert-modern {
            background: rgba(248, 215, 218, 0.9);
            border: 1px solid rgba(245, 198, 203, 0.5);
            border-radius: 16px;
            backdrop-filter: blur(10px);
        }

        .invalid-feedback-modern {
            display: block;
            width: 100%;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #dc3545;
            background: rgba(248, 215, 218, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 12px;
            border-left: 4px solid #dc3545;
        }

        .recaptcha-wrapper {
            display: flex;
            justify-content: center;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Dark mode adjustments */
        @media (prefers-color-scheme: dark) {
            .login-form-wrapper {
                background: rgba(33, 37, 41, 0.95);
                border: 1px solid rgba(255, 255, 255, 0.1);
            }

            .form-control-modern {
                background: rgba(52, 58, 64, 0.8);
                color: #fff;
                border-color: rgba(255, 255, 255, 0.15);
            }

            .form-control-modern:focus {
                background: rgba(52, 58, 64, 1);
            }

            .form-label-modern {
                color: #adb5bd !important;
            }
        }

        /* Mobile responsiveness */
        @media (max-width: 576px) {
            .login-container {
                padding: 1rem;
            }

            .login-form-wrapper {
                padding: 2rem 1.5rem;
                border-radius: 20px;
            }

            .icon-circle {
                width: 70px;
                height: 70px;
            }

            .display-6 {
                font-size: 1.75rem;
            }
        }
    </style>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.className = 'ti ti-eye-off';
            } else {
                passwordField.type = 'password';
                toggleIcon.className = 'ti ti-eye';
            }
        }

        // Add smooth focus transitions
        document.addEventListener('DOMContentLoaded', function() {
            const formControls = document.querySelectorAll('.form-control-modern');
            
            formControls.forEach(control => {
                control.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                control.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });
        });
    </script>
@endsection

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