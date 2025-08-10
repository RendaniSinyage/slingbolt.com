@if ($settings['home_status'] == 'on')
<section class="hero-section pitch-redesign" id="home">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7 text-center">
                @if (!empty($settings['home_offer_text']))
                <div class="hero-offer">
                    {!! $settings['home_offer_text'] !!}
                </div>
                @endif

                <h1 class="hero-heading">
                    {!! $settings['home_heading'] ?? 'Default Heading' !!}
                </h1>

                <p class="hero-description">
                    {!! $settings['home_description'] ?? 'Default Description' !!}
                </p>

                <div class="hero-buttons">
                    @if (!empty($settings['home_buy_now_link']))
                        <a href="{{ $settings['home_buy_now_link'] }}" class="btn btn-primary btn-lg">
                            {{ __('Get Started') }}
                        </a>
                    @endif
                    @if (!empty($settings['home_live_demo_link']))
                        <a href="{{ $settings['home_live_demo_link'] }}" class="btn btn-secondary btn-lg">
                            {{ __('Live Demo') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="hero-image-wrapper">
                     @php
                        $default_banner = $logo . '/' . ($settings['home_banner'] ?? 'default-banner.png');
                    @endphp
                    <img src="{{ $default_banner }}" class="hero-image" alt="Banner Image" />
                </div>
            </div>
        </div>
    </div>
</section>
@endif