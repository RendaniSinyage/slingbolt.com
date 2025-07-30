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
                                    <input type="checkbox" id="planBillingToggle" class="plan-toggle-input">
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

            <!-- Pricing Cards -->
            <div class="row justify-content-center gy-3 gx-4">
                <!-- Monthly Plans -->
                <div id="plan-monthly-pricing" class="col-12 {{ $has_yearly_plans ? 'd-none' : '' }}">
                    <div class="row justify-content-center gy-3 gx-4">
                        @if($monthly_plans->count() > 0)
                            @foreach($monthly_plans as $key => $plan)
                                @php
                                    $display_name = str_replace(' (yearly)', '', $plan->name);
                                    $monthly_price = intval($plan->price);
                                    $is_popular = $key == 1;
                                @endphp
                                
                                <div class="col-lg-4 col-sm-6">
                                    <div class="card plan-pricing-card {{ $is_popular ? 'plan-popular' : '' }}">
                                        @if($is_popular)
                                            <div class="plan-popular-badge">
                                                <span>Most Popular</span>
                                            </div>
                                        @endif
                                        
                                        <div class="card-body text-center">
                                            <h3 class="plan-card-name">{{ $display_name }}</h3>
                                            <div class="plan-price-container">
                                                <span class="plan-currency">{{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}</span>
                                                <span class="plan-price">{{ $monthly_price }}</span>
                                                <span class="plan-period">/{{ $plan->duration }}</span>
                                            </div>
                                            <p class="plan-card-description">Perfect for getting started</p>
                                            
                                            <a href="{{ Auth::check() ? route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                               class="btn {{ $is_popular ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill d-inline-flex align-items-center">
                                                Get Started
                                                <i data-feather="arrow-right" class="ms-2"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Yearly Plans -->
                @if($has_yearly_plans)
                    <div id="plan-yearly-pricing" class="col-12">
                        <div class="row justify-content-center gy-3 gx-4">
                            @foreach($yearly_plans as $key => $plan)
                                @php
                                    $display_name = str_replace(' (yearly)', '', $plan->name);
                                    $yearly_price = intval($plan->price);
                                    $monthly_equivalent = round($yearly_price / 12, 2);
                                    $is_popular = $key == 1;
                                @endphp
                                
                                <div class="col-lg-4 col-sm-6">
                                    <div class="card plan-pricing-card {{ $is_popular ? 'plan-popular' : '' }}">
                                        @if($is_popular)
                                            <div class="plan-popular-badge">
                                                <span>Most Popular</span>
                                            </div>
                                        @endif
                                        
                                        <div class="card-body text-center">
                                            <h3 class="plan-card-name">{{ $display_name }}</h3>
                                            <div class="plan-price-container">
                                                <span class="plan-currency">{{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}</span>
                                                <span class="plan-price">{{ $monthly_equivalent }}</span>
                                                <span class="plan-period">/month</span>
                                            </div>
                                            <p class="plan-card-description">Billed annually ({{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}{{ $yearly_price }})</p>
                                            
                                            <a href="{{ Auth::check() ? route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                               class="btn {{ $is_popular ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill d-inline-flex align-items-center">
                                                Get Started
                                                <i data-feather="arrow-right" class="ms-2"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Feature Comparison Tables -->
            <div class="plan-feature-comparison mt-5">
                <!-- Monthly Features -->
                <div id="plan-monthly-features" class="plan-feature-tables {{ $has_yearly_plans ? 'd-none' : '' }}">
                    @if(!empty($monthly_categories))
                        @php $category_index = 0; @endphp
                        @foreach($monthly_categories as $category_name => $features)
                            @php $is_first = $category_index === 0; @endphp
                            <div class="plan-feature-category mb-4">
                                <div class="plan-category-header" data-bs-toggle="collapse" data-bs-target="#plan-monthly-category-{{ $category_index }}" aria-expanded="{{ $is_first ? 'true' : 'false' }}">
                                    <h4 class="plan-category-title">{{ $category_name }}</h4>
                                    <i class="ti ti-chevron-down plan-collapse-icon"></i>
                                </div>
                                <div class="collapse {{ $is_first ? 'show' : '' }}" id="plan-monthly-category-{{ $category_index }}">
                                    <div class="table-responsive">
                                        <table class="table plan-feature-table">
                                            <thead>
                                                <tr>
                                                    <th class="plan-feature-header">Features</th>
                                                    @foreach($monthly_plans as $key => $plan)
                                                        @php $display_name = str_replace(' (yearly)', '', $plan->name); @endphp
                                                        <th class="plan-plan-header {{ $key == 1 ? 'plan-popular-plan' : '' }}">{{ $display_name }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($features as $feature)
                                                    <tr>
                                                        <td class="plan-feature-name">{{ $feature }}</td>
                                                        @foreach($monthly_plans as $key => $plan)
                                                            <td class="text-center">
                                                                @if(str_contains($plan->description, $feature))
                                                                    <i class="ti ti-check text-success fs-5"></i>
                                                                @else
                                                                    <i class="ti ti-x text-muted fs-5"></i>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @php $category_index++; @endphp
                        @endforeach
                    @endif
                </div>

                <!-- Yearly Features -->
                @if($has_yearly_plans)
                    <div id="plan-yearly-features" class="plan-feature-tables">
                        @if(!empty($yearly_categories))
                            @php $category_index = 0; @endphp
                            @foreach($yearly_categories as $category_name => $features)
                                @php $is_first = $category_index === 0; @endphp
                                <div class="plan-feature-category mb-4">
                                    <div class="plan-category-header" data-bs-toggle="collapse" data-bs-target="#plan-yearly-category-{{ $category_index }}" aria-expanded="{{ $is_first ? 'true' : 'false' }}">
                                        <h4 class="plan-category-title">{{ $category_name }}</h4>
                                        <i class="ti ti-chevron-down plan-collapse-icon"></i>
                                    </div>
                                    <div class="collapse {{ $is_first ? 'show' : '' }}" id="plan-yearly-category-{{ $category_index }}">
                                        <div class="table-responsive">
                                            <table class="table plan-feature-table">
                                                <thead>
                                                    <tr>
                                                        <th class="plan-feature-header">Features</th>
                                                        @foreach($yearly_plans as $key => $plan)
                                                            @php $display_name = str_replace(' (yearly)', '', $plan->name); @endphp
                                                            <th class="plan-plan-header {{ $key == 1 ? 'plan-popular-plan' : '' }}">{{ $display_name }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($features as $feature)
                                                        <tr>
                                                            <td class="plan-feature-name">{{ $feature }}</td>
                                                            @foreach($yearly_plans as $key => $plan)
                                                                <td class="text-center">
                                                                    @if(str_contains($plan->description, $feature))
                                                                        <i class="ti ti-check text-success fs-5"></i>
                                                                    @else
                                                                        <i class="ti ti-x text-muted fs-5"></i>
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @php $category_index++; @endphp
                            @endforeach
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>

    <style>
        /* Scoped Pricing Styles */
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

        .plan-pricing-card {
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .plan-pricing-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .plan-pricing-card.plan-popular {
            border-color: rgba(255, 255, 255, 0.8);
            transform: scale(1.05);
        }

        .plan-popular-badge {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 8px 24px;
            border-radius: 0 0 16px 16px;
            font-size: 0.875rem;
            font-weight: 600;
            z-index: 10;
        }

        .plan-card-name {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            margin-top: 1rem;
        }

        .plan-pricing-card.plan-popular .plan-card-name {
            margin-top: 2.5rem;
        }

        .plan-price-container {
            display: flex;
            align-items: baseline;
            justify-content: center;
            margin-bottom: 0.5rem;
        }

        .plan-currency {
            font-size: 1.25rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }

        .plan-price {
            font-size: 3rem;
            font-weight: 800;
            color: white;
            line-height: 1;
            margin: 0 4px;
        }

        .plan-period {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        .plan-card-description {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .plan-feature-comparison {
            max-width: 1200px;
            margin: 0 auto;
        }

        .plan-feature-tables {
            transition: opacity 0.3s ease;
        }

        .plan-feature-category {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .plan-category-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            color: white;
            padding: 1.5rem 2rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            user-select: none;
        }

        .plan-category-header:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.25), rgba(255, 255, 255, 0.15));
        }

        .plan-category-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: white;
        }

        .plan-collapse-icon {
            font-size: 1.5rem;
            transition: transform 0.3s ease;
            color: white;
        }

        .plan-category-header[aria-expanded="true"] .plan-collapse-icon {
            transform: rotate(180deg);
        }

        .plan-feature-table {
            background: white;
            border: none;
            margin-bottom: 0;
        }

        .plan-feature-table thead th {
            background: #f8fafc;
            border: none;
            padding: 1.5rem 1rem;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }

        .plan-feature-header {
            width: 40%;
            color: #1a202c !important;
            font-size: 1.125rem;
        }

        .plan-plan-header {
            text-align: center;
            width: 20%;
        }

        .plan-plan-header.plan-popular-plan {
            background: linear-gradient(135deg, #667eea, #8b5cf6);
            color: white !important;
        }

        .plan-feature-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.2s ease;
        }

        .plan-feature-table tbody tr:hover {
            background: #f8fafc;
        }

        .plan-feature-table tbody td {
            padding: 1rem;
            border: none;
            vertical-align: middle;
        }

        .plan-feature-name {
            font-weight: 500;
            color: #374151;
        }

        @media (max-width: 768px) {
            .plan-toggle-wrapper {
                gap: 0.75rem;
            }

            .plan-toggle-label {
                font-size: 0.8rem;
            }

            .plan-pricing-card.plan-popular {
                transform: none;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('planBillingToggle');
            const monthlyPricing = document.getElementById('plan-monthly-pricing');
            const yearlyPricing = document.getElementById('plan-yearly-pricing');
            const monthlyFeatures = document.getElementById('plan-monthly-features');
            const yearlyFeatures = document.getElementById('plan-yearly-features');

            if (toggle && monthlyPricing && yearlyPricing) {
                toggle.addEventListener('change', function() {
                    if (this.checked) {
                        // Switch to yearly
                        monthlyPricing.classList.add('d-none');
                        yearlyPricing.classList.remove('d-none');
                        if (monthlyFeatures) monthlyFeatures.classList.add('d-none');
                        if (yearlyFeatures) yearlyFeatures.classList.remove('d-none');
                    } else {
                        // Switch to monthly
                        monthlyPricing.classList.remove('d-none');
                        yearlyPricing.classList.add('d-none');
                        if (monthlyFeatures) monthlyFeatures.classList.remove('d-none');
                        if (yearlyFeatures) yearlyFeatures.classList.add('d-none');
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