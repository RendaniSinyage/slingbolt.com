<!-- [ testimonial ] start -->
@if ($settings['testimonials_status'] == 'on')
    <section class="testimonial section-gap">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4">
                    <div class="title mb-4">
                        <span class="d-block mb-2 fw-bold text-uppercase">{{ __('TESTIMONIALS') }}</span>
                        <h2 class="mb-2">{!! $settings['testimonials_heading'] !!}</h2>
                        <p>{!! $settings['testimonials_description'] !!}</p>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="row justify-content-center gy-3">
                        @if (is_array(json_decode($settings['testimonials'])) || is_object(json_decode($settings['testimonials'])))
                            @foreach (json_decode($settings['testimonials']) as $key => $value)
                                <div class="col-xxl-4 col-sm-6 col-lg-6 col-md-4">
                                    <div class="card bg-dark shadow-none mb-0">
                                        <div class="card-body p-3">
                                            <div class="d-flex mb-3 align-items-center justify-content-between">
                                                <span class="theme-avtar avtar avtar-sm bg-light-dark rounded-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="36"
                                                        height="23" viewBox="0 0 36 23" fill="none">
                                                        <path
                                                            d="M12.4728 22.6171H0.770508L10.6797 0.15625H18.2296L12.4728 22.6171ZM29.46 22.6171H17.7577L27.6669 0.15625H35.2168L29.46 22.6171Z"
                                                            fill="white" />
                                                    </svg>
                                                </span>
                                                <span>
                                                    @for ($i = 1; $i <= (int) $value->testimonials_star; $i++)
                                                        <i data-feather="star"></i>
                                                    @endfor
                                                </span>
                                            </div>
                                            <h3 class="text-white">{{ $value->testimonials_title }}</h3>
                                            <p class="hljs-comment">
                                                {{ $value->testimonials_description }}
                                            </p>
                                            <div class="d-flex  align-items-center ">
                                                <img src="{{ $logo . '/' . $value->testimonials_user_avtar }}"
                                                    class="wid-40 rounded-circle me-3" alt="">
                                                <span>
                                                    <b class="fw-bold d-block">{{ $value->testimonials_user }}</b>
                                                    {{ $value->testimonials_designation }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <p class="mb-0 f-w-600">
                        {!! $settings['testimonials_long_description'] !!}
                    </p>
                </div>
            </div>
        </div>
    </section>
@endif
<!-- [ testimonial ] end -->