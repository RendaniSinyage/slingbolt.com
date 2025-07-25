<!-- [ Hero Section ] start -->
@if ($settings['home_status'] == 'on')
<section class="hero-section" id="home">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <!-- Left Content -->
            <div class="col-lg-6">
                <div class="hero-content">
                    <!-- Badge -->
                    @if($settings['home_offer_text'])
                    <div class="hero-badge">
                        {{ $settings['home_offer_text'] }}
                    </div>
                    @endif

                    <!-- Main Heading -->
                    <h1 class="hero-title">
                        {{ $settings['home_heading'] }}
                    </h1>

                    <!-- Description -->
                    <p class="hero-description">
                        {{ $settings['home_description'] }}
                    </p>

                    <!-- Buttons -->
                    <div class="hero-buttons">
                        @if ($settings['home_buy_now_link'])
                        <a href="{{ $settings['home_buy_now_link'] }}" class="btn-primary-hero">
                            Get Started
                            <i class="ti ti-arrow-right ms-2"></i>
                        </a>
                        @endif
                        
                        @if ($settings['home_live_demo_link'])
                        <a href="{{ $settings['home_live_demo_link'] }}" class="btn-demo">
                            Demo
                            <i class="ti ti-external-link ms-2"></i>
                        </a>
                        @endif
                    </div>

                    <!-- Trust Stats -->
                    @if($settings['home_trusted_by'])
                    <div class="trust-stats">
                        <div class="trust-item">
                            <div class="trust-number">10,000+</div>
                            <div class="trust-label">Business</div>
                        </div>
                        <div class="trust-item">
                            <div class="trust-number">160+</div>
                            <div class="trust-label">Countries</div>
                        </div>
                        <div class="trust-item">
                            <div class="trust-number">550K+</div>
                            <div class="trust-label">Total Downloads</div>
                        </div>
                        <div class="trust-item">
                            <div class="trust-number">20+</div>
                            <div class="trust-label">Language Supports</div>
                        </div>
                        <div class="trust-item">
                            <div class="trust-number">93%</div>
                            <div class="trust-label">Customer Satisfactions</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Right Content - Dashboard Image -->
            <div class="col-lg-6">
                <div class="hero-image">
                    @if(Storage::exists('/uploads/landing_page_image/'.$settings['home_banner']))
                    <div class="dashboard-preview">
                        <img src="{{ $logo . '/' . $settings['home_banner'] }}" alt="Dashboard Preview" class="img-fluid">
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, var(--bs-primary) 0%, rgba(var(--bs-primary-rgb), 0.8) 100%);
    color: white;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 2;
    padding: 60px 0;
}

/* Hero Badge */
.hero-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 50px;
    padding: 8px 20px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 24px;
    backdrop-filter: blur(10px);
}

/* Hero Title */
.hero-title {
    font-size: 48px;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 24px;
    color: white;
}

/* Hero Description */
.hero-description {
    font-size: 18px;
    line-height: 1.6;
    margin-bottom: 32px;
    color: rgba(255, 255, 255, 0.9);
    max-width: 500px;
}

/* Hero Buttons */
.hero-buttons {
    display: flex;
    gap: 16px;
    margin-bottom: 48px;
    flex-wrap: wrap;
}

.btn-primary-hero {
    background: white;
    color: var(--bs-primary);
    text-decoration: none;
    padding: 14px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    display: inline-flex;
    align-items: center;
    transition: all 0.3s ease;
}

.btn-primary-hero:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    color: var(--bs-primary);
}

.btn-demo {
    background: transparent;
    color: white;
    text-decoration: none;
    padding: 14px 28px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    display: inline-flex;
    align-items: center;
    transition: all 0.3s ease;
}

.btn-demo:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.5);
    color: white;
}

/* Trust Stats */
.trust-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 32px;
}

.trust-item {
    text-align: center;
}

.trust-number {
    font-size: 24px;
    font-weight: 700;
    color: white;
    margin-bottom: 4px;
}

.trust-label {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 500;
}

/* Hero Image */
.hero-image {
    position: relative;
    z-index: 2;
    padding: 40px 0;
}

.dashboard-preview {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.dashboard-preview img {
    width: 100%;
    height: auto;
    display: block;
}

/* Responsive */
@media (max-width: 991px) {
    .hero-title {
        font-size: 36px;
    }
    
    .hero-description {
        font-size: 16px;
    }
    
    .hero-content {
        text-align: center;
        padding: 40px 0;
    }
    
    .trust-stats {
        justify-content: center;
        gap: 24px;
    }
    
    .hero-buttons {
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .hero-title {
        font-size: 28px;
    }
    
    .trust-stats {
        gap: 16px;
    }
    
    .trust-number {
        font-size: 20px;
    }
    
    .btn-primary-hero,
    .btn-demo {
        padding: 12px 20px;
        font-size: 14px;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .hero-buttons a {
        width: 100%;
        max-width: 250px;
        justify-content: center;
    }
}
</style>
@endif
<!-- [ Hero Section ] End -->