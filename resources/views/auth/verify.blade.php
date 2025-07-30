@extends('layouts.auth')
@section('page-title')
    {{ __('Verify Email') }}
@endsection
@php
    $logo = \App\Models\Utility::get_file('uploads/logo');
    $company_logo = Utility::getValByName('company_logo');
    $lang = \App::getLocale('lang');
@endphp

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
                    <a href="{{ route('verification.notice', $code) }}" tabindex="0" class="dropdown-item ">
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
        border-radius: 20px;
        padding: 1.25rem;
        box-shadow: 
            0 25px 50px rgba(0, 0, 0, 0.15),
            0 0 0 1px rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.18);
        width: 100%;
        max-width: 400px;
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

    .verification-text {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 1.25rem;
        line-height: 1.5;
        text-align: center;
    }

    .alert-success {
        background: rgba(13, 110, 253, 0.1);
        border: 1px solid rgba(13, 110, 253, 0.25);
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 1rem;
        color: #084298;
        font-size: 0.85rem;
        text-align: center;
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
        margin-bottom: 0.75rem;
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

    .btn-danger {
        background: #dc3545;
        border: none;
        border-radius: 8px;
        padding: 0.625rem 1rem;
        font-weight: 600;
        font-size: 0.9rem;
        color: white;
        width: 100%;
        transition: all 0.3s ease;
    }

    .btn-danger:hover {
        background: #dc3545 !important;
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(220, 53, 69, 0.4);
        filter: brightness(1.1);
    }

    .btn-danger:focus,
    .btn-danger:active,
    .btn-danger.active {
        background: #dc3545 !important;
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }

    .verification-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .landing-dark .verification-text {
        color: #adb5bd !important;
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
        
        .verification-text {
            font-size: 0.85rem;
        }
    }
</style>

<div class="card-body">
    @if (session('status') == 'verification-link-sent')
        <div class="alert-success">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif
    
    <div>
        <h2>{{ __('Verify Email') }}</h2>
        <div class="verification-text">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>
    </div>
    
    <div class="verification-actions">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                {{ __('Resend Verification Email') }}
            </button>
        </form>
        
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-danger">{{ __('Logout') }}</button>
        </form>
    </div>
</div>
@endsection