<!-- [ Header ] start -->
<header class="main-header">
    @if ($settings['topbar_status'] == 'on')
        <div class="announcement bg-primary text-center p-2 position-relative overflow-hidden">
            <div class="announcement-content position-relative z-index-1">
                <p class="mb-0 text-white">{!! $settings['topbar_notification_msg'] !!}</p>
            </div>
            <!-- Animated background effect -->
            <div class="announcement-bg position-absolute top-0 start-0 w-100 h-100 opacity-10"></div>
        </div>
    @endif
    
    @if ($settings['menubar_status'] == 'on')
        <div class="container-fluid px-3 px-lg-4">
            <nav class="navbar navbar-expand-lg default top-nav-collapse py-3">
                <!-- Logo Section -->
                <div class="navbar-brand-wrapper">
                    <a class="navbar-brand d-flex align-items-center" href="/">
                        <img src="{{ $logo .'/'. $settings['site_logo'] }}" alt="logo" class="header-logo">
                    </a>
                </div>

                <!-- Main Navigation -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="#home">
                                <i class="ti ti-home me-1"></i>
                                {{ $settings['home_title'] }}
                            </a>
                        </li>
                        @if ($settings['feature_status'] == 'on')
                        <li class="nav-item">
                            <a class="nav-link" href="#features">
                                <i class="ti ti-star me-1"></i>
                                {{ $settings['feature_title'] }}
                            </a>
                        </li>
                        @endif
                        @if ($settings['plan_status'] == 'on')
                        <li class="nav-item">
                            <a class="nav-link" href="#plan">
                                <i class="ti ti-package me-1"></i>
                                {{ $settings['plan_title'] }}
                            </a>
                        </li>
                        @endif
                        @if ($settings['faq_status'] == 'on')
                        <li class="nav-item">
                            <a class="nav-link" href="#faq">
                                <i class="ti ti-help me-1"></i>
                                {{ $settings['faq_title'] }}
                            </a>
                        </li>
                        @endif

                        @if (is_array(json_decode($settings['menubar_page'])) || is_object(json_decode($settings['menubar_page'])))
                            @foreach (json_decode($settings['menubar_page']) as $key => $value)
                                @if ($value->header == 'on' && $value->template_name == 'page_content')
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('custom.page', $value->page_slug) }}">
                                            <i class="ti ti-file-text me-1"></i>
                                            {{ $value->menubar_page_name }}
                                        </a>
                                    </li>
                                @elseif($value->header == 'on')
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ $value->page_url }}" target="_blank">
                                            <i class="ti ti-external-link me-1"></i>
                                            {{ $value->menubar_page_name }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="navbar-actions d-flex align-items-center gap-2">
                    <!-- Login Button -->
                    <a href="{{ route('login') }}" class="btn btn-theme-outline btn-animated d-flex align-items-center gap-2">
                        <i class="ti ti-login"></i>
                        <span class="d-none d-md-inline">{{ __('Sign In') }}</span>
                    </a>

                    <!-- Register Button -->
                    <a href="{{ route('register') }}" class="btn btn-theme-primary btn-animated d-flex align-items-center gap-2">
                        <i class="ti ti-user-plus"></i>
                        <span class="d-none d-md-inline">{{ __('Get Started') }}</span>
                    </a>

                    <!-- Mobile Menu Toggle -->
                    <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </nav>
        </div>
    @endif
</header>

<!-- Enhanced Header Styles with Green Theme -->
<style>
:root {
    --header-primary: #10b981; /* Green from hero section */
    --header-primary-rgb: 16, 185, 129;
    --header-primary-hover: #059669;
    --header-secondary: #065f46;
}

/* Header Base Styles */
.main-header {
    position: relative;
    z-index: 1000;
    background: rgba(var(--bs-body-bg-rgb), 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(var(--bs-border-color-rgb), 0.1);
    transition: all 0.3s ease;
}

.main-header.scrolled {
    background: rgba(var(--bs-body-bg-rgb), 0.98);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
}

/* Announcement Bar */
.announcement {
    position: relative;
    overflow: hidden;
    background: var(--header-primary) !important;
}

.announcement-bg {
    background: linear-gradient(45deg, 
        rgba(255, 255, 255, 0.1) 0%, 
        rgba(255, 255, 255, 0.2) 50%, 
        rgba(255, 255, 255, 0.1) 100%);
    animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { transform: translateX(-100%); }
    50% { transform: translateX(100%); }
}

/* Logo and Brand */
.navbar-brand-wrapper {
    min-width: 150px;
}

.header-logo {
    height: 40px;
    width: auto;
    transition: transform 0.3s ease;
}

.navbar-brand:hover .header-logo {
    transform: scale(1.05);
}

/* Navigation Links */
.navbar-nav .nav-link {
    position: relative;
    font-weight: 500;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    color: var(--bs-body-color);
}

.navbar-nav .nav-link:hover {
    background-color: rgba(var(--header-primary-rgb), 0.1);
    color: var(--header-primary);
    transform: translateY(-1px);
}

.navbar-nav .nav-link.active {
    background-color: rgba(var(--header-primary-rgb), 0.15);
    color: var(--header-primary);
    font-weight: 600;
}

.navbar-nav .nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: var(--header-primary);
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.navbar-nav .nav-link:hover::after,
.navbar-nav .nav-link.active::after {
    width: 80%;
}

/* Action Buttons */
.btn-theme-outline {
    border: 1.5px solid var(--header-primary);
    color: var(--header-primary);
    background: transparent;
    border-radius: 50px;
    padding: 0.5rem 1.25rem;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-theme-outline:hover {
    background: var(--header-primary);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(var(--header-primary-rgb), 0.4);
}

.btn-theme-primary {
    background: var(--header-primary);
    color: white;
    border: none;
    border-radius: 50px;
    padding: 0.5rem 1.25rem;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-theme-primary:hover {
    background: var(--header-primary-hover);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(var(--header-primary-rgb), 0.5);
    filter: brightness(1.1);
}

.btn-animated::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.btn-animated:hover::before {
    left: 100%;
}

/* Mobile Menu Toggle */
.navbar-toggler {
    border: none;
    padding: 0.5rem;
    border-radius: 8px;
    background: rgba(var(--header-primary-rgb), 0.1);
    transition: all 0.3s ease;
}

.navbar-toggler:hover {
    background: rgba(var(--header-primary-rgb), 0.2);
    transform: scale(1.05);
}

.navbar-toggler:focus {
    box-shadow: 0 0 0 0.25rem rgba(var(--header-primary-rgb), 0.25);
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23333' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    width: 1.5em;
    height: 1.5em;
}

/* Dark mode support */
[data-bs-theme="dark"] .navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23fff' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Responsive Design */
@media (max-width: 991.98px) {
    .navbar-collapse {
        background: var(--bs-body-bg);
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-top: 1rem;
        padding: 1rem;
    }
    
    .navbar-nav {
        margin: 0 !important;
    }
    
    .navbar-nav .nav-link {
        margin: 0.25rem 0;
    }
    
    .navbar-actions {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--bs-border-color);
    }
    
    .btn-theme-outline,
    .btn-theme-primary {
        flex: 1;
        justify-content: center;
    }
}

@media (max-width: 575.98px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .navbar {
        padding: 0.75rem 0;
    }
    
    .header-logo {
        height: 32px;
    }
}

/* Scroll effect */
.navbar.scrolled {
    background: rgba(var(--bs-body-bg-rgb), 0.95);
    backdrop-filter: blur(15px);
}
</style>

<!-- Header JavaScript for scroll effects -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('.main-header');
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
            navbar.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
            navbar.classList.remove('scrolled');
        }
    });
    
    // Active link highlighting based on scroll position
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    window.addEventListener('scroll', function() {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            if (scrollY >= sectionTop) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    });
});
</script>

<!-- [ Header ] End -->