{{-- Toast Notification --}}
<div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
    <div id="liveToast" class="toast text-white  fade" role="alert" aria-live="assertive"
        aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body"> </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
</div>

{{-- Footer --}}
<footer class="site-footer bg-gray-100">
    <div class="container">
        <div class="footer-row">
            <div class="ftr-col cmp-detail">
                <div class="footer-logo mb-3">
                    <a href="#">
                        <img src="{{ $logo . '/' . $settings['site_logo'] }}" alt="logo">
                    </a>
                </div>
                <p>
                    {!! $settings['site_description'] !!}
                </p>
            </div>
            <div class="ftr-col">
                <ul class="list-unstyled">
                    @if (is_array(json_decode($settings['menubar_page'])) || is_object(json_decode($settings['menubar_page'])))
                        @foreach (json_decode($settings['menubar_page']) as $key => $value)
                            @if ($value->footer == 'on' && $value->header == 'off' && $value->template_name == 'page_content')
                                <li><a
                                        href="{{ route('custom.page', $value->page_slug) }}">{!! $value->menubar_page_name !!}</a>
                                </li>
                            @endif
                            @if ($value->footer == 'on' && $value->header == 'on' && $value->template_name == 'page_content')
                                <li><a
                                        href="{{ route('custom.page', $value->page_slug) }}">{!! $value->menubar_page_name !!}</a>
                                </li>
                            @endif
                            @if ($value->footer == 'on' && $value->header == 'on' && $value->template_name == 'page_url')
                                <li><a href="{{ $value->page_url }}">{!! $value->menubar_page_name !!}</a></li>
                            @endif
                            @if ($value->footer == 'on' && $value->header == 'off' && $value->template_name == 'page_url')
                                <li><a href="{{ $value->page_url }}">{!! $value->menubar_page_name !!}</a></li>
                            @endif
                        @endforeach
                    @endif
                </ul>
            </div>

            @if ($settings['joinus_status'] == 'on')
                <div class="ftr-col ftr-subscribe">
                    <h2>{!! $settings['joinus_heading'] !!}</h2>
                    <p>{!! $settings['joinus_description'] !!}</p>
                    <form method="post" action="{{ route('join_us_store') }}">
                        @csrf
                        <div class="input-wrapper border border-dark">
                            <input type="email" name="email" placeholder="Type your email address...">
                            <button type="submit" class="btn btn-dark rounded-pill">{{ __('Join Us') }}!</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
    <div class="border-top border-dark text-center p-2">
        <p class="mb-0"> &copy;
            {{ date('Y') }}
            {{ Utility::getValByName('footer_text') ? Utility::getValByName('footer_text') : config('app.name', 'ERPGo') }}
        </p>
    </div>
</footer>
<!-- [ Footer ] end -->

{{-- Scripts --}}
<script src="{{ asset('assets/js/plugins/popper.min.js')}}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.min.js')}}"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js')}}"></script>

<script>
    // Start [ Menu hide/show on scroll ]
    let ost = 0;
    document.addEventListener("scroll", function() {
        let cOst = document.documentElement.scrollTop;
        if (cOst == 0) {
            document.querySelector(".navbar").classList.add("top-nav-collapse");
        } else if (cOst > ost) {
            document.querySelector(".navbar").classList.add("top-nav-collapse");
            document.querySelector(".navbar").classList.remove("default");
        } else {
            document.querySelector(".navbar").classList.add("default");
            document
                .querySelector(".navbar")
                .classList.remove("top-nav-collapse");
        }
        ost = cOst;
    });
    // End [ Menu hide/show on scroll ]

    var scrollSpy = new bootstrap.ScrollSpy(document.body, {
        target: "#navbar-example",
    });
    feather.replace();
</script>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/custom.js') }}"></script>

@if ($message = Session::get('success'))
<script>
    show_toastr('success', '{!! $message !!}');
</script>
@endif
@if ($message = Session::get('error'))
<script>
    show_toastr('error', '{!! $message !!}');
</script>
@endif

@if ($get_cookie['enable_cookie'] == 'on')
    @include('layouts.cookie_consent')
@endif