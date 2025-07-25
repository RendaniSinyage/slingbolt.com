@if ($settings['home_status'] == 'on')
<section id="home" class="hero-section d-flex flex-column align-items-center justify-content-center text-center py-5" style="min-height: 90vh; background: transparent;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                @if (!empty($settings['home_offer_text']))
                <span class="badge bg-dark text-white rounded-pill px-3 py-2 mb-4 fw-bold small">
                    {!! $settings['home_offer_text'] !!}
                </span>
                @endif

                <h1 class="fw-bold display-4 mb-4">
                    {!! $settings['home_heading'] ?? 'Supercharge Your Growing Business from Your WordPress Dashboard' !!}
                </h1>

                <p class="lead text-secondary mb-4">
                    {!! $settings['home_description'] ?? 'WP ERP optimizes your small to medium businesses with powerful HR, CRM, and Accounting tools.' !!}
                </p>

                <div class="d-flex justify-content-center gap-3 mb-5">
                    @if (!empty($settings['home_live_demo_link']))
                    <a href="{{ $settings['home_live_demo_link'] }}" class="btn btn-outline-primary rounded-pill px-4">
                        {{ __('Live Demo') }}
                    </a>
                    @endif

                    @if (!empty($settings['home_buy_now_link']))
                    <a href="{{ $settings['home_buy_now_link'] }}" class="btn btn-primary rounded-pill px-4">
                        {{ __('Buy Now') }}
                    </a>
                    @endif
                </div>
            </div>
        </div>

        @php
            $banner_hrm = isset($settings['home_banner_hrm']) ? $logo . '/' . $settings['home_banner_hrm'] : ($logo . '/' . ($settings['home_banner'] ?? 'default-banner.png'));
            $banner_crm = isset($settings['home_banner_crm']) ? $logo . '/' . $settings['home_banner_crm'] : ($logo . '/' . ($settings['home_banner'] ?? 'default-banner.png'));
            $banner_accounting = isset($settings['home_banner_accounting']) ? $logo . '/' . $settings['home_banner_accounting'] : ($logo . '/' . ($settings['home_banner'] ?? 'default-banner.png'));
            $banner_project = isset($settings['home_banner_project']) ? $logo . '/' . $settings['home_banner_project'] : ($logo . '/' . ($settings['home_banner'] ?? 'default-banner.png'));
            $default_banner = $logo . '/' . ($settings['home_banner'] ?? 'default-banner.png');
        @endphp

        <div class="row justify-content-center mt-3">
            <div class="col-lg-10 position-relative text-center">

                <!-- Layered Frames Effect - Above the main image -->
                <div class="mockup-wrapper">
                    <!-- Frame layers that appear above the main image -->
                    <div class="frame-layer frame-layer-1"></div>
                    <div class="frame-layer frame-layer-2"></div>
                    
                    <!-- Main Dashboard Image -->
                    <div class="main-dashboard">
                        <img id="banner-image" src="{{ $default_banner }}" class="img-fluid rounded shadow banner-img" alt="Banner Image" />
                    </div>
                </div>

                <!-- Module Buttons -->
                <div class="d-flex justify-content-center gap-1 mt-4 flex-wrap">
                    <button class="btn module-tab active-tab" data-img="{{ $banner_accounting }}">Accounting</button>
                    <button class="btn module-tab" data-img="{{ $banner_crm }}">CRM</button>
                    <button class="btn module-tab" data-img="{{ $banner_hrm }}">HRM</button>
                    <button class="btn module-tab" data-img="{{ $banner_project }}">Project</button>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<style>
    /* Mockup wrapper for layered effect */
    .mockup-wrapper {
        position: relative;
        display: inline-block;
        max-width: 100%;
    }

    /* Frame layers with 3D perspective - OUTLINES ONLY */
    .frame-layer {
        position: absolute;
        background: transparent;
        border: 2px solid rgba(255, 255, 255, 0.4);
        border-radius: 15px;
        pointer-events: none;
        z-index: 2;
        transform-style: preserve-3d;
    }

    /* First frame layer - closest, slight perspective */
    .frame-layer-1 {
        top: -8px;
        left: 12px;
        right: -12px;
        bottom: 8px;
        opacity: 0.7;
        transform: perspective(1000px) rotateX(2deg) rotateY(-1deg);
        border-color: rgba(255, 255, 255, 0.5);
    }

    /* Second frame layer - further back, more perspective */
    .frame-layer-2 {
        top: -16px;
        left: 24px;
        right: -24px;
        bottom: 16px;
        opacity: 0.4;
        transform: perspective(1000px) rotateX(4deg) rotateY(-2deg);
        border-color: rgba(255, 255, 255, 0.3);
    }

    /* Main dashboard container with 3D perspective */
    .main-dashboard {
        position: relative;
        z-index: 3;
        display: inline-block;
        perspective: 1000px;
        transform-style: preserve-3d;
    }

    /* Banner image styling with 3D transforms */
    .banner-img {
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        max-width: 100%;
        height: auto;
        transform-style: preserve-3d;
    }

    /* 3D Flip animation instead of fade */
    .banner-img.flip-out {
        transform: perspective(1000px) rotateY(-90deg);
        opacity: 0.3;
    }

    .banner-img.flip-in {
        transform: perspective(1000px) rotateY(90deg);
        opacity: 0.3;
    }

    .banner-img.flip-complete {
        transform: perspective(1000px) rotateY(0deg);
        opacity: 1;
    }

    /* Module tabs styling - smaller and more compact */
    .module-tab {
        transition: background-color 0.3s, color 0.3s, transform 0.3s;
        border: 1.5px solid #0d6efd;
        background: transparent;
        color: #0d6efd;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 13px;
        white-space: nowrap;
    }

    .module-tab:hover {
        background-color: rgba(13, 110, 253, 0.1);
        transform: translateY(-1px);
    }

    .module-tab.active-tab {
        background-color: #0d6efd;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .frame-layer-1 {
            top: -6px;
            left: 8px;
            right: -8px;
            bottom: 6px;
            transform: perspective(800px) rotateX(1.5deg) rotateY(-0.5deg);
        }

        .frame-layer-2 {
            top: -12px;
            left: 16px;
            right: -16px;
            bottom: 12px;
            transform: perspective(800px) rotateX(3deg) rotateY(-1deg);
        }

        .module-tab {
            padding: 5px 12px;
            font-size: 12px;
        }
    }

    @media (max-width: 576px) {
        .frame-layer-1 {
            top: -4px;
            left: 6px;
            right: -6px;
            bottom: 4px;
            transform: perspective(600px) rotateX(1deg) rotateY(-0.3deg);
        }

        .frame-layer-2 {
            top: -8px;
            left: 12px;
            right: -12px;
            bottom: 8px;
            transform: perspective(600px) rotateX(2deg) rotateY(-0.6deg);
        }

        .module-tab {
            padding: 4px 10px;
            font-size: 11px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bannerImage = document.getElementById('banner-image');
        const buttons = document.querySelectorAll('.module-tab');

        buttons.forEach(button => {
            button.addEventListener('click', () => {
                if(button.classList.contains('active-tab')) return;

                const newSrc = button.getAttribute('data-img');

                // 3D Flip animation sequence
                bannerImage.classList.add('flip-out');

                setTimeout(() => {
                    bannerImage.src = newSrc;
                    bannerImage.classList.remove('flip-out');
                    bannerImage.classList.add('flip-in');
                }, 400);

                setTimeout(() => {
                    bannerImage.classList.remove('flip-in');
                    bannerImage.classList.add('flip-complete');
                }, 450);

                setTimeout(() => {
                    bannerImage.classList.remove('flip-complete');
                }, 850);

                // Update active button styling
                buttons.forEach(btn => btn.classList.remove('active-tab'));
                button.classList.add('active-tab');
            });
        });
    });
</script>