<!-- [ subscription ] start -->
@if (isset($settings['plan_status']) && $settings['plan_status'] == 'on')
    <section class="subscription bg-primary section-gap" id="plan">
        <div class="container">
            <!-- Header -->
            <div class="row mb-5 justify-content-center">
                <div class="col-xxl-6">
                    <div class="title text-center">
                        <span class="d-block mb-2 fw-bold text-uppercase" style="color: rgba(255,255,255,0.8);">{{ __('PLAN') }}</span>
                        <h2 class="mb-4 text-white">{!! $settings['plan_heading'] !!}</h2>
                        <p class="text-white" style="opacity: 0.9;">{!! $settings['plan_description'] !!}</p>
                    </div>
                </div>
            </div>

            @php
                $monthly_plans = \App\Models\Plan::where('price', '>', 0)->where('name', 'not like', '%(yearly)%')->where('is_disable', 1)->orderBy('price', 'ASC')->get();
                $yearly_plans = \App\Models\Plan::where('price', '>', 0)->where('name', 'like', '%(yearly)%')->where('is_disable', 1)->orderBy('price', 'ASC')->get();
                $admin_payment_setting = App\Models\Utility::getAdminPaymentSetting();
                $has_yearly_plans = $yearly_plans->count() > 0;

                // Parse features for monthly plans
                $monthly_categories = [];
                foreach($monthly_plans as $plan) {
                    $lines = explode("\n", $plan->description);
                    $current_category = 'General';

                    foreach($lines as $line) {
                        $line = trim($line);
                        if(empty($line)) continue;

                        if(str_starts_with($line, '##')) {
                            $current_category = trim(str_replace('##', '', $line));
                            if(!isset($monthly_categories[$current_category])) {
                                $monthly_categories[$current_category] = [];
                            }
                        } else {
                            if(!isset($monthly_categories[$current_category])) {
                                $monthly_categories[$current_category] = [];
                            }
                            if(!in_array($line, $monthly_categories[$current_category])) {
                                $monthly_categories[$current_category][] = $line;
                            }
                        }
                    }
                }

                // Parse features for yearly plans
                $yearly_categories = [];
                foreach($yearly_plans as $plan) {
                    $lines = explode("\n", $plan->description);
                    $current_category = 'General';

                    foreach($lines as $line) {
                        $line = trim($line);
                        if(empty($line)) continue;

                        if(str_starts_with($line, '##')) {
                            $current_category = trim(str_replace('##', '', $line));
                            if(!isset($yearly_categories[$current_category])) {
                                $yearly_categories[$current_category] = [];
                            }
                        } else {
                            if(!isset($yearly_categories[$current_category])) {
                                $yearly_categories[$current_category] = [];
                            }
                            if(!in_array($line, $yearly_categories[$current_category])) {
                                $yearly_categories[$current_category][] = $line;
                            }
                        }
                    }
                }
            @endphp

            <!-- Billing Toggle -->
            @if($has_yearly_plans)
                <div class="row justify-content-center mb-5">
                    <div class="col-auto">
                        <div class="plan-billing-toggle">
                            <div class="plan-toggle-wrapper">
                                <span class="plan-toggle-label plan-monthly">Monthly</span>
                                <div class="plan-toggle-switch">
                                    <input type="checkbox" id="planBillingToggle" class="plan-toggle-input" checked>
                                    <label for="planBillingToggle" class="plan-toggle-slider"></label>
                                </div>
                                <span class="plan-toggle-label plan-yearly">
                                    Annual 
                                    <span class="plan-save-badge">Save 25%</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Combined Pricing and Features Section -->
            <div class="pricing-container">
                <!-- Monthly Plans -->
                <div id="plan-monthly-container" class="plan-container d-none">
                    @if($monthly_plans->count() > 0)
                        <!-- Plan Headers with Pricing -->
                        <div class="plan-headers-row">
                            <div class="feature-column">
                                <div class="feature-header-space"></div>
                            </div>
                            @foreach($monthly_plans as $key => $plan)
                                @php
                                    $display_name = str_replace(' (yearly)', '', $plan->name);
                                    $monthly_price = intval($plan->price);
                                    $is_popular = $key == 1;
                                @endphp
                                <div class="plan-column">
                                    <div class="plan-header-card {{ $is_popular ? 'popular-plan' : '' }}">
                                        @if($is_popular)
                                            <div class="popular-badge">Most Popular</div>
                                        @endif
                                        <h3 class="plan-name">{{ $display_name }}</h3>
                                        <div class="plan-pricing">
                                            <span class="currency">{{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}</span>
                                            <span class="price">{{ $monthly_price }}</span>
                                            <span class="period">/{{ $plan->duration }}</span>
                                        </div>
                                        <p class="plan-description">Perfect for getting started</p>
                                        @if($plan->trial && $plan->trial_days > 0)
                                            <div class="trial-info">
                                                <span class="trial-badge">{{ $plan->trial_days }}-day free trial</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Feature Categories -->
                        @if(!empty($monthly_categories))
                            @php $category_index = 0; @endphp
                            @foreach($monthly_categories as $category_name => $features)
                                <div class="feature-category">
                                    <div class="feature-category-header collapsible-header" 
                                         data-bs-toggle="collapse" 
                                         data-bs-target="#monthly-category-{{ $category_index }}" 
                                         aria-expanded="{{ $category_index == 0 ? 'true' : 'false' }}"
                                         aria-controls="monthly-category-{{ $category_index }}">
                                        <div class="feature-column">
                                            <h4 class="category-title">
                                                {{ $category_name }}
                                                <i class="ti ti-chevron-down collapse-icon ms-2"></i>
                                            </h4>
                                        </div>
                                        @foreach($monthly_plans as $key => $plan)
                                            <div class="plan-column {{ $key == 1 ? 'popular-column' : '' }}">
                                                <div class="plan-category-spacer"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <div class="collapse {{ $category_index == 0 ? 'show' : '' }}" id="monthly-category-{{ $category_index }}">
                                        @foreach($features as $feature)
                                            <div class="feature-row">
                                                <div class="feature-column">
                                                    <span class="feature-name">{{ $feature }}</span>
                                                </div>
                                                @foreach($monthly_plans as $key => $plan)
                                                    <div class="plan-column {{ $key == 1 ? 'popular-column' : '' }}">
                                                        <div class="feature-check">
                                                            @if(str_contains($plan->description, $feature))
                                                                <i class="ti ti-check text-success fs-5"></i>
                                                            @else
                                                                <i class="ti ti-x text-muted fs-5"></i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @php $category_index++; @endphp
                            @endforeach
                        @endif

                        <!-- CTA Buttons -->
                        <div class="cta-buttons-row">
                            <div class="feature-column">
                                <div class="cta-spacer"></div>
                            </div>
                            @foreach($monthly_plans as $key => $plan)
                                @php 
                                    $is_popular = $key == 1;
                                    $has_trial = $plan->trial && $plan->trial_days > 0;
                                    $purchase_url = Auth::check() ? route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]);
                                    $trial_url = Auth::check() ? route('plan.trial', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id), 'trial' => true]);
                                @endphp
                                <div class="plan-column {{ $is_popular ? 'popular-column' : '' }}">
                                    <div class="cta-button-container">
                                        @if($has_trial)
                                            <!-- Trial Button -->
                                            <a href="{{ $trial_url }}"
                                               class="btn btn-primary rounded-pill w-100 mb-2">
                                                Start Free
                                            </a>
                                            <!-- Purchase Button -->
                                            <a href="{{ $purchase_url }}"
                                               class="btn btn-outline-primary rounded-pill w-100">
                                                Buy Now
                                            </a>
                                        @else
                                            <!-- Single Purchase Button -->
                                            <a href="{{ $purchase_url }}"
                                               class="btn {{ $is_popular ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill w-100">
                                                Get Started
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Yearly Plans -->
                @if($has_yearly_plans)
                    <div id="plan-yearly-container" class="plan-container">
                        <!-- Plan Headers with Pricing -->
                        <div class="plan-headers-row">
                            <div class="feature-column">
                                <div class="feature-header-space"></div>
                            </div>
                            @foreach($yearly_plans as $key => $plan)
                                @php
                                    $display_name = str_replace(' (yearly)', '', $plan->name);
                                    $yearly_price = intval($plan->price);
                                    $monthly_equivalent = round($yearly_price / 12, 2);
                                    $is_popular = $key == 1;
                                @endphp
                                <div class="plan-column">
                                    <div class="plan-header-card {{ $is_popular ? 'popular-plan' : '' }}">
                                        @if($is_popular)
                                            <div class="popular-badge">Most Popular</div>
                                        @endif
                                        <h3 class="plan-name">{{ $display_name }}</h3>
                                        <div class="plan-pricing">
                                            <span class="currency">{{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}</span>
                                            <span class="price">{{ $monthly_equivalent }}</span>
                                            <span class="period">/month</span>
                                        </div>
                                        <p class="plan-description">Billed annually ({{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}{{ $yearly_price }})</p>
                                        @if($plan->trial && $plan->trial_days > 0)
                                            <div class="trial-info">
                                                <span class="trial-badge">{{ $plan->trial_days }}-day free trial</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Feature Categories -->
                        @if(!empty($yearly_categories))
                            @php $category_index = 0; @endphp
                            @foreach($yearly_categories as $category_name => $features)
                                <div class="feature-category">
                                    <div class="feature-category-header collapsible-header" 
                                         data-bs-toggle="collapse" 
                                         data-bs-target="#yearly-category-{{ $category_index }}" 
                                         aria-expanded="{{ $category_index == 0 ? 'true' : 'false' }}"
                                         aria-controls="yearly-category-{{ $category_index }}">
                                        <div class="feature-column">
                                            <h4 class="category-title">
                                                {{ $category_name }}
                                                <i class="ti ti-chevron-down collapse-icon ms-2"></i>
                                            </h4>
                                        </div>
                                        @foreach($yearly_plans as $key => $plan)
                                            <div class="plan-column {{ $key == 1 ? 'popular-column' : '' }}">
                                                <div class="plan-category-spacer"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <div class="collapse {{ $category_index == 0 ? 'show' : '' }}" id="yearly-category-{{ $category_index }}">
                                        @foreach($features as $feature)
                                            <div class="feature-row">
                                                <div class="feature-column">
                                                    <span class="feature-name">{{ $feature }}</span>
                                                </div>
                                                @foreach($yearly_plans as $key => $plan)
                                                    <div class="plan-column {{ $key == 1 ? 'popular-column' : '' }}">
                                                        <div class="feature-check">
                                                            @if(str_contains($plan->description, $feature))
                                                                <i class="ti ti-check text-success fs-5"></i>
                                                            @else
                                                                <i class="ti ti-x text-muted fs-5"></i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @php $category_index++; @endphp
                            @endforeach
                        @endif

                        <!-- CTA Buttons -->
                        <div class="cta-buttons-row">
                            <div class="feature-column">
                                <div class="cta-spacer"></div>
                            </div>
                            @foreach($yearly_plans as $key => $plan)
                                @php 
                                    $is_popular = $key == 1;
                                    $has_trial = $plan->trial && $plan->trial_days > 0;
                                    $purchase_url = Auth::check() ? route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]);
                                    $trial_url = Auth::check() ? route('plan.trial', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id), 'trial' => true]);
                                @endphp
                                <div class="plan-column {{ $is_popular ? 'popular-column' : '' }}">
                                    <div class="cta-button-container">
                                        @if($has_trial)
                                            <!-- Trial Button -->
                                            <a href="{{ $trial_url }}"
                                               class="btn btn-primary rounded-pill w-100 mb-2">
                                                Start Free
                                            </a>
                                            <!-- Purchase Button -->
                                            <a href="{{ $purchase_url }}"
                                               class="btn btn-outline-primary rounded-pill w-100">
                                                Buy Now
                                            </a>
                                        @else
                                            <!-- Single Purchase Button -->
                                            <a href="{{ $purchase_url }}"
                                               class="btn {{ $is_popular ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill w-100">
                                                Get Started
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <style>
        /* Pricing Container Layout */
        .pricing-container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            overflow: hidden;
            backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
        }

        .plan-container {
            width: 100%;
        }

        /* Grid Layout */
        .plan-headers-row,
        .feature-category-header,
        .feature-row,
        .cta-buttons-row {
            display: flex;
            align-items: stretch;
            min-height: 60px;
        }

        .feature-column {
            flex: 0 0 35%;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.25);
            border-right: 2px solid rgba(255, 255, 255, 0.4);
        }

        .plan-column {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            border-right: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .plan-column:last-child {
            border-right: none;
        }

        .plan-column.popular-column {
            background: rgba(255, 255, 255, 0.2);
            border-left: 3px solid rgba(255, 255, 255, 0.6);
            border-right: 3px solid rgba(255, 255, 255, 0.6);
        }

        /* Billing Toggle */
        .plan-billing-toggle {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 8px;
            display: inline-block;
        }

        .plan-toggle-wrapper {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .plan-toggle-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.9);
            white-space: nowrap;
        }

        .plan-toggle-label.plan-yearly {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .plan-save-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .plan-toggle-input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .plan-toggle-slider {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .plan-toggle-slider:before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .plan-toggle-input:checked + .plan-toggle-slider {
            background: rgba(255, 255, 255, 0.6);
        }

        .plan-toggle-input:checked + .plan-toggle-slider:before {
            transform: translateX(24px);
        }

        /* Plan Header Cards */
        .plan-header-card {
            text-align: center;
            padding: 2rem 1rem;
            height: 100%;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px 15px 0 0;
        }

        .plan-header-card.popular-plan {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-10px);
            z-index: 10;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .popular-badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .plan-name {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .plan-pricing {
            margin-bottom: 1rem;
        }

        .currency {
            font-size: 1.2rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }

        .price {
            font-size: 3rem;
            font-weight: 800;
            color: white;
            line-height: 1;
            margin: 0 4px;
        }

        .period {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        .plan-description {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            margin: 0 0 1rem 0;
        }

        /* Trial Badge */
        .trial-info {
            margin-top: 0.5rem;
        }

        .trial-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .trial-then-buy {
            text-align: center;
        }

        /* Feature Categories */
        .feature-category {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .feature-category:last-of-type {
            border-bottom: none;
        }

        .feature-category-header.collapsible-header {
            cursor: pointer;
            transition: background-color 0.3s ease;
            user-select: none;
            background: rgba(255, 255, 255, 0.15);
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
        }

        .feature-category-header.collapsible-header:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .category-title {
            color: white;
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .collapse-icon {
            font-size: 1.4rem;
            transition: transform 0.3s ease;
            color: white;
            margin-left: auto;
            font-weight: bold;
        }

        .feature-category-header[aria-expanded="true"] .collapse-icon {
            transform: rotate(180deg);
        }

        .feature-name {
            color: white;
            font-weight: 500;
            font-size: 1rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .feature-check {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .feature-check .ti-check {
            color: #22c55e !important;
            font-size: 1.5rem !important;
            font-weight: bold;
        }

        .feature-check .ti-x {
            color: #ef4444 !important;
            font-size: 1.5rem !important;
            font-weight: bold;
            opacity: 0.7;
        }

        .plan-category-spacer {
            height: 100%;
            width: 100%;
        }

        /* CTA Buttons */
        .cta-buttons-row {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.05);
        }

        .cta-button-container {
            width: 100%;
            padding: 1rem;
            text-align: center;
        }

        .cta-button-container .btn.mb-2 {
            margin-bottom: 0.5rem !important;
        }

        .cta-spacer {
            height: 100%;
        }

        .feature-header-space {
            height: 220px; /* Increased to accommodate trial badges */
        }

        /* Button Styling */
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .btn-outline-primary {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.8);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .plan-headers-row,
            .feature-category-header,
            .feature-row,
            .cta-buttons-row {
                flex-direction: column;
            }

            .feature-column,
            .plan-column {
                flex: none;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .plan-column.popular-column {
                border-left: none;
                border-right: none;
                border-top: 2px solid rgba(255, 255, 255, 0.4);
                border-bottom: 2px solid rgba(255, 255, 255, 0.4);
            }

            .plan-header-card.popular-plan {
                transform: none;
            }

            .feature-header-space {
                height: auto;
            }

            .plan-toggle-wrapper {
                gap: 0.75rem;
            }

            .plan-toggle-label {
                font-size: 0.8rem;
            }

            .trial-badge {
                font-size: 0.7rem;
                padding: 3px 10px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('planBillingToggle');
            const monthlyContainer = document.getElementById('plan-monthly-container');
            const yearlyContainer = document.getElementById('plan-yearly-container');

            if (toggle && monthlyContainer && yearlyContainer) {
                toggle.addEventListener('change', function() {
                    if (this.checked) {
                        // Switch to yearly
                        monthlyContainer.classList.add('d-none');
                        yearlyContainer.classList.remove('d-none');
                    } else {
                        // Switch to monthly
                        monthlyContainer.classList.remove('d-none');
                        yearlyContainer.classList.add('d-none');
                    }
                });
            }
        });
    </script>
@endif
<!-- [ subscription ] end -->

<!-- [ FAqs ] start -->
@if (isset($settings['faq_status']) && $settings['faq_status'] == 'on')
    <section class="faqs section-gap bg-gray-100" id="faq">
        <div class="container">
            <div class="row mb-2">
                <div class="col-xxl-6">
                    <div class="title mb-4">
                        <span class="d-block mb-2 fw-bold text-uppercase">{{ $settings['faq_title'] }}</span>
                        <h2 class="mb-4">{!! $settings['faq_heading'] !!}</h2>
                        <p>{!! $settings['faq_description'] !!}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="accordion accordion-flush" id="accordionFlushExample">
                        @if (is_array(json_decode($settings['faqs'], true)) || is_object(json_decode($settings['faqs'], true)))
                            @foreach (json_decode($settings['faqs'], true) as $key => $value)
                                @if ($key % 2 == 0)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="{{ 'flush-heading' . $key }}">
                                            <button class="accordion-button collapsed fw-bold" type="button"
                                                data-bs-toggle="collapse" data-bs-target="{{ '#flush-' . $key }}"
                                                aria-expanded="false" aria-controls="{{ 'flush-collapse' . $key }}">
                                                {!! $value['faq_questions'] !!}
                                            </button>
                                        </h2>
                                        <div id="{{ 'flush-' . $key }}" class="accordion-collapse collapse"
                                            aria-labelledby="{{ 'flush-heading' . $key }}"
                                            data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body">
                                                {!! $value['faq_answer'] !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="accordion accordion-flush" id="accordionFlushExample2">
                        @if (is_array(json_decode($settings['faqs'], true)) || is_object(json_decode($settings['faqs'], true)))
                            @foreach (json_decode($settings['faqs'], true) as $key => $value)
                                @if ($key % 2 != 0)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="{{ 'flush-heading' . $key }}">
                                            <button class="accordion-button collapsed fw-bold" type="button"
                                                data-bs-toggle="collapse" data-bs-target="{{ '#flush-' . $key }}"
                                                aria-expanded="false" aria-controls="{{ 'flush-collapse' . $key }}">
                                                {!! $value['faq_questions'] !!}
                                            </button>
                                        </h2>
                                        <div id="{{ 'flush-' . $key }}" class="accordion-collapse collapse"
                                            aria-labelledby="{{ 'flush-heading' . $key }}"
                                            data-bs-parent="#accordionFlushExample2">
                                            <div class="accordion-body">
                                                {!! $value['faq_answer'] !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
<!-- [ FAqs ] end -->