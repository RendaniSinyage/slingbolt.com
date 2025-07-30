@extends('layouts.auth')
@section('page-title')
    {{ __('Register') }}
@endsection
@php
    $settings = Utility::settings();
    $logo = \App\Models\Utility::get_file('uploads/logo');
    $setting = \Modules\LandingPage\Entities\LandingPageSetting::settings();
@endphp
@push('custom-scripts')
    @if ($settings['recaptcha_module'] == 'on')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush
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
                    <a href="{{ route('register', [$ref, $code]) }}" tabindex="0" class="dropdown-item ">
                        <span>{{ Str::ucfirst($language) }}</span>
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
        border-radius: 20px;
        padding: 1.25rem;
        box-shadow: 
            0 25px 50px rgba(0, 0, 0, 0.15),
            0 0 0 1px rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.18);
        width: 100%;
        max-width: 360px;
        margin: 1rem auto;
    }

    h2 {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 1rem;
        text-align: center;
    }

    .landing-dark .card-body {
        background: rgba(33, 37, 41, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .landing-dark h2 {
        color: #fff !important;
    }

    .form-group {
        position: relative;
        margin-bottom: 0.875rem;
    }

    .form-label {
        position: absolute;
        top: 50%;
        left: 0.65rem;
        transform: translateY(-50%);
        font-size: 0.85rem;
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
        border-radius: 8px;
        padding: 0.625rem;
        font-size: 0.85rem;
        line-height: 1.2;
        transition: all 0.3s ease;
        width: 100%;
        min-height: 38px;
    }

    .form-control:focus {
        background: rgba(255, 255, 255, 1);
        border-color: var(--color-customColor, #667eea);
        box-shadow: 0 0 0 4px rgba(var(--color-customColor-rgb, 102, 126, 234), 0.1);
        outline: none;
    }

    .form-control:focus + .form-label,
    .form-control:not(:placeholder-shown) + .form-label {
        top: 0;
        font-size: 0.75rem;
        color: var(--color-customColor, #667eea);
        background: rgba(255, 255, 255, 0.9);
        padding: 0 0.5rem;
        border-radius: 6px;
    }

    .form-check {
        margin-bottom: 1rem;
    }

    .form-check-input {
        width: 0.875rem;
        height: 0.875rem;
        border: 2px solid #d1d5db;
        border-radius: 3px;
    }

    .form-check-input:checked {
        background-color: var(--color-customColor, #667eea);
        border-color: var(--color-customColor, #667eea);
    }

    .form-check-label {
        font-size: 0.8rem;
        color: #64748b;
        margin-left: 0.4rem;
    }

    .form-check-label a {
        color: var(--color-customColor, #667eea);
        text-decoration: none;
    }

    .btn-primary {
        background: var(--color-customColor, #667eea);
        border: none;
        border-radius: 8px;
        padding: 0.625rem 1rem;
        font-weight: 600;
        font-size: 0.9rem;
        color: white;
        width: 100%;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }

    .btn-primary:hover {
        background: var(--color-customColor, #667eea) !important;
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(var(--color-customColor-rgb, 102, 126, 234), 0.4);
        filter: brightness(1.1);
    }

    .btn-primary:focus,
    .btn-primary:active,
    .btn-primary.active {
        background: var(--color-customColor, #667eea) !important;
        border-color: var(--color-customColor, #667eea) !important;
        box-shadow: 0 0 0 0.2rem rgba(var(--color-customColor-rgb, 102, 126, 234), 0.25) !important;
    }

    .text-center p {
        margin-top: 0.875rem;
        padding-top: 0.875rem;
        border-top: 1px solid rgba(148, 163, 184, 0.2);
        color: #64748b;
        font-size: 0.85rem;
    }

    .text-primary {
        color: var(--color-customColor, #667eea) !important;
        text-decoration: none;
        font-weight: 600;
    }

    .text-primary:hover {
        color: var(--color-customColor, #667eea) !important;
        filter: brightness(1.1);
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.75rem;
        margin-top: 0.4rem;
        padding: 0.4rem 0.6rem;
        background: rgba(248, 215, 218, 0.1);
        border-radius: 6px;
        border-left: 3px solid #dc3545;
    }

    .text-danger {
        background: rgba(248, 215, 218, 0.9);
        border: 1px solid rgba(245, 198, 203, 0.5);
        border-radius: 8px;
        padding: 0.6rem;
        margin-bottom: 0.8rem;
        color: #721c24;
        font-size: 0.8rem;
    }

    .landing-dark .form-control {
        background: rgba(52, 58, 64, 0.8) !important;
        color: #fff !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
    }

    .landing-dark .form-control:focus {
        background: rgba(52, 58, 64, 1) !important;
        border-color: var(--color-customColor, #667eea) !important;
    }

    .landing-dark .form-label {
        color: #adb5bd !important;
    }

    .landing-dark .form-control:focus + .form-label,
    .landing-dark .form-control:not(:placeholder-shown) + .form-label {
        color: var(--color-customColor, #667eea) !important;
        background: rgba(33, 37, 41, 0.9) !important;
    }

    .landing-dark .text-center p {
        color: #adb5bd !important;
        border-top-color: rgba(255, 255, 255, 0.1) !important;
    }

    .landing-dark .form-check-label {
        color: #adb5bd !important;
    }

    .form-group .g-recaptcha {
        display: flex;
        justify-content: center;
        margin: 0.75rem 0;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: scale(0.85);
        transform-origin: center;
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 1rem 0.875rem;
            border-radius: 16px;
            margin: 0.8rem;
        }
        
        h2 {
            font-size: 1.3rem;
        }
    }
</style>

<div class="card-body">
    <div>
        <h2 class="mb-3 f-w-600">{{ __('Register') }}</h2>
    </div>
    <form method="POST" action="{{ route('register.store', ['plan' => $plan]) }}" class='needs-validation' novalidate>
        @if (session('status'))
            <div class="mb-4 font-medium text-lg text-green-600 text-danger">
                {{ __('Email SMTP settings does not configured so please contact to your site admin.') }}
            </div>
        @endif
        @csrf
        <div class="">
            <div class="form-group mb-3">
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                    name="name" value="{{ old('name') }}" autocomplete="name" autofocus
                    placeholder=" " required="required">
                <label for="name" class="form-label">{{ __('Name') }}</label>
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-group mb-3">
                <input class="form-control @error('email') is-invalid @enderror" id="email" type="email"
                    name="email" value="{{ old('email') }}" autocomplete="email" autofocus
                    placeholder=" " required="required">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-3">
                        <input id="password" type="password" data-indicator="pwindicator"
                            class="form-control pwstrength @error('password') is-invalid @enderror" name="password"
                            autocomplete="new-password" placeholder=" " required="required">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-3">
                        <input id="password_confirmation" type="password" data-indicator="password_confirmation"
                            class="form-control pwstrength @error('password_confirmation') is-invalid @enderror"
                            name="password_confirmation" autocomplete="new-password"
                            placeholder=" " required="required">
                        <label for="password_confirmation" class="form-label">{{ __('Confirm') }}</label>
                        @error('password_confirmation')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="form-check custom-checkbox">
                <input type="checkbox" class="form-check-input" id="termsCheckbox" name="terms" required="required">
                <label class="form-check-label text-sm" for="termsCheckbox">{{ __('I agree to the ') }}
                    @if (is_array(json_decode($setting['menubar_page'])) || is_object(json_decode($setting['menubar_page'])))
                        @foreach (json_decode($setting['menubar_page']) as $key => $value)
                            @if (in_array($value->menubar_page_name, ['Terms and Conditions']) && isset($value->template_name))
                                <a href="{{ $value->template_name == 'page_content' ? route('custom.page', $value->page_slug) : $value->page_url }}"
                                    target="_blank">{{ $value->menubar_page_name }}</a>
                            @endif
                        @endforeach
                        {{ __('and the ') }}
                        @foreach (json_decode($setting['menubar_page']) as $key => $value)
                            @if (in_array($value->menubar_page_name, ['Privacy Policy']) && isset($value->template_name))
                                <a href="{{ $value->template_name == 'page_content' ? route('custom.page', $value->page_slug) : $value->page_url }}"
                                    target="_blank">{{ $value->menubar_page_name }}</a>
                            @endif
                        @endforeach
                    @endif
                </label>
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
                <input type="hidden" name="ref_code" value="{{ $ref }}">
                <button type="submit" class="btn btn-primary btn-block mt-2">{{ __('Register') }}</button>
            </div>

        </div>
        <p class="my-4 text-center">{{ __('Already have an account?') }} <a href="{{ route('login', $lang) }}"
                class="text-primary">{{ __('Login') }}</a></p>
    </form>

</div>
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