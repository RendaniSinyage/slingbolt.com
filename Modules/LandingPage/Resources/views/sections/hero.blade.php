<!-- [ Banner ] start -->
@if ($settings['home_status'] == 'on')
    <section class="main-banner bg-primary" id="home">
        <div class="container-fluid">
            <div class="row min-vh-100 align-items-center g-0">
                <div class="col-xl-5 col-lg-6 col-md-12">
                    <div class="hero-content p-4 p-lg-5">
                        <!-- Offer Badge -->
                        @if($settings['home_offer_text'])
                        <div class="offer-badge mb-4">
                            <span class="badge-content">
                                <i class="ti ti-discount-2 me-2"></i>
                                {{ $settings['home_offer_text'] }}
                                <i class="ti ti-sparkles ms-2"></i>
                            </span>
                        </div>
                        @endif

                        <!-- Main Heading -->
                        <h1 class="hero-title mb-4">
                            {{ $settings['home_heading'] }}
                            <span class="highlight-text">Success</span>
                        </h1>

                        <!-- Description -->
                        <p class="hero-description mb-4">
                            {{ $settings['home_description'] }}
                        </p>

                        <!-- Trust Indicator -->
                        @if($settings['home_trusted_by'])
                        <div class="trust-indicator mb-4">
                            <div class="trust-avatars">
                                <div class="avatar-stack">
                                    <div class="avatar"></div>
                                    <div class="avatar"></div>
                                <!-- [ Banner ] start -->
