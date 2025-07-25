<!-- [ Header ] start -->
<header class="main-header">
    @if ($settings['topbar_status'] == 'on')
        <div class="announcement bg-dark text-center p-2">
            <p class="mb-0">{!! $settings['topbar_notification_msg'] !!}</p>
        </div>
    @endif
    
    @if ($settings['menubar_status'] == 'on')
        <div class="container">
            <nav class="navbar navbar-expand-md  default top-nav-collapse">
                <div class="header-left">
                    <a class="navbar-brand bg-transparent" href="/">
                        <img src="{{ $logo .'/'. $settings['site_logo'] }}" alt="logo">
                    </a>
                </div>
                <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" href="#home">{{ $settings['home_title'] }}</a>
                        </li>
                        @if ($settings['feature_status'] == 'on')
                        <li class="nav-item">
                            <a class="nav-link" href="#features">{{ $settings['feature_title'] }}</a>
                        </li>
                        @endif
                        @if ($settings['plan_status'] == 'on')
                        <li class="nav-item">
                            <a class="nav-link" href="#plan">{{ $settings['plan_title'] }}</a>
                        </li>
                        @endif
                        @if ($settings['faq_status'] == 'on')
                        <li class="nav-item">
                            <a class="nav-link" href="#faq">{{ $settings['faq_title'] }}</a>
                        </li>
                        @endif

                        @if (is_array(json_decode($settings['menubar_page'])) || is_object(json_decode($settings['menubar_page'])))
                            @foreach (json_decode($settings['menubar_page']) as $key => $value)
                                @if ($value->header == 'on' && $value->template_name == 'page_content')
                                    <li class="nav-item">
                                        <a class="nav-link"
                                            href="{{ route('custom.page', $value->page_slug) }}">{{ $value->menubar_page_name }}</a>
                                    </li>
                                @elseif($value->header == 'on')
                                    <li class="nav-item">
                                        <a class="nav-link"
                                            href="{{ $value->page_url }}">{{ $value->menubar_page_name }}</a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    </ul>
                    <button class="navbar-toggler bg-primary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
                <div class="ms-auto d-flex justify-content-end gap-2">
                    <a href="{{ route('login') }}" class="btn btn-outline-dark rounded">
                        <span class="hide-mob me-2">
                            {{ __('Login') }}
                        </span>
                        <i data-feather="log-in"></i>
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-dark rounded">
                        <span class="hide-mob me-2">
                            {{ __('Register') }}
                        </span>
                        <i data-feather="user-check"></i>
                    </a>
                    <button class="navbar-toggler " type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </nav>
        </div>
    @endif
</header>
<!-- [ Header ] End -->