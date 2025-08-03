@php
    use App\Models\Utility;
    $setting = \App\Models\Utility::settings();
    $logo = \App\Models\Utility::get_file('uploads/logo');

    $company_logo = $setting['company_logo_dark'] ?? '';
    $company_logos = $setting['company_logo_light'] ?? '';
    $company_small_logo = $setting['company_small_logo'] ?? '';

    $emailTemplate = \App\Models\EmailTemplate::emailTemplateData();
    $lang = Auth::user()->lang;

    $userPlan = \App\Models\Plan::getPlan(\Auth::user()->show_dashboard());
@endphp

<!-- Always show logo area on larger screens -->
<div class="main-logo-area d-none d-lg-block">
    <div class="m-header main-logo">
        <a href="#" class="b-brand">
            @if ($setting['cust_darklayout'] && $setting['cust_darklayout'] == 'on')
                <img src="{{ $logo . '/' . (isset($company_logos) && !empty($company_logos) ? $company_logos : 'logo-dark.png') . '?' . time() }}"
                    alt="{{ config('app.name', 'ERPGo-SaaS') }}" class="logo logo-lg">
            @else
                <img src="{{ $logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-light.png') . '?' . time() }}"
                    alt="{{ config('app.name', 'ERPGo-SaaS') }}" class="logo logo-lg">
            @endif
        </a>
    </div>
</div>

<!-- Hamburger menu button - visible on all screens -->
<div class="hamburger-menu-wrapper">
    <button class="hamburger-btn" id="menu-toggle" type="button">
        <div class="hamburger hamburger--arrowturn">
            <div class="hamburger-box">
                <div class="hamburger-inner"></div>
            </div>
        </div>
    </button>
</div>

