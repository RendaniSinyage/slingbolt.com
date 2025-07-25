<!-- [ discover ] start -->
@if ($settings['discover_status'] == 'on')
    <section class="bg-dark section-gap">
        <div class="container">
            <div class="row mb-2 justify-content-center">
                <div class="col-xxl-6">
                    <div class="title text-center mb-4">
                        <span class="d-block mb-2 text-uppercase">{{ __('DISCOVER') }}</span>
                        <h2 class="mb-4">{!! $settings['discover_heading'] !!}</h2>
                        <p>{!! $settings['discover_description'] !!}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                @if (is_array(json_decode($settings['discover_of_features'], true)) ||
                        is_object(json_decode($settings['discover_of_features'], true)))
                    @foreach (json_decode($settings['discover_of_features'], true) as $key => $value)
                        <div class="col-xxl-3 col-sm-6 col-lg-4 ">
                            <div class="card   border {{ $key == 1 ? 'bg-primary' : 'bg-transparent' }}">
                                <div class="card-body text-center">
                                    <span class="theme-avtar avtar avtar-xl mx-auto mb-4">
                                        <img src="{{ $logo . '/' . $value['discover_logo'] }}" alt="">
                                    </span>
                                    <h3 class="mb-3 {{ $key == 1 ? '' : 'text-white' }} ">{!! $value['discover_heading'] !!}
                                    </h3>
                                    <p class="{{ $key == 1 ? 'text-body' : '' }}">
                                        {!! $value['discover_description'] !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="d-flex flex-column justify-content-center flex-sm-row gap-3 mt-3">
            </div>
        </div>
    </section>
@endif
<!-- [ discover ] end -->

<!-- [ Screenshots ] start -->
@if ($settings['screenshots_status'] == 'on')
    <section class="screenshots section-gap">
        <div class="container">
            <div class="row mb-2 justify-content-center">
                <div class="col-xxl-6">
                    <div class="title text-center mb-4">
                        <span class="d-block mb-2 fw-bold text-uppercase">{{ __('SCREENSHOTS') }}</span>
                        <h2 class="mb-4">{!! $settings['screenshots_heading'] !!}</h2>
                        <p>{!! $settings['screenshots_description'] !!}</p>
                    </div>
                </div>
            </div>
            <div class="row gy-4 gx-4">
                @if (is_array(json_decode($settings['screenshots'], true)) || is_object(json_decode($settings['screenshots'], true)))
                    @foreach (json_decode($settings['screenshots'], true) as $value)
                        <div class="col-md-4 col-sm-6">
                            <div class="screenshot-card">
                                <div class="img-wrapper">
                                    <img src="{{ $logo . '/' . $value['screenshots'] }}"
                                        class="img-fluid header-img mb-4 shadow-sm" alt="">
                                </div>
                                <h5 class="mb-0">{!! $value['screenshots_heading'] !!}</h5>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>
@endif
<!-- [ Screenshots ] end -->