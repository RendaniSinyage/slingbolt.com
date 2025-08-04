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

@if (isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on')
    <nav class="dash-sidebar light-sidebar transprent-bg">
@else
    <nav class="dash-sidebar light-sidebar ">
@endif
<div class="navbar-wrapper">
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
                @else
                    <li class="dash-item {{ Request::segment(1) == null || Request::segment(1) == 'dashboard' ? ' active' : '' }}">
                        <a href="{{ route('dashboard') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-home"></i></span><span
                                class="dash-mtext">{{ __('Dashboard') }}</span>
                        </a>
                    </li>
                @endif
                <!--------------------- End Dashboard ----------------------------------->

                <!--------------------- Start HRM ----------------------------------->
                @if (!empty($userPlan) && $userPlan->hrm == 1)
                    @if (Gate::check('manage employee') || Gate::check('manage setsalary'))
                        <li
                            class="dash-item dash-hasmenu {{ Request::segment(1) == 'holiday-calender' ||
                            Request::segment(1) == 'leavetype' ||
                            Request::segment(1) == 'leave' ||
                            Request::segment(1) == 'attendanceemployee' ||
                            Request::segment(1) == 'document-upload' ||
                            Request::segment(1) == 'document' ||
                            Request::segment(1) == 'performanceType' ||
                            Request::segment(1) == 'branch' ||
                            Request::segment(1) == 'department' ||
                            Request::segment(1) == 'designation' ||
                            Request::segment(1) == 'employee' ||
                            Request::segment(1) == 'leave_requests' ||
                            Request::segment(1) == 'holidays' ||
                            Request::segment(1) == 'policies' ||
                            Request::segment(1) == 'leave_calender' ||
                            Request::segment(1) == 'award' ||
                            Request::segment(1) == 'transfer' ||
                            Request::segment(1) == 'resignation' ||
                            Request::segment(1) == 'training' ||
                            Request::segment(1) == 'travel' ||
                            Request::segment(1) == 'promotion' ||
                            Request::segment(1) == 'complaint' ||
                            Request::segment(1) == 'warning' ||
                            Request::segment(1) == 'termination' ||
                            Request::segment(1) == 'announcement' ||
                            Request::segment(1) == 'job' ||
                            Request::segment(1) == 'job-application' ||
                            Request::segment(1) == 'candidates-job-applications' ||
                            Request::segment(1) == 'job-onboard' ||
                            Request::segment(1) == 'custom-question' ||
                            Request::segment(1) == 'interview-schedule' ||
                            Request::segment(1) == 'career' ||
                            Request::segment(1) == 'holiday' ||
                            Request::segment(1) == 'setsalary' ||
                            Request::segment(1) == 'payslip' ||
                            Request::segment(1) == 'paysliptype' ||
                            Request::segment(1) == 'company-policy' ||
                            Request::segment(1) == 'job-stage' ||
                            Request::segment(1) == 'job-category' ||
                            Request::segment(1) == 'terminationtype' ||
                            Request::segment(1) == 'awardtype' ||
                            Request::segment(1) == 'trainingtype' ||
                            Request::segment(1) == 'goaltype' ||
                            Request::segment(1) == 'paysliptype' ||
                            Request::segment(1) == 'allowanceoption' ||
                            Request::segment(1) == 'competencies' ||
                            Request::segment(1) == 'loanoption' ||
                            Request::segment(1) == 'deductionoption'
                                ? 'active dash-trigger'
                                : '' }}">
                            <a href="#!" class="dash-link ">
                                <span class="dash-micon">
                                    <i class="ti ti-user"></i>
                                </span>
                                <span class="dash-mtext">
                                    {{ __('HRM System') }}
                                </span>
                                <span class="dash-arrow">
                                    <i data-feather="chevron-right"></i>
                                </span>
                            </a>
                            <ul class="dash-submenu">
                                <!-- HRM menu items without setup items -->
                                <li class="dash-item  {{ Request::segment(1) == 'employee' ? 'active dash-trigger' : '' }}   ">
                                    @if (\Auth::user()->type == 'Employee')
                                        @php
                                            $employee = App\Models\Employee::where('user_id', \Auth::user()->id)->first();
                                        @endphp
                                        <a class="dash-link"
                                            href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ __('Employee') }}</a>
                                    @else
                                        <a href="{{ route('employee.index') }}" class="dash-link">
                                            {{ __('Employee Setup') }}
                                        </a>
                                    @endif
                                </li>
                                <!-- Add other HRM functional items here, not setup items -->
                            </ul>
                        </li>
                    @endif
                @endif
                <!--------------------- End HRM ----------------------------------->

                <!--------------------- Start Account ----------------------------------->
                @if (!empty($userPlan) && $userPlan->account == 1)
                    @if (Gate::check('manage customer') || Gate::check('manage vender') || Gate::check('manage invoice'))
                        <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'customer' || Request::segment(1) == 'vender' || Request::segment(1) == 'invoice' ? 'active dash-trigger' : '' }}">
                            <a href="#!" class="dash-link"><span class="dash-micon"><i class="ti ti-box"></i></span><span class="dash-mtext">{{ __('Accounting System ') }}</span><span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="dash-submenu">
                                <!-- Accounting functional items without setup -->
                            </ul>
                        </li>
                    @endif
                @endif
                <!--------------------- End Account ----------------------------------->

                <!--------------------- Start CRM ----------------------------------->
                @if (!empty($userPlan) && $userPlan->crm == 1)
                    @if (Gate::check('manage lead') || Gate::check('manage deal') || Gate::check('manage form builder') || Gate::check('manage contract'))
                        <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'leads' || Request::segment(1) == 'deals' || Request::segment(1) == 'form_builder' || Request::segment(1) == 'contract' || Request::segment(1) == 'zoom-meeting' || Request::segment(1) == 'zoom-meeting-calender' ? ' active dash-trigger' : '' }}">
                            <a href="#!" class="dash-link"><span class="dash-micon"><i class="ti ti-layers-difference"></i></span><span class="dash-mtext">{{ __('CRM System') }}</span><span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="dash-submenu">
                                @can('manage lead')
                                    <li class="dash-item {{ Request::route()->getName() == 'leads.list' || Request::route()->getName() == 'leads.index' || Request::route()->getName() == 'leads.show' ? ' active' : '' }}">
                                        <a class="dash-link" href="{{ route('leads.index') }}">{{ __('Leads') }}</a>
                                    </li>
                                @endcan
                                @can('manage deal')
                                    <li class="dash-item {{ Request::route()->getName() == 'deals.list' || Request::route()->getName() == 'deals.index' || Request::route()->getName() == 'deals.show' ? ' active' : '' }}">
                                        <a class="dash-link" href="{{ route('deals.index') }}">{{ __('Deals') }}</a>
                                    </li>
                                @endcan
                                @can('manage form builder')
                                    <li class="dash-item {{ Request::segment(1) == 'form_builder' || Request::segment(1) == 'form_response' ? 'active open' : '' }}">
                                        <a class="dash-link" href="{{ route('form_builder.index') }}">{{ __('Form Builder') }}</a>
                                    </li>
                                @endcan
                                @can('manage contract')
                                    <li class="dash-item  {{ Request::route()->getName() == 'contract.index' || Request::route()->getName() == 'contract.show' ? 'active' : '' }}">
                                        <a class="dash-link" href="{{ route('contract.index') }}">{{ __('Contract') }}</a>
                                    </li>
                                @endcan
                                <!-- Zoom Meeting moved to CRM -->
                                <li class="dash-item {{ Request::segment(1) == 'zoom-meeting' || Request::segment(1) == 'zoom-meeting-calender' ? 'active' : '' }}">
                                    <a href="{{ route('zoom-meeting.index') }}" class="dash-link">{{ __('Zoom Meeting') }}</a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endif
                <!--------------------- End CRM ----------------------------------->

                <!--------------------- Start Project ----------------------------------->
                @if (!empty($userPlan) && $userPlan->project == 1)
                    @if (Gate::check('manage project'))
                        <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'project' || Request::segment(1) == 'projects' || Request::segment(1) == 'taskboard' || Request::segment(1) == 'timesheet-list' || Request::segment(1) == 'bugs-report' || Request::segment(1) == 'calendar' || Request::segment(1) == 'time-tracker' || Request::segment(1) == 'project_report' ? 'active dash-trigger' : '' }}">
                            <a href="#!" class="dash-link"><span class="dash-micon"><i class="ti ti-share"></i></span><span class="dash-mtext">{{ __('Project System') }}</span><span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="dash-submenu">
                                @can('manage project')
                                    <li class="dash-item  {{ Request::segment(1) == 'project' || Request::route()->getName() == 'projects.list' || Request::route()->getName() == 'projects.index' || Request::route()->getName() == 'projects.show' || request()->is('projects/*') ? 'active' : '' }}">
                                        <a class="dash-link" href="{{ route('projects.index') }}">{{ __('Projects') }}</a>
                                    </li>
                                @endcan
                                @can('manage project task')
                                    <li class="dash-item {{ request()->is('taskboard*') ? 'active' : '' }}">
                                        <a class="dash-link" href="{{ route('taskBoard.view', 'list') }}">{{ __('Tasks') }}</a>
                                    </li>
                                @endcan
                                @can('manage timesheet')
                                    <li class="dash-item {{ request()->is('timesheet-list*') ? 'active' : '' }}">
                                        <a class="dash-link" href="{{ route('timesheet.list') }}">{{ __('Timesheet') }}</a>
                                    </li>
                                @endcan
                                @can('manage bug report')
                                    <li class="dash-item {{ request()->is('bugs-report*') ? 'active' : '' }}">
                                        <a class="dash-link" href="{{ route('bugs.view', 'list') }}">{{ __('Bug') }}</a>
                                    </li>
                                @endcan
                                @can('manage project task')
                                    <li class="dash-item {{ request()->is('calendar*') ? 'active' : '' }}">
                                        <a class="dash-link" href="{{ route('task.calendar', ['all']) }}">{{ __('Task Calendar') }}</a>
                                    </li>
                                @endcan
                                @if (\Auth::user()->type != 'super admin')
                                    <li class="dash-item  {{ Request::segment(1) == 'time-tracker' ? 'active open' : '' }}">
                                        <a class="dash-link" href="{{ route('time.tracker') }}">{{ __('Tracker') }}</a>
                                    </li>
                                @endif
                                @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'Employee')
                                    <li class="dash-item  {{ Request::route()->getName() == 'project_report.index' || Request::route()->getName() == 'project_report.show' ? 'active' : '' }}">
                                        <a class="dash-link" href="{{ route('project_report.index') }}">{{ __('Project Report') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                @endif
                <!--------------------- End Project ----------------------------------->

                <!--------------------- Start User Management System ----------------------------------->
                @if (\Auth::user()->type != 'super admin' && (Gate::check('manage user') || Gate::check('manage role') || Gate::check('manage client')))
                    <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'users' || Request::segment(1) == 'roles' || Request::segment(1) == 'clients' || Request::segment(1) == 'userlogs' ? ' active dash-trigger' : '' }}">
                        <a href="#!" class="dash-link "><span class="dash-micon"><i class="ti ti-users"></i></span><span class="dash-mtext">{{ __('User Management') }}</span><span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                        <ul class="dash-submenu">
                            @can('manage user')
                                <li class="dash-item {{ Request::route()->getName() == 'users.index' || Request::route()->getName() == 'users.create' || Request::route()->getName() == 'users.edit' || Request::route()->getName() == 'user.userlog' ? ' active' : '' }}">
                                    <a class="dash-link" href="{{ route('users.index') }}">{{ __('User') }}</a>
                                </li>
                            @endcan
                            @can('manage role')
                                <li class="dash-item {{ Request::route()->getName() == 'roles.index' || Request::route()->getName() == 'roles.create' || Request::route()->getName() == 'roles.edit' ? ' active' : '' }} ">
                                    <a class="dash-link" href="{{ route('roles.index') }}">{{ __('Role') }}</a>
                                </li>
                            @endcan
                            @can('manage client')
                                <li class="dash-item {{ Request::route()->getName() == 'clients.index' || Request::segment(1) == 'clients' || Request::route()->getName() == 'clients.edit' ? ' active' : '' }}">
                                    <a class="dash-link" href="{{ route('clients.index') }}">{{ __('Client') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endif
                <!--------------------- End User Management System----------------------------------->

                <!--------------------- Start Products System ----------------------------------->
                @if (Gate::check('manage product & service'))
                    <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'productservice' || Request::segment(1) == 'productstock' ? 'active dash-trigger' : '' }}">
                        <a href="#!" class="dash-link ">
                            <span class="dash-micon"><i class="ti ti-shopping-cart"></i></span><span class="dash-mtext">{{ __('Products System') }}</span><span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="dash-submenu">
                            <li class="dash-item {{ Request::segment(1) == 'productservice' ? 'active' : '' }}">
                                <a href="{{ route('productservice.index') }}" class="dash-link">{{ __('Product & Services') }}</a>
                            </li>
                            <li class="dash-item {{ Request::segment(1) == 'productstock' ? 'active' : '' }}">
                                <a href="{{ route('productstock.index') }}" class="dash-link">{{ __('Product Stock') }}</a>
                            </li>
                        </ul>
                    </li>
                @endif
                <!--------------------- End Products System ----------------------------------->

                <!--------------------- Start POS System ----------------------------------->
                @if (!empty($userPlan) && $userPlan->pos == 1)
                    @if (Gate::check('manage warehouse') || Gate::check('manage purchase') || Gate::check('manage pos'))
                        <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'warehouse' || Request::segment(1) == 'purchase' || Request::segment(1) == 'quotation' || Request::route()->getName() == 'pos.barcode' || Request::route()->getName() == 'pos.print' || Request::route()->getName() == 'pos.show' ? ' active dash-trigger' : '' }}">
                            <a href="#!" class="dash-link"><span class="dash-micon"><i class="ti ti-layers-difference"></i></span><span class="dash-mtext">{{ __('POS System') }}</span><span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="dash-submenu">
                                @can('manage warehouse')
                                    <li class="dash-item {{ Request::route()->getName() == 'warehouse.index' || Request::route()->getName() == 'warehouse.show' ? ' active' : '' }}">
                                        <a class="dash-link" href="{{ route('warehouse.index') }}">{{ __('Warehouse') }}</a>
                                    </li>
                                @endcan
                                @can('manage purchase')
                                    <li class="dash-item {{ Request::route()->getName() == 'purchase.index' || Request::route()->getName() == 'purchase.create' || Request::route()->getName() == 'purchase.edit' || Request::route()->getName() == 'purchase.show' ? ' active' : '' }}">
                                        <a class="dash-link" href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a>
                                    </li>
                                @endcan
                                @can('manage quotation')
                                    <li class="dash-item {{ Request::route()->getName() == 'quotation.index' || Request::route()->getName() == 'quotations.create' || Request::route()->getName() == 'quotation.edit' || Request::route()->getName() == 'quotation.show' ? ' active' : '' }}">
                                        <a class="dash-link" href="{{ route('quotation.index') }}">{{ __('Quotation') }}</a>
                                    </li>
                                @endcan
                                @can('manage pos')
                                    <li class="dash-item {{ Request::route()->getName() == 'pos.index' ? ' active' : '' }}">
                                        <a class="dash-link" href="{{ route('pos.index') }}">{{ __(' Add POS') }}</a>
                                    </li>
                                    <li class="dash-item {{ Request::route()->getName() == 'pos.report' || Request::route()->getName() == 'pos.show' ? ' active' : '' }}">
                                        <a class="dash-link" href="{{ route('pos.report') }}">{{ __('POS') }}</a>
                                    </li>
                                @endcan
                                @can('manage warehouse')
                                    <li class="dash-item {{ Request::route()->getName() == 'warehouse-transfer.index' || Request::route()->getName() == 'warehouse-transfer.show' ? ' active' : '' }}">
                                        <a class="dash-link" href="{{ route('warehouse-transfer.index') }}">{{ __('Transfer') }}</a>
                                    </li>
                                @endcan
                                @can('create barcode')
                                    <li class="dash-item {{ Request::route()->getName() == 'pos.barcode' || Request::route()->getName() == 'pos.print' ? ' active' : '' }}">
                                        <a class="dash-link" href="{{ route('pos.barcode') }}">{{ __('Print Barcode') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif
                @endif
                <!--------------------- End POS System ----------------------------------->

                <!-- Support System -->
                <li class="dash-item {{ Request::segment(1) == 'support' ? 'active' : '' }}">
                    <a href="{{ route('support.index') }}" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-headphones"></i></span><span class="dash-mtext">{{ __('Support System') }}</span>
                    </a>
                </li>

                <!-- Messenger -->
                <li class="dash-item {{ Request::segment(1) == 'chats' ? 'active' : '' }}">
                    <a href="{{ url('chats') }}" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-message-circle"></i></span><span class="dash-mtext">{{ __('Messenger') }}</span>
                    </a>
                </li>

                <!--------------------- Start Module Setup ----------------------------------->
                @if (Gate::check('manage constant tax') || Gate::check('manage constant category') || Gate::check('manage constant unit') || 
                    Gate::check('manage constant custom field') || Gate::check('manage lead stage') || Gate::check('manage pipeline') || 
                    Gate::check('manage source') || Gate::check('manage label') || Gate::check('manage stage') || 
                    Gate::check('manage project task stage') || Gate::check('manage bug status') || 
                    Gate::check('manage branch') || Gate::check('manage department') || Gate::check('manage designation') || 
                    Gate::check('manage leave type') || Gate::check('manage document type') || Gate::check('manage payslip type') || 
                    Gate::check('manage allowance option') || Gate::check('manage loan option') || Gate::check('manage deduction option') || 
                    Gate::check('manage goal type') || Gate::check('manage training type') || Gate::check('manage award type') || 
                    Gate::check('manage termination type') || Gate::check('manage job category') || Gate::check('manage job stage') || 
                    Gate::check('manage performance type') || Gate::check('manage competencies') || \Auth::user()->type == 'company')

                    <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'taxes' || Request::segment(1) == 'product-category' || Request::segment(1) == 'product-unit' || Request::segment(1) == 'payment-method' || Request::segment(1) == 'custom-field' || Request::segment(1) == 'chart-of-account-type' || Request::segment(1) == 'stages' || Request::segment(1) == 'labels' || Request::segment(1) == 'sources' || Request::segment(1) == 'lead_stages' || Request::segment(1) == 'pipelines' || Request::segment(1) == 'contractType' || Request::segment(1) == 'bugstatus' || Request::segment(1) == 'project-task-stages' || Request::segment(1) == 'leavetype' || Request::segment(1) == 'document' || Request::segment(1) == 'performanceType' || Request::segment(1) == 'branch' || Request::segment(1) == 'department' || Request::segment(1) == 'designation' || Request::segment(1) == 'job-stage' || Request::segment(1) == 'job-category' || Request::segment(1) == 'terminationtype' || Request::segment(1) == 'awardtype' || Request::segment(1) == 'trainingtype' || Request::segment(1) == 'goaltype' || Request::segment(1) == 'paysliptype' || Request::segment(1) == 'allowanceoption' || Request::segment(1) == 'competencies' || Request::segment(1) == 'loanoption' || Request::segment(1) == 'deductionoption' || Request::segment(1) == 'notification_templates' ? ' active dash-trigger' : '' }}">
                        <a href="#!" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-tool"></i></span><span class="dash-mtext">{{ __('Module Setup') }}</span>
                            <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="dash-submenu">

                            <!-- Notification Template (single item) -->
                            @if (\Auth::user()->type == 'company')
                                <li class="dash-item {{ Request::segment(1) == 'notification_templates' ? 'active' : '' }}">
                                    <a href="{{ route('notification-templates.index') }}" class="dash-link">{{ __('Notification Template') }}</a>
                                </li>
                            @endif

                            <!-- Accounting Setup (was originally a single link) -->
                            @if (Gate::check('manage constant tax') || Gate::check('manage constant category') || Gate::check('manage constant unit') || Gate::check('manage constant custom field'))
                                <li class="dash-item {{ Request::segment(1) == 'taxes' || Request::segment(1) == 'product-category' || Request::segment(1) == 'product-unit' || Request::segment(1) == 'payment-method' || Request::segment(1) == 'custom-field' || Request::segment(1) == 'chart-of-account-type' ? 'active' : '' }}">
                                    <a class="dash-link" href="{{ route('taxes.index') }}">{{ __('Accounting Setup') }}</a>
                                </li>
                            @endif

                            <!-- CRM Setup (was originally a single link) -->
                            @if (Gate::check('manage lead stage') || Gate::check('manage pipeline') || Gate::check('manage source') || Gate::check('manage label') || Gate::check('manage stage') || Gate::check('manage contract type'))
                                <li class="dash-item {{ Request::segment(1) == 'stages' || Request::segment(1) == 'labels' || Request::segment(1) == 'sources' || Request::segment(1) == 'lead_stages' || Request::segment(1) == 'pipelines' || Request::segment(1) == 'contractType' ? 'active' : '' }}">
                                    <a class="dash-link" href="{{ route('pipelines.index') }}">{{ __('CRM Setup') }}</a>
                                </li>
                            @endif

                            <!-- Project Setup (keep as submenu since it had submenu in original) -->
                            @if (Gate::check('manage project task stage') || Gate::check('manage bug status'))
                                <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'bugstatus' || Request::segment(1) == 'project-task-stages' ? 'active dash-trigger' : '' }}">
                                    <a class="dash-link" href="#">{{ __('Project Setup') }}<span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                                    <ul class="dash-submenu">
                                        @can('manage project task stage')
                                            <li class="dash-item  {{ Request::route()->getName() == 'project-task-stages.index' ? 'active' : '' }}">
                                                <a class="dash-link" href="{{ route('project-task-stages.index') }}">{{ __('Project Task Stages') }}</a>
                                            </li>
                                        @endcan
                                        @can('manage bug status')
                                            <li class="dash-item {{ Request::route()->getName() == 'bugstatus.index' ? 'active' : '' }}">
                                                <a class="dash-link" href="{{ route('bugstatus.index') }}">{{ __('Bug Status') }}</a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endif

                            <!-- HRM Setup (was originally a single link) -->
                            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'HR')
                                <li class="dash-item {{ Request::segment(1) == 'leavetype' || Request::segment(1) == 'document' || Request::segment(1) == 'performanceType' || Request::segment(1) == 'branch' || Request::segment(1) == 'department' || Request::segment(1) == 'designation' || Request::segment(1) == 'job-stage' || Request::segment(1) == 'competencies' || Request::segment(1) == 'job-category' || Request::segment(1) == 'terminationtype' || Request::segment(1) == 'awardtype' || Request::segment(1) == 'trainingtype' || Request::segment(1) == 'goaltype' || Request::segment(1) == 'paysliptype' || Request::segment(1) == 'allowanceoption' || Request::segment(1) == 'loanoption' || Request::segment(1) == 'deductionoption' ? 'active' : '' }}">
                                    <a class="dash-link" href="{{ route('branch.index') }}">{{ __('HRM Setup') }}</a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif
                <!--------------------- End Module Setup ----------------------------------->

                <!--------------------- Start Settings ----------------------------------->
                @if (Gate::check('manage company plan') || Gate::check('manage order') || Gate::check('manage company settings') || Gate::check('manage print settings'))
                    <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'settings' || Request::segment(1) == 'plans' || Request::segment(1) == 'stripe' || Request::segment(1) == 'order' || Request::route()->getName() == 'print-setting' || Request::route()->getName() == 'pos-print-setting' ? ' active dash-trigger' : '' }}">
                        <a href="#!" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-settings"></i></span><span class="dash-mtext">{{ __('Settings') }}</span>
                            <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="dash-submenu">
                            @if (Gate::check('manage company settings'))
                                <li class="dash-item {{ Request::segment(1) == 'settings' && Request::route()->getName() != 'print-setting' ? ' active' : '' }}">
                                    <a href="{{ route('settings') }}" class="dash-link">{{ __('System Settings') }}</a>
                                </li>
                            @endif

                            <!-- Print Settings Group -->
                            @if (Gate::check('manage print settings'))
                                <li class="dash-item dash-hasmenu {{ Request::route()->getName() == 'print-setting' || Request::route()->getName() == 'pos-print-setting' ? 'active dash-trigger' : '' }}">
                                    <a class="dash-link" href="#">{{ __('Print Settings') }}<span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                                    <ul class="dash-submenu">
                                        @if ($userPlan->account == 1)
                                            <li class="dash-item {{ Request::route()->getName() == 'print-setting' ? ' active' : '' }}">
                                                <a class="dash-link" href="{{ route('print.setting') }}">{{ __('Accounting') }}</a>
                                            </li>
                                        @endif
                                        @if ($userPlan->pos == 1)
                                            <li class="dash-item {{ Request::route()->getName() == 'pos-print-setting' ? ' active' : '' }}">
                                                <a class="dash-link" href="{{ route('pos.print.setting') }}">{{ __('POS') }}</a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif

                            @if (Gate::check('manage company plan'))
                                <li class="dash-item{{ Request::route()->getName() == 'plans.index' || Request::route()->getName() == 'stripe' ? ' active' : '' }}">
                                    <a href="{{ route('plans.index') }}" class="dash-link">{{ __('Setup Subscription Plan') }}</a>
                                </li>
                            @endif
                            
                            <li class="dash-item{{ Request::route()->getName() == 'referral-program.company' ? ' active' : '' }}">
                                <a href="{{ route('referral-program.company') }}" class="dash-link">{{ __('Referral Program') }}</a>
                            </li>

                            @if (Gate::check('manage order') && Auth::user()->type == 'company')
                                <li class="dash-item {{ Request::segment(1) == 'order' ? 'active' : '' }}">
                                    <a href="{{ route('order.index') }}" class="dash-link">{{ __('Order') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                <!--------------------- End Settings ----------------------------------->

            </ul>
        @endif

        <!--------------------- Start Client Menu ----------------------------------->
        @if (\Auth::user()->type == 'client')
            <ul class="dash-navbar">
                @if (Gate::check('manage client dashboard'))
                    <li class="dash-item {{ Request::segment(1) == 'dashboard' ? ' active' : '' }}">
                        <a href="{{ route('client.dashboard.view') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-home"></i></span><span class="dash-mtext">{{ __('Dashboard') }}</span>
                        </a>
                    </li>
                @endif
                @if (Gate::check('manage deal'))
                    <li class="dash-item {{ Request::segment(1) == 'deals' ? ' active' : '' }}">
                        <a href="{{ route('deals.index') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-rocket"></i></span><span class="dash-mtext">{{ __('Deals') }}</span>
                        </a>
                    </li>
                @endif
                @if (Gate::check('manage contract'))
                    <li class="dash-item {{ Request::route()->getName() == 'contract.index' || Request::route()->getName() == 'contract.show' ? 'active' : '' }}">
                        <a href="{{ route('contract.index') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-rocket"></i></span><span class="dash-mtext">{{ __('Contract') }}</span>
                        </a>
                    </li>
                @endif
                @if (Gate::check('manage project'))
                    <li class="dash-item  {{ Request::segment(1) == 'projects' ? ' active' : '' }}">
                        <a href="{{ route('projects.index') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-share"></i></span><span class="dash-mtext">{{ __('Project') }}</span>
                        </a>
                    </li>
                @endif
                @if (Gate::check('manage project'))
                    <li class="dash-item  {{ Request::route()->getName() == 'project_report.index' || Request::route()->getName() == 'project_report.show' ? 'active' : '' }}">
                        <a class="dash-link" href="{{ route('project_report.index') }}">
                            <span class="dash-micon"><i class="ti ti-chart-line"></i></span><span class="dash-mtext">{{ __('Project Report') }}</span>
                        </a>
                    </li>
                @endif
                @if (Gate::check('manage project task'))
                    <li class="dash-item  {{ Request::segment(1) == 'taskboard' ? ' active' : '' }}">
                        <a href="{{ route('taskBoard.view', 'list') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-list-check"></i></span><span class="dash-mtext">{{ __('Tasks') }}</span>
                        </a>
                    </li>
                @endif
                @if (Gate::check('manage bug report'))
                    <li class="dash-item {{ Request::segment(1) == 'bugs-report' ? ' active' : '' }}">
                        <a href="{{ route('bugs.view', 'list') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-bug"></i></span><span class="dash-mtext">{{ __('Bugs') }}</span>
                        </a>
                    </li>
                @endif
                @if (Gate::check('manage project task'))
                    <li class="dash-item {{ Request::segment(1) == 'calendar' ? ' active' : '' }}">
                        <a href="{{ route('task.calendar', ['all']) }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-calendar"></i></span><span class="dash-mtext">{{ __('Task Calendar') }}</span>
                        </a>
                    </li>
                @endif
                <li class="dash-item {{ Request::segment(1) == 'support' ? 'active' : '' }}">
                    <a href="{{ route('support.index') }}" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-headphones"></i></span><span class="dash-mtext">{{ __('Support System') }}</span>
                    </a>
                </li>
            </ul>
        @endif
        <!--------------------- End Client Menu ----------------------------------->

        <!--------------------- Start Super Admin Menu ----------------------------------->
        @if (\Auth::user()->type == 'super admin')
            <ul class="dash-navbar">
                @if (Gate::check('manage super admin dashboard'))
                    <li class="dash-item {{ Request::segment(1) == 'dashboard' ? ' active' : '' }}">
                        <a href="{{ route('client.dashboard.view') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-home"></i></span><span class="dash-mtext">{{ __('Dashboard') }}</span>
                        </a>
                    </li>
                @endif

                @can('manage user')
                    <li class="dash-item {{ Request::route()->getName() == 'users.index' || Request::route()->getName() == 'users.create' || Request::route()->getName() == 'users.edit' ? ' active' : '' }}">
                        <a href="{{ route('users.index') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-users"></i></span><span class="dash-mtext">{{ __('Companies') }}</span>
                        </a>
                    </li>
                @endcan

                @if (Gate::check('manage plan'))
                    <li class="dash-item  {{ Request::segment(1) == 'plans' ? 'active' : '' }}">
                        <a href="{{ route('plans.index') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-trophy"></i></span><span class="dash-mtext">{{ __('Plan') }}</span>
                        </a>
                    </li>
                @endif

                @if (\Auth::user()->type == 'super admin')
                    <li class="dash-item {{ request()->is('plan_request*') ? 'active' : '' }}">
                        <a href="{{ route('plan_request.index') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-arrow-up-right-circle"></i></span><span class="dash-mtext">{{ __('Plan Request') }}</span>
                        </a>
                    </li>
                @endif

                <li class="dash-item  {{ Request::segment(1) == '' ? 'active' : '' }}">
                    <a href="{{ route('referral-program.index') }}" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-discount-2"></i></span><span class="dash-mtext">{{ __('Referral Program') }}</span>
                    </a>
                </li>

                @if (Gate::check('manage coupon'))
                    <li class="dash-item {{ Request::segment(1) == 'coupons' ? 'active' : '' }}">
                        <a href="{{ route('coupons.index') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-gift"></i></span><span class="dash-mtext">{{ __('Coupon') }}</span>
                        </a>
                    </li>
                @endif

                @if (Gate::check('manage order'))
                    <li class="dash-item  {{ Request::segment(1) == 'orders' ? 'active' : '' }}">
                        <a href="{{ route('order.index') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-shopping-cart-plus"></i></span><span class="dash-mtext">{{ __('Order') }}</span>
                        </a>
                    </li>
                @endif

                <li class="dash-item {{ Request::segment(1) == 'email_template' || Request::route()->getName() == 'manage.email.language' ? ' active' : '' }}">
                    <a href="{{ route('email_template.index') }}" class="dash-link">
                        <span class="dash-micon"><i class="ti ti-template"></i></span>
                        <span class="dash-mtext">{{ __('Email Template') }}</span>
                    </a>
                </li>

                @if (\Auth::user()->type == 'super admin')
                    @include('landingpage::menu.landingpage')
                @endif

                @if (Gate::check('manage system settings'))
                    <li class="dash-item {{ Request::route()->getName() == 'systems.index' ? ' active' : '' }}">
                        <a href="{{ route('systems.index') }}" class="dash-link">
                            <span class="dash-micon"><i class="ti ti-settings"></i></span><span class="dash-mtext">{{ __('Settings') }}</span>
                        </a>
                    </li>
                @endif

            </ul>
        @endif
        <!--------------------- End Super Admin Menu ----------------------------------->

        <div class="navbar-footer border-top ">
            <div class="d-flex align-items-center py-3 px-3 border-bottom">
                <div class="me-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="29" height="30" viewBox="0 0 29 30" fill="none">
                        <circle cx="14.5" cy="15.1846" r="14.5" fill="#6FD943"></circle>
                        <path opacity="0.4" d="M22.08 8.66459C21.75 8.28459 21.4 7.92459 21.02 7.60459C19.28 6.09459 17 5.18461 14.5 5.18461C12.01 5.18461 9.73999 6.09459 7.98999 7.60459C7.60999 7.92459 7.24999 8.28459 6.92999 8.66459C5.40999 10.4146 4.5 12.6946 4.5 15.1846C4.5 17.6746 5.40999 19.9546 6.92999 21.7046C7.24999 22.0846 7.60999 22.4446 7.98999 22.7646C9.73999 24.2746 12.01 25.1846 14.5 25.1846C17 25.1846 19.28 24.2746 21.02 22.7646C21.4 22.4446 21.75 22.0846 22.08 21.7046C23.59 19.9546 24.5 17.6746 24.5 15.1846C24.5 12.6946 23.59 10.4146 22.08 8.66459ZM14.5 19.6246C13.54 19.6246 12.65 19.3146 11.93 18.7946C11.52 18.5146 11.17 18.1646 10.88 17.7546C10.37 17.0346 10.06 16.1346 10.06 15.1846C10.06 14.2346 10.37 13.3346 10.88 12.6146C11.17 12.2046 11.52 11.8546 11.93 11.5746C12.65 11.0546 13.54 10.7446 14.5 10.7446C15.46 10.7446 16.35 11.0546 17.08 11.5646C17.49 11.8546 17.84 12.2046 18.13 12.6146C18.64 13.3346 18.95 14.2346 18.95 15.1846C18.95 16.1346 18.64 17.0346 18.13 17.7546C17.84 18.1646 17.49 18.5146 17.08 18.8046C16.35 19.3146 15.46 19.6246 14.5 19.6246Z" fill="#162C4E"></path>
                        <path d="M22.08 8.66459L18.18 12.5746C18.16 12.5846 18.15 12.6046 18.13 12.6146C17.84 12.2046 17.49 11.8546 17.08 11.5646C17.09 11.5446 17.1 11.5346 17.12 11.5146L21.02 7.60459C21.4 7.92459 21.75 8.28459 22.08 8.66459Z" fill="#162C4E"></path>
                        <path d="M11.9297 18.7947C11.9197 18.8147 11.9097 18.8347 11.8897 18.8547L7.98969 22.7647C7.60969 22.4447 7.24969 22.0847 6.92969 21.7047L10.8297 17.7947C10.8397 17.7747 10.8597 17.7647 10.8797 17.7547C11.1697 18.1647 11.5197 18.5147 11.9297 18.7947Z" fill="#162C4E"></path>
                        <path d="M11.9297 11.5746C11.5197 11.8546 11.1697 12.2045 10.8797 12.6145C10.8597 12.6045 10.8497 12.5846 10.8297 12.5746L6.92969 8.66453C7.24969 8.28453 7.60969 7.92453 7.98969 7.60453L11.8897 11.5146C11.9097 11.5346 11.9197 11.5546 11.9297 11.5746Z" fill="#162C4E"></path>
                        <path d="M22.08 21.7046C21.75 22.0846 21.4 22.4446 21.02 22.7646L17.12 18.8546C17.1 18.8346 17.09 18.8246 17.08 18.8046C17.49 18.5146 17.84 18.1646 18.13 17.7546C18.15 17.7646 18.16 17.7746 18.18 17.7946L22.08 21.7046Z" fill="#162C4E"></path>
                    </svg>
                </div>
                <div>
                    <b class="d-block f-w-700">{{ __('You need help?') }}</b>
                    <span>{{ __('Check out our repository') }} </span>
                </div>
            </div>
        </div>
    </div>
</div>
</nav>