<!-- Sidebar overlay for mobile -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<!-- Navigation sidebar - hidden by default, shown when hamburger is clicked -->
@if (isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on')
    <nav class="dash-sidebar light-sidebar transprent-bg sidebar-collapsed" id="main-sidebar">
@else
    <nav class="dash-sidebar light-sidebar sidebar-collapsed" id="main-sidebar">
@endif
<div class="navbar-wrapper">
    <!-- Mobile logo (shown in sidebar on small screens) -->
    <div class="m-header main-logo d-lg-none">
        <a href="#" class="b-brand">
            @if ($setting['cust_darklayout'] && $setting['cust_darklayout'] == 'on')
                <img src="{{ $logo . '/' . (isset($company_logos) && !empty($company_logos) ? $company_logos : 'logo-dark.png') . '?' . time() }}"
                    alt="{{ config('app.name', 'ERPGo-SaaS') }}" class="logo logo-lg">
            @else
                <img src="{{ $logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-light.png') . '?' . time() }}"
                    alt="{{ config('app.name', 'ERPGo-SaaS') }}" class="logo logo-lg">
            @endif
        </a>
    </div>
    
    <div class="navbar-content">
        @if (\Auth::user()->type != 'client' && \Auth::user()->type != 'super admin')
            <ul class="dash-navbar">
                <!--------------------- Start Dashboard ----------------------------------->
                @if (Gate::check('show hrm dashboard') ||
                        Gate::check('show project dashboard') ||
                        Gate::check('show account dashboard') ||
                        Gate::check('show crm dashboard') ||
                        Gate::check('show pos dashboard'))
                    <li class="dash-item dash-hasmenu
                                {{ Request::segment(1) == null ||
                                Request::segment(1) == 'dashboard' ||
                                Request::segment(1) == 'account-dashboard' ||
                                Request::segment(1) == 'hrm-dashboard' ||
                                Request::segment(1) == 'crm-dashboard' ||
                                Request::segment(1) == 'project-dashboard' ||
                                Request::segment(1) == 'pos-dashboard' ||
                                Request::segment(1) == 'income report' ||
                                Request::segment(1) == 'report' ||
                                Request::segment(1) == 'reports-monthly-cashflow' ||
                                Request::segment(1) == 'reports-quarterly-cashflow' ||
                                Request::segment(1) == 'reports-payroll' ||
                                Request::segment(1) == 'reports-leave' ||
                                Request::segment(1) == 'reports-monthly-attendance' ||
                                Request::segment(1) == 'reports-lead' ||
                                Request::segment(1) == 'reports-deal' ||
                                Request::segment(1) == 'reports-warehouse' ||
                                Request::segment(1) == 'reports-daily-purchase' ||
                                Request::segment(1) == 'reports-monthly-purchase' ||
                                Request::segment(1) == 'reports-daily-pos' ||
                                Request::segment(1) == 'reports-monthly-pos' ||
                                Request::segment(1) == 'reports-pos-vs-purchase'
                                    ? 'active dash-trigger'
                                    : '' }}">
                        <a href="#!" class="dash-link ">
                            <span class="dash-micon">
                                <i class="ti ti-home"></i>
                            </span>
                            <span class="dash-mtext">{{ __('Dashboard') }}</span>
                            <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="dash-submenu">
                            
                            <!-- Overview Group -->
                            <li class="dash-item dash-hasmenu {{ Request::segment(1) == null || 
                                Request::segment(1) == 'account-dashboard' || 
                                Request::segment(1) == 'hrm-dashboard' || 
                                Request::segment(1) == 'crm-dashboard' || 
                                Request::segment(1) == 'project-dashboard' || 
                                Request::segment(1) == 'pos-dashboard' ? 'active dash-trigger' : '' }}">
                                <a class="dash-link" href="#">{{ __('Overview') }}<span
                                        class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                                <ul class="dash-submenu">
                                    @if ($userPlan->account == 1 && Gate::check('show account dashboard'))
                                        <li class="dash-item {{ Request::segment(1) == null || Request::segment(1) == 'account-dashboard' ? ' active' : '' }}">
                                            <a class="dash-link" href="{{ route('dashboard') }}">{{ __('Accounting') }}</a>
                                        </li>
                                    @endif
                                    
                                    @if ($userPlan->hrm == 1)
                                        @can('show hrm dashboard')
                                            <li class="dash-item {{ Request::segment(1) == 'hrm-dashboard' ? ' active' : '' }}">
                                                <a class="dash-link" href="{{ route('hrm.dashboard') }}">{{ __('HRM') }}</a>
                                            </li>
                                        @endcan
                                    @endif
                                    
                                    @if ($userPlan->crm == 1)
                                        @can('show crm dashboard')
                                            <li class="dash-item {{ Request::segment(1) == 'crm-dashboard' ? ' active' : '' }}">
                                                <a class="dash-link" href="{{ route('crm.dashboard') }}">{{ __('CRM') }}</a>
                                            </li>
                                        @endcan
                                    @endif
                                    
                                    @if ($userPlan->project == 1)
                                        @can('show project dashboard')
                                            <li class="dash-item {{ Request::route()->getName() == 'project.dashboard' ? ' active' : '' }}">
                                                <a class="dash-link" href="{{ route('project.dashboard') }}">{{ __('Project') }}</a>
                                            </li>
                                        @endcan
                                    @endif
                                    
                                    @if ($userPlan->pos == 1)
                                        @can('show pos dashboard')
                                            <li class="dash-item {{ Request::segment(1) == 'pos-dashboard' ? ' active' : '' }}">
                                                <a class="dash-link" href="{{ route('pos.dashboard') }}">{{ __('POS') }}</a>
                                            </li>
                                        @endcan
                                    @endif
                                </ul>
                            </li>

                            <!-- Reports Group -->
                            <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'report' ||
                                Request::segment(1) == 'reports-monthly-cashflow' ||
                                Request::segment(1) == 'reports-quarterly-cashflow' ||
                                Request::segment(1) == 'reports-payroll' ||
                                Request::segment(1) == 'reports-leave' ||
                                Request::segment(1) == 'reports-monthly-attendance' ||
                                Request::segment(1) == 'reports-lead' ||
                                Request::segment(1) == 'reports-deal' ||
                                Request::segment(1) == 'reports-warehouse' ||
                                Request::segment(1) == 'reports-daily-purchase' ||
                                Request::segment(1) == 'reports-monthly-purchase' ||
                                Request::segment(1) == 'reports-daily-pos' ||
                                Request::segment(1) == 'reports-monthly-pos' ||
                                Request::segment(1) == 'reports-pos-vs-purchase' ? 'active dash-trigger' : '' }}">
                                <a class="dash-link" href="#">{{ __('Reports') }}<span
                                        class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                                <ul class="dash-submenu">
                                    
                                    <!-- Accounting Reports -->
                                    @if ($userPlan->account == 1 && (Gate::check('income report') ||
                                            Gate::check('expense report') ||
                                            Gate::check('income vs expense report') ||
                                            Gate::check('tax report') ||
                                            Gate::check('loss & profit report') ||
                                            Gate::check('invoice report') ||
                                            Gate::check('bill report') ||
                                            Gate::check('stock report') ||
                                            Gate::check('manage transaction') ||
                                            Gate::check('statement report')))
                                        <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'report' || 
                                            Request::segment(1) == 'reports-monthly-cashflow' || 
                                            Request::segment(1) == 'reports-quarterly-cashflow' ? 'active dash-trigger' : '' }}">
                                            <a class="dash-link" href="#">{{ __('Accounting') }}<span
                                                    class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                                            <ul class="dash-submenu">
                                                @can('statement report')
                                                    <li class="dash-item {{ Request::route()->getName() == 'report.account.statement' ? ' active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.account.statement') }}">{{ __('Account Statement') }}</a>
                                                    </li>
                                                @endcan
                                                @can('invoice report')
                                                    <li class="dash-item {{ Request::route()->getName() == 'report.invoice.summary' ? ' active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.invoice.summary') }}">{{ __('Invoice Summary') }}</a>
                                                    </li>
                                                @endcan
                                                <li class="dash-item {{ Request::route()->getName() == 'report.sales' ? ' active' : '' }}">
                                                    <a class="dash-link" href="{{ route('report.sales') }}">{{ __('Sales Report') }}</a>
                                                </li>
                                                <li class="dash-item {{ Request::route()->getName() == 'report.receivables' ? ' active' : '' }}">
                                                    <a class="dash-link" href="{{ route('report.receivables') }}">{{ __('Receivables') }}</a>
                                                </li>
                                                <li class="dash-item {{ Request::route()->getName() == 'report.payables' ? ' active' : '' }}">
                                                    <a class="dash-link" href="{{ route('report.payables') }}">{{ __('Payables') }}</a>
                                                </li>
                                                @can('bill report')
                                                    <li class="dash-item {{ Request::route()->getName() == 'report.bill.summary' ? ' active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.bill.summary') }}">{{ __('Bill Summary') }}</a>
                                                    </li>
                                                @endcan
                                                @can('stock report')
                                                    <li class="dash-item {{ Request::route()->getName() == 'report.product.stock.report' ? ' active' : '' }}">
                                                        <a href="{{ route('report.product.stock.report') }}" class="dash-link">{{ __('Product Stock') }}</a>
                                                    </li>
                                                @endcan
                                                @can('loss & profit report')
                                                    <li class="dash-item {{ request()->is('reports-monthly-cashflow') || request()->is('reports-quarterly-cashflow') ? 'active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.monthly.cashflow') }}">{{ __('Cash Flow') }}</a>
                                                    </li>
                                                @endcan
                                                @can('manage transaction')
                                                    <li class="dash-item {{ Request::route()->getName() == 'transaction.index' || Request::route()->getName() == 'transfer.create' || Request::route()->getName() == 'transaction.edit' ? ' active' : '' }}">
                                                        <a class="dash-link" href="{{ route('transaction.index') }}">{{ __('Transaction') }}</a>
                                                    </li>
                                                @endcan
                                                @can('income report')
                                                    <li class="dash-item {{ Request::route()->getName() == 'report.income.summary' ? ' active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.income.summary') }}">{{ __('Income Summary') }}</a>
                                                    </li>
                                                @endcan
                                                @can('expense report')
                                                    <li class="dash-item {{ Request::route()->getName() == 'report.expense.summary' ? ' active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.expense.summary') }}">{{ __('Expense Summary') }}</a>
                                                    </li>
                                                @endcan
                                                @can('income vs expense report')
                                                    <li class="dash-item {{ Request::route()->getName() == 'report.income.vs.expense.summary' ? ' active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.income.vs.expense.summary') }}">{{ __('Income VS Expense') }}</a>
                                                    </li>
                                                @endcan
                                                @can('tax report')
                                                    <li class="dash-item {{ Request::route()->getName() == 'report.tax.summary' ? ' active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.tax.summary') }}">{{ __('Tax Summary') }}</a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </li>
                                    @endif

                                    <!-- HRM Reports -->
                                    @if ($userPlan->hrm == 1)
                                        @can('manage report')
                                            <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'reports-monthly-attendance' ||
                                                Request::segment(1) == 'reports-leave' ||
                                                Request::segment(1) == 'reports-payroll' ? 'active dash-trigger' : '' }}">
                                                <a class="dash-link" href="#">{{ __('HRM') }}<span
                                                        class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                                                <ul class="dash-submenu">
                                                    <li class="dash-item {{ request()->is('reports-payroll') ? 'active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.payroll') }}">{{ __('Payroll') }}</a>
                                                    </li>
                                                    <li class="dash-item {{ request()->is('reports-leave') ? 'active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.leave') }}">{{ __('Leave') }}</a>
                                                    </li>
                                                    <li class="dash-item {{ request()->is('reports-monthly-attendance') ? 'active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.monthly.attendance') }}">{{ __('Monthly Attendance') }}</a>
                                                    </li>
                                                </ul>
                                            </li>
                                        @endcan
                                    @endif

                                    <!-- CRM Reports -->
                                    @if ($userPlan->crm == 1)
                                        @can('show crm dashboard')
                                            <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'reports-lead' || 
                                                Request::segment(1) == 'reports-deal' ? 'active dash-trigger' : '' }}">
                                                <a class="dash-link" href="#">{{ __('CRM') }}<span
                                                        class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                                                <ul class="dash-submenu">
                                                    <li class="dash-item {{ request()->is('reports-lead') ? 'active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.lead') }}">{{ __('Lead') }}</a>
                                                    </li>
                                                    <li class="dash-item {{ request()->is('reports-deal') ? 'active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.deal') }}">{{ __('Deal') }}</a>
                                                    </li>
                                                </ul>
                                            </li>
                                        @endcan
                                    @endif

                                    <!-- POS Reports -->
                                    @if ($userPlan->pos == 1)
                                        @can('show pos dashboard')
                                            <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'reports-warehouse' ||
                                                Request::segment(1) == 'reports-daily-purchase' ||
                                                Request::segment(1) == 'reports-monthly-purchase' ||
                                                Request::segment(1) == 'reports-daily-pos' ||
                                                Request::segment(1) == 'reports-monthly-pos' ||
                                                Request::segment(1) == 'reports-pos-vs-purchase' ? 'active dash-trigger' : '' }}">
                                                <a class="dash-link" href="#">{{ __('POS') }}<span
                                                        class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                                                <ul class="dash-submenu">
                                                    <li class="dash-item {{ request()->is('reports-warehouse') ? 'active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.warehouse') }}">{{ __('Warehouse Report') }}</a>
                                                    </li>
                                                    <li class="dash-item {{ request()->is('reports-daily-purchase') || request()->is('reports-monthly-purchase') ? 'active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.daily.purchase') }}">{{ __('Purchase Daily/Monthly Report') }}</a>
                                                    </li>
                                                    <li class="dash-item {{ request()->is('reports-daily-pos') || request()->is('reports-monthly-pos') ? 'active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.daily.pos') }}">{{ __('POS Daily/Monthly Report') }}</a>
                                                    </li>
                                                    <li class="dash-item {{ request()->is('reports-pos-vs-purchase') ? 'active' : '' }}">
                                                        <a class="dash-link" href="{{ route('report.pos.vs.purchase') }}">{{ __('Pos VS Purchase Report') }}</a>
                                                    </li>
                                                </ul>
                                            </li>
                                        @endcan
                                    @endif

                                </ul>
                            </li>

                        </ul>
                    </li>
                @elseif(!Gate::check('show hrm dashboard') ||
                    !Gate::check('show project dashboard') ||
                    !Gate::check('show account dashboard') ||
                    !Gate::check('show crm dashboard') ||
                    !Gate::check('show pos dashboard')
                     && \Auth::user()->type != 'super admin')

                        <li class="dash-item dash-hasmenu {{ Request::segment(1) == null || Request::segment(1) == 'dashboard' ? ' active' : '' }}">
                            <a href="{{ route('dashboard') }}" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-home"></i></span><span
                                    class="dash-mtext">{{ __('Dashboard') }}</span>
                            </a>
                        </li>
                @endif
                <!--------------------- End Dashboard ----------------------------------->

                <!-- Add all other menu items here (HRM, Accounting, CRM, etc.) -->
                <!-- ... rest of your existing menu structure ... -->

            </ul>
        @endif
        
        <!-- Add sections for client and super admin as in original -->
        @if (\Auth::user()->type == 'client')
            <!-- Client menu items -->
        @endif
        
        @if (\Auth::user()->type == 'super admin')
            <!-- Super admin menu items -->
        @endif

    </div>
