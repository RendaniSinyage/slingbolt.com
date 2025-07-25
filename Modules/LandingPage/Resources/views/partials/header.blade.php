<!-- [ Header ] start -->
<header class="main-header position-relative z-10">
    @if ($settings['topbar_status'] == 'on')
        <div class="announcement bg-dark text-center py-2 small">
            <p class="mb-0 text-white">{!! $settings['topbar_notification_msg'] !!}</p>
        </div>
    @endif

    @if ($settings['menubar_status'] == 'on')
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-3 shadow-sm">
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
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                        <!-- Static style menu (can be adjusted to match your settings dynamically) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown">
                                Products
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">HR Manager</a></li>
                                <li><a class="dropdown-item" href="#">CRM</a></li>
                                <li><a class="dropdown-item" href="#">Accounting</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="extensionsDropdown" role="button" data-bs-toggle="dropdown">
                                Extensions
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Project Management</a></li>
                                <li><a class="dropdown-item" href="#">Asset Manager</a></li>
                                <li><a class="dropdown-item" href="#">Document Manager</a></li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#pricing">Pricing</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#blog">Blog</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="helpDropdown" role="button" data-bs-toggle="dropdown">
                                Help
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Support</a></li>
                                <li><a class="dropdown-item" href="#">Docs</a></li>
                            </ul>
                        </li>
                    </ul>

                    <!-- Sign In button -->
                    <div class="d-flex">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-pill px-4">
                            {{ __('Sign In') }}
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    @endif
</header>
<!-- [ Header ] end -->
