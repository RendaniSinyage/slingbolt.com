/* Final CTA Section */
        .final-cta-section {
            background: white;
            padding: 3rem 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .final-cta-section.hidden {
            display: none;
        }

        .final-cta-section .btn {
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .final-cta-section .btn:hover {
            transform: translateY(-3px);
        }        /* Get Started Buttons */
        .get-started-section {
            text-align: center;
            padding: 2rem 0;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .get-started-section.hidden {
            display: none;
        }<!-- [ subscription ] start -->
@if (isset($settings['plan_status']) && $settings['plan_status'] == 'on')
    <section class="subscription bg-gray-50 section-gap" id="plan">
        <div class="container">
            <!-- Header -->
            <div class="row mb-5 justify-content-center">
                <div class="col-xxl-6">
                    <div class="title text-center">
                        <span class="d-block mb-2 fw-bold text-uppercase text-primary">{{ __('PRICING') }}</span>
                        <h2 class="mb-4 display-5 fw-bold">{!! $settings['plan_heading'] !!}</h2>
                        <p class="lead text-muted">{!! $settings['plan_description'] !!}</p>
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

                        if(strpos($line, '##') === 0) {
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

                        if(strpos($line, '##') === 0) {
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
                        <div class="billing-toggle">
                            <div class="toggle-wrapper">
                                <span class="toggle-label monthly">Monthly</span>
                                <div class="toggle-switch">
                                    <input type="checkbox" id="billingToggle" class="toggle-input">
                                    <label for="billingToggle" class="toggle-slider"></label>
                                </div>
                                <span class="toggle-label yearly">
                                    Annual 
                                    <span class="save-badge">Save 25%</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Pricing Cards -->
            <div class="pricing-container">
                <!-- Monthly Plans -->
                <div id="monthly-pricing" class="pricing-grid {{ $has_yearly_plans ? 'hidden' : '' }}">
                    @if($monthly_plans->count() > 0)
                        @foreach($monthly_plans as $key => $plan)
                            @php
                                $display_name = str_replace(' (yearly)', '', $plan->name);
                                $monthly_price = intval($plan->price);
                                $is_popular = $key == 1;
                            @endphp
                            
                            <div class="pricing-card {{ $is_popular ? 'popular' : '' }}">
                                @if($is_popular)
                                    <div class="popular-badge">
                                        <span>Most Popular</span>
                                    </div>
                                @endif
                                
                                <div class="card-header">
                                    <h3 class="plan-name">{{ $display_name }}</h3>
                                    <div class="price-container">
                                        <span class="currency">{{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}</span>
                                        <span class="price">{{ $monthly_price }}</span>
                                        <span class="period">/{{ $plan->duration }}</span>
                                    </div>
                                    <p class="plan-description">Perfect for getting started</p>
                                </div>
                                
                                <div class="card-footer">
                                    <a href="{{ Auth::check() ? route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                       class="btn btn-plan {{ $is_popular ? 'btn-primary' : 'btn-outline' }}">
                                        Get Started
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Yearly Plans -->
                @if($has_yearly_plans)
                    <div id="yearly-pricing" class="pricing-grid">
                        @foreach($yearly_plans as $key => $plan)
                            @php
                                $display_name = str_replace(' (yearly)', '', $plan->name);
                                $yearly_price = intval($plan->price);
                                $monthly_equivalent = round($yearly_price / 12, 2);
                                $is_popular = $key == 1;
                            @endphp
                            
                            <div class="pricing-card {{ $is_popular ? 'popular' : '' }}">
                                @if($is_popular)
                                    <div class="popular-badge">
                                        <span>Most Popular</span>
                                    </div>
                                @endif
                                
                                <div class="card-header">
                                    <h3 class="plan-name">{{ $display_name }}</h3>
                                    <div class="price-container">
                                        <span class="currency">{{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}</span>
                                        <span class="price">{{ $monthly_equivalent }}</span>
                                        <span class="period">/month</span>
                                    </div>
                                    <p class="plan-description">Billed annually ({{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}{{ $yearly_price }})</p>
                                </div>
                                
                                <div class="card-footer">
                                    <a href="{{ Auth::check() ? route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                       class="btn btn-plan {{ $is_popular ? 'btn-primary' : 'btn-outline' }}">
                                        Get Started
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Feature Comparison Tables -->
            <div class="feature-comparison mt-5">

                <!-- Monthly Features -->
                <div id="monthly-features" class="feature-tables {{ $has_yearly_plans ? 'hidden' : '' }}">
                    @if(!empty($monthly_categories))
                        @php $category_index = 0; @endphp
                        @foreach($monthly_categories as $category_name => $features)
                            @php $is_first = $category_index === 0; @endphp
                            <div class="feature-category mb-4">
                                <div class="category-header" data-bs-toggle="collapse" data-bs-target="#monthly-category-{{ $category_index }}" aria-expanded="{{ $is_first ? 'true' : 'false' }}">
                                    <h4 class="category-title">{{ $category_name }}</h4>
                                    <i class="ti ti-chevron-down collapse-icon"></i>
                                </div>
                                <div class="collapse {{ $is_first ? 'show' : '' }}" id="monthly-category-{{ $category_index }}">
                                    <div class="table-responsive">
                                        <table class="table feature-table">
                                            <thead>
                                                <tr>
                                                    <th class="feature-header">Features</th>
                                                    @foreach($monthly_plans as $key => $plan)
                                                        @php $display_name = str_replace(' (yearly)', '', $plan->name); @endphp
                                                        <th class="plan-header {{ $key == 1 ? 'popular-plan' : '' }}">{{ $display_name }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($features as $feature)
                                                    <tr>
                                                        <td class="feature-name">{{ $feature }}</td>
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
                    <div id="yearly-features" class="feature-tables">
                        @if(!empty($yearly_categories))
                            @php $category_index = 0; @endphp
                            @foreach($yearly_categories as $category_name => $features)
                                @php $is_first = $category_index === 0; @endphp
                                <div class="feature-category mb-4">
                                    <div class="category-header" data-bs-toggle="collapse" data-bs-target="#yearly-category-{{ $category_index }}" aria-expanded="{{ $is_first ? 'true' : 'false' }}">
                                        <h4 class="category-title">{{ $category_name }}</h4>
                                        <i class="ti ti-chevron-down collapse-icon"></i>
                                    </div>
                                    <div class="collapse {{ $is_first ? 'show' : '' }}" id="yearly-category-{{ $category_index }}">
                                        <div class="table-responsive">
                                            <table class="table feature-table">
                                                <thead>
                                                    <tr>
                                                        <th class="feature-header">Features</th>
                                                        @foreach($yearly_plans as $key => $plan)
                                                            @php $display_name = str_replace(' (yearly)', '', $plan->name); @endphp
                                                            <th class="plan-header {{ $key == 1 ? 'popular-plan' : '' }}">{{ $display_name }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($features as $feature)
                                                        <tr>
                                                            <td class="feature-name">{{ $feature }}</td>
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

                <!-- Get Started Buttons Below All Categories -->
                <div class="final-cta-section mt-5">
                    <div class="row justify-content-center">
                        @if($has_yearly_plans)
                            <div id="monthly-final-cta" class="col-12 {{ $has_yearly_plans ? 'hidden' : '' }}">
                                <div class="d-flex justify-content-center gap-3 flex-wrap">
                                    @foreach($monthly_plans as $key => $plan)
                                        <a href="{{ Auth::check() ? route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                           class="btn {{ $key == 1 ? 'btn-primary' : 'btn-outline-primary' }} btn-lg px-5 py-3">
                                            Get {{ str_replace(' (yearly)', '', $plan->name) }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                            <div id="yearly-final-cta" class="col-12">
                                <div class="d-flex justify-content-center gap-3 flex-wrap">
                                    @foreach($yearly_plans as $key => $plan)
                                        <a href="{{ Auth::check() ? route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                           class="btn {{ $key == 1 ? 'btn-primary' : 'btn-outline-primary' }} btn-lg px-5 py-3">
                                            Get {{ str_replace(' (yearly)', '', $plan->name) }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="col-12">
                                <div class="d-flex justify-content-center gap-3 flex-wrap">
                                    @foreach($monthly_plans as $key => $plan)
                                        <a href="{{ Auth::check() ? route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                           class="btn {{ $key == 1 ? 'btn-primary' : 'btn-outline-primary' }} btn-lg px-5 py-3">
                                            Get {{ str_replace(' (yearly)', '', $plan->name) }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .subscription {
            padding: 5rem 0;
        }

        .billing-toggle {
            background: white;
            border-radius: 50px;
            padding: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }

        .toggle-wrapper {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .toggle-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #64748b;
            white-space: nowrap;
        }

        .toggle-label.yearly {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .save-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .toggle-switch {
            position: relative;
        }

        .toggle-input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
            background: #e2e8f0;
            border-radius: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .toggle-slider:before {
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

        .toggle-input:checked + .toggle-slider {
            background: var(--color-customColor, #667eea);
        }

        .toggle-input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }

        .pricing-container {
            position: relative;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .pricing-grid.hidden {
            display: none;
        }

        .pricing-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 2px solid transparent;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .pricing-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .pricing-card.popular {
            border-color: var(--color-customColor, #667eea);
            transform: scale(1.05);
        }

        .pricing-card.popular:hover {
            transform: scale(1.05) translateY(-8px);
        }

        .popular-badge {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, var(--color-customColor, #667eea), #8b5cf6);
            color: white;
            padding: 8px 24px;
            border-radius: 0 0 16px 16px;
            font-size: 0.875rem;
            font-weight: 600;
            z-index: 10;
        }

        .card-header {
            padding: 2rem 2rem 1rem;
            text-align: center;
        }

        .pricing-card.popular .card-header {
            padding-top: 3rem;
        }

        .plan-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
        }

        .price-container {
            display: flex;
            align-items: baseline;
            justify-content: center;
            margin-bottom: 0.5rem;
        }

        .currency {
            font-size: 1.25rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }

        .price {
            font-size: 3.5rem;
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
        }

        .card-footer {
            padding: 1.5rem 2rem 2rem;
        }

        .btn-plan {
            width: 100%;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-customColor, #667eea), #8b5cf6);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: var(--color-customColor, #667eea);
            border: 2px solid var(--color-customColor, #667eea);
        }

        .btn-outline:hover {
            background: var(--color-customColor, #667eea);
            color: white;
            transform: translateY(-2px);
        }

        /* Feature Comparison Tables */
        .feature-comparison {
            max-width: 1200px;
            margin: 0 auto;
        }
        .category-header {
            background: linear-gradient(135deg, var(--color-customColor, #667eea), #8b5cf6);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 16px 16px 0 0;
            cursor: pointer;
            display: flex;
            justify-content: between;
            align-items: center;
            transition: all 0.3s ease;
            user-select: none;
        }

        .category-header:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .category-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            flex: 1;
            color: white;
        }

        .collapse-icon {
            font-size: 1.5rem;
            transition: transform 0.3s ease;
            color: white;
        }

        .category-header[aria-expanded="true"] .collapse-icon {
            transform: rotate(180deg);
        }

        .feature-category {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .feature-comparison {
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-tables {
            transition: opacity 0.3s ease;
        }

        .feature-tables.hidden {
            display: none;
        }

        .feature-table {
            background: white;
            border-radius: 0 0 16px 16px;
            border: none;
            margin-bottom: 0;
        }

        .feature-table thead th {
            background: #f8fafc;
            border: none;
            padding: 1.5rem 1rem;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }

        .feature-header {
            width: 40%;
            color: #1a202c !important;
            font-size: 1.125rem;
        }

        .plan-header {
            text-align: center;
            width: 20%;
        }

        .plan-header.popular-plan {
            background: linear-gradient(135deg, var(--color-customColor, #667eea), #8b5cf6);
            color: white !important;
            position: relative;
        }

        .feature-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.2s ease;
        }

        .feature-table tbody tr:hover {
            background: #f8fafc;
        }

        .feature-table tbody tr:last-child {
            border-bottom: none;
        }

        .feature-table tbody td {
            padding: 1rem;
            border: none;
            vertical-align: middle;
        }

        .feature-name {
            font-weight: 500;
            color: #374151;
        }

        .feature-table .ti-check {
            color: #10b981 !important;
        }

        .feature-table .ti-x {
            color: #d1d5db !important;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .pricing-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .pricing-card.popular {
                transform: none;
            }

            .pricing-card.popular:hover {
                transform: translateY(-8px);
            }

            .subscription {
                padding: 3rem 0;
            }

            .toggle-wrapper {
                gap: 0.75rem;
            }

            .toggle-label {
                font-size: 0.8rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('billingToggle');
            const monthlyPricing = document.getElementById('monthly-pricing');
            const yearlyPricing = document.getElementById('yearly-pricing');
            const monthlyFeatures = document.getElementById('monthly-features');
            const yearlyFeatures = document.getElementById('yearly-features');

            if (toggle && monthlyPricing && yearlyPricing) {
                toggle.addEventListener('change', function() {
                    if (this.checked) {
                        // Switch to yearly
                        monthlyPricing.classList.add('hidden');
                        yearlyPricing.classList.remove('hidden');
                        if (monthlyFeatures) monthlyFeatures.classList.add('hidden');
                        if (yearlyFeatures) yearlyFeatures.classList.remove('hidden');
                        
                        // Switch final CTA buttons
                        const monthlyFinalCta = document.getElementById('monthly-final-cta');
                        const yearlyFinalCta = document.getElementById('yearly-final-cta');
                        if (monthlyFinalCta) monthlyFinalCta.classList.add('hidden');
                        if (yearlyFinalCta) yearlyFinalCta.classList.remove('hidden');
                    } else {
                        // Switch to monthly
                        monthlyPricing.classList.remove('hidden');
                        yearlyPricing.classList.add('hidden');
                        if (monthlyFeatures) monthlyFeatures.classList.remove('hidden');
                        if (yearlyFeatures) yearlyFeatures.classList.add('hidden');
                        
                        // Switch final CTA buttons
                        const monthlyFinalCta = document.getElementById('monthly-final-cta');
                        const yearlyFinalCta = document.getElementById('yearly-final-cta');
                        if (monthlyFinalCta) monthlyFinalCta.classList.remove('hidden');
                        if (yearlyFinalCta) yearlyFinalCta.classList.add('hidden');
                    }
                });
            }
        });
    </script>
@endif
<!-- [ subscription ] end -->