</div>
</nav>

<style>
/* Hamburger Menu Styles */
.hamburger-menu-wrapper {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1050;
    background: var(--bs-white);
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 10px;
}

.hamburger-btn {
    background: none;
    border: none;
    padding: 8px;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.hamburger-btn:hover {
    background-color: var(--bs-light);
}

/* Logo positioning for desktop */
.main-logo-area {
    position: fixed;
    top: 20px;
    left: 80px;
    z-index: 1040;
    background: var(--bs-white);
    padding: 10px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Sidebar styles */
.dash-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 280px;
    background: var(--bs-white);
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    z-index: 1045;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.dash-sidebar.sidebar-open {
    transform: translateX(0);
}

/* Overlay for mobile */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    background: rgba(0,0,0,0.5);
    z-index: 1044;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .main-logo-area {
        display: none !important;
    }
    
    .hamburger-menu-wrapper {
        left: 20px;
    }
}

@media (min-width: 992px) {
    .hamburger-menu-wrapper {
        left: 20px;
    }
    
    .main-logo-area {
        left: 80px;
    }
}

/* Hamburger animation */
.hamburger {
    padding: 0;
    display: inline-block;
    cursor: pointer;
    transition-property: opacity, filter;
    transition-duration: 0.15s;
    transition-timing-function: linear;
    font: inherit;
    color: inherit;
    text-transform: none;
    background-color: transparent;
    border: 0;
    margin: 0;
    overflow: visible;
}

.hamburger-box {
    width: 24px;
    height: 18px;
    display: inline-block;
    position: relative;
}

.hamburger-inner {
    display: block;
    top: 50%;
    margin-top: -1px;
}

.hamburger-inner,
.hamburger-inner::before,
.hamburger-inner::after {
    width: 24px;
    height: 2px;
    background-color: var(--bs-dark);
    border-radius: 2px;
    position: absolute;
    transition-property: transform;
    transition-duration: 0.15s;
    transition-timing-function: ease;
}

.hamburger-inner::before,
.hamburger-inner::after {
    content: "";
    display: block;
}

.hamburger-inner::before {
    top: -6px;
}

.hamburger-inner::after {
    bottom: -6px;
}

/* Arrow turn animation */
.hamburger--arrowturn.is-active .hamburger-inner {
    transform: rotate(-45deg);
}

.hamburger--arrowturn.is-active .hamburger-inner::before {
    transform: rotate(90deg) translateX(-6px);
}

.hamburger--arrowturn.is-active .hamburger-inner::after {
    opacity: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('main-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const hamburger = document.querySelector('.hamburger');

    function toggleMenu() {
        sidebar.classList.toggle('sidebar-open');
        overlay.classList.toggle('active');
        hamburger.classList.toggle('is-active');
        
        // Prevent body scroll when menu is open on mobile
        if (window.innerWidth < 992) {
            document.body.classList.toggle('overflow-hidden');
        }
    }

    function closeMenu() {
        sidebar.classList.remove('sidebar-open');
        overlay.classList.remove('active');
        hamburger.classList.remove('is-active');
        document.body.classList.remove('overflow-hidden');
    }

    // Toggle menu on hamburger click
    menuToggle.addEventListener('click', toggleMenu);

    // Close menu on overlay click
    overlay.addEventListener('click', closeMenu);

    // Close menu on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMenu();
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            document.body.classList.remove('overflow-hidden');
        }
    });

    // Close menu when clicking on menu links (mobile)
    const menuLinks = sidebar.querySelectorAll('.dash-link');
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only close on mobile and if it's not a submenu toggle
            if (window.innerWidth < 992 && !this.nextElementSibling) {
                setTimeout(closeMenu, 100);
            }
        });
    });
});
</script>