<!-- [ features ] start -->
@if ($settings['feature_status'] == 'on')
    <section class="features-section section-gap bg-dark" id="features">
        <div class="container">
            <div class="row gy-3">
                <div class="col-xxl-4">
                    <span class="d-block mb-2 text-uppercase">{{ $settings['feature_title'] }}</span>
                    <div class="title mb-4">
                        <h2><b class="fw-bold">{!! $settings['feature_heading'] !!}</b></h2>
                    </div>
                    <p class="mb-3">{!! $settings['feature_description'] !!}</p>
                    @if ($settings['feature_buy_now_link'])
                        <a href="{{ $settings['feature_buy_now_link'] }}"
                            class="btn btn-primary rounded-pill d-inline-flex align-items-center">{{ __('Buy Now') }}
                            <i data-feather="lock" class="ms-2"></i></a>
                    @endif
                </div>
                <div class="col-xxl-8">
                    <div class="row">
                        @if (is_array(json_decode($settings['feature_of_features'], true)) ||
                                is_object(json_decode($settings['feature_of_features'], true)))
                            @foreach (json_decode($settings['feature_of_features'], true) as $key => $value)
                                <div class="col-lg-4 col-sm-6 d-flex">
                                    <div class="card {{ $key == 0 ? 'bg-primary' : '' }}">
                                        <div class="card-body">
                                            <span class="theme-avtar avtar avtar-xl mb-4">
                                                <img src="{{ $logo . '/' . $value['feature_logo'] }}" alt="">
                                            </span>
                                            <h3 class="mb-3 {{ $key == 0 ? '' : 'text-white' }}">
                                                {!! $value['feature_heading'] !!}</h3>
                                            <p class=" f-w-600 mb-0 {{ $key == 0 ? 'text-body' : '' }}">
                                                {!! $value['feature_description'] !!}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="mt-5">
                    <div class="title text-center mb-4">
                        <span class="d-block mb-2 text-uppercase">{{ $settings['feature_title'] }}</span>
                        <h2 class="mb-4">{!! $settings['highlight_feature_heading'] !!}</h2>
                        <p>{!! $settings['highlight_feature_description'] !!}</p>
                    </div>
                    @if(Storage::exists('/uploads/landing_page_image/'.$settings['highlight_feature_image']))
                    <div class="features-preview">
                        <img class="img-fluid m-auto d-block"
                            src="{{ $logo . '/' . $settings['highlight_feature_image'] }}" alt="">
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    
    <!-- [ element ] start -->
    <section class="element-section  section-gap ">
        <div class="container">
            @if (is_array(json_decode($settings['other_features'], true)) ||
                    is_object(json_decode($settings['other_features'], true)))
                @foreach (json_decode($settings['other_features'], true) as $key => $value)
                    @if ($key % 2 == 0)
                        <div class="row align-items-center justify-content-center mb-4">
                            <div class="col-lg-4 col-md-6">
                                <div class="title mb-4">
                                    <span class="d-block fw-bold mb-2 text-uppercase">{{ __('Features') }}</span>
                                    <h2>
                                        {!! $value['other_features_heading'] !!}
                                    </h2>
                                </div>
                                <p class="mb-3">{!! $value['other_featured_description'] !!}</p>
                                <a href="{{ $value['other_feature_buy_now_link'] }}"
                                    class="btn btn-primary rounded-pill d-inline-flex align-items-center">{{ __('Buy Now ') }}
                                    <i data-feather="lock" class="ms-2"></i></a>
                            </div>
                            <div class="col-lg-7 col-md-6 res-img">
                                @if(Storage::exists('/uploads/landing_page_image/'.$value['other_features_image']))
                                <div class="img-wrapper">
                                    <img src="{{ $logo . '/' . $value['other_features_image'] }}" alt=""
                                        class="img-fluid header-img">
                                </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="row align-items-center justify-content-center mb-4">
                            <div class="col-lg-7 col-md-6">
                                <div class="img-wrapper">
                                    <img src="{{ $logo . '/' . $value['other_features_image'] }}" alt=""
                                        class="img-fluid header-img">
                                </div>
                            </div>
                            <div class="col-lg-4  col-md-6">
                                <div class="title mb-4">
                                    <span class="d-block fw-bold mb-2 text-uppercase">{{ __('Features') }}</span>
                                    <h2>
                                        {!! $value['other_features_heading'] !!}
                                    </h2>
                                </div>
                                <p class="mb-3">{!! $value['other_featured_description'] !!}</p>
                                <a href="{{ $value['other_feature_buy_now_link'] }}"
                                    class="btn btn-primary rounded-pill d-inline-flex align-items-center">{{ __('Buy Now ') }}
                                    <i data-feather="lock" class="ms-2"></i></a>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </section>
    <!-- [ element ] end -->
@endif
<!-- [ features ] end -->