@if ($settings['home_status'] == 'on')
    <section class="main-banner bg-primary" id="home">
        <div class="container-fluid">
            <div class="row min-vh-100 align-items-center g-0">
                <div class="col-xl-5 col-lg-6 col-md-12">
                    <div class="hero-content p-4 p-lg-5">
                        <!-- Offer Badge -->
                        @if($settings['home_offer_text'])
                        <div class="offer-badge mb-4">
                            <span class="badge-content">
                                <i class="ti ti-discount-2 me-2"></i>
                                {{ $settings['home_offer_text'] }}
                                <i class="ti ti-sparkles ms-2"></i>
                            </span>
                        </div>
                        @endif

                        <!-- Main Heading -->
                        <h1 class="hero-title mb-4">
                            {{ $settings['home_heading'] }}
                            <span class="highlight-text">Success</span>
                        </h1>

                        <!-- Description -->
                        <p class="hero-description mb-4">
                            {{ $settings['home_description'] }}
                        </p>

                        <!-- Trust Indicator -->
                        @if($settings['home_trusted_by'])
                        <div class="trust-indicator mb-4">
                            <div class="trust-avatars">
                                <div class="avatar-stack">
                                    <div class="avatar"></div>
                                    <div class="avatar"></div>
                                    <div class="avatar"></div>
                                    <div class="avatar"></div>
                                    <div class="avatar-more">+</div>
                                </div>
                                <div class="trust-text">
                                    <span class="trust-number">{{ $settings['home_trusted_by'] }}</span>
                                    <span class="trust-label">trust our platform</span>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- CTA Buttons -->
                        <div class="hero-actions">
                            @if ($settings['home_live_demo_link'])
                                <a href="{{ $settings['home_live_demo_link'] }}" class="btn btn-hero-primary">
                                    <i class="ti ti-play me-2"></i>
                                    {{ __('Live Demo') }}
                                </a>
                            @endif
                            @if ($settings['home_buy_now_link'])
                                <a href="{{ $settings['home_buy_now_link'] }}" class="btn btn-hero-outline">
                                    <i class="ti ti-rocket me-2"></i>
                                    {{ __('Get Started') }}
                                </a>
                            @endif
                        </div>

                        <!-- Feature Pills -->
                        <div class="feature-pills mt-4">
                            <span class="pill">
                                <i class="ti ti-check me-1"></i>
                                Free trial
                            </span>
                            <span class="pill">
                                <i class="ti ti-check me-1"></i>
                                No credit card
                            </span>
                            <span class="pill">
                                <i class="ti ti-check me-1"></i>
                                Cancel anytime
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-7 col-lg-6 col-md-12">
                    <div class="hero-visual position-relative">
                        @if(Storage::exists('/uploads/landing_page_image/'.$settings['home_banner']))
                        <div class="main-image-container">
                            <img class="main-image" src="{{ $logo . '/' . $settings['home_banner'] }}" alt="Hero Image">
                            
                            <!-- Floating Elements -->
                            <div class="floating-element element-1">
                                <div class="stat-card">
                                    <i class="ti ti-trending-up text-success"></i>
                                    <span class="stat-number">+127%</span>
                                    <span class="stat-label">Growth</span>
                                </div>
                            </div>
                            
                            <div class="floating-element element-2">
                                <div class="notification-card">
                                    <div class="notification-avatar"></div>
                                    <div class="notification-content">
                                        <span class="notification-title">New Sale!</span>
                                        <span class="notification-desc">$2,450 earned</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="floating-element element-3">
                                <div class="rating-card">
                                    <div class="stars">
                                        <i class="ti ti-star-filled"></i>
                                        <i class="ti ti-star-filled"></i>
                                        <i class="ti ti-star-filled"></i>
                                        <i class="ti ti-star-filled"></i>
                                        <i class="ti ti-star-filled"></i>
                                    </div>
                                    <span class="rating-text">5.0 Rating</span>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Trusted Logos -->
                        @if($settings['home_logo'])
                        <div class="trusted-logos mt-5">
                            <div class="logos-container">
                                @foreach (explode(',', $settings['home_logo']) as $k => $home_logo)
                                    @if($home_logo)
                                    <div class="logo-item">
                                        <img src="{{ $logo.'/'.$home_logo }}" alt="Trusted Partner" class="trusted-logo">
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Background Elements -->
        <div class="hero-bg-elements">
            <div class="bg-shape shape-1"></div>
            <div class="bg-shape shape-2"></div>
            <div class="bg-shape shape-3"></div>
        </div>
    </section>

    <!-- Enhanced Hero Styles -->
    <style>
    .main-banner {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .hero-bg-elements {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    .bg-shape {
        position: absolute;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .shape-1 {
        width: 300px;
        height: 300px;
        top: -150px;
        right: -150px;
        animation: float 6s ease-in-out infinite;
    }

    .shape-2 {
        width: 200px;
        height: 200px;
        bottom: -100px;
        left: -100px;
        animation: float 8s ease-in-out infinite reverse;
    }

    .shape-3 {
        width: 150px;
        height: 150px;
        top: 50%;
        left: 10%;
        animation: float 7s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .offer-badge {
        display: inline-block;
        animation: bounce 2s infinite;
    }

    .badge-content {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
    }

    @keyframes bounce {
        0%, 20%, 53%, 80%, 100% { transform: translateY(0); }
        40%, 43% { transform: translateY(-10px); }
        70% { transform: translateY(-5px); }
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        color: white;
        line-height: 1.1;
        margin-bottom: 1.5rem;
    }

    .highlight-text {
        background: linear-gradient(45deg, #fbbf24, #f59e0b);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-description {
        font-size: 1.25rem;
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.6;
        max-width: 500px;
    }

    .trust-indicator {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .avatar-stack {
        display: flex;
        align-items: center;
    }

    .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 3px solid white;
        margin-left: -10px;
        background: linear-gradient(45deg, #f59e0b, #ef4444, #8b5cf6, #06b6d4);
    }

    .avatar:first-child {
        margin-left: 0;
    }

    .avatar-more {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        border: 3px solid white;
        margin-left: -10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.2rem;
    }

    .trust-text {
        display: flex;
        flex-direction: column;
    }

    .trust-number {
        font-size: 1.1rem;
        font-weight: 700;
        color: white;
    }

    .trust-label {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.8);
    }

    .hero-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
    }

    .btn-hero-primary {
        background: white;
        color: #059669;
        border: none;
        padding: 0.875rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-hero-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        color: #059669;
    }

    .btn-hero-outline {
        background: transparent;
        color: white;
        border: 2px solid white;
        padding: 0.875rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
    }

    .btn-hero-outline:hover {
        background: white;
        color: #059669;
        transform: translateY(-2px);
    }

    .feature-pills {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .pill {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.875rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .hero-visual {
        position: relative;
        z-index: 2;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .main-image-container {
        position: relative;
        max-width: 600px;
        width: 100%;
    }

    .main-image {
        width: 100%;
        height: auto;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .floating-element {
        position: absolute;
        animation: floatElement 6s ease-in-out infinite;
    }

    .element-1 {
        top: 20%;
        right: -10%;
        animation-delay: 0s;
    }

    .element-2 {
        bottom: 30%;
        left: -15%;
        animation-delay: 2s;
    }

    .element-3 {
        top: 10%;
        left: 10%;
        animation-delay: 4s;
    }

    @keyframes floatElement {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
    }

    .stat-card, .notification-card, .rating-card {
        background: white;
        border-radius: 15px;
        padding: 1rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(10px);
    }

    .stat-card {
        text-align: center;
        min-width: 120px;
    }

    .stat-number {
        display: block;
        font-size: 1.5rem;
        font-weight: 800;
        color: #059669;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .notification-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 200px;
    }

    .notification-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(45deg, #f59e0b, #ef4444);
    }

    .notification-content {
        display: flex;
        flex-direction: column;
    }

    .notification-title {
        font-weight: 600;
        color: #111827;
        font-size: 0.875rem;
    }

    .notification-desc {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .rating-card {
        text-align: center;
        min-width: 140px;
    }

    .stars {
        color: #fbbf24;
        margin-bottom: 0.5rem;
    }

    .rating-text {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 600;
    }

    .trusted-logos {
        margin-top: 3rem;
    }

    .logos-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 2rem;
        flex-wrap: wrap;
        opacity: 0.7;
    }

    .logo-item {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .trusted-logo {
        height: 40px;
        width: auto;
        filter: brightness(0) invert(1);
        opacity: 0.8;
        transition: all 0.3s ease;
    }

    .trusted-logo:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    /* Responsive Design */
    @media (max-width: 1199.98px) {
        .hero-title {
            font-size: 3rem;
        }
    }

    @media (max-width: 991.98px) {
        .main-banner .row {
            min-height: auto;
        }
        
        .hero-content {
            text-align: center;
            padding: 3rem 2rem;
        }
        
        .hero-title {
            font-size: 2.5rem;
        }
        
        .floating-element {
            display: none;
        }
        
        .hero-visual {
            padding: 2rem;
        }
    }

    @media (max-width: 575.98px) {
        .hero-title {
            font-size: 2rem;
        }
        
        .hero-description {
            font-size: 1.1rem;
        }
        
        .hero-actions {
            flex-direction: column;
            align-items: center;
        }
        
        .btn-hero-primary,
        .btn-hero-outline {
            width: 100%;
            max-width: 280px;
            justify-content: center;
        }
        
        .feature-pills {
            justify-content: center;
        }
        
        .trust-indicator {
            justify-content: center;
        }
    }
    </style>
@endif
<!-- [ Banner ] end -->