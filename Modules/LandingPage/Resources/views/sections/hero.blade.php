<!-- [ Banner ] start -->
@if ($settings['home_status'] == 'on')
    <section class="main-banner bg-primary" id="home" style="background: linear-gradient(135deg, var(--color-customColor) 0%, #764ba2 50%, #f093fb 100%); padding: 120px 0 80px;">
        <div class="container-offset">
            <div class="row gy-3 g-0 align-items-center">
                <div class="col-xxl-6 col-md-12 text-center">
                    @if($settings['home_offer_text'])
                    <span class="badge py-2 px-3 bg-white text-dark rounded-pill fw-bold mb-3">
                        {{ $settings['home_offer_text'] }}
                    </span>
                    @endif
                    
                    <h1 class="mb-4 text-white" style="font-size: 4rem; font-weight: 700; line-height: 1.1;">
                        {{ $settings['home_heading'] }}
                    </h1>
                    
                    <p class="mb-4 text-white lead" style="font-size: 1.25rem; opacity: 0.9; max-width: 600px; margin-left: auto; margin-right: auto;">
                        {{ $settings['home_description'] }}
                    </p>
                    
                    <div class="d-flex gap-3 mt-4 banner-btn justify-content-center">
                        @if ($settings['home_live_demo_link'])
                            <a href="{{ $settings['home_live_demo_link'] }}" class="btn btn-light btn-lg rounded-pill px-4 py-3" style="background: rgba(255,255,255,0.95); color: #333; font-weight: 600;">
                                {{ __('Get Started') }}
                                <i data-feather="arrow-right" class="ms-2"></i>
                            </a>
                        @endif
                        @if ($settings['home_buy_now_link'])
                            <a href="{{ $settings['home_buy_now_link'] }}" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3" style="border: 2px solid rgba(255,255,255,0.5); color: white; font-weight: 600;">
                                {{ __('Demo') }}
                                <i data-feather="play" class="ms-2"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Module tabs section below hero -->
    @if(Storage::exists('/uploads/landing_page_image/'.$settings['home_banner']))
    <section class="bg-primary" style="background: var(--color-customColor) !important; padding: 60px 0;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="module-tab-wrapper text-center">
                        <!-- Dashboard Image -->
                        <div class="dashboard-preview mb-4">
                            <div class="module-images">
                                <img class="img-fluid module-image active" 
                                     src="{{ $logo . '/' . $settings['home_banner'] }}"
                                     data-module="hrm"
                                     alt="HRM Dashboard"
                                     style="border-radius: 15px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); max-width: 90%;">
                                
                                <img class="img-fluid module-image" 
                                     src="{{ $logo . '/crm-dashboard.png' }}"
                                     data-module="crm"
                                     alt="CRM Dashboard"
                                     style="display: none; border-radius: 15px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); max-width: 90%;">
                                
                                <img class="img-fluid module-image" 
                                     src="{{ $logo . '/accounting-dashboard.png' }}"
                                     data-module="accounting"
                                     alt="Accounting Dashboard"
                                     style="display: none; border-radius: 15px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); max-width: 90%;">
                            </div>
                        </div>
                        
                        <!-- Module Tabs -->
                        <div class="module-tabs-nav">
                            <div class="d-flex justify-content-center gap-0" style="background: rgba(255,255,255,0.2); border-radius: 50px; padding: 8px; display: inline-flex;">
                                <button class="module-tab-btn active" data-module="hrm" 
                                        style="background: rgba(255,255,255,0.9); color: var(--color-customColor); border: none; padding: 12px 24px; border-radius: 25px; font-weight: 600; transition: all 0.3s ease;">
                                    HRM
                                </button>
                                <button class="module-tab-btn" data-module="crm" 
                                        style="background: transparent; color: rgba(255,255,255,0.8); border: none; padding: 12px 24px; border-radius: 25px; font-weight: 600; transition: all 0.3s ease;">
                                    CRM
                                </button>
                                <button class="module-tab-btn" data-module="accounting" 
                                        style="background: transparent; color: rgba(255,255,255,0.8); border: none; padding: 12px 24px; border-radius: 25px; font-weight: 600; transition: all 0.3s ease;">
                                    Accounting
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Statistics section -->
    <section class="stats-section bg-primary" style="background: linear-gradient(135deg, var(--color-customColor) 0%, #764ba2 100%); padding: 60px 0;">
        <div class="container">
            <div class="row text-center text-white">
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <h3 class="fw-bold" style="font-size: 2.5rem;">10,000+</h3>
                    <p class="mb-0" style="opacity: 0.8;">Business</p>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <h3 class="fw-bold" style="font-size: 2.5rem;">160+</h3>
                    <p class="mb-0" style="opacity: 0.8;">Countries</p>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <h3 class="fw-bold" style="font-size: 2.5rem;">550K+</h3>
                    <p class="mb-0" style="opacity: 0.8;">Total Downloads</p>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <h3 class="fw-bold" style="font-size: 2.5rem;">20+</h3>
                    <p class="mb-0" style="opacity: 0.8;">Language Supports</p>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <h3 class="fw-bold" style="font-size: 2.5rem;">93%</h3>
                    <p class="mb-0" style="opacity: 0.8;">Customer Satisfactions</p>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <div class="d-flex justify-content-center gap-1 mb-2 flex-wrap">
                        <span class="badge bg-light text-dark px-2">HRM</span>
                        <span class="badge bg-light text-dark px-2">CRM</span>
                        <span class="badge bg-light text-dark px-2">Accounting</span>
                    </div>
                    <p class="mb-0" style="opacity: 0.8;">Core Modules</p>
                </div>
            </div>
        </div>
    </section>

    <style>
    .module-tab-btn:hover,
    .module-tab-btn.active {
        background: rgba(255,255,255,0.9) !important;
        color: var(--color-customColor) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .banner-btn a:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }

    @media (max-width: 768px) {
        .main-banner h1 {
            font-size: 2.5rem !important;
        }
        
        .module-tabs-nav .d-flex {
            flex-direction: column !important;
            gap: 8px !important;
        }
        
        .module-tab-btn {
            width: 100% !important;
        }
        
        .stats-section h3 {
            font-size: 2rem !important;
        }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.module-tab-btn');
        const moduleImages = document.querySelectorAll('.module-image');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetModule = this.getAttribute('data-module');
                
                // Update active tab styling
                tabButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.style.background = 'transparent';
                    btn.style.color = 'rgba(255,255,255,0.8)';
                });
                
                this.classList.add('active');
                this.style.background = 'rgba(255,255,255,0.9)';
                this.style.color = 'var(--color-customColor)';
                
                // Show corresponding image
                moduleImages.forEach(img => {
                    if (img.getAttribute('data-module') === targetModule) {
                        img.style.display = 'block';
                        img.classList.add('active');
                    } else {
                        img.style.display = 'none';
                        img.classList.remove('active');
                    }
                });
            });
        });
    });
    </script>
@endif
<!-- [ Banner ] end -->