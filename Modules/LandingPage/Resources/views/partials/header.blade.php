<!-- [ Header ] start -->
<header class="main-header position-relative z-10">
    @if ($settings['topbar_status'] == 'on')
        <div class="announcement bg-dark text-center py-2 small">
            <p class="mb-0 text-white">{!! $settings['topbar_notification_msg'] !!}</p>
        </div>
    @endif

    @if ($settings['menubar_status'] == 'on')
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-3">
            <div class="container d-flex align-items-center justify-content-between">
                <!-- Logo -->
                <a class="navbar-brand" href="/">
                    <img src="{{ $logo . '/' . $settings['site_logo'] }}" alt="logo" height="40">
                </a>

                <!-- Toggle button for mobile -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                    aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navbar items -->
                <div class="collapse navbar-collapse" id="mainNavbar">
                    @php
                        // Determine logo type and set appropriate text color
                        $logo_filename = $settings['site_logo'] ?? '';
                        $is_light_logo = str_contains(strtolower($logo_filename), 'light') ||
                                        str_contains(strtolower($logo_filename), 'white') ||
                                        str_contains(strtolower($logo_filename), 'inverse');
                        $nav_text_color = $is_light_logo ? 'text-white' : 'text-white';
                    @endphp

                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                        <!-- Features - only show if feature section is enabled -->
                        @if (isset($settings['feature_status']) && $settings['feature_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link {{ $nav_text_color }}" href="#features">Features</a>
                            </li>
                        @endif

                        <!-- Pricing - only show if plan section is enabled -->
                        @if (isset($settings['plan_status']) && $settings['plan_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link {{ $nav_text_color }}" href="#plan">Pricing</a>
                            </li>
                        @endif

                        <!-- Discover - only show if discover section is enabled -->
                        @if (isset($settings['discover_status']) && $settings['discover_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link {{ $nav_text_color }}" href="#discover">Discover</a>
                            </li>
                        @endif

                        <!-- Screenshots - only show if screenshots section is enabled -->
                        @if (isset($settings['screenshots_status']) && $settings['screenshots_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link {{ $nav_text_color }}" href="#screenshots">Screenshots</a>
                            </li>
                        @endif

                        <!-- FAQ - only show if FAQ section is enabled -->
                        @if (isset($settings['faq_status']) && $settings['faq_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link {{ $nav_text_color }}" href="#faq">FAQ</a>
                            </li>
                        @endif

                        <!-- Testimonials - only show if testimonials section is enabled -->
                        @if (isset($settings['testimonials_status']) && $settings['testimonials_status'] == 'on')
                            <li class="nav-item">
                                <a class="nav-link {{ $nav_text_color }}" href="#testimonials">Testimonials</a>
                            </li>
                        @endif
                    </ul>

                    <!-- Auth buttons -->
                    <div class="d-flex gap-2">
                        <a href="{{ route('login') }}" class="btn rounded-pill px-4" style="background-color: white; color: #333; border: 1px solid rgba(255,255,255,0.3);">
                            {{ __('Sign In') }}
                        </a>
                        <a href="{{ route('register') }}" class="btn rounded-pill px-4" style="background-color: white; color: #333; border: 1px solid rgba(255,255,255,0.3);">
                            {{ __('Get Started') }}
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    @endif
</header>
<!-- [ Header ] end -->