<!-- [ Hero Section ] start -->
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

                <!-- Dynamic Headings -->
                <h1 class="fw-bold display-4 mb-2">
                    {!! $settings['home_heading_line1'] ?? 'Supercharge' !!}
                </h1>

                <h1 class="fw-bold display-5 mb-2">
                    {!! $settings['home_heading_line2'] ?? 'Your Growing Business from Your' !!}
                </h1>

                <h1 class="fw-bold display-5 mb-4">
                    {!! $settings['home_heading_line3'] ?? 'WordPress Dashboard' !!}
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

        <!-- Banner Image with Overlapping Effect and Module Tabs Below -->
        @php
            $banner_hrm = isset($settings['home_banner_hrm']) ? $logo . '/' . $settings['home_banner_hrm'] : ($logo . '/' . ($settings['home_banner'] ?? 'default-banner.png'));
            $banner_crm = isset($settings['home_banner_crm']) ? $logo . '/' . $settings['home_banner_crm'] : ($logo . '/' . ($settings['home_banner'] ?? 'default-banner.png'));
            $banner_accounting = isset($settings['home_banner_accounting']) ? $logo . '/' . $settings['home_banner_accounting'] : ($logo . '/' . ($settings['home_banner'] ?? 'default-banner.png'));
            $default_banner = $logo . '/' . ($settings['home_banner'] ?? 'default-banner.png');
        @endphp

        <div class="row justify-content-center mt-3">
            <div class="col-lg-10 position-relative text-center">

                <!-- Overlapping Layers Above Banner -->
                <div class="position-absolute w-100 d-flex justify-content-center" style="top: -40px; z-index: 0;">
                    <div class="overlap-tab bg-white shadow rounded" style="width: 92%; height: 20px; transform: translateY(-15px); opacity: 0.4;"></div>
                    <div class="overlap-tab bg-white shadow rounded ms-2" style="width: 88%; height: 20px; transform: translateY(-8px); opacity: 0.3;"></div>
                </div>

                <!-- Banner Image -->
                <div class="position-relative z-1">
                    <img id="banner-image" src="{{ $default_banner }}" class="img-fluid rounded shadow" alt="Banner Image">
                </div>

                <!-- Module Tabs Below Image -->
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button class="btn btn-outline-primary module-tab active" data-img="{{ $banner_hrm }}">HRM</button>
                    <button class="btn btn-outline-primary module-tab" data-img="{{ $banner_crm }}">CRM</button>
                    <button class="btn btn-outline-primary module-tab" data-img="{{ $banner_accounting }}">Accounting</button>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
<!-- [ Hero Section ] end -->
