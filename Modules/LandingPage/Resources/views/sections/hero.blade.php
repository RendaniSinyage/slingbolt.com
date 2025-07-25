<!-- [ Banner ] start -->
@if ($settings['home_status'] == 'on')
    <section class="main-banner bg-primary" id="home">
        <div class="container-offset">
            <div class="row gy-3 g-0 align-items-center">
                <div class="col-xxl-4 col-md-6">
                    <span class="badge py-2 px-3 bg-white text-dark rounded-pill fw-bold mb-3">
                        {{ $settings['home_offer_text'] }}
                    </span>
                    <h1 class="mb-3">
                        {{ $settings['home_heading'] }}
                    </h1>
                    <h6 class="mb-0">{{ $settings['home_description'] }}</h6>
                    <div class="d-flex gap-3 mt-4 banner-btn">
                        @if ($settings['home_live_demo_link'])
                            <a href="{{ $settings['home_live_demo_link'] }}" class="btn btn-outline-dark">
                                {{ __('Live Demo') }}
                            </a>
                        @endif
                        @if ($settings['home_buy_now_link'])
                            <a href="{{ $settings['home_buy_now_link'] }}"
                                class="btn btn-outline-dark">{{ __('Buy Now') }} </a>
                        @endif
                    </div>
                </div>
                <div class="col-xxl-8 col-md-6">
                    @if(Storage::exists('/uploads/landing_page_image/'.$settings['home_banner']))
                    <div class="{{ $settings['home_banner'] ? 'dash-preview' : '' }}">
                        <img class="img-fluid preview-img" src="{{ $logo . '/' . $settings['home_banner'] }}"
                            alt="">
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
<!-- [ Banner ] end -->