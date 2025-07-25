<!-- [ subscription ] start -->
@if (isset($settings['plan_status']) && $settings['plan_status'] == 'on')
    <section class="subscription bg-primary section-gap" id="plan">
        <div class="container">
            <div class="row mb-2 justify-content-center">
                <div class="col-xxl-6">
                    <div class="title text-center mb-4">
                        <span class="d-block mb-2 fw-bold text-uppercase">{{ __('PLAN') }}</span>
                        <h2 class="mb-4">{!! $settings['plan_heading'] !!}</h2>
                        <p>{!! $settings['plan_description'] !!}</p>
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

            <!-- Toggle for yearly/monthly (only show if yearly plans exist) -->
            @if($has_yearly_plans)
                <div class="row justify-content-center mb-4">
                    <div class="col-auto">
                        <div class="btn-group bg-white rounded-pill p-1" role="group" style="box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <button type="button" class="btn btn-outline-dark rounded-pill px-4" id="monthly-btn">Pay monthly</button>
                            <button type="button" class="btn btn-dark rounded-pill px-4" id="yearly-btn">Pay yearly (save 25%)*</button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Monthly Plans (hidden by default) -->
            <div id="monthly-plans" class="plans-container" style="display: none;">
                @if(!empty($monthly_categories))
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <div style="position: relative;">

                                <!-- Beautiful Large Most Popular Label for Monthly Plans -->
                                @if(count($monthly_plans) > 1)
                                    <div class="position-relative" style="height: 30px; margin-bottom: -1px;">
                                        <div class="position-absolute" style="top: 0; left: {{ 40 + (60 / count($monthly_plans) * 1.5) }}%; width: {{ 60 / count($monthly_plans) }}%; transform: translateX(-50%); z-index: 1000;">
                                            <div class="text-center px-3 py-2 text-white fw-bold" style="
                                                background: linear-gradient(135deg, #ff6b35, #f7931e);
                                                border-radius: 25px 25px 0 0;
                                                position: relative;
                                                box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
                                                overflow: hidden;
                                            ">
                                                <span style="position: relative; z-index: 2; font-size: 0.9rem; letter-spacing: 0.5px;">MOST POPULAR</span>
                                                <div style="
                                                    position: absolute;
                                                    bottom: -20px;
                                                    left: -10px;
                                                    right: -10px;
                                                    height: 20px;
                                                    background: linear-gradient(135deg, #ff6b35, #f7931e);
                                                    border-radius: 0 0 25px 25px;
                                                "></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- First table with prices -->
                                @if(!empty($monthly_categories))
                                    @php $first_category = array_key_first($monthly_categories); @endphp
                                    <div class="table-responsive" style="margin-bottom: 8px;">
                                        <table class="table shadow-none mb-0" style="border-radius: 15px; overflow: hidden; background-color: var(--bs-body-bg, white); border: transparent;">
                                            <thead>
                                                <tr>
                                                    <th class="text-start fw-bold" style="width: 40%; background-color: #e9ecef; color: var(--bs-body-color, inherit); border: transparent;">{{ $first_category }}</th>
                                                    @foreach($monthly_plans as $key => $plan)
                                                        @php
                                                            $display_name = str_replace(' (yearly)', '', $plan->name);
                                                            $monthly_price = intval($plan->price);
                                                        @endphp
                                                        <th class="text-center fw-bold" style="background-color: {{ $key == 1 ? '#f7931e' : '#e9ecef' }}; color: {{ $key == 1 ? 'white' : 'var(--bs-body-color, inherit)' }}; width: {{ 60 / count($monthly_plans) }}%; border: transparent; {{ $key == 0 ? 'border-left: 2px solid #dee2e6;' : '' }}">
                                                            <div class="plan-header">
                                                                <h5 class="mb-1" style="color: {{ $key == 1 ? 'white' : 'var(--bs-body-color, inherit)' }};">{{ $display_name }}</h5>
                                                                <h3 class="mb-0" style="color: {{ $key == 1 ? 'white' : 'var(--bs-body-color, inherit)' }};">{{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}{{ $monthly_price }}</h3>
                                                                <small class="{{ $key == 1 ? 'text-light' : 'text-muted' }}">/{{ $plan->duration }}</small>
                                                            </div>
                                                        </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($monthly_categories[$first_category] as $feature)
                                                    <tr>
                                                        <td class="text-start" style="width: 40%; color: var(--bs-body-color, inherit); border: transparent;">{{ $feature }}</td>
                                                        @foreach($monthly_plans as $key => $plan)
                                                            <td class="text-center" style="background-color: {{ $key == 1 ? '#fef5f0' : 'inherit' }}; width: {{ 60 / count($monthly_plans) }}%; border: transparent; {{ $key == 0 ? 'border-left: 2px solid #dee2e6;' : '' }}">
                                                                @if(str_contains($plan->description, $feature))
                                                                    <i class="ti ti-circle-check text-success fs-4"></i>
                                                                @else
                                                                    <i class="ti ti-circle-x text-muted fs-4"></i>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Other monthly tables -->
                                    @foreach($monthly_categories as $category_name => $features)
                                        @if($category_name !== $first_category)
                                            <div class="table-responsive" style="margin-bottom: 8px;">
                                                <table class="table shadow-none mb-0" style="border-radius: 15px; overflow: hidden; background-color: var(--bs-body-bg, white); border: transparent;">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-start fw-bold" style="width: 40%; background-color: #e9ecef; color: var(--bs-body-color, inherit); border: transparent;">{{ $category_name }}</th>
                                                            @foreach($monthly_plans as $key => $plan)
                                                                @php $display_name = str_replace(' (yearly)', '', $plan->name); @endphp
                                                                <th class="text-center fw-bold" style="background-color: {{ $key == 1 ? '#f7931e' : '#e9ecef' }}; color: {{ $key == 1 ? 'white' : 'var(--bs-body-color, inherit)' }}; width: {{ 60 / count($monthly_plans) }}%; border: transparent; {{ $key == 0 ? 'border-left: 2px solid #dee2e6;' : '' }}">{{ $display_name }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($features as $feature)
                                                            <tr>
                                                                <td class="text-start" style="width: 40%; color: var(--bs-body-color, inherit); border: transparent;">{{ $feature }}</td>
                                                                @foreach($monthly_plans as $key => $plan)
                                                                    <td class="text-center" style="background-color: {{ $key == 1 ? '#fef5f0' : 'inherit' }}; width: {{ 60 / count($monthly_plans) }}%; border: transparent; {{ $key == 0 ? 'border-left: 2px solid #dee2e6;' : '' }}">
                                                                        @if(str_contains($plan->description, $feature))
                                                                            <i class="ti ti-circle-check text-success fs-4"></i>
                                                                        @else
                                                                            <i class="ti ti-circle-x text-muted fs-4"></i>
                                                                        @endif
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    @endforeach

                                    <!-- Monthly buttons -->
                                    <div class="table-responsive">
                                        <table class="table shadow-none mb-0" style="border-radius: 0 0 15px 15px; overflow: hidden; background-color: var(--bs-body-bg, white); border: transparent;">
                                            <tbody>
                                                <tr>
                                                    <td class="text-start" style="width: 40%; background-color: #e9ecef; border: transparent;"></td>
                                                    @foreach($monthly_plans as $key => $plan)
                                                        <td class="text-center py-3" style="background-color: {{ $key == 1 ? '#fef5f0' : '#e9ecef' }}; width: {{ 60 / count($monthly_plans) }}%; border: transparent; {{ $key == 0 ? 'border-left: 2px solid #dee2e6;' : '' }}">
                                                            <a href="{{ Auth::check() ? route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                                               class="btn btn-dark rounded-pill px-4" style="background-color: #000; border: none;">Try for free</a>
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Yearly Plans (shown by default) -->
            @if($has_yearly_plans)
                <div id="yearly-plans" class="plans-container">
                    @if(!empty($yearly_categories))
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <div style="position: relative;">

                                    <!-- Beautiful Large Most Popular Label for Yearly Plans -->
                                    @if(count($yearly_plans) > 1)
                                        <div class="position-relative" style="height: 30px; margin-bottom: -1px;">
                                            <div class="position-absolute" style="top: 0; left: {{ 40 + (60 / count($yearly_plans) * 1.5) }}%; width: {{ 60 / count($yearly_plans) }}%; transform: translateX(-50%); z-index: 1000;">
                                                <div class="text-center px-3 py-2 text-white fw-bold" style="
                                                    background: linear-gradient(135deg, #ff6b35, #f7931e);
                                                    border-radius: 25px 25px 0 0;
                                                    position: relative;
                                                    box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
                                                    overflow: hidden;
                                                ">
                                                    <span style="position: relative; z-index: 2; font-size: 0.9rem; letter-spacing: 0.5px;">MOST POPULAR</span>
                                                    <div style="
                                                        position: absolute;
                                                        bottom: -20px;
                                                        left: -10px;
                                                        right: -10px;
                                                        height: 20px;
                                                        background: linear-gradient(135deg, #ff6b35, #f7931e);
                                                        border-radius: 0 0 25px 25px;
                                                    "></div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- First yearly table with prices -->
                                    @if(!empty($yearly_categories))
                                        @php $first_yearly_category = array_key_first($yearly_categories); @endphp
                                        <div class="table-responsive" style="margin-bottom: 8px;">
                                            <table class="table shadow-none mb-0" style="border-radius: 15px; overflow: hidden; background-color: var(--bs-body-bg, white); border: transparent;">
                                                <thead>
                                                    <tr>
                                                        <th class="text-start fw-bold" style="width: 40%; background-color: #e9ecef; color: var(--bs-body-color, inherit); border: transparent;">{{ $first_yearly_category }}</th>
                                                        @foreach($yearly_plans as $key => $plan)
                                                            @php
                                                                $display_name = str_replace(' (yearly)', '', $plan->name);
                                                                $monthly_price = round($plan->price / 12, 2);
                                                            @endphp
                                                            <th class="text-center fw-bold" style="background-color: {{ $key == 1 ? '#f7931e' : '#e9ecef' }}; color: {{ $key == 1 ? 'white' : 'var(--bs-body-color, inherit)' }}; width: {{ 60 / count($yearly_plans) }}%; border: transparent; {{ $key == 0 ? 'border-left: 2px solid #dee2e6;' : '' }}">
                                                                <div class="plan-header">
                                                                    <h5 class="mb-1" style="color: {{ $key == 1 ? 'white' : 'var(--bs-body-color, inherit)' }};">{{ $display_name }}</h5>
                                                                    <h3 class="mb-0" style="color: {{ $key == 1 ? 'white' : 'var(--bs-body-color, inherit)' }};">{{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}{{ $monthly_price }}</h3>
                                                                    <small class="{{ $key == 1 ? 'text-light' : 'text-muted' }}">/month<br><span style="font-size: 0.8em;">billed once yearly</span></small>
                                                                </div>
                                                            </th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($yearly_categories[$first_yearly_category] as $feature)
                                                        <tr>
                                                            <td class="text-start" style="width: 40%; color: var(--bs-body-color, inherit); border: transparent;">{{ $feature }}</td>
                                                            @foreach($yearly_plans as $key => $plan)
                                                                <td class="text-center" style="background-color: {{ $key == 1 ? '#fef5f0' : 'inherit' }}; width: {{ 60 / count($yearly_plans) }}%; border: transparent; {{ $key == 0 ? 'border-left: 2px solid #dee2e6;' : '' }}">
                                                                    @if(str_contains($plan->description, $feature))
                                                                        <i class="ti ti-circle-check text-success fs-4"></i>
                                                                    @else
                                                                        <i class="ti ti-circle-x text-muted fs-4"></i>
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Other yearly tables -->
                                        @foreach($yearly_categories as $category_name => $features)
                                            @if($category_name !== $first_yearly_category)
                                                <div class="table-responsive" style="margin-bottom: 8px;">
                                                    <table class="table shadow-none mb-0" style="border-radius: 15px; overflow: hidden; background-color: var(--bs-body-bg, white); border: transparent;">
                                                        <thead>
                                                            <tr>
                                                                <th class="text-start fw-bold" style="width: 40%; background-color: #e9ecef; color: var(--bs-body-color, inherit); border: transparent;">{{ $category_name }}</th>
                                                                @foreach($yearly_plans as $key => $plan)
                                                                    @php $display_name = str_replace(' (yearly)', '', $plan->name); @endphp
                                                                    <th class="text-center fw-bold" style="background-color: {{ $key == 1 ? '#f7931e' : '#e9ecef' }}; color: {{ $key == 1 ? 'white' : 'var(--bs-body-color, inherit)' }}; width: {{ 60 / count($yearly_plans) }}%; border: transparent; {{ $key == 0 ? 'border-left: 2px solid #dee2e6;' : '' }}">{{ $display_name }}</th>
                                                                @endforeach
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($features as $feature)
                                                                <tr>
                                                                    <td class="text-start" style="width: 40%; color: var(--bs-body-color, inherit); border: transparent;">{{ $feature }}</td>
                                                                    @foreach($yearly_plans as $key => $plan)
                                                                        <td class="text-center" style="background-color: {{ $key == 1 ? '#fef5f0' : 'inherit' }}; width: {{ 60 / count($yearly_plans) }}%; border: transparent; {{ $key == 0 ? 'border-left: 2px solid #dee2e6;' : '' }}">
                                                                            @if(str_contains($plan->description, $feature))
                                                                                <i class="ti ti-circle-check text-success fs-4"></i>
                                                                            @else
                                                                                <i class="ti ti-circle-x text-muted fs-4"></i>
                                                                            @endif
                                                                        </td>
                                                                    @endforeach
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        @endforeach

                                        <!-- Yearly buttons -->
                                        <div class="table-responsive">
                                            <table class="table shadow-none mb-0" style="border-radius: 0 0 15px 15px; overflow: hidden; background-color: var(--bs-body-bg, white); border: transparent;">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-start" style="width: 40%; background-color: #e9ecef; border: transparent;"></td>
                                                        @foreach($yearly_plans as $key => $plan)
                                                            <td class="text-center py-3" style="background-color: {{ $key == 1 ? '#fef5f0' : '#e9ecef' }}; width: {{ 60 / count($yearly_plans) }}%; border: transparent; {{ $key == 0 ? 'border-left: 2px solid #dee2e6;' : '' }}">
                                                                <a href="{{ Auth::check() ? route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) : route('register', ['plan' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                                                   class="btn btn-dark rounded-pill px-4" style="background-color: #000; border: none;">Try for free</a>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <!-- JavaScript for toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const monthlyBtn = document.getElementById('monthly-btn');
            const yearlyBtn = document.getElementById('yearly-btn');
            const monthlyPlans = document.getElementById('monthly-plans');
            const yearlyPlans = document.getElementById('yearly-plans');

            if (monthlyBtn && yearlyBtn && monthlyPlans && yearlyPlans) {
                monthlyBtn.addEventListener('click', function() {
                    monthlyBtn.className = 'btn btn-dark rounded-pill px-4';
                    yearlyBtn.className = 'btn btn-outline-dark rounded-pill px-4';
                    monthlyPlans.style.display = 'block';
                    yearlyPlans.style.display = 'none';
                });

                yearlyBtn.addEventListener('click', function() {
                    yearlyBtn.className = 'btn btn-dark rounded-pill px-4';
                    monthlyBtn.className = 'btn btn-outline-dark rounded-pill px-4';
                    monthlyPlans.style.display = 'none';
                    yearlyPlans.style.display = 'block';
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