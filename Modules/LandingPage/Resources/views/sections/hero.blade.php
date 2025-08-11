@if ($settings['home_status'] == 'on')
<section id="home" class="hero-section d-flex flex-column align-items-center justify-content-center text-center py-5" style="min-height: 90vh; background: transparent;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                @if (!empty($settings['home_offer_text']))
                <span class="badge rounded-pill px-3 py-2 mb-4 fw-bold small" style="background-color: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.3);">
                    {!! $settings['home_offer_text'] !!}
                </span>
                @endif

                <h1 class="fw-bold display-4 mb-4 text-white">
                    {!! $settings['home_heading'] ?? 'Supercharge Your Growing Business from Your WordPress Dashboard' !!}
                </h1>

                <p class="lead text-white mb-4">
                    {!! $settings['home_description'] ?? 'WP ERP optimizes your small to medium businesses with powerful HR, CRM, and Accounting tools.' !!}
                </p>

                <div class="d-flex justify-content-center gap-3 mb-5">
                    @if (!empty($settings['home_live_demo_link']))
                    <a href="{{ $settings['home_live_demo_link'] }}" class="btn btn-outline-primary rounded-pill px-4">
                        {{ __('Live Demo') }}
                    </a>
                    @endif

                    @if (!empty($settings['home_buy_now_link']))
                    <a href="{{ $settings['home_buy_now_link'] }}" class="btn rounded-pill px-4" style="background-color: white; color: var(--transparent); border: 1px solid white;">
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

    /* Frame layers showing only at top with perspective - like tabs */
    .frame-layer {
        position: absolute;
        background: transparent;
        border: 2px solid rgba(255, 255, 255, 0.4);
        border-bottom: none;
        border-radius: 15px 15px 0 0;
        pointer-events: none;
        z-index: 2;
        height: 25px;
        transform-style: preserve-3d;
    }

    /* First layer - smaller than image */
    .frame-layer-1 {
        top: -20px;
        left: 10px;
        right: 10px;
        opacity: 0.8;
        border-color: rgba(255, 255, 255, 0.6);
        transform: perspective(1200px) rotateX(8deg) rotateY(-1deg);
    }

    /* Second layer - even smaller */
    .frame-layer-2 {
        top: -35px;
        left: 20px;
        right: 20px;
        opacity: 0.5;
        height: 20px;
        border-color: rgba(255, 255, 255, 0.4);
        transform: perspective(1200px) rotateX(12deg) rotateY(-1.5deg);
    }

    /* Main dashboard container with 3D perspective */
    .main-dashboard {
        position: relative;
        z-index: 3;
        display: inline-block;
        perspective: 1000px;
        transform-style: preserve-3d;
    }

    /* Banner image styling - no shadows, same border effect */
    .banner-img {
        border: 2px solid rgba(255, 255, 255, 0.6);
        border-radius: 15px;
        box-shadow: none;
        transition: none;
        max-width: 100%;
        height: auto;
    }

    /* Module tabs styling - white border and text when not active */
    .module-tab {
        transition: background-color 0.3s, color 0.3s, transform 0.3s;
        border: 1.5px solid rgba(255, 255, 255, 0.8);
        background: transparent;
        color: rgba(255, 255, 255, 0.9);
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 13px;
        white-space: nowrap;
    }

    .module-tab:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
        border-color: white;
        transform: translateY(-1px);
    }

    .module-tab.active-tab {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .frame-layer-1 {
            top: -15px;
            left: 8px;
            right: 8px;
            height: 20px;
            transform: perspective(1000px) rotateX(6deg) rotateY(-0.8deg);
        }

        .frame-layer-2 {
            top: -25px;
            left: 15px;
            right: 15px;
            height: 15px;
            transform: perspective(1000px) rotateX(9deg) rotateY(-1.2deg);
        }

        .module-tab {
            padding: 5px 12px;
            font-size: 12px;
        }
    }

    @media (max-width: 576px) {
        .frame-layer-1 {
            top: -12px;
            left: 6px;
            right: 6px;
            height: 18px;
            transform: perspective(800px) rotateX(5deg) rotateY(-0.6deg);
        }

        .frame-layer-2 {
            top: -20px;
            left: 12px;
            right: 12px;
            height: 14px;
            transform: perspective(800px) rotateX(7deg) rotateY(-0.9deg);
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

                // Instant change - no animation
                bannerImage.src = newSrc;

                // Update active button styling
                buttons.forEach(btn => btn.classList.remove('active-tab'));
                button.classList.add('active-tab');
            });
        });
    });
</script>