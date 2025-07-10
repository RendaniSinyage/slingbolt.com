@extends('layouts.admin')
@section('page-title')
    {{ __('System Settings') }}
@endsection
@php
    use App\Models\Utility;
    use App\Models\WebhookSetting;
    $logo = \App\Models\Utility::get_file('uploads/logo');

    $logo_light = !empty($setting['company_logo_light']) ? $setting['company_logo_light'] : '';
    $logo_dark = !empty($setting['company_logo_dark']) ? $setting['company_logo_dark'] : '';
    $company_favicon = !empty($setting['company_favicon']) ? $setting['company_favicon'] : '';

    $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';
    $SITE_RTL = isset($setting['SITE_RTL']) ? $setting['SITE_RTL'] : 'off';

    $currantLang = Utility::languages();
    $lang = \App\Models\Utility::getValByName('default_language');
    $webhookSetting = WebhookSetting::where('created_by', '=', \Auth::user()->creatorId())->get();

@endphp

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('System Settings') }}</li>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
@endpush

@push('script-page')
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
    <script>
        $('.summernote-simple0').on('summernote.blur', function() {
            $.ajax({
                url: "{{ route('offerlatter.update', $offerlang) }}",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    content: $(this).val()
                },
                type: 'POST',
                success: function(response) {
                    if (response.is_success) {
                        show_toastr('success', response.success, 'success');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                },
                error: function(response) {

                    response = response.responseJSON;
                    if (response.is_success) {
                        show_toastr('error', response.error, 'error');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                }
            })
        });
        $('.summernote-simple1').on('summernote.blur', function() {
            $.ajax({
                url: "{{ route('joiningletter.update', $joininglang) }}",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    content: $(this).val()
                },
                type: 'POST',
                success: function(response) {
                    if (response.is_success) {
                        show_toastr('success', response.success, 'success');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                },
                error: function(response) {

                    response = response.responseJSON;
                    if (response.is_success) {
                        show_toastr('error', response.error, 'error');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                }
            })
        });
        $('.summernote-simple2').on('summernote.blur', function() {
            $.ajax({
                url: "{{ route('experiencecertificate.update', $explang) }}",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    content: $(this).val()
                },
                type: 'POST',
                success: function(response) {
                    if (response.is_success) {
                        show_toastr('success', response.success, 'success');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                },
                error: function(response) {

                    response = response.responseJSON;
                    if (response.is_success) {
                        show_toastr('error', response.error, 'error');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                }
            })
        });
        $('.summernote-simple3').on('summernote.blur', function() {
            $.ajax({
                url: "{{ route('noc.update', $noclang) }}",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    content: $(this).val()
                },
                type: 'POST',
                success: function(response) {
                    if (response.is_success) {
                        show_toastr('success', response.success, 'success');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                },
                error: function(response) {

                    response = response.responseJSON;
                    if (response.is_success) {
                        show_toastr('error', response.error, 'error');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                }
            })
        });

        //footer notes
        $('.summernote-simple4').on('summernote.blur', function() {

            $.ajax({
                url: "{{ route('system.settings.footernote') }}",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    notes: $(this).val()
                },
                type: 'POST',
                success: function(response) {
                    if (response.is_success) {
                        // show_toastr('Success', response.success,'success');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                },
                error: function(response) {
                    response = response.responseJSON;
                    if (response.is_success) {
                        show_toastr('error', response.error, 'error');
                    } else {
                        show_toastr('error', response, 'error');
                    }
                }
            })
        });
    </script>

    <script>
        if ($('#cust-darklayout').length > 0) {
            var custthemedark = document.querySelector("#cust-darklayout");
            custthemedark.addEventListener("click", function() {
                if (custthemedark.checked) {
                    $('#main-style-link').attr('href', '{{ config('app.url') }}' +
                        '/public/assets/css/style-dark.css');
                    document.body.style.background = 'linear-gradient(141.55deg, #22242C 3.46%, #22242C 99.86%)';

                    $('.dash-sidebar .main-logo a img').attr('src', '{{ $logo . $logo_light }}');

                } else {
                    $('#main-style-link').attr('href', '{{ config('app.url') }}' + '/public/assets/css/style.css');
                    document.body.style.setProperty('background',
                        'linear-gradient(141.55deg, rgba(240, 244, 243, 0) 3.46%, #f0f4f3 99.86%)', 'important');

                    $('.dash-sidebar .main-logo a img').attr('src', '{{ $logo . $logo_dark }}');

                }
            });
        }
        if ($('#cust-theme-bg').length > 0) {
            var custthemebg = document.querySelector("#cust-theme-bg");
            custthemebg.addEventListener("click", function() {
                if (custthemebg.checked) {
                    document.querySelector(".dash-sidebar").classList.add("transprent-bg");
                    document
                        .querySelector(".dash-header:not(.dash-mob-header)")
                        .classList.add("transprent-bg");
                } else {
                    document.querySelector(".dash-sidebar").classList.remove("transprent-bg");
                    document
                        .querySelector(".dash-header:not(.dash-mob-header)")
                        .classList.remove("transprent-bg");
                }
            });
        }
    </script>

    <script>
        $(document).on("change", "select[name='invoice_template'], input[name='invoice_color']", function() {
            var template = $("select[name='invoice_template']").val();
            var color = $("input[name='invoice_color']:checked").val();
            $('#invoice_frame').attr('src', '{{ url('/invoices/preview') }}/' + template + '/' + color);
        });

        $(document).on("change", "select[name='proposal_template'], input[name='proposal_color']", function() {
            var template = $("select[name='proposal_template']").val();
            var color = $("input[name='proposal_color']:checked").val();
            $('#proposal_frame').attr('src', '{{ url('/proposal/preview') }}/' + template + '/' + color);
        });

        $(document).on("chnge", "select[name='bill_template'], input[name='bill_color']", function() {
            var template = $("select[name='bill_template']").val();
            var color = $("input[name='bill_color']:checked").val();
            $('#bill_frame').attr('src', '{{ url('/bill/preview') }}/' + template + '/' + color);
        });
    </script>

    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300,
        })



        $('.colorPicker').on('click', function(e) {
            $('body').removeClass('custom-color');
            if (/^theme-\d+$/) {
                $('body').removeClassRegex(/^theme-\d+$/);
            }
            $('body').addClass('custom-color');
            $('.themes-color-change').removeClass('active_color');
            $(this).addClass('active_color');
            const input = document.getElementById("color-picker");
            setColor();
            input.addEventListener("input", setColor);

            function setColor() {
                $(':root').css('--color-customColor', input.value);
            }

            $(`input[name='color_flag`).val('true');
        });


        $('.themes-color-change').on('click', function() {

            $(`input[name='color_flag`).val('false');

            var color_val = $(this).data('value');
            $('body').removeClass('custom-color');
            if (/^theme-\d+$/) {
                $('body').removeClassRegex(/^theme-\d+$/);
            }
            $('body').addClass(color_val);
            $('.theme-color').prop('checked', false);
            $('.themes-color-change').removeClass('active_color');
            $('.colorPicker').removeClass('active_color');
            $(this).addClass('active_color');
            $(`input[value=${color_val}]`).prop('checked', true);
        });

        $.fn.removeClassRegex = function(regex) {
            return $(this).removeClass(function(index, classes) {
                return classes.split(/\s+/).filter(function(c) {
                    return regex.test(c);
                }).join(' ');
            });
        };
    </script>

    <script>
        document.getElementById('company_logo').onchange = function() {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image').src = src
        }
        document.getElementById('company_logo_light').onchange = function() {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image1').src = src
        }
        document.getElementById('company_favicon').onchange = function() {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image2').src = src
        }
    </script>

    <script>
        $(document).on('change', '#vat_gst_number_switch', function() {
            if ($(this).is(':checked')) {
                $('.tax_type_div').removeClass('d-none');
            } else {
                $('.tax_type_div').addClass('d-none');
            }
        });
    </script>

    <script type="text/javascript">
        $(document).on("click", '.send_email', function(e) {
            e.preventDefault();
            var title = $(this).attr('data-title');
            var size = 'md';
            var url = $(this).attr('data-url');

            if (typeof url != 'undefined') {
                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass('modal-' + size);
                $("#commonModal").modal('show');


                $.post(url, {
                    _token: '{{ csrf_token() }}',
                    mail_driver: $("#mail_driver").val(),
                    mail_host: $("#mail_host").val(),
                    mail_port: $("#mail_port").val(),
                    mail_username: $("#mail_username").val(),
                    mail_password: $("#mail_password").val(),
                    mail_encryption: $("#mail_encryption").val(),
                    mail_from_address: $("#mail_from_address").val(),
                    mail_from_name: $("#mail_from_name").val(),

                }, function(data) {
                    $('#commonModal .body').html(data);
                });
            }
        });
        $(document).on('submit', '#test_email', function(e) {
            e.preventDefault();
            var post = $(this).serialize();
            var url = $(this).attr('action');
            $.ajax({
                type: "post",
                url: url,
                data: post,
                cache: false,
                beforeSend: function() {
                    $('#test_email .btn-create').attr('disabled', 'disabled');
                },
                success: function(data) {
                    if (data.success) {
                        show_toastr('success', data.message, 'success');
                    } else {
                        show_toastr('error', data.message, 'error');
                    }
                    $('#commonModal').modal('hide');


                },
                complete: function() {
                    $('#test_email .btn-create').removeAttr('disabled');
                },
            });
        });
    </script>

    <script>
        $(document).on('keyup change', '.currency_preview', function() {
            var data = $('#currency_setting').serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route('currency.preview') }}',
                data: data,
                success: function(price) {
                    $('.preview').text(price);
                }
            });
        });
    </script>
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top setting-sidebar" style="top:30px;">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            <a href="#brand-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Brand Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#system-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('System Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#company-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Company Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#currency-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Currency Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#email-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Email Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#tracker-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Time Tracker Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#payment-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Payment Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#zoom-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Zoom Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#slack-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Slack Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#telegram-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Telegram Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#twilio-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Twilio Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#email-notification-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Email Notification Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#offer-letter-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Offer Letter Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#joining-letter-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Joining Letter Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#experience-certificate-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Experience Certificate Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#noc-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('NOC Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#google-calender"
                                class="list-group-item list-group-item-action border-0">{{ __('Google Calendar Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#webhook-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Webhook Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#ip-restriction-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('IP Restriction Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            {{-- <a href="#biometric-attendance" id="biometric-attendance-tab"
                                class="list-group-item list-group-item-action border-0">{{ __('Biometric Attendance Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a> --}}
                        </div>
                    </div>
                </div>

                <div class="col-xl-9 setting-menu-div">

                    @can('manage business settings')
    <!--Business Setting-->
    <div id="brand-settings" class="card">
        {{ Form::model($setting, ['route' => 'business.setting', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
        <div class="card-header p-3">
            <h5>{{ __('Brand Settings') }}</h5>
            <small class="text-muted">{{ __('Edit your brand details') }}</small>
        </div>
        <div class="card-body p-3">
            <div class="row row-gap-1 mb-4">
                <div class="col-lg-4 col-sm-6 col-md-6">
                    <div class="card mb-0">
                        <div class="card-header p-3">
                            <h5>{{ __('Logo dark') }}</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="setting-card setting-logo-box">
                                <div class="logo-content">
                                    <img id="image"
                                        src="{{ $logo . '/' . (isset($logo_dark) && !empty($logo_dark) ? $logo_dark : 'logo-dark.png') . '?' . time() }}">
                                </div>
                                <div class="choose-files mt-3 text-center">
                                    <label for="company_logo">
                                        <div class=" bg-primary company_logo_update"> <i
                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                        </div>
                                        <input type="file" name="company_logo_dark" id="company_logo"
                                            class="form-control file" data-filename="company_logo_update">
                                    </label>
                                </div>
                                @error('company_logo')
                                    <div class="row">
                                        <span class="invalid-logo" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6 col-md-6">
                    <div class="card mb-0">
                        <div class="card-header p-3">
                            <h5>{{ __('Logo Light') }}</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class=" setting-card setting-logo-box">
                                <div class="logo-content dark-logo">
                                    <img id="image1"
                                        src="{{ $logo . '/' . (isset($logo_light) && !empty($logo_light) ? $logo_light : 'logo-light.png') . '?' . time() }}">
                                </div>
                                <div class="choose-files mt-3 text-center">
                                    <label for="company_logo_light">
                                        <div class="bg-primary dark_logo_update"> <i
                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                        </div>
                                        <input type="file" class="form-control file"
                                            name="company_logo_light" id="company_logo_light"
                                            data-filename="dark_logo_update">
                                    </label>
                                </div>
                                @error('company_logo_light')
                                    <div class="row">
                                        <span class="invalid-logo" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6 col-md-6">
                    <div class="card mb-0">
                        <div class="card-header p-3">
                            <h5>{{ __('Favicon') }}</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class=" setting-card setting-logo-box">
                                <div class="logo-content">
                                    <img id="image2"
                                        src="{{ $logo . '/' . (isset($company_favicon) && !empty($company_favicon) ? $company_favicon : 'favicon.png') . '?' . time() }}">
                                </div>
                                <div class="choose-files mt-3 text-center">
                                    <label for="company_favicon">
                                        <div class="bg-primary company_favicon_update"> <i
                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                        </div>
                                        <input type="file" class="form-control file"
                                            id="company_favicon" name="company_favicon"
                                            data-filename="company_favicon_update">
                                    </label>
                                </div>
                                @error('logo')
                                    <div class="row">
                                        <span class="invalid-logo" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-gap-1">
                <div class="col-md-4 col-12">
                    <div class="form-group mb-0">
                        {{ Form::label('title_text', __('Title Text'), ['class' => 'form-label']) }}
                        {{ Form::text('title_text', Utility::getValByName('title_text') ? Utility::getValByName('title_text') : 'ErpGo Saas', ['class' => 'form-control', 'placeholder' => __('Title Text')]) }}
                        @error('title_text')
                            <span class="invalid-title_text" role="alert">
                                <strong class="text-danger">{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4 col-12">
                    <div class="form-group mb-0">
                        {{ Form::label('footer_text', __('Footer Text'), ['class' => 'form-label']) }}
                        {{ Form::text('footer_text', Utility::getValByName('footer_text'), ['class' => 'form-control', 'placeholder' => __('Enter Footer Text')]) }}
                        @error('footer_text')
                            <span class="invalid-footer_text" role="alert">
                                <strong class="text-danger">{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4 col-12">
                    <div class="form-group mb-0">
                        {{ Form::label('default_language', __('Default Language'), ['class' => 'form-label text-dark']) }}
                        <div class="changeLanguage">
                            <select name="default_language" id="default_language"
                                class="form-control select">
                                @foreach (\App\Models\Utility::languages() as $code => $language)
                                    <option @if ($lang == $code) selected @endif
                                        value="{{ $code }}">
                                        {{ ucFirst($language) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('default_language')
                            <span class="invalid-default_language" role="alert">
                                <strong class="text-danger">{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>

        </div>
        <div class="card-body border-top border-1 p-3">
            <h4 class="small-title h5 mb-3">{{ __('Theme Customizer') }}</h4>
            <div class="setting-card">
                <div class="row row-gap-1">
                    <div class="col-xxl-4 col-sm-6 col-12">
                        <div class="card h-100 mb-0">
                            <div class="card-header p-3">
                                <h6 class="mb-0 d-flex align-items-center gap-2">
                                    <i data-feather="credit-card"></i>{{ __('Primary color settings') }}
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="color-wrp mt-0">
                                    <div class="theme-color themes-color">
                                        <a href="#!"
                                            class="themes-color-change {{ $color == 'theme-1' ? 'active_color' : '' }}"
                                            data-value="theme-1"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-1"{{ $color == 'theme-1' ? 'checked' : '' }}>
                                        <a href="#!"
                                            class="themes-color-change {{ $color == 'theme-2' ? 'active_color' : '' }}"
                                            data-value="theme-2"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-2"{{ $color == 'theme-2' ? 'checked' : '' }}>
                                        <a href="#!"
                                            class="themes-color-change {{ $color == 'theme-3' ? 'active_color' : '' }}"
                                            data-value="theme-3"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-3"{{ $color == 'theme-3' ? 'checked' : '' }}>
                                        <a href="#!"
                                            class="themes-color-change {{ $color == 'theme-4' ? 'active_color' : '' }}"
                                            data-value="theme-4"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-4"{{ $color == 'theme-4' ? 'checked' : '' }}>
                                        <a href="#!"
                                            class="themes-color-change {{ $color == 'theme-5' ? 'active_color' : '' }}"
                                            data-value="theme-5"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-5"{{ $color == 'theme-5' ? 'checked' : '' }}>
                                        <a href="#!"
                                            class="themes-color-change {{ $color == 'theme-6' ? 'active_color' : '' }}"
                                            data-value="theme-6"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-6"{{ $color == 'theme-6' ? 'checked' : '' }}>
                                        <a href="#!"
                                            class="themes-color-change {{ $color == 'theme-7' ? 'active_color' : '' }}"
                                            data-value="theme-7"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-7"{{ $color == 'theme-7' ? 'checked' : '' }}>
                                        <a href="#!"
                                            class="themes-color-change {{ $color == 'theme-8' ? 'active_color' : '' }}"
                                            data-value="theme-8"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-8"{{ $color == 'theme-8' ? 'checked' : '' }}>
                                        <a href="#!"
                                            class="themes-color-change {{ $color == 'theme-9' ? 'active_color' : '' }}"
                                            data-value="theme-9"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-9"{{ $color == 'theme-9' ? 'checked' : '' }}>
                                        <a href="#!"
                                            class="themes-color-change {{ $color == 'theme-10' ? 'active_color' : '' }}"
                                            data-value="theme-10"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-10"{{ $color == 'theme-10' ? 'checked' : '' }}>
                                    </div>
                                    <div class="color-picker-wrp">
                                        <input type="color" value="{{ $color ? $color : '' }}"
                                            class="colorPicker {{ isset($flag) && $flag == 'true' ? 'active_color' : '' }}"
                                            name="custom_color" id="color-picker">
                                        <input type='hidden' name="color_flag"
                                            value={{ isset($flag) && $flag == 'true' ? 'true' : 'false' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-sm-6 col-12">
                        <div class="card h-100 mb-0">
                            <div class="card-header p-3">
                                <h6 class="mb-0 d-flex align-items-center gap-2">
                                    <i data-feather="layout"></i>{{ __('Sidebar settings') }}
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="form-check form-switch d-flex gap-2 flex-column p-0">
                                    <label class="form-check-label f-w-600 pl-1"
                                        for="cust-theme-bg">{{ __('Transparent layout') }}</label>
                                    <input type="checkbox" class="form-check-input mx-0"
                                        id="cust-theme-bg" name="cust_theme_bg"
                                        {{ !empty($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on' ? 'checked' : '' }} />

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-sm-6 col-12">
                        <div class="card h-100 mb-0">
                            <div class="card-header p-3">
                                <h6 class="mb-0 d-flex align-items-center gap-2">
                                    <i data-feather="sun"></i>{{ __('Layout settings') }}
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="form-check form-switch d-flex gap-2 flex-column p-0">
                                    <label class="form-check-label f-w-600 pl-1"
                                        for="cust-darklayout">{{ __('Dark Layout') }}</label>
                                    <input type="checkbox" class="form-check-input mx-0"
                                        id="cust-darklayout" name="cust_darklayout"
                                        {{ !empty($setting['cust_darklayout']) && $setting['cust_darklayout'] == 'on' ? 'checked' : '' }} />


                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-2 col-sm-6 col-12">
                        <div class="card h-100 mb-0">
                            <div class="card-header p-3">
                                <h6 class="mb-0 d-flex align-items-center gap-2">
                                    <i class="ti ti-align-right"></i>{{ __('Enable RTL') }}
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="form-check form-switch d-flex gap-2 flex-column p-0">
                                    <label class="form-check-label f-w-600 pl-1"
                                        for="SITE_RTL">{{ __('RTL Layout') }}</label>
                                    <input type="checkbox" name="SITE_RTL" id="SITE_RTL"
                                        class="form-check-input mx-0"
                                        {{ $SITE_RTL == 'on' ? 'checked="checked"' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer p-3 text-end">
            <div class="form-group mb-0">
                <input class="btn btn-print-invoice btn-primary" type="submit"
                    value="{{ __('Save Changes') }}">
            </div>
        </div>
        {{ Form::close() }}
    </div>
@endcan
                    <!--System Settings-->
                    <div id="system-settings" class="card">
                        <div class="card-header p-3">
                            <h5>{{ __('System Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your system details') }}</small>
                        </div>
                        {{ Form::model($setting, ['route' => 'system.settings', 'method' => 'post']) }}
                        <div class="card-body p-3">
                            <div class="row">
                                {{-- <div class="form-group col-md-6">
                                    {{ Form::label('site_currency', __('Currency *'), ['class' => 'form-label']) }}
                                    {{ Form::text('site_currency', $setting['site_currency'], ['class' => 'form-control font-style', 'required', 'placeholder' => __('Enter Currency')]) }}
                                    <small> {{ __('Note: Add currency code as per three-letter ISO code.') }}<br>
                                        <a href="https://stripe.com/docs/currencies"
                                            target="_blank">{{ __('You can find out how to do that here.') }}</a></small>
                                    <br>
                                    @error('site_currency')
                                        <span class="invalid-site_currency" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('site_currency_symbol', __('Currency Symbol *'), ['class' => 'form-label']) }}
                                    {{ Form::text('site_currency_symbol', null, ['class' => 'form-control']) }}
                                    @error('site_currency_symbol')
                                        <span class="invalid-site_currency_symbol" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-label"
                                        for="example3cols3Input">{{ __('Currency Symbol Position') }}</label>
                                    <div class="row ms-1">
                                        <div class="form-check col-md-6">
                                            <input class="form-check-input" type="radio"
                                                name="site_currency_symbol_position" value="pre"
                                                @if (@$setting['site_currency_symbol_position'] == 'pre') checked @endif id="flexCheckDefault">
                                            <label class="form-check-label" for="flexCheckDefault">
                                                {{ __('Pre') }}
                                            </label>
                                        </div>
                                        <div class="form-check col-md-6">
                                            <input class="form-check-input" type="radio"
                                                name="site_currency_symbol_position" value="post"
                                                @if (@$setting['site_currency_symbol_position'] == 'post') checked @endif id="flexCheckChecked">
                                            <label class="form-check-label" for="flexCheckChecked">
                                                {{ __('Post') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('decimal_number', __('Decimal Number Format'), ['class' => 'form-label']) }}
                                    {{ Form::number('decimal_number', null, ['class' => 'form-control']) }}
                                    @error('decimal_number')
                                        <span class="invalid-decimal_number" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div> --}}
                                <div class="form-group col-md-6">
                                    <label for="site_date_format" class="form-label">{{ __('Date Format') }}</label>
                                    <select type="text" name="site_date_format" class="form-control selectric"
                                        id="site_date_format">
                                        <option value="M j, Y"
                                            @if (@$setting['site_date_format'] == 'M j, Y') selected="selected" @endif>Jan 1,2015</option>
                                        <option value="d-m-Y"
                                            @if (@$setting['site_date_format'] == 'd-m-Y') selected="selected" @endif>dd-mm-yyyy</option>
                                        <option value="m-d-Y"
                                            @if (@$setting['site_date_format'] == 'm-d-Y') selected="selected" @endif>mm-dd-yyyy</option>
                                        <option value="Y-m-d"
                                            @if (@$setting['site_date_format'] == 'Y-m-d') selected="selected" @endif>yyyy-mm-dd</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="site_time_format" class="form-label">{{ __('Time Format') }}</label>
                                    <select type="text" name="site_time_format" class="form-control selectric"
                                        id="site_time_format">
                                        <option value="g:i A"
                                            @if (@$setting['site_time_format'] == 'g:i A') selected="selected" @endif>10:30 PM</option>
                                        <option value="g:i a"
                                            @if (@$setting['site_time_format'] == 'g:i a') selected="selected" @endif>10:30 pm</option>
                                        <option value="H:i"
                                            @if (@$setting['site_time_format'] == 'H:i') selected="selected" @endif>22:30</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('customer_prefix', __('Customer Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('customer_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Customer Prefix')]) }}
                                    @error('customer_prefix')
                                        <span class="invalid-customer_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('vender_prefix', __('Vendor Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('vender_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Vendor Prifix')]) }}
                                    @error('vender_prefix')
                                        <span class="invalid-vender_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('proposal_prefix', __('Proposal Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('proposal_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Proposal Prifix')]) }}
                                    @error('proposal_prefix')
                                        <span class="invalid-proposal_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('invoice_prefix', __('Invoice Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('invoice_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Invoice Prifix')]) }}
                                    @error('invoice_prefix')
                                        <span class="invalid-invoice_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('bill_prefix', __('Bill Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('bill_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Bill Prifix')]) }}
                                    @error('bill_prefix')
                                        <span class="invalid-bill_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('quotation_prefix', __('Quotation Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('quotation_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Quotation Prifix')]) }}
                                    @error('quotation_prefix')
                                        <span class="invalid-quotation_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('purchase_prefix', __('Purchase Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('purchase_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Purchase Prifix')]) }}
                                    @error('purchase_prefix')
                                        <span class="invalid-purchase_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('pos_prefix', __('Pos Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('pos_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Pos Prifix')]) }}
                                    @error('pos_prefix')
                                        <span class="invalid-pos_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('journal_prefix', __('Journal Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('journal_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Journal Prifix')]) }}
                                    @error('journal_prefix')
                                        <span class="invalid-journal_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('expense_prefix', __('Expense Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('expense_prefix', null, ['class' => 'form-control', 'placeholder' => __('Enter Expense Prifix')]) }}
                                    @error('expense_prefix')
                                        <span class="invalid-expense_prefix" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('shipping_display', __('Display Shipping in Proposal / Invoice / Bill'), ['class' => 'form-label']) }}
                                    <div class=" form-switch form-switch-left">
                                        <input type="checkbox" class="form-check-input" name="shipping_display"
                                            id="email_tempalte_13"
                                            {{ isset($setting['shipping_display']) && $setting['shipping_display'] == 'on' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email_tempalte_13"></label>
                                    </div>
                                    @error('shipping_display')
                                        <span class="invalid-shipping_display" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('footer_title', __('Proposal/Invoice/Bill/Purchase/POS Footer Title'), ['class' => 'form-label']) }}
                                    {{ Form::text('footer_title', null, ['class' => 'form-control', 'placeholder' => __('Enter Footer Title')]) }}
                                    @error('footer_title')
                                        <span class="invalid-footer_title" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-12 mb-0">
                                    {{ Form::label('footer_notes', __('Proposal/Invoice/Bill/Purchase/POS Footer Note'), ['class' => 'form-label']) }}
                                    <textarea class="summernote-simple4 summernote-simple">{!! isset($setting['footer_notes']) ? $setting['footer_notes'] : '' !!}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer p-3 text-end">
                            <div class="form-group mb-0">
                                <input class="btn btn-print-invoice btn-primary" type="submit"
                                    value="{{ __('Save Changes') }}">
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Company Settings-->
                    <div id="company-settings" class="card">
                        <div class="card-header p-3">
                            <h5>{{ __('Company Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your company details') }}</small>
                        </div>
                        {{ Form::model($setting, ['route' => 'company.settings', 'method' => 'post']) }}
                        <div class="card-body p-3 pb-0">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {{ Form::label('company_name', __('Company Name *'), ['class' => 'form-label']) }}
                                    {{ Form::text('company_name', null, ['class' => 'form-control font-style', 'placeholder' => __('Enter Company Name')]) }}
                                    @error('company_name')
                                        <span class="invalid-company_name" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('company_address', __('Address'), ['class' => 'form-label']) }}
                                    {{ Form::text('company_address', null, ['class' => 'form-control font-style', 'placeholder' => __('Enter Company Address')]) }}
                                    @error('company_address')
                                        <span class="invalid-company_address" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('company_city', __('City'), ['class' => 'form-label']) }}
                                    {{ Form::text('company_city', null, ['class' => 'form-control font-style', 'placeholder' => __('Enter Company City')]) }}
                                    @error('company_city')
                                        <span class="invalid-company_city" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('company_state', __('State'), ['class' => 'form-label']) }}
                                    {{ Form::text('company_state', null, ['class' => 'form-control font-style', 'placeholder' => __('Enter Company State')]) }}
                                    @error('company_state')
                                        <span class="invalid-company_state" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('company_zipcode', __('Zip/Post Code'), ['class' => 'form-label']) }}
                                    {{ Form::text('company_zipcode', null, ['class' => 'form-control', 'placeholder' => __('Enter Company Zip')]) }}
                                    @error('company_zipcode')
                                        <span class="invalid-company_zipcode" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group  col-md-6">
                                    {{ Form::label('company_country', __('Country'), ['class' => 'form-label']) }}
                                    {{ Form::text('company_country', null, ['class' => 'form-control font-style', 'placeholder' => __('Enter Company Country')]) }}
                                    @error('company_country')
                                        <span class="invalid-company_country" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('company_telephone', __('Telephone'), ['class' => 'form-label']) }}
                                    {{ Form::text('company_telephone', null, ['class' => 'form-control', 'placeholder' => __('Enter Company Telephone')]) }}
                                    @error('company_telephone')
                                        <span class="invalid-company_telephone" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('registration_number', __('Company Registration Number'), ['class' => 'form-label']) }}
                                    {{ Form::text('registration_number', null, ['class' => 'form-control', 'placeholder' => __('Enter Company Registration Number')]) }}
                                    @error('registration_number')
                                        <span class="invalid-registration_number" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('company_start_time', __('Company Start Time'), ['class' => 'form-label']) }}
                                    {{ Form::time('company_start_time', null, ['class' => 'form-control']) }}
                                    @error('company_start_time')
                                        <span class="invalid-company_start_time" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('company_end_time', __('Company End Time'), ['class' => 'form-label']) }}
                                    {{ Form::time('company_end_time', null, ['class' => 'form-control']) }}
                                    @error('company_end_time')
                                        <span class="invalid-company_end_time" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>


                                <div class="form-group col-md-12">
                                    {{ Form::label('timezone', __('Timezone'), ['class' => 'form-label']) }}
                                    <select type="text" name="timezone" class="form-control custom-select"
                                        id="timezone">
                                        <option value="">{{ __('Select Timezone') }}</option>
                                        @foreach ($timezones as $k => $timezone)
                                            <option value="{{ $k }}"
                                                {{ isset($setting['timezone']) && $setting['timezone'] == $k ? 'selected' : '' }}>
                                                {{ $timezone }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>




                                <div class="form-group col-xxl-3 col-sm-6">
                                    <div class="card mb-0">
                                        <div class="card-body p-3">
                                            <label class="form-check-label"
                                                for="ip_restrict">{{ __('Ip Restrict') }}</label>
                                            <div class="form-check form-switch custom-switch-v1 float-end">
                                                <input type="checkbox" class=" form-check-input" name="ip_restrict"
                                                    id="ip_restrict"
                                                    {{ isset($setting['ip_restrict']) && $setting['ip_restrict'] == 'on' ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <div class="form-group col-xxl-3 col-sm-6">
                                    <div class="card mb-0">
                                        <div class="card-body p-3">
                                            <label for="vat_gst_number_switch">{{ __('Tax Number') }}</label>
                                            <div class="form-check form-switch custom-switch-v1 float-end">
                                                <input type="checkbox" name="vat_gst_number_switch"
                                                    class="form-check-input input-primary pointer" value="on"
                                                    id="vat_gst_number_switch"
                                                    {{ isset($setting['vat_gst_number_switch']) && $setting['vat_gst_number_switch'] == 'on' ? ' checked ' : '' }}>

                                                <label class="form-check-label" for="vat_gst_number_switch"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="col-xxl-6 col-12 tax_type_div mb-3 {{ isset($setting['vat_gst_number_switch']) && $setting['vat_gst_number_switch'] != 'on' ? ' d-none ' : '' }}">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="form-check form-check-inline form-group mb-0">
                                                <input type="radio" id="customRadio8" name="tax_type" value="VAT"
                                                    class="form-check-input"
                                                    {{ isset($setting['tax_type']) && $setting['tax_type'] == 'VAT' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="customRadio8">{{ __('VAT Number') }}</label>
                                            </div>

                                            <div class="form-check form-check-inline form-group mb-0">
                                                <input type="radio" id="customRadio7" name="tax_type" value="GST"
                                                    class="form-check-input"
                                                    {{ isset($setting['tax_type']) && $setting['tax_type'] == 'GST' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="customRadio7">{{ __('GST Number') }}</label>
                                            </div>
                                        </div>
                                        {{ Form::text('vat_number', null, ['class' => 'form-control flex-1', 'placeholder' => __('Enter VAT / GST Number')]) }}
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="card-footer p-3 text-end">
                            <div class="form-group mb-0">
                                <input class="btn btn-print-invoice btn-primary" type="submit"
                                    value="{{ __('Save Changes') }}">
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Currency Settings-->
                    <div id="currency-settings" class="card">
                        <div class="card-header p-3">
                            <h5>{{ __('Currency Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your currency details') }}</small>
                        </div>
                        {{ Form::model($setting, ['route' => 'currency.settings', 'method' => 'post', 'id' => 'currency_setting']) }}
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {{ Form::label('site_currency', __('Currency'), ['class' => 'form-label']) }}
                                    {{ Form::text('site_currency', isset($setting['site_currency']) ? $setting['site_currency'] : '', ['class' => 'form-control font-style currency_preview', 'required', 'placeholder' => __('Enter Currency')]) }}
                                    <small> {{ __('Note: Add currency code as per three-letter ISO code.') }}<br>
                                        <a href="https://stripe.com/docs/currencies"
                                            target="_blank">{{ __('You can find out how to do that here.') }}</a></small>
                                    <br>
                                    @error('site_currency')
                                        <span class="invalid-site_currency" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('site_currency_symbol', __('Currency Symbol'), ['class' => 'form-label']) }}
                                    {{ Form::text('site_currency_symbol', null, ['class' => 'form-control currency_preview', 'placeholder' => __('Enter Currency Symbol')]) }}
                                    @error('site_currency_symbol')
                                        <span class="invalid-site_currency_symbol" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('decimal_number', __('Decimal Number Format'), ['class' => 'form-label']) }}
                                    {{ Form::number('decimal_number', null, ['class' => 'form-control currency_preview']) }}
                                    @error('decimal_number')
                                        <span class="invalid-decimal_number" role="alert">
                                            <strong class="text-danger">{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="float_number" class="form-label">{{ __('Float Number') }}</label>
                                    <select type="text" name="float_number"
                                        class="form-control selectric currency_preview" id="float_number">
                                        <option value="comma"
                                            @if (@$setting['float_number'] == 'comma') selected="selected" @endif>
                                            {{ __('Comma') }}</option>
                                        <option value="dot"
                                            @if (@$setting['float_number'] == 'dot') selected="selected" @endif>
                                            {{ __('Dot') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="decimal_separator"
                                        class="form-label">{{ __('Decimal Separator') }}</label>
                                    <select type="text" name="decimal_separator"
                                        class="form-control selectric currency_preview" id="decimal_separator">
                                        <option value="dot"
                                            @if (@$setting['decimal_separator'] == 'dot') selected="selected" @endif>
                                            {{ __('Dot') }}</option>
                                        <option value="comma"
                                            @if (@$setting['decimal_separator'] == 'comma') selected="selected" @endif>
                                            {{ __('Comma') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="thousand_separator"
                                        class="form-label">{{ __('Thousands Separator') }}</label>
                                    <select type="text" name="thousand_separator"
                                        class="form-control selectric currency_preview" id="thousand_separator">
                                        <option value="dot"
                                            @if (@$setting['thousand_separator'] == 'dot') selected="selected" @endif>
                                            {{ __('Dot') }}</option>
                                        <option value="comma"
                                            @if (@$setting['thousand_separator'] == 'comma') selected="selected" @endif>
                                            {{ __('Comma') }}</option>
                                    </select>
                                </div>






                                <div class="col-xxl-4 col-md-6">
                                    <div class="card">
                                        <div class="card-header p-2">
                                            <label class="form-label mb-0"
                                                for="example3cols3Input">{{ __('Currency Symbol Position') }}</label>
                                        </div>
                                        <div class="card-body p-2">
                                            <div class="form-group mb-0">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input currency_preview" type="radio"
                                                        name="site_currency_symbol_position" value="pre"
                                                        @if (@$settings['site_currency_symbol_position'] == 'pre') checked @endif
                                                        id="flexCheckDefault">
                                                    <label class="form-check-label" for="flexCheckDefault">
                                                        {{ __('Pre') }}
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input currency_preview" type="radio"
                                                        name="site_currency_symbol_position" value="post"
                                                        @if (@$settings['site_currency_symbol_position'] == 'post') checked @endif
                                                        id="flexCheckChecked">
                                                    <label class="form-check-label" for="flexCheckChecked">
                                                        {{ __('Post') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-6">
                                    <div class="card">
                                        <div class="card-header p-2">
                                            {{ Form::label('currency_space', __('Currency Symbol Space'), ['class' => 'form-label mb-0']) }}
                                        </div>
                                        <div class="card-body p-2">
                                            <div class="form-group mb-0">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input currency_preview" type="radio"
                                                        name="currency_space" value="withspace"
                                                        @if (@$settings['currency_space'] == 'withspace') checked @endif id="withspace">
                                                    <label class="form-check-label" for="withspace">
                                                        {{ __('With space') }}
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input currency_preview" type="radio"
                                                        name="currency_space" value="withoutspace"
                                                        @if (@$settings['currency_space'] == 'withoutspace') checked @endif
                                                        id="withoutspace">
                                                    <label class="form-check-label" for="withoutspace">
                                                        {{ __('Without space') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-6">
                                    <div class="card">
                                        <div class="card-header p-2">
                                            {{ Form::label('currency_symbol', __('Currency Symbol & Name'), ['class' => 'form-label mb-0']) }}
                                        </div>
                                        <div class="form-group mb-0">
                                            <div class="card-body p-2">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input currency_preview" type="radio"
                                                        name="currency_symbol" value="withcurrencysymbol"
                                                        @if (@$settings['currency_symbol'] == 'withcurrencysymbol') checked @endif
                                                        id="withcurrencysymbol">
                                                    <label class="form-check-label" for="withcurrencysymbol">
                                                        {{ __('With Currency Symbol') }}
                                                    </label>
                                                </div>
                                                <div class="form-check ">
                                                    <input class="form-check-input currency_preview" type="radio"
                                                        name="currency_symbol" value="withcurrencyname"
                                                        @if (@$settings['currency_symbol'] == 'withcurrencyname') checked @endif
                                                        id="withcurrencyname">
                                                    <label class="form-check-label" for="withcurrencyname">
                                                        {{ __('With Currency Name') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto form-group mb-0">
                                    <div class="card mb-0">
                                        <div class="card-body p-3 d-flex align-items-center gap-3">
                                            {{ Form::label('preview', __('Preview :'), ['class' => 'form-label mb-0']) }}
                                            <div class="preview">
                                                {{ __('$ 10.000,00') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer p-3 text-end">
                            <div class="form-group mb-0">
                                <input class="btn btn-print-invoice btn-primary" type="submit"
                                    value="{{ __('Save Changes') }}">
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Email Settings-->
                    <div id="email-settings" class="card">
                        <div class="card-header p-3">
                            <h5>{{ __('Email Settings') }}</h5>
                            <small
                                class="text-muted">{{ __('This SMTP will be used for sending your company-level email. If this field is empty, then SuperAdmin SMTP will be used for sending emails.') }}</small>
                        </div>
                        {{ Form::model($emailSetting, ['route' => 'company.email.settings', 'method' => 'post']) }}
                        <div class="card-body p-3">
                            @csrf
                            <div class="row row-gap-2">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        {{ Form::label('mail_driver', __('Mail Driver'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_driver', isset($emailSetting['mail_driver']) ? $emailSetting['mail_driver'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Driver')]) }}
                                        @error('mail_driver')
                                            <span class="invalid-mail_driver" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        {{ Form::label('mail_host', __('Mail Host'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_host', isset($emailSetting['mail_host']) ? $emailSetting['mail_host'] : '', ['class' => 'form-control ', 'placeholder' => __('Enter Mail Host')]) }}
                                        @error('mail_host')
                                            <span class="invalid-mail_driver" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        {{ Form::label('mail_port', __('Mail Port'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_port', isset($emailSetting['mail_port']) ? $emailSetting['mail_port'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Port')]) }}
                                        @error('mail_port')
                                            <span class="invalid-mail_port" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        {{ Form::label('mail_username', __('Mail Username'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_username', isset($emailSetting['mail_username']) ? $emailSetting['mail_username'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Username')]) }}
                                        @error('mail_username')
                                            <span class="invalid-mail_username" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        {{ Form::label('mail_password', __('Mail Password'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_password', isset($emailSetting['mail_password']) ? $emailSetting['mail_password'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Password')]) }}
                                        @error('mail_password')
                                            <span class="invalid-mail_password" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        {{ Form::label('mail_encryption', __('Mail Encryption'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_encryption', isset($emailSetting['mail_encryption']) ? $emailSetting['mail_encryption'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail Encryption')]) }}
                                        @error('mail_encryption')
                                            <span class="invalid-mail_encryption" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        {{ Form::label('mail_from_address', __('Mail From Address'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_from_address', isset($emailSetting['mail_from_address']) ? $emailSetting['mail_from_address'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail From Address')]) }}
                                        @error('mail_from_address')
                                            <span class="invalid-mail_from_address" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        {{ Form::label('mail_from_name', __('Mail From Name'), ['class' => 'form-label']) }}
                                        {{ Form::text('mail_from_name', isset($emailSetting['mail_from_name']) ? $emailSetting['mail_from_name'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Mail From Name')]) }}
                                        @error('mail_from_name')
                                            <span class="invalid-mail_from_name" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer p-3">
                            <div class="form-group d-flex flex-wrap gap-2 align-items-center justify-content-end mb-0">
                                <a href="#" data-url="{{ route('test.mail') }}"
                                    data-title="{{ __('Send Test Mail') }}" class="btn btn-primary send_email">
                                    {{ __('Send Test Mail') }}
                                </a>
                                <input class="btn btn-primary" type="submit" value="{{ __('Save Changes') }}">
                            </div>
                        </div>

                        {{ Form::close() }}
                    </div>

                    <!--Time-Tracker Settings-->
                    <div id="tracker-settings" class="card">
                        <div class="card-header p-3">
                            <h5>{{ __('Time Tracker Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your Time Tracker settings') }}</small>
                        </div>
                        {{ Form::model($setting, ['route' => 'tracker.settings', 'method' => 'post']) }}
                        <div class="card-body p-3">
                            <div class="row row-gap-2">
                                <div class="form-group mb-0 col-md-6">
                                    <label class="form-label">{{ __('Application URL') }}</label> <br>
                                    <span
                                        class="text-sm d-block mb-2">{{ __('Application URL to log into the app.') }}</span>
                                    {{ Form::text('apps_url', URL::to('/'), ['class' => 'form-control', 'placeholder' => __('Application URL'), 'readonly' => 'true']) }}
                                </div>
                                <div class="form-group mb-0 col-md-6">
                                    <label class="form-label">{{ __('Tracking Interval') }}</label> <br>
                                    <span
                                        class="text-sm d-block mb-2">{{ __('Image Screenshot Take Interval time ( 1 = 1 min)') }}</span>
                                    {{ Form::number('interval_time', isset($setting['interval_time']) ? $setting['interval_time'] : '10', ['class' => 'form-control', 'placeholder' => __('Enter Tracking Interval Time')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="card-footer p-3 text-end">
                            <div class="form-group mb-0">
                                <input class="btn btn-print-invoice btn-primary" type="submit"
                                    value="{{ __('Save Changes') }}">
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Payment Settings-->
                    <div class="card" id="payment-settings">
                        <div class="card-header p-3">
                            <h5>{{ 'Payment Settings' }}</h5>
                            <small
                                class="text-secondary font-weight-bold">{{ __('These details will be used to collect invoice payments. Each invoice will have a payment button based on the below configuration.') }}</small>
                        </div>
                        {{ Form::model($setting, ['route' => 'company.payment.settings', 'method' => 'POST']) }}
                        @csrf
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-12">
                                    <div class="faq justify-content-center">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="accordion accordion-flush setting-accordion" id="accordionExample">

    @if(isset($enabledGateways['is_bank_transfer_enabled']))
    <!-- Bank Transfer -->
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button collapsed" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseBank"
                aria-expanded="false" aria-controls="collapseOne">
                <span class="d-flex align-items-center">
                    {{ __('Bank Transfer') }}
                </span>
                <div class="d-flex align-items-center">
                    <span class="me-2">{{ __('Enable') }}:</span>
                    <div class="form-check form-switch custom-switch-v1">
                        <input type="hidden"
                            name="is_bank_transfer_enabled"
                            value="off">
                        <input type="checkbox"
                            class="form-check-input input-primary"
                            id="customswitchv1-1 is_bank_transfer_enabled"
                            name="is_bank_transfer_enabled"
                            {{ isset($company_payment_setting['is_bank_transfer_enabled']) && $company_payment_setting['is_bank_transfer_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                    </div>
                </div>
            </button>
        </h2>
        <div id="collapseBank" class="accordion-collapse collapse"
            aria-labelledby="headingOne"
            data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <div class="row gy-4">
                    <div class="col-lg-12">
                        <div class="input-edits">
                            <div class="form-group">
                                {{ Form::label('bank_details', __('Bank Details'), ['class' => 'col-form-label']) }}
                                {{ Form::textarea('bank_details', isset($company_payment_setting['bank_details']) ? $company_payment_setting['bank_details'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Your Bank Details'), 'rows' => 4]) }}
                                <small class="text-xs">
                                    {{ __('Example : Bank : bank name </br> Account Number : 0000 0000 </br>') }}
                                </small>
                                @if ($errors->has('bank_details'))
                                    <span class="invalid-feedback d-block">
                                        {{ $errors->first('bank_details') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($enabledGateways['is_stripe_enabled']))
    <!-- Stripe -->
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button collapsed" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseOne"
                aria-expanded="false" aria-controls="collapseOne">
                <span class="d-flex align-items-center">
                    {{ __('Stripe') }}
                </span>
                <div class="d-flex align-items-center">
                    <span class="me-2">{{ __('Enable') }}:</span>
                    <div class="form-check form-switch custom-switch-v1">
                        <input type="hidden" name="is_stripe_enabled"
                            value="off">
                        <input type="checkbox"
                            class="form-check-input input-primary"
                            id="customswitchv1-1 is_stripe_enabled"
                            name="is_stripe_enabled"
                            {{ isset($company_payment_setting['is_stripe_enabled']) && $company_payment_setting['is_stripe_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                    </div>
                </div>
            </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse"
            aria-labelledby="headingOne"
            data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <div class="row gy-4">
                    <div class="col-lg-6">
                        <div class="input-edits">
                            <div class="form-group">
                                {{ Form::label('stripe_key', __('Stripe Key'), ['class' => 'col-form-label']) }}
                                {{ Form::text('stripe_key', isset($company_payment_setting['stripe_key']) ? $company_payment_setting['stripe_key'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Stripe Key')]) }}
                                @if ($errors->has('stripe_key'))
                                    <span class="invalid-feedback d-block">
                                        {{ $errors->first('stripe_key') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="input-edits">
                            <div class="form-group">
                                {{ Form::label('stripe_secret', __('Stripe Secret'), ['class' => 'col-form-label']) }}
                                {{ Form::text('stripe_secret', isset($company_payment_setting['stripe_secret']) ? $company_payment_setting['stripe_secret'] : '', ['class' => 'form-control ', 'placeholder' => __('Enter Stripe Secret')]) }}
                                @if ($errors->has('stripe_secret'))
                                    <span class="invalid-feedback d-block">
                                        {{ $errors->first('stripe_secret') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($enabledGateways['is_paypal_enabled']))
    <!-- Paypal -->
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingTwo">
            <button class="accordion-button collapsed" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseTwo"
                aria-expanded="false" aria-controls="collapseTwo">
                <span class="d-flex align-items-center">
                    {{ __('Paypal') }}
                </span>
                <div class="d-flex align-items-center">
                    <span class="me-2">{{ __('Enable') }}:</span>
                    <div class="form-check form-switch custom-switch-v1">
                        <input type="hidden" name="is_paypal_enabled"
                            value="off">
                        <input type="checkbox"
                            class="form-check-input input-primary"
                            id="customswitchv1-1 is_paypal_enabled"
                            name="is_paypal_enabled"
                            {{ isset($company_payment_setting['is_paypal_enabled']) && $company_payment_setting['is_paypal_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                    </div>
                </div>
            </button>
        </h2>
        <div id="collapseTwo" class="accordion-collapse collapse"
            aria-labelledby="headingTwo"
            data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <div class="d-flex">
                    <div class="mr-2" style="margin-right: 15px;">
                        <div class="border card p-3">
                            <div class="form-check">
                                <label
                                    class="form-check-label text-dark me-2">
                                    <input type="radio"
                                        name="paypal_mode" value="sandbox"
                                        class="form-check-input"
                                        {{ (isset($company_payment_setting['paypal_mode']) && $company_payment_setting['paypal_mode'] == '') || (isset($company_payment_setting['paypal_mode']) && $company_payment_setting['paypal_mode'] == 'sandbox') ? 'checked="checked"' : '' }}>
                                    {{ __('Sandbox') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mr-2" style="margin-right: 15px;">
                        <div class="border card p-3">
                            <div class="form-check">
                                <label
                                    class="form-check-label text-dark me-2">
                                    <input type="radio"
                                        name="paypal_mode" value="live"
                                        class="form-check-input"
                                        {{ isset($company_payment_setting['paypal_mode']) && $company_payment_setting['paypal_mode'] == 'live' ? 'checked="checked"' : '' }}>
                                    {{ __('Live') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row gy-4">
                    <div class="col-lg-6">
                        <div class="input-edits">
                            <div class="form-group">
                                <label class="col-form-label"
                                    for="paypal_client_id">{{ __('Client ID') }}</label>
                                <input type="text"
                                    name="paypal_client_id"
                                    id="paypal_client_id"
                                    class="form-control"
                                    value="{{ !isset($company_payment_setting['paypal_client_id']) || is_null($company_payment_setting['paypal_client_id']) ? '' : $company_payment_setting['paypal_client_id'] }}"
                                    placeholder="{{ __('Client ID') }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="input-edits">
                            <div class="form-group">
                                <label class="col-form-label"
                                    for="paypal_secret_key">{{ __('Secret Key') }}</label>
                                <input type="text"
                                    name="paypal_secret_key"
                                    id="paypal_secret_key"
                                    class="form-control"
                                    value="{{ isset($company_payment_setting['paypal_secret_key']) ? $company_payment_setting['paypal_secret_key'] : '' }}"
                                    placeholder="{{ __('Secret Key') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($enabledGateways['is_paystack_enabled']))
    <!-- Paystack -->
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingThree">
            <button class="accordion-button collapsed" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseThree"
                aria-expanded="false" aria-controls="collapseThree">
                <span class="d-flex align-items-center">
                    {{ __('Paystack') }}
                </span>
                <div class="d-flex align-items-center">
                    <span class="me-2">{{ __('Enable') }}:</span>
                    <div class="form-check form-switch custom-switch-v1">
                        <input type="hidden" name="is_paystack_enabled"
                            value="off">
                        <input type="checkbox"
                            class="form-check-input input-primary"
                            id="customswitchv1-1 is_paystack_enabled"
                            name="is_paystack_enabled"
                            {{ isset($company_payment_setting['is_paystack_enabled']) && $company_payment_setting['is_paystack_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                    </div>
                </div>
            </button>
        </h2>
        <div id="collapseThree" class="accordion-collapse collapse"
            aria-labelledby="headingThree"
            data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <div class="row gy-4">
                    <div class="col-lg-6">
                        <div class="input-edits">
                            <div class="form-group">
                                <label for="paypal_client_id"
                                    class="col-form-label">{{ __('Public Key') }}</label>
                                <input type="text"
                                    name="paystack_public_key"
                                    id="paystack_public_key"
                                    class="form-control"
                                    value="{{ isset($company_payment_setting['paystack_public_key']) ? $company_payment_setting['paystack_public_key'] : '' }}"
                                    placeholder="{{ __('Public Key') }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="input-edits">
                            <div class="form-group">
                                <label for="paystack_secret_key"
                                    class="col-form-label">{{ __('Secret Key') }}</label>
                                <input type="text"
                                    name="paystack_secret_key"
                                    id="paystack_secret_key"
                                    class="form-control"
                                    value="{{ isset($company_payment_setting['paystack_secret_key']) ? $company_payment_setting['paystack_secret_key'] : '' }}"
                                    placeholder="{{ __('Secret Key') }}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($enabledGateways['is_flutterwave_enabled']))
    <!-- Flutterwave -->
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFour">
            <button class="accordion-button collapsed" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseFour"
                aria-expanded="false" aria-controls="collapseFour">
                <span class="d-flex align-items-center">
                    {{ __('Flutterwave') }}
                </span>
                <div class="d-flex align-items-center">
                    <span class="me-2">{{ __('Enable') }}:</span>
                    <div class="form-check form-switch custom-switch-v1">
                        <input type="hidden"
                            name="is_flutterwave_enabled" value="off">
                        <input type="checkbox"
                            class="form-check-input input-primary"
                            id="customswitchv1-1 is_flutterwave_enabled"
                            name="is_flutterwave_enabled"
                            {{ isset($company_payment_setting['is_flutterwave_enabled']) && $company_payment_setting['is_flutterwave_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                    </div>
                </div>
            </button>
        </h2>
        <div id="collapseFour" class="accordion-collapse collapse"
            aria-labelledby="headingFour"
            data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <div class="row gy-4">
                    <div class="col-lg-6">
                        <div class="input-edits">
                            <div class="form-group">
                                <label for="paypal_client_id"
                                    class="col-form-label">{{ __('Public Key') }}</label>
                                <input type="text"
                                    name="flutterwave_public_key"
                                    id="flutterwave_public_key"
                                    class="form-control"
                                    value="{{ isset($company_payment_setting['flutterwave_public_key']) ? $company_payment_setting['flutterwave_public_key'] : '' }}"
                                    placeholder="Public Key">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="input-edits">
                            <div class="form-group">
                                <label for="paystack_secret_key"
                                    class="col-form-label">{{ __('Secret Key') }}</label>
                                <input type="text"
                                    name="flutterwave_secret_key"
                                    id="flutterwave_secret_key"
                                    class="form-control"
                                    value="{{ isset($company_payment_setting['flutterwave_secret_key']) ? $company_payment_setting['flutterwave_secret_key'] : '' }}"
                                    placeholder="Secret Key">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($enabledGateways['is_razorpay_enabled']))
    <!-- Razorpay -->
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFive">
            <button class="accordion-button collapsed" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseFive"
                aria-expanded="false" aria-controls="collapseFive">
                <span class="d-flex align-items-center">
                    {{ __('Razorpay') }}
                </span>
                <div class="d-flex align-items-center">
                    <span class="me-2">{{ __('Enable') }}:</span>
                    <div class="form-check form-switch custom-switch-v1">
                        <input type="hidden" name="is_razorpay_enabled"
                            value="off">
                        <input type="checkbox"
                            class="form-check-input input-primary"
                            id="customswitchv1-1 is_razorpay_enabled"
                            name="is_razorpay_enabled"
                            {{ isset($company_payment_setting['is_razorpay_enabled']) && $company_payment_setting['is_razorpay_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                    </div>
                </div>
            </button>
        </h2>
        <div id="collapseFive" class="accordion-collapse collapse"
            aria-labelledby="headingFive"
            data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <div class="row gy-4">
                    <div class="col-lg-6">
                        <div class="input-edits">
                            <div class="form-group">
                                <label for="paypal_client_id"
                                    class="col-form-label">{{ __('Public Key') }}</label>
                                <input type="text"
                                    name="razorpay_public_key"
                                    id="razorpay_public_key"
                                    class="form-control"
                                    value="{{ !isset($company_payment_setting['razorpay_public_key']) || is_null($company_payment_setting['razorpay_public_key']) ? '' : $company_payment_setting['razorpay_public_key'] }}"
                                    placeholder="Public Key">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="input-edits">
                            <div class="form-group">
                                <label for="paystack_secret_key"
                                    class="col-form-label">
                                    {{ __('Secret Key') }}</label>
                                <input type="text"
                                    name="razorpay_secret_key"
                                    id="razorpay_secret_key"
                                    class="form-control"
                                    value="{{ !isset($company_payment_setting['razorpay_secret_key']) || is_null($company_payment_setting['razorpay_secret_key']) ? '' : $company_payment_setting['razorpay_secret_key'] }}"
                                    placeholder="Secret Key">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($enabledGateways['is_payfast_enabled']))
    <!-- Payfast -->
    <div class="accordion accordion-flush setting-accordion"
        id="accordionExample">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseOne13"
                    aria-expanded="false" aria-controls="collapseOne13">
                    <span class="d-flex align-items-center">
                        {{ __('PayFast') }}
                    </span>
                    <div class="d-flex align-items-center">
                        <span class="me-2">{{ __('Enable') }}:</span>
                        <div
                            class="form-check form-switch custom-switch-v1">
                            <input type="hidden"
                                name="is_payfast_enabled"
                                value="off">
                            <input type="checkbox"
                                class="form-check-input"
                                name="is_payfast_enabled"
                                id="is_payfast_enabled"
                                {{ isset($company_payment_setting['is_payfast_enabled']) && $company_payment_setting['is_payfast_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseOne13" class="accordion-collapse collapse"
                aria-labelledby="headingOne"
                data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    <div class="row">
                        <label class="paypal-label col-form-label"
                            for="payfast_mode">{{ __('Payfast Mode') }}</label>
                        <div class="d-flex">
                            <div class="mr-2"
                                style="margin-right: 15px;">
                                <div class="border card p-3">
                                    <div class="form-check">
                                        <label
                                            class="form-check-labe text-dark {{ isset($company_payment_setting['payfast_mode']) && $company_payment_setting['payfast_mode'] == 'sandbox' ? 'active' : '' }}">
                                            <input type="radio"
                                                name="payfast_mode"
                                                value="sandbox"
                                                class="form-check-input"
                                                {{ isset($company_payment_setting['payfast_mode']) && $company_payment_setting['payfast_mode'] == 'sandbox' ? 'checked="checked"' : '' }}>
                                            {{ __('Sandbox') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mr-2"
                                style="margin-right: 15px;">
                                <div class="border card p-3">
                                    <div class="form-check">
                                        <label
                                            class="form-check-labe text-dark">
                                            <input type="radio"
                                                name="payfast_mode"
                                                value="live"
                                                class="form-check-input"
                                                {{ isset($company_payment_setting['payfast_mode']) && $company_payment_setting['payfast_mode'] == 'live' ? 'checked="checked"' : '' }}>

                                            {{ __('Live') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="paytm_public_key"
                                    class="col-form-label">{{ __('Merchant ID') }}</label>
                                <input type="text"
                                    name="payfast_merchant_id"
                                    id="payfast_merchant_id"
                                    class="form-control"
                                    value="{{ !isset($company_payment_setting['payfast_merchant_id']) || is_null($company_payment_setting['payfast_merchant_id']) ? '' : $company_payment_setting['payfast_merchant_id'] }}"
                                    placeholder="Merchant ID">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="paytm_secret_key"
                                    class="col-form-label">{{ __('Merchant Key') }}</label>
                                <input type="text"
                                    name="payfast_merchant_key"
                                    id="payfast_merchant_key"
                                    class="form-control"
                                    value="{{ !isset($company_payment_setting['payfast_merchant_key']) || is_null($company_payment_setting['payfast_merchant_key']) ? '' : $company_payment_setting['payfast_merchant_key'] }}"
                                    placeholder="Merchant Key">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="payfast_signature"
                                    class="col-form-label">{{ __('Salt Passphrase') }}</label>
                                <input type="text"
                                    name="payfast_signature"
                                    id="payfast_signature"
                                    class="form-control"
                                    value="{{ !isset($company_payment_setting['payfast_signature']) || is_null($company_payment_setting['payfast_signature']) ? '' : $company_payment_setting['payfast_signature'] }}"
                                    placeholder="Salt Passphrase">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($enabledGateways['company_ozow_payment_is_enabled']))
    {{-- Ozow (South African Gateway) --}}
    <div class="accordion-item">
        <h2 class="accordion-header" id="ozow-payment">
            <button class="accordion-button" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#ozow-payment-settings"
                aria-expanded="true"
                aria-controls="ozow-payment-settings">
                <span class="d-flex align-items-center"
                    for="ozow-payment">
                    {{ __('Ozow') }}
                </span>
                <div class="d-flex align-items-center">
                    <label class="form-check-label m-1"
                        for="company_ozow_payment_is_enabled">{{ __('Enable') }}</label>
                    <div class="form-check form-switch custom-switch-v1">
                        <input type="hidden"
                            name="company_ozow_payment_is_enabled"
                            value="off">
                        <input type="checkbox"
                            class="form-check-input input-primary"
                            name="company_ozow_payment_is_enabled"
                            id="company_ozow_payment_is_enabled"
                            {{ isset($company_payment_setting['company_ozow_payment_is_enabled']) && $company_payment_setting['company_ozow_payment_is_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                    </div>
                </div>
            </button>
        </h2>
        <div id="ozow-payment-settings"
            class="accordion-collapse collapse"
            aria-labelledby="headingThirty"
            data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <div class="row">
                    <div class="col-md-12">
                        <label for="company_ozow_payment_mode"
                            class="col-form-label">{{ __('Ozow Mode') }}</label>
                        <div class="d-flex">
                            <div class="me-2">
                                <div class="border card p-3">
                                    <div class="form-check">
                                        <label
                                            class="form-check-labe text-dark {{ isset($company_payment_setting['company_ozow_payment_mode']) && $company_payment_setting['company_ozow_payment_mode'] == 'sandbox' ? 'active' : '' }}">
                                            <input type="radio"
                                                name="company_ozow_payment_mode"
                                                value="sandbox"
                                                class="form-check-input"
                                                {{ (isset($company_payment_setting['company_ozow_payment_mode']) && $company_payment_setting['company_ozow_payment_mode'] == '') || (isset($company_payment_setting['company_ozow_payment_mode']) && $company_payment_setting['company_ozow_payment_mode'] == 'sandbox') ? 'checked="checked"' : '' }}>{{ __('Sandbox') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="me-2">
                                <div class="border card p-3">
                                    <div class="form-check">
                                        <label
                                            class="form-check-labe text-dark {{ isset($company_payment_setting['company_ozow_payment_mode']) && $company_payment_setting['company_ozow_payment_mode'] == 'live' ? 'active' : '' }}">
                                            <input type="radio"
                                                name="company_ozow_payment_mode"
                                                value="live"
                                                class="form-check-input"
                                                {{ isset($company_payment_setting['company_ozow_payment_mode']) && $company_payment_setting['company_ozow_payment_mode'] == 'live' ? 'checked="checked"' : '' }}>{{ __('Live') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company_ozow_site_key"
                                class="form-label">{{ __('Ozow Site Key') }}</label>
                            <input type="text"
                                name="company_ozow_site_key"
                                id="company_ozow_site_key"
                                class="form-control"
                                value="{{ !isset($company_payment_setting['company_ozow_site_key']) || is_null($company_payment_setting['company_ozow_site_key']) ? '' : $company_payment_setting['company_ozow_site_key'] }}"
                                placeholder="{{ __('Ozow Site Key') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company_ozow_private_key"
                                class="form-label">{{ __('Ozow Private Key') }}</label>
                            <input type="text"
                                name="company_ozow_private_key"
                                id="company_ozow_private_key"
                                class="form-control"
                                value="{{ !isset($company_payment_setting['company_ozow_private_key']) || is_null($company_payment_setting['company_ozow_private_key']) ? '' : $company_payment_setting['company_ozow_private_key'] }}"
                                placeholder="{{ __('Ozow Private Key') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company_ozow_api_key"
                                class="form-label">{{ __('Ozow Api Key') }}</label>
                            <input type="text"
                                name="company_ozow_api_key"
                                id="company_ozow_api_key"
                                class="form-control"
                                value="{{ !isset($company_payment_setting['company_ozow_api_key']) || is_null($company_payment_setting['company_ozow_api_key']) ? '' : $company_payment_setting['company_ozow_api_key'] }}"
                                placeholder="{{ __('Ozow Api Key') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    



                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer p-3 text-end">
                            <div class="form-group mb-0">
                                <input class="btn btn-print-invoice  btn-primary" type="submit"
                                    value="{{ __('Save Changes') }}">
                            </div>
                        </div>
                        </form>
                    </div>

                    <!--Zoom - Metting Settings-->
                    <div id="zoom-settings" class="card">
                        <div class="card-header p-3">
                            <h5>{{ __('Zoom Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your Zoom settings') }}</small>
                        </div>
                        {{ Form::model($setting, ['route' => 'zoom.settings', 'method' => 'post']) }}
                        <div class="card-body p-3">
                            <div class="row row-gap-1">
                                <div class="form-group mb-0 col-md-6">
                                    <label class="form-label">{{ __('Zoom Account ID') }}</label> <br>
                                    {{ Form::text('zoom_account_id', isset($setting['zoom_account_id']) ? $setting['zoom_account_id'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Zoom Accound Id')]) }}
                                </div>
                                <div class="form-group mb-0 col-md-6">
                                    <label class="form-label">{{ __('Zoom Client ID') }}</label> <br>
                                    {{ Form::text('zoom_client_id', isset($setting['zoom_client_id']) ? $setting['zoom_client_id'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Zoom Client Id')]) }}
                                </div>
                                <div class="form-group mb-0 col-md-6">
                                    <label class="form-label">{{ __('Zoom Client Secret Key') }}</label> <br>
                                    {{ Form::text('zoom_client_secret', isset($setting['zoom_client_secret']) ? $setting['zoom_client_secret'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Zoom Client Secret Key')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="card-footer p-3 text-end">
                            <div class="form-group mb-0">
                                <input class="btn btn-print-invoice btn-primary" type="submit"
                                    value="{{ __('Save Changes') }}">
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Slack Settings-->
                    <div id="slack-settings" class="card">
                        <div class="card-header p-3">
                            <h5>{{ __('Slack Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your Slack settings') }}</small>
                        </div>
                        {{ Form::open(['route' => 'slack.settings', 'id' => 'slack-setting', 'method' => 'post', 'class' => 'd-contents']) }}
                        <div class="card-body p-3">
                            <div class="row gy-2">
                                <div class="form-group col-md-12 mb-1">
                                    <label class="form-label">{{ __('Slack Webhook URL') }}</label> <br>
                                    {{ Form::text('slack_webhook', isset($comSetting['slack_webhook']) ? $comSetting['slack_webhook'] : '', ['class' => 'form-control w-100', 'placeholder' => __('Enter Slack Webhook URL'), 'required' => 'required']) }}
                                </div>
                                <div class="col-md-12 mb-1">
                                    <h5 class="small-title">{{ __('Module Settings') }}</h5>
                                </div>
                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Lead') }}</span>
                                                {{ Form::checkbox('lead_notification', '1', isset($comSetting['lead_notification']) && $comSetting['lead_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'lead_notification']) }}
                                                <label class="form-check-label" for="lead_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Deal') }}</span>
                                                {{ Form::checkbox('deal_notification', '1', isset($comSetting['deal_notification']) && $comSetting['deal_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'deal_notification']) }}
                                                <label class="form-check-label" for="deal_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('Lead to Deal Conversion') }}</span>
                                                {{ Form::checkbox('leadtodeal_notification', '1', isset($comSetting['leadtodeal_notification']) && $comSetting['leadtodeal_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'leadtodeal_notification']) }}
                                                <label class="form-check-label" for="leadtodeal_notification"></label>

                                            </div>

                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Contract') }}</span>
                                                {{ Form::checkbox('contract_notification', '1', isset($comSetting['contract_notification']) && $comSetting['contract_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'contract_notification']) }}
                                                <label class="form-check-label" for="contract_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Project') }}</span>
                                                {{ Form::checkbox('project_notification', '1', isset($comSetting['project_notification']) && $comSetting['project_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'project_notification']) }}
                                                <label class="form-check-label" for="project_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Task') }}</span>
                                                {{ Form::checkbox('task_notification', '1', isset($comSetting['task_notification']) && $comSetting['task_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'task_notification']) }}
                                                <label class="form-check-label" for="task_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('Task Stage Updated') }}</span>
                                                {{ Form::checkbox('taskmove_notification', '1', isset($comSetting['taskmove_notification']) && $comSetting['taskmove_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'taskmove_notification']) }}
                                                <label class="form-check-label" for="taskmove_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Task Comment') }}</span>
                                                {{ Form::checkbox('taskcomment_notification', '1', isset($comSetting['taskcomment_notification']) && $comSetting['taskcomment_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'taskcomment_notification']) }}
                                                <label class="form-check-label" for="taskcomment_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Monthly Payslip') }}</span>
                                                {{ Form::checkbox('payslip_notification', '1', isset($comSetting['payslip_notification']) && $comSetting['payslip_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'payslip_notification']) }}
                                                <label class="form-check-label" for="payslip_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Award') }}</span>
                                                {{ Form::checkbox('award_notification', '1', isset($comSetting['award_notification']) && $comSetting['award_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'award_notification']) }}
                                                <label class="form-check-label" for="award_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Announcement') }}</span>
                                                {{ Form::checkbox('announcement_notification', '1', isset($comSetting['announcement_notification']) && $comSetting['announcement_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'announcement_notification']) }}
                                                <label class="form-check-label" for="announcement_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Holiday') }}</span>
                                                {{ Form::checkbox('holiday_notification', '1', isset($comSetting['holiday_notification']) && $comSetting['holiday_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'holiday_notification']) }}
                                                <label class="form-check-label" for="holiday_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Support Ticket') }}</span>
                                                {{ Form::checkbox('support_notification', '1', isset($comSetting['support_notification']) && $comSetting['support_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'support_notification']) }}
                                                <label class="form-check-label" for="support_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Event') }}</span>
                                                {{ Form::checkbox('event_notification', '1', isset($comSetting['event_notification']) && $comSetting['event_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'event_notification']) }}
                                                <label class="form-check-label" for="event_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Meeting') }}</span>
                                                {{ Form::checkbox('meeting_notification', '1', isset($comSetting['meeting_notification']) && $comSetting['meeting_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'meeting_notification']) }}
                                                <label class="form-check-label" for="meeting_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Company Policy') }}</span>
                                                {{ Form::checkbox('policy_notification', '1', isset($comSetting['policy_notification']) && $comSetting['policy_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'policy_notification']) }}
                                                <label class="form-check-label" for="policy_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Invoice') }}</span>
                                                {{ Form::checkbox('invoice_notification', '1', isset($comSetting['invoice_notification']) && $comSetting['invoice_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'invoice_notification']) }}
                                                <label class="form-check-label" for="invoice_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Revenue') }}</span>
                                                {{ Form::checkbox('revenue_notification', '1', isset($comSetting['revenue_notification']) && $comSetting['revenue_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'revenue_notification']) }}
                                                <label class="form-check-label" for="revenue_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Bill') }}</span>
                                                {{ Form::checkbox('bill_notification', '1', isset($comSetting['bill_notification']) && $comSetting['bill_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'bill_notification']) }}
                                                <label class="form-check-label" for="bill_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Invoice Payment') }}</span>
                                                {{ Form::checkbox('payment_notification', '1', isset($comSetting['payment_notification']) && $comSetting['payment_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'payment_notification']) }}
                                                <label class="form-check-label" for="payment_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Budget') }}</span>
                                                {{ Form::checkbox('budget_notification', '1', isset($comSetting['budget_notification']) && $comSetting['budget_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'budget_notification']) }}
                                                <label class="form-check-label" for="budget_notification"></label>
                                            </div>
                                        </li>

                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer p-3 text-end">
                            <div class="form-group mb-0">
                                <input class="btn btn-print-invoice btn-primary" type="submit"
                                    value="{{ __('Save Changes') }}">
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Telegram Settings-->
                    <div id="telegram-settings" class="card">
                        <div class="card-header p-3">
                            <h5>{{ __('Telegram Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your Telegram settings') }}</small>
                        </div>
                        {{ Form::open(['route' => 'telegram.settings', 'id' => 'telegram-setting', 'method' => 'post', 'class' => 'd-contents']) }}

                        <div class="card-body p-3">
                            <div class="row row-gap-1">
                                <div class="form-group mb-0 col-md-6">
                                    <label class="form-label">{{ __('Telegram AccessToken') }}</label> <br>
                                    {{ Form::text('telegram_accestoken', isset($comSetting['telegram_accestoken']) ? $comSetting['telegram_accestoken'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Telegram AccessToken')]) }}
                                </div>
                                <div class="form-group mb-0 col-md-6">
                                    <label class="form-label ">{{ __('Telegram ChatID') }}</label> <br>
                                    {{ Form::text('telegram_chatid', isset($comSetting['telegram_chatid']) ? $comSetting['telegram_chatid'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Telegram ChatID')]) }}
                                </div>

                                <div class="col-md-12">
                                    <h5 class="small-title">{{ __('Module Settings') }}</h5>
                                </div>
                            </div>
                            <div class="row gy-2">
                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Lead') }}</span>
                                                {{ Form::checkbox('telegram_lead_notification', '1', isset($comSetting['telegram_lead_notification']) && $comSetting['telegram_lead_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_lead_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_lead_notification"></label>

                                            </div>

                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Deal') }}</span>
                                                {{ Form::checkbox('telegram_deal_notification', '1', isset($comSetting['telegram_deal_notification']) && $comSetting['telegram_deal_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_deal_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_deal_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('Lead to Deal Conversion') }}</span>
                                                {{ Form::checkbox('telegram_leadtodeal_notification', '1', isset($comSetting['telegram_leadtodeal_notification']) && $comSetting['telegram_leadtodeal_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_leadtodeal_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_leadtodeal_notification"></label>

                                            </div>

                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Contract') }}</span>
                                                {{ Form::checkbox('telegram_contract_notification', '1', isset($comSetting['telegram_contract_notification']) && $comSetting['telegram_contract_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_contract_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_contract_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Project') }}</span>
                                                {{ Form::checkbox('telegram_project_notification', '1', isset($comSetting['telegram_project_notification']) && $comSetting['telegram_project_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_project_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_project_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Task') }}</span>
                                                {{ Form::checkbox('telegram_task_notification', '1', isset($comSetting['telegram_task_notification']) && $comSetting['telegram_task_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_task_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_task_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('Task Stage Updated') }}</span>
                                                {{ Form::checkbox('telegram_taskmove_notification', '1', isset($comSetting['telegram_taskmove_notification']) && $comSetting['telegram_taskmove_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_taskmove_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_taskmove_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Task Comment') }}</span>
                                                {{ Form::checkbox('telegram_taskcomment_notification', '1', isset($comSetting['telegram_taskcomment_notification']) && $comSetting['telegram_taskcomment_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_taskcomment_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_taskcomment_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Monthly Payslip') }}</span>
                                                {{ Form::checkbox('telegram_payslip_notification', '1', isset($comSetting['telegram_payslip_notification']) && $comSetting['telegram_payslip_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_payslip_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_payslip_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Award') }}</span>
                                                {{ Form::checkbox('telegram_award_notification', '1', isset($comSetting['telegram_award_notification']) && $comSetting['telegram_award_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_award_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_award_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Announcement') }}</span>
                                                {{ Form::checkbox('telegram_announcement_notification', '1', isset($comSetting['telegram_announcement_notification']) && $comSetting['telegram_announcement_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_announcement_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_announcement_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Holiday') }}</span>
                                                {{ Form::checkbox('telegram_holiday_notification', '1', isset($comSetting['telegram_holiday_notification']) && $comSetting['telegram_holiday_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_holiday_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_holiday_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Support Ticket') }}</span>
                                                {{ Form::checkbox('telegram_support_notification', '1', isset($comSetting['telegram_support_notification']) && $comSetting['telegram_support_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_support_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_support_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Event') }}</span>
                                                {{ Form::checkbox('telegram_event_notification', '1', isset($comSetting['telegram_event_notification']) && $comSetting['telegram_event_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_event_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_event_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Meeting') }}</span>
                                                {{ Form::checkbox('telegram_meeting_notification', '1', isset($comSetting['telegram_meeting_notification']) && $comSetting['telegram_meeting_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_meeting_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_meeting_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Company Policy') }}</span>
                                                {{ Form::checkbox('telegram_policy_notification', '1', isset($comSetting['telegram_policy_notification']) && $comSetting['telegram_policy_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_policy_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_policy_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Invoice') }}</span>
                                                {{ Form::checkbox('telegram_invoice_notification', '1', isset($comSetting['telegram_invoice_notification']) && $comSetting['telegram_invoice_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_invoice_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_invoice_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Revenue') }}</span>
                                                {{ Form::checkbox('telegram_revenue_notification', '1', isset($comSetting['telegram_revenue_notification']) && $comSetting['telegram_revenue_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_revenue_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_revenue_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Bill') }}</span>
                                                {{ Form::checkbox('telegram_bill_notification', '1', isset($comSetting['telegram_bill_notification']) && $comSetting['telegram_bill_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_bill_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_bill_notification"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Invoice Payment') }}</span>
                                                {{ Form::checkbox('telegram_payment_notification', '1', isset($comSetting['telegram_payment_notification']) && $comSetting['telegram_payment_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_payment_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_payment_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Budget') }}</span>
                                                {{ Form::checkbox('telegram_budget_notification', '1', isset($comSetting['telegram_budget_notification']) && $comSetting['telegram_budget_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_budget_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_budget_notification"></label>
                                            </div>
                                        </li>

                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer p-3 text-end">
                            <div class="form-group mb-0">
                                <input class="btn btn-print-invoice btn-primary" type="submit"
                                    value="{{ __('Save Changes') }}">
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Twilio Settings-->
                    <div id="twilio-settings" class="card">
                        <div class="card-header p-3">
                            <h5>{{ __('Twilio Settings') }}</h5>
                            <small class="text-muted">{{ __('Edit your Twilio settings') }}</small>
                        </div>
                        {{ Form::model($setting, ['route' => 'twilio.setting', 'method' => 'post']) }}
                        <div class="card-body p-3">
                            <div class="row row-gap-1">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        {{ Form::label('twilio_sid', __('Twilio SID '), ['class' => 'form-label']) }}
                                        {{ Form::text('twilio_sid', isset($comSetting['twilio_sid']) ? $comSetting['twilio_sid'] : '', ['class' => 'form-control w-100', 'placeholder' => __('Enter Twilio SID'), 'required' => 'required']) }}
                                        @error('twilio_sid')
                                            <span class="invalid-twilio_sid" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        {{ Form::label('twilio_token', __('Twilio Token'), ['class' => 'form-label']) }}
                                        {{ Form::text('twilio_token', isset($comSetting['twilio_token']) ? $comSetting['twilio_token'] : '', ['class' => 'form-control w-100', 'placeholder' => __('Enter Twilio Token'), 'required' => 'required']) }}
                                        @error('twilio_token')
                                            <span class="invalid-twilio_token" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        {{ Form::label('twilio_from', __('Twilio From'), ['class' => 'form-label']) }}
                                        {{ Form::text('twilio_from', isset($comSetting['twilio_from']) ? $comSetting['twilio_from'] : '', ['class' => 'form-control w-100', 'placeholder' => __('Enter Twilio From'), 'required' => 'required']) }}
                                        @error('twilio_from')
                                            <span class="invalid-twilio_from" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <h5 class="small-title mb-3">{{ __('Module Settings') }}</h5>
                                </div>
                            </div>
                            <div class="row gy-2">
                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Customer') }}</span>
                                                {{ Form::checkbox('twilio_customer_notification', '1', isset($comSetting['twilio_customer_notification']) && $comSetting['twilio_customer_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_customer_notification']) }}
                                                <label class="form-check-label"
                                                    for="twilio_customer_notification"></label>
                                            </div>

                                        </li>
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Vendor') }}</span>
                                                {{ Form::checkbox('twilio_vender_notification', '1', isset($comSetting['twilio_vender_notification']) && $comSetting['twilio_vender_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_vender_notification']) }}
                                                <label class="form-check-label"
                                                    for="twilio_vender_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Invoice') }}</span>
                                                {{ Form::checkbox('twilio_invoice_notification', '1', isset($comSetting['twilio_invoice_notification']) && $comSetting['twilio_invoice_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_invoice_notification']) }}
                                                <label class="form-check-label"
                                                    for="twilio_invoice_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Revenue') }}</span>
                                                {{ Form::checkbox('twilio_revenue_notification', '1', isset($comSetting['twilio_revenue_notification']) && $comSetting['twilio_revenue_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_revenue_notification']) }}
                                                <label class="form-check-label"
                                                    for="twilio_revenue_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Bill') }}</span>
                                                {{ Form::checkbox('twilio_bill_notification', '1', isset($comSetting['twilio_bill_notification']) && $comSetting['twilio_bill_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_bill_notification']) }}
                                                <label class="form-check-label" for="twilio_bill_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Proposal') }}</span>
                                                {{ Form::checkbox('twilio_proposal_notification', '1', isset($comSetting['twilio_proposal_notification']) && $comSetting['twilio_proposal_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_proposal_notification']) }}
                                                <label class="form-check-label"
                                                    for="twilio_proposal_notification"></label>
                                            </div>
                                        </li>

                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('New Payment') }}</span>
                                                {{ Form::checkbox('twilio_payment_notification', '1', isset($comSetting['twilio_payment_notification']) && $comSetting['twilio_payment_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_payment_notification']) }}
                                                <label class="form-check-label"
                                                    for="twilio_payment_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item">
                                            <div class=" form-switch form-switch-right">
                                                <span>{{ __('Invoice Reminder') }}</span>
                                                {{ Form::checkbox('twilio_reminder_notification', '1', isset($comSetting['twilio_reminder_notification']) && $comSetting['twilio_reminder_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'twilio_reminder_notification']) }}
                                                <label class="form-check-label"
                                                    for="twilio_reminder_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer p-3 text-end">
                            <div class="form-group mb-0">
                                <input class="btn btn-print-invoice btn-primary" type="submit"
                                    value="{{ __('Save Changes') }}">
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Email Notification Settings-->
                    <div id="email-notification-settings" class="card">
                        <div class="col-md-12">
                            <div class="card-header p-3">
                                <h5>{{ __('Email Notification Settings') }}</h5>
                                <small class="text-muted">{{ __('Edit email notification settings') }}</small>
                            </div>
                            {{ Form::model($setting, ['route' => ['status.email.language'], 'method' => 'post']) }}
                            @csrf
                            <div class="card-body p-3">
                                <div class="row row-gap-1">
                                    @foreach ($EmailTemplates as $EmailTemplate)
                                        @isset($EmailTemplate->template)
                                            <div class="col-lg-4 col-md-6 col-sm-6 form-group mb-0">
                                                <div class="list-group">
                                                    <div class="list-group-item form-switch form-switch-right p-3">
                                                        <label class="form-label mb-0">{{ $EmailTemplate->name }}</label>
                                                        {{--                                                    <input class="form-check-input email-template-checkbox" --}}
                                                        {{--                                                           id="email_tempalte_{{!empty($EmailTemplate->template)?$EmailTemplate->template->id:''}}" type="checkbox" --}}
                                                        {{--                                                           @if (!empty($EmailTemplate->template) ? $EmailTemplate->template->is_active : 0 == 1) checked="checked" @endif --}}
                                                        {{--                                                           type="checkbox" --}}
                                                        {{--                                                           value="{{!empty($EmailTemplate->template)?$EmailTemplate->template->is_active:1}}" --}}
                                                        {{--                                                           data-url="{{route('status.email.language',[!empty($EmailTemplate->template)?$EmailTemplate->template->id:''])}}" /> --}}
                                                        {{--                                                    <label class="form-check-label" for="email_tempalte_{{!empty($EmailTemplate->template)?$EmailTemplate->template->id:''}}"></label> --}}

                                                        <input class="form-check-input" name='{{ $EmailTemplate->id }}'
                                                            id="email_tempalte_{{ $EmailTemplate->template->id }}"
                                                            type="checkbox"
                                                            @if ($EmailTemplate->template->is_active == 1) checked="checked" @endif
                                                            type="checkbox" value="1"
                                                            data-url="{{ route('status.email.language', [$EmailTemplate->template->id]) }}" />
                                                        <label class="form-check-label"
                                                            for="email_tempalte_{{ $EmailTemplate->template->id }}"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endisset
                                    @endforeach
                                </div>

                            </div>
                            <div class="card-footer p-3 text-end">
                                <div class="form-group mb-0">
                                    <input class="btn btn-print-invoice btn-primary" type="submit"
                                        value="{{ __('Save Changes') }}">
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <!--Start HRM letter Settings-->

                    <div id="offer-letter-settings" class="card">
                        <div class="col-md-12">
                            <div
                                class="card-header p-3 d-flex flex-wrap align-items-center gap-3 justify-content-between">
                                <h5>{{ __('Offer Letter Settings') }}</h5>
                                <div class="d-flex drp-languages">
                                    <ul class="list-unstyled m-0">
                                        <li class="dropdown dash-h-item drp-language">
                                            <a class="dash-head-link dropdown-toggle arrow-none m-0"
                                                data-bs-toggle="dropdown" href="#" role="button"
                                                aria-haspopup="false" aria-expanded="false" id="dropdownLanguage">
                                                <span class="drp-text hide-mob text-primary me-2">
                                                    {{ ucfirst($offerlangName->full_name) }}
                                                </span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end"
                                                aria-labelledby="dropdownLanguage">
                                                @foreach ($currantLang as $code => $offerlangs)
                                                    <a href="{{ route('get.offerlatter.language', ['noclangs' => $noclang, 'explangs' => $explang, 'offerlangs' => $code, 'joininglangs' => $joininglang]) }}"
                                                        class="dropdown-item ms-1 {{ $offerlangs == $code ? 'text-primary' : '' }}">{{ ucFirst($offerlangs) }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </li>
                                    </ul>


                                </div>
                            </div>
                            <div class="card-body p-3">
                                <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="card mb-0">
                                        <div class="card-header p-3 card-body p-3">
                                            <div class="row">
                                                <p class="col-12 col-md-4 col-sm-6">{{ __('Applicant Name') }} : <span
                                                        class="pull-end text-primary">{applicant_name}</span></p>
                                                <p class="col-12 col-md-4 col-sm-6">{{ __('Company Name') }} : <span
                                                        class="pull-right text-primary">{app_name}</span></p>
                                                <p class="col-12 col-md-4 col-sm-6">{{ __('Job title') }} : <span
                                                        class="pull-right text-primary">{job_title}</span></p>
                                                <p class="col-12 col-md-4 col-sm-6">{{ __('Job type') }} : <span
                                                        class="pull-right text-primary">{job_type}</span></p>
                                                <p class="col-12 col-md-4 col-sm-6">{{ __('Proposed Start Date') }} :
                                                    <span class="pull-right text-primary">{start_date}</span></p>
                                                <p class="col-12 col-md-4 col-sm-6">{{ __('Working Location') }} : <span
                                                        class="pull-right text-primary">{workplace_location}</span>
                                                </p>
                                                <p class="col-12 col-md-4 col-sm-6">{{ __('Days Of Week') }} : <span
                                                        class="pull-right text-primary">{days_of_week}</span></p>
                                                <p class="col-12 col-md-4 col-sm-6">{{ __('Salary') }} : <span
                                                        class="pull-right text-primary">{salary}</span></p>
                                                <p class="col-12 col-md-4 col-sm-6">{{ __('Salary Type') }} : <span
                                                        class="pull-right text-primary">{salary_type}</span></p>
                                                <p class="col-12 col-md-4 col-sm-6">{{ __('Salary Duration') }} : <span
                                                        class="pull-end text-primary">{salary_duration}</span></p>
                                                <p class="col-12 col-md-4 col-sm-6">{{ __('Offer Expiration Date') }} :
                                                    <span class="pull-right text-primary">{offer_expiration_date}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3 table-border-style ">

                                {{ Form::open(['route' => ['offerlatter.update', $offerlang], 'method' => 'post']) }}
                                <div class="form-group col-12 mb-0">
                                    {{ Form::label('content', __(' Format'), ['class' => 'form-label text-dark']) }}
                                    <textarea name="content" class="summernote-simple0 summernote-simple">{!! isset($currOfferletterLang->content) ? $currOfferletterLang->content : '' !!}</textarea>

                                </div>
                                {{--                                <div class="card-footer p-3 text-end"> --}}
                                {{--                                    {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }} --}}
                                {{--                                </div> --}}

                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>

                    <div id="joining-letter-settings" class="card">
                        <div class="col-md-12">
                            <div
                                class="card-header p-3 d-flex flex-wrap align-items-center gap-3 justify-content-between">
                                <h5>{{ __('Joining Letter Settings') }}</h5>
                                <div class="d-flex drp-languages">
                                    <ul class="list-unstyled m-0">
                                        <li class="dropdown dash-h-item drp-language">
                                            <a class="dash-head-link dropdown-toggle arrow-none m-0"
                                                data-bs-toggle="dropdown" href="#" role="button"
                                                aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                                <span class="drp-text hide-mob text-primary me-2">
                                                    {{ ucfirst($joininglangName->full_name) }}
                                                </span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end"
                                                aria-labelledby="dropdownLanguage1">
                                                @foreach ($currantLang as $code => $joininglangs)
                                                    <a href="{{ route('get.joiningletter.language', ['noclangs' => $noclang, 'explangs' => $explang, 'offerlangs' => $offerlang, 'joininglangs' => $code]) }}"
                                                        class="dropdown-item {{ $joininglangs == $code ? 'text-primary' : '' }}">{{ ucFirst($joininglangs) }}</a>
                                                @endforeach
                                            </div>
                                        </li>

                                    </ul>

                                </div>

                            </div>
                            <div class="card-body p-3 ">
                                <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>

                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="card mb-0">
                                        <div class="card-header p-3 card-body p-3">
                                            <div class="row text-xs">
                                                <div class="row">
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Applicant Name') }} :
                                                        <span class="pull-end text-primary">{date}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Company Name') }} : <span
                                                            class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Employee Name') }} :
                                                        <span class="pull-right text-primary">{employee_name}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Address') }} : <span
                                                            class="pull-right text-primary">{address}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Designation') }} : <span
                                                            class="pull-right text-primary">{designation}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Start Date') }} : <span
                                                            class="pull-right text-primary">{start_date}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Branch') }} : <span
                                                            class="pull-right text-primary">{branch}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Start Time') }} : <span
                                                            class="pull-end text-primary">{start_time}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('End Time') }} : <span
                                                            class="pull-right text-primary">{end_time}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Number of Hours') }} :
                                                        <span class="pull-right text-primary">{total_hours}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3 table-border-style ">

                                {{ Form::open(['route' => ['joiningletter.update', $joininglang], 'method' => 'post']) }}
                                <div class="form-group col-12">
                                    {{ Form::label('content', __(' Format'), ['class' => 'form-label text-dark']) }}
                                    <textarea name="content" class="summernote-simple1 summernote-simple">{!! isset($currjoiningletterLang->content) ? $currjoiningletterLang->content : '' !!}</textarea>

                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>

                    <div id="experience-certificate-settings" class="card">
                        <div class="col-md-12">
                            <div
                                class="card-header p-3 d-flex flex-wrap align-items-center gap-3 justify-content-between">
                                <h5>{{ __('Experience Certificate Settings') }}</h5>
                                <div class="d-flex drp-languages">
                                    <ul class="list-unstyled m-0">
                                        <li class="dropdown dash-h-item drp-language">
                                            <a class="dash-head-link dropdown-toggle arrow-none m-0"
                                                data-bs-toggle="dropdown" href="#" role="button"
                                                aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                                <span class="drp-text hide-mob text-primary me-2">
                                                    {{ ucfirst($explangName->full_name) }}
                                                </span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end"
                                                aria-labelledby="dropdownLanguage1">
                                                @foreach ($currantLang as $code => $explangs)
                                                    <a href="{{ route('get.experiencecertificate.language', ['noclangs' => $noclang, 'explangs' => $code, 'offerlangs' => $offerlang, 'joininglangs' => $joininglang]) }}"
                                                        class="dropdown-item {{ $explangs == $code ? 'text-primary' : '' }}">{{ ucFirst($explangs) }}</a>
                                                @endforeach
                                            </div>
                                        </li>

                                    </ul>

                                </div>

                            </div>
                            <div class="card-body p-3 ">
                                <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>

                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="card mb-0">
                                        <div class="card-header p-3 card-body p-3">
                                            <div class="row text-xs">
                                                <div class="row">
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Company Name') }} : <span
                                                            class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Employee Name') }} :
                                                        <span class="pull-right text-primary">{employee_name}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Date of Issuance') }} :
                                                        <span class="pull-right text-primary">{date}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Designation') }} : <span
                                                            class="pull-right text-primary">{designation}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Start Date') }} : <span
                                                            class="pull-right text-primary">{start_date}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Branch') }} : <span
                                                            class="pull-right text-primary">{branch}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Start Time') }} : <span
                                                            class="pull-end text-primary">{start_time}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('End Time') }} : <span
                                                            class="pull-right text-primary">{end_time}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Number of Hours') }} :
                                                        <span class="pull-right text-primary">{total_hours}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3 table-border-style ">

                                {{ Form::open(['route' => ['experiencecertificate.update', $explang], 'method' => 'post']) }}
                                <div class="form-group col-12 mb-0">
                                    {{ Form::label('content', __(' Format'), ['class' => 'form-label text-dark']) }}
                                    <textarea name="content" class="summernote-simple2 summernote-simple">{!! isset($curr_exp_cetificate_Lang->content) ? $curr_exp_cetificate_Lang->content : '' !!}</textarea>

                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>

                    <div id="noc-settings" class="card">
                        <div class="col-md-12">
                            <div
                                class="card-header p-3 d-flex flex-wrap align-items-center gap-3 justify-content-between">
                                <h5>{{ __('NOC Settings') }}</h5>
                                <div class="d-flex drp-languages">
                                    <ul class="list-unstyled m-0">
                                        <li class="dropdown dash-h-item drp-language">
                                            <a class="dash-head-link dropdown-toggle arrow-none m-0"
                                                data-bs-toggle="dropdown" href="#" role="button"
                                                aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                                <span class="drp-text hide-mob text-primary me-2">
                                                    {{ ucfirst($noclangName->full_name) }}
                                                </span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end"
                                                aria-labelledby="dropdownLanguage1">
                                                @foreach ($currantLang as $code => $noclangs)
                                                    <a href="{{ route('get.noc.language', ['noclangs' => $code, 'explangs' => $explang, 'offerlangs' => $offerlang, 'joininglangs' => $joininglang]) }}"
                                                        class="dropdown-item {{ $noclangs == $code ? 'text-primary' : '' }}">{{ ucfirst($noclangs) }}</a>
                                                @endforeach
                                            </div>
                                        </li>

                                    </ul>

                                </div>
                            </div>
                            <div class="card-body p-3 ">
                                <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="card mb-0">
                                        <div class="card-header p-3 card-body p-3">
                                            <div class="row text-xs">
                                                <div class="row">
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Date') }} : <span
                                                            class="pull-end text-primary">{date}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Company Name') }} : <span
                                                            class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Employee Name') }} :
                                                        <span class="pull-right text-primary">{employee_name}</span></p>
                                                    <p class="col-12 col-md-4 col-sm-6">{{ __('Designation') }} : <span
                                                            class="pull-right text-primary">{designation}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3 table-border-style ">
                                {{ Form::open(['route' => ['noc.update', $noclang], 'method' => 'post']) }}
                                <div class="form-group col-12">
                                    {{ Form::label('content', __(' Format'), ['class' => 'form-label text-dark']) }}
                                    <textarea name="content" class="summernote-simple3 summernote-simple">{!! isset($currnocLang->content) ? $currnocLang->content : '' !!}</textarea>

                                </div>

                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>

                    <!--End HRM letter Settings-->

                    <div id="google-calender" class="card">
                        {{ Form::open(['url' => route('google.calender.settings'), 'enctype' => 'multipart/form-data']) }}
                        <div class="card-header p-3 d-flex align-items-center gap-2 justify-content-between flex-wrap">
                            <h5>{{ __('Google Calendar Settings') }}</h5>
                            <div class="form-group mb-0">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="google_calendar_enable" id="google_calendar_enable"
                                        data-toggle="switchbutton" data-onstyle="primary"
                                        {{ isset($setting['google_calendar_enable']) && $setting['google_calendar_enable'] == 'on' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="google_calendar_enable"></label>
                                </div>

                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="row row-gap-1">
                                <div class="mb-0 col-md-6 col-sm-12 form-group">
                                    {{ Form::label('Google calendar id', __('Google Calendar Id'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('google_clender_id', !empty($setting['google_clender_id']) ? $setting['google_clender_id'] : '', ['class' => 'form-control ', 'placeholder' => 'Google Calendar Id', 'required' => 'required']) }}
                                </div>
                                <div class="mb-0 col-md-6 col-sm-12 form-group">
                                    {{ Form::label('Google calendar json file', __('Google Calendar json File'), ['class' => 'col-form-label']) }}
                                    <input type="file" class="form-control" name="google_calender_json_file"
                                        id="file">
                                    {{-- {{Form::text('zoom_secret_key', !empty($settings['zoom_secret_key']) ? $settings['zoom_secret_key'] : '' ,array('class'=>'form-control', 'placeholder'=>'Google Calendar json File'))}} --}}
                                </div>
                            </div>
                        </div>
                        <div class="card-footer p-3 text-end">
                            <div class="form-group mb-0">
                                <button class="btn-submit btn btn-primary" type="submit">
                                    {{ __('Save Changes') }}
                                </button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                <div id="webhook-settings" class="card">
                    <div class="col-md-12">
                        <div class="card-header p-3 d-flex align-items-center gap-2 flex-wrap justify-content-between">
                                    <h5>{{ __('Webhook Settings') }}</h5>
                                @can('create webhook')
                                        <a href="#" data-size="lg" data-url="{{ route('webhook.create') }}"
                                            data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Create') }}"
                                            data-title="{{ __('Create New Webhook') }}" class="btn btn-sm btn-primary">
                                            <i class="ti ti-plus"></i>
                                        </a>

                                @endcan
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Module') }}</th>
                                            <th>{{ __('Url') }}</th>
                                            <th>{{ __('Method') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="font-style">
                                        @forelse ($webhookSetting as $webhooksetting)
                                            <tr>
                                                <td>{{ ucwords($webhooksetting->module) }}</td>
                                                <td>{{ $webhooksetting->url }}</td>
                                                <td>{{ ucwords($webhooksetting->method) }}</td>
                                                <td class="Action">
                                                    <span>
                                                        @can('edit webhook')
                                                            <div class="action-btn me-2">
                                                                <a href="#"
                                                                    class="mx-3 btn btn-sm align-items-center bg-info"
                                                                    data-url="{{ URL::to('webhook-settings/' . $webhooksetting->id . '/edit') }}"
                                                                    data-ajax-popup="true" data-bs-toggle="tooltip"
                                                                    title="{{ __('Edit') }}"
                                                                    data-title="{{ __('Webhook Edit') }}">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('delete webhook')
                                                            <div class="action-btn ">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['webhook.destroy', $webhooksetting->id],
                                                                    'id' => 'delete-form-' . $webhooksetting->id,
                                                                ]) !!}
                                                                <a href="#!"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger"
                                                                    data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                                    <i class="ti ti-trash text-white text-white"></i>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="text-center">
                                                <td colspan="4">{{ __('No Data Found.!') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="ip-restriction-settings" class="card">
                    <div class="col-md-12">
                        <div class="card-header p-3 d-flex align-items-center gap-2 flex-wrap justify-content-between">
                                    <h5>{{ __('IP Restriction Settings') }}</h5>
                                @can('create webhook')
                                        <a data-size="md" data-url="{{ route('create.ip') }}" data-ajax-popup="true"
                                            data-bs-toggle="tooltip" title="{{ __('Create') }}"
                                            data-title="{{ __('Create New IP') }}" class="btn btn-sm btn-primary">
                                            <i class="ti ti-plus text-white"></i>
                                        </a>

                                @endcan
                        </div>
                        <div class="card-body  table-border-style">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="w-75">{{ __('IP') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="font-style">
                                        @forelse ($ips as $ip)
                                            <tr>
                                                <td>{{ $ip->ip }}</td>

                                                <td class="Action">
                                                    <span>
                                                        @can('edit webhook')
                                                            <div class="action-btn me-2">
                                                                <a class="mx-3 btn btn-sm align-items-center bg-info"
                                                                    data-url="{{ route('edit.ip', $ip->id) }}"
                                                                    data-ajax-popup="true" data-bs-toggle="tooltip"
                                                                    title="{{ __('Edit') }}"
                                                                    data-title="{{ __('Edit IP') }}">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('delete webhook')
                                                            <div class="action-btn ">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['destroy.ip', $ip->id], 'id' => 'delete-form-' . $ip->id]) !!}
                                                                <a class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger"
                                                                    data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                                    <i class="ti ti-trash text-white text-white"></i>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="text-center">
                                                <td colspan="4">{{ __('No Data Found.!') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Biometric Attendance Seetings --}}
                {{-- <div class="card" id="biometric-attendance">
                        <div class="col-md-12">
                            {{ Form::open(['route' => ['biometric-settings.store'], 'method' => 'post']) }}
                            <div class="card-header p-3">
                                <div class="row">
                                    <div class="col-lg-8 col-md-8 col-sm-8">
                                        <h5 class="">
                                            {{ __('Biometric Attendance') }}
                                        </h5>
                                        <small class="text-muted">
                                            <b class="text-danger">{{ __('Note') }}: </b>
                                            {{ __('Note that you can use the biometric attendance system only if you are using the ZKTeco machine for biometric attendance.') }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        {{ Form::label('zkteco_api_url', __('ZKTeco Api URL'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('zkteco_api_url', !empty($setting['zkteco_api_url']) ? $setting['zkteco_api_url'] : '', ['class' => 'form-control ', 'placeholder' => 'ZKTeco Api URL', 'required' => 'required']) }}
                                        <small>
                                            <b class="text-dark">{{ __('Example:') }}</b> http://110.78.645.123:8080
                                        </small>
                                    </div>
                                    <div class="col-md-3 form-group">
                                        {{ Form::label('username', __('Username'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('username', !empty($setting['username']) ? $setting['username'] : '', ['class' => 'form-control ', 'placeholder' => 'Username', 'required' => 'required']) }}
                                    </div>
                                    <div class="col-md-3 form-group">
                                        {{ Form::label('user_password', __('Password'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('user_password', !empty($setting['user_password']) ? $setting['user_password'] : '', ['class' => 'form-control ', 'placeholder' => 'Password', 'required' => 'required']) }}
                                    </div>
                                    <div class="col-md-8 form-group">
                                        {{ Form::label('auth_token', __('Auth Token'), ['class' => 'form-label']) }}
                                        @if (empty($setting['auth_token']))
                                            <small class="text-danger">
                                                {{ __('Please first generate auth token.') }}
                                            </small>
                                        @endif
                                        {{ Form::textarea('', !empty($setting['auth_token']) ? $setting['auth_token'] : null, ['class' => 'form-control font-style', 'disabled' => 'disabled', 'rows' => 3]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer p-3 text-end">
                                <button class="btn-submit btn btn-primary" type="submit">
                                    {{ __('Generate Token') }}
                                </button>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div> --}}

            </div>
        </div>
    </div>
    </div>
@endsection
