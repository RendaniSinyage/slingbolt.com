<!-- Updated Header with Modern Design -->
<header class="main-header modern-header">
    @if ($settings['topbar_status'] == 'on')
        <div class="announcement-bar">
            <div class="container">
                <div class="announcement-content">
                    <span class="announcement-text">{!! $settings['topbar_notification_msg'] !!}</span>
                </div>
            </div>
        </div>
    @endif
    
    @if ($settings['menubar_status'] == 'on')
        <div class="main-nav-wrapper">
            <div class="container">
                <nav class="navbar navbar-expand-lg modern-navbar">
                    <div class="navbar-brand-wrapper">
                        <a class="navbar-brand" href="/">
                            <img src="{{ $logo .'/'. $settings['site_logo'] }}" alt="logo" class="brand-logo">
                        </a>
                    </div>
                    
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav main-nav">
                            <li class="nav-item">
                                <a class="nav-link active" href="#home">{{ $settings['home_title'] }}</a>
                            </li>
                            @if ($settings['feature_status'] == 'on')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#features" data-bs-toggle="dropdown">
                                    {{ $settings['feature_title'] }}
                                    <i class="ti ti-chevron-down"></i>
                                </a>
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
                                            <a class="nav-link" href="{{ route('custom.page', $value->page_slug) }}">
                                                {{ $value->menubar_page_name }}
                                            </a>
                                        </li>
                                    @elseif($value->header == 'on')
                                        <li class="nav-item dropdown">
                                            <a class="nav-link dropdown-toggle" href="{{ $value->page_url }}">
                                                {{ $value->menubar_page_name }}
                                                <i class="ti ti-chevron-down"></i>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            @endif
                            
                            <li class="nav-item">
                                <a class="nav-link" href="#blog">Blog</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#help" data-bs-toggle="dropdown">
                                    Help
                                    <i class="ti ti-chevron-down"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="navbar-actions">
                        <a href="{{ route('register') }}" class="btn btn-get-started">
                            <span>Get Started</span>
                        </a>
                        
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                    </div>
                </nav>
            </div>
        </div>
    @endif
</header>

<style>
/* Modern Header Styles */
.modern-header {
    position: relative;
    z-index: 1000;
}

.announcement-bar {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    color: white;
    padding: 12px 0;
    text-align: center;
    font-size: 14px;
    font-weight: 500;
}

.announcement-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.main-nav-wrapper {
    background: var(--bs-primary, #007bff);
    padding: 0;
    position: sticky;
    top: 0;
    z-index: 999;
    transition: all 0.3s ease;
}

.modern-navbar {
    padding: 16px 0;
    align-items: center;
}

.navbar-brand-wrapper {
    flex: 0 0 auto;
}

.brand-logo {
    height: 40px;
    width: auto;
    object-fit: contain;
}

.main-nav {
    flex: 1;
    justify-content: center;
    gap: 32px;
    margin: 0;
}

.nav-item {
    position: relative;
}

.nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    font-weight: 500;
    font-size: 15px;
    padding: 8px 0 !important;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s ease;
    border: none;
    background: none;
}

.nav-link:hover,
.nav-link.active {
    color: #ffffff !important;
}

.nav-link.dropdown-toggle::after {
    display: none;
}

.nav-link .ti-chevron-down {
    font-size: 12px;
    transition: transform 0.2s ease;
}

.nav-link:hover .ti-chevron-down {
    transform: rotate(180deg);
}

.navbar-actions {
    display: flex;
    align-items: center;
    gap: 16px;
}

.btn-get-started {
    background: #ffffff;
    color: var(--bs-primary, #007bff);
    border: 2px solid #ffffff;
    border-radius: 24px;
    padding: 10px 24px;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-get-started:hover {
    background: transparent;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
}

.navbar-toggler {
    border: none;
    padding: 8px;
    background: none;
}

.navbar-toggler:focus {
    box-shadow: none;
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Scrolled state */
.main-nav-wrapper.scrolled {
    background: var(--bs-primary, #007bff);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.15);
}

.main-nav-wrapper.scrolled .modern-navbar {
    padding: 12px 0;
}

/* Mobile responsiveness */
@media (max-width: 991.98px) {
    .main-nav {
        flex-direction: column;
        gap: 16px;
        margin-top: 16px;
    }
    
    .navbar-actions {
        margin-top: 16px;
        justify-content: center;
    }
    
    .btn-sign-in span {
        display: none;
    }
}

@media (max-width: 768px) {
    .announcement-bar {
        padding: 8px 0;
        font-size: 13px;
    }
    
    .modern-navbar {
        padding: 12px 0;
    }
    
    .brand-logo {
        height: 32px;
    }
}
</style>

<script>
// Add scroll effect
document.addEventListener('DOMContentLoaded', function() {
    const navWrapper = document.querySelector('.main-nav-wrapper');
    
    if (navWrapper) {
        let lastScrollTop = 0;
        
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > 50) {
                navWrapper.classList.add('scrolled');
            } else {
                navWrapper.classList.remove('scrolled');
            }
            
            lastScrollTop = scrollTop;
        });
    }
    
    // Smooth scroll for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>