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
    .card-body {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 3rem;
        box-shadow: 
            0 25px 50px rgba(0, 0, 0, 0.15),
            0 0 0 1px rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.18);
        width: 100%;
        max-width: 450px;
        margin: 2rem auto;
    }

    h2 {
        font-size: 2.25rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 2rem;
        text-align: center;
    }

    /* Dark mode support */
    .landing-dark .card-body {
        background: rgba(33, 37, 41, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .landing-dark h2 {
        color: #fff;
    }

    .landing-dark .form-control {
        background: rgba(52, 58, 64, 0.8);
        color: #fff;
        border-color: rgba(255, 255, 255, 0.15);
    }

    .landing-dark .form-control:focus {
        background: rgba(52, 58, 64, 1);
        border-color: #667eea;
    }

    .landing-dark .form-label {
        color: #adb5bd;
    }

    .landing-dark .form-control:focus + .form-label,
    .landing-dark .form-control:not(:placeholder-shown) + .form-label {
        color: #667eea;
        background: rgba(33, 37, 41, 0.9);
    }

    .landing-dark .text-center p {
        color: #adb5bd;
        border-top-color: rgba(255, 255, 255, 0.1);
    }

    .landing-dark .d-flex.flex-wrap a {
        color: #667eea;
    }

    .form-group {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .form-label {
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
        z-index: 2;
    }

    .form-control {
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

    .form-control:focus {
        background: rgba(255, 255, 255, 1);
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .form-control:focus + .form-label,
    .form-control:not(:placeholder-shown) + .form-label {
        top: 0;
        font-size: 0.875rem;
        color: #667eea;
        background: rgba(255, 255, 255, 0.9);
        padding: 0 0.5rem;
        border-radius: 8px;
    }

    .d-flex.flex-wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .d-flex.flex-wrap a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .d-flex.flex-wrap a:hover {
        color: #5a6fd8;
    }

    .btn-primary {
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

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
    }

    .text-center p {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(148, 163, 184, 0.2);
        color: #64748b;
    }

    .text-primary {
        color: #667eea !important;
        text-decoration: none;
        font-weight: 600;
    }

    .text-primary:hover {
        color: #5a6fd8 !important;
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

    .text-danger {
        background: rgba(248, 215, 218, 0.9);
        border: 1px solid rgba(245, 198, 203, 0.5);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        color: #721c24;
    }

    /* reCAPTCHA styling */
    .form-group .g-recaptcha {
        display: flex;
        justify-content: center;
        margin: 1rem 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 2rem 1.5rem;
            border-radius: 20px;
            margin: 1rem;
        }
        
        h2 {
            font-size: 1.75rem;
        }
    }
</style>

<div class="card-body">
    <div>
        <h2 class="mb-3 f-w-600">{{ __('Login') }}</h2>
    </div>
    {{ Form::open(['route' => 'login', 'method' => 'post', 'id' => 'loginForm', 'class' => 'login-form', 'class'=>'needs-validation', 'novalidate']) }}
    @if (session('status'))
        <div class="mb-4 font-medium text-lg text-green-600 text-danger">
            {{ session('status') }}
        </div>
    @endif
    <div class="custom-login-form">
        <div class="form-group mb-3">
            {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => ' ', 'required' => 'required', 'id' => 'email']) }}
            <label class="form-label" for="email">{{ __('Email') }}</label>
            @error('email')
                <span class="error invalid-email text-danger" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group mb-3">
            {{ Form::password('password', ['class' => 'form-control', 'placeholder' => ' ', 'id' => 'input-password', 'required' => 'required']) }}
            <label class="form-label" for="input-password">{{ __('Password') }}</label>
            @error('password')
                <span class="error invalid-password text-danger" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                @if (Route::has('password.request'))
                    <span><a href="{{ route('password.request', $lang) }}" tabindex="0">{{ __('Forgot your password?') }}</a></span>
                @endif
            </div>
        </div>

        @if ($settings['recaptcha_module'] == 'on')
            @if (isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox')
                <div class="form-group col-lg-12 col-md-12 mt-3">
                    {!! NoCaptcha::display() !!}
                    @error('g-recaptcha-response')
                        <span class="small text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            @else
                <div class="form-group col-lg-12 col-md-12 mt-3">
                    <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" class="form-control">
                    @error('g-recaptcha-response')
                        <span class="error small text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            @endif
        @endif

        <div class="d-grid">
            {{ Form::submit(__('Login'), ['class' => 'btn btn-primary mt-2', 'id' => 'saveBtn']) }}
        </div>
        @if ($settings['enable_signup'] == 'on')
            <p class="my-4 text-center">{{ __("Don't have an account?") }}
                <a href="{{ route('register', ['0',$lang]) }}" class="text-primary">{{ __('Register') }}</a>
            </p>
        @endif
    </div>
    {{ Form::close() }}
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