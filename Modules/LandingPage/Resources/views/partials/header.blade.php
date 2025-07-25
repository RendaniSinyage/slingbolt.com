<!-- [ Header ] start -->
<header class="main-header">
    @if ($settings['topbar_status'] == 'on')
        <div class="top-banner">
            <div class="container">
                <div class="top-banner-content">
                    {!! $settings['topbar_notification_msg'] !!}
                </div>
            </div>
        </div>
    @endif
    
    @if ($settings['menubar_status'] == 'on')
        <div class="header-nav">
            <div class="container">
                <nav class="navbar navbar-expand-lg">
                    <!-- Logo -->
                    <a class="navbar-brand" href="/">
                        <img src="{{ $logo .'/'. $settings['site_logo'] }}" alt="logo" class="logo">
                    </a>

                    <!-- Navigation Menu -->
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto me-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="#home">{{ $settings['home_title'] }}</a>
                            </li>
                            @if ($settings['feature_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link" href="#features">{{ $settings['feature_title'] }}</a>
                            </li>
                            @endif
                            @if ($settings['plan_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link" href="#plan">{{ $settings['plan_title'] }}</a>
                            </li>
                            @endif
                            @if ($settings['faq_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link" href="#faq">{{ $settings['faq_title'] }}</a>
                            </li>
                            @endif

                            @if (is_array(json_decode($settings['menubar_page'])) || is_object(json_decode($settings['menubar_page'])))
                                @foreach (json_decode($settings['menubar_page']) as $key => $value)
                                    @if ($value->header == 'on' && $value->template_name == 'page_content')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('custom.page', $value->page_slug) }}">{{ $value->menubar_page_name }}</a>
                                        </li>
                                    @elseif($value->header == 'on')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ $value->page_url }}" target="_blank">{{ $value->menubar_page_name }}</a>
                                        </li>
                                    @endif
                                @endforeach
                            @endif
                        </ul>
                    </div>

                    <!-- Auth Buttons -->
                    <div class="auth-buttons">
                        <a href="{{ route('login') }}" class="btn-signin">
                            <i class="ti ti-login me-1"></i>
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" class="btn-getstarted">
                            <i class="ti ti-user-plus me-1"></i>
                            Get Started
                        </a>
                    </div>

                    <!-- Mobile Toggle -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </nav>
            </div>
        </div>
    @endif
</header>

<style>
/* Top Banner */
.top-banner {
    background: var(--bs-primary);
    color: white;
    padding: 8px 0;
    font-size: 14px;
    text-align: center;
    font-weight: 500;
}

/* Header Navigation */
.header-nav {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.navbar {
    padding: 15px 0;
}

.navbar-brand .logo {
    height: 40px;
    width: auto;
}

/* Navigation Links */
.navbar-nav .nav-link {
    color: #374151;
    font-weight: 500;
    padding: 8px 20px;
    margin: 0 5px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.navbar-nav .nav-link:hover {
    color: var(--bs-primary);
    background: rgba(var(--bs-primary-rgb), 0.1);
}

/* Auth Buttons */
.auth-buttons {
    display: flex;
    gap: 15px;
    align-items: center;
}

.btn-signin {
    color: var(--bs-primary);
    text-decoration: none;
    font-weight: 600;
    padding: 8px 20px;
    border-radius: 6px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.btn-signin:hover {
    background: rgba(var(--bs-primary-rgb), 0.1);
    color: var(--bs-primary);
}

.btn-getstarted {
    background: var(--bs-primary);
    color: white;
    text-decoration: none;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.btn-getstarted:hover {
    background: rgba(var(--bs-primary-rgb), 0.9);
    color: white;
    transform: translateY(-1px);
}

/* Mobile */
.navbar-toggler {
    border: none;
    padding: 4px 8px;
}

.navbar-toggler:focus {
    box-shadow: none;
}

@media (max-width: 991px) {
    .navbar-collapse {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }
    
    .auth-buttons {
        margin-top: 20px;
        justify-content: center;
    }
    
    .navbar-nav {
        text-align: center;
    }
}
</style>

<!-- [ Header ] End -->