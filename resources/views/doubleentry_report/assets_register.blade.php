@extends('layouts.admin')

@section('page-title')
    {{ __('Assets Register') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Assets Register') }}</li>
@endsection

@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        var filename = $('#filename').val();

        function saveAsPDF() {
            var printContents = document.getElementById('printableArea').innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#filter").click(function() {
                $("#show_filter").toggle();
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            callback();

            function callback() {
                var start_date = $(".startDate").val();
                var end_date = $(".endDate").val();

                $('.start_date').val(start_date);
                $('.end_date').val(end_date);
            }
        });
    </script>
@endpush

@section('action-btn')
    <div class="float-end">
        <a href="#" onclick="saveAsPDF()" class="btn btn-sm btn-primary-subtle me-1" data-bs-toggle="tooltip"
            title="{{ __('Print') }}" data-original-title="{{ __('Print') }}"><i class="ti ti-printer"></i></a>
    </div>
    <div class="float-end me-2">
        {{ Form::open(['route' => ['reports.assets.register.export']]) }}
        <input type="hidden" name="start_date" class="start_date">
        <input type="hidden" name="end_date" class="end_date">
        <button type="submit" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="{{ __('Export') }}"
            data-original-title="{{ __('Export') }}"><i class="ti ti-file-export"></i></button>
        {{ Form::close() }}
    </div>

    <div class="float-end me-2" id="filter">
        <button id="filter" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Filter') }}" data-original-title="{{ __('Filter') }}"><i class="ti ti-filter"></i></button>
    </div>
@endsection

@section('content')
    <div class="mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="card" id="show_filter" style="display:none;">
                        <div class="card-body">
                            {{ Form::open(['route' => ['reports.assets.register'], 'method' => 'GET', 'id' => 'report_assets_register']) }}
                            <div class="col-xl-12">
                                <div class="row justify-content-between">
                                    <div class="col-xl-12">
                                        <div class="row justify-content-end align-items-center">
                                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                                <div class="btn-box">
                                                    {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                                    {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'startDate form-control']) }}
                                                </div>
                                            </div>

                                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                                <div class="btn-box">
                                                    {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                                    {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'endDate form-control']) }}
                                                </div>
                                            </div>

                                            <div class="col-auto mt-4">
                                                <a href="#" class="btn btn-sm btn-primary"
                                                    onclick="document.getElementById('report_assets_register').submit(); return false;"
                                                    data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                                    data-original-title="{{ __('apply') }}">
                                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                                </a>

                                                <a href="{{ route('reports.assets.register') }}"
                                                    class="btn btn-sm btn-danger " data-bs-toggle="tooltip"
                                                    title="{{ __('Reset') }}" data-original-title="{{ __('Reset') }}">
                                                    <span class="btn-inner--icon"><i
                                                            class="ti ti-refresh text-white-off "></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center" id="printableArea">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                      <h5>{{ 'Assets Register of ' . Auth::user()->name . ' as of ' . \Carbon\Carbon::parse($filter['endDateRange'])->format('d F Y') }}</h5>
                        </h5>
                    </div>
                    <div class="card-body overflow-auto">
                        <div class="account-table-inner">
                            <div class="account-title d-flex align-items-center justify-content-between border-top border-bottom py-2">
                                <h6 class="mb-0">{{ __('Account') }}</h6>
                                <h6 class="mb-0 text-center">{{ __('Account Code') }}</h6>
                                <h6 class="mb-0 text-center">{{ __('Asset Type') }}</h6>
                                <h6 class="mb-0 text-center">{{ __('Purchase Date') }}</h6>
                                <h6 class="mb-0 text-center">{{ __('Current Value') }}</h6>
                                <h6 class="mb-0 text-center">{{ __('Depreciation') }}</h6>
                                <h6 class="mb-0 text-end">{{ __('Net Value') }}</h6>
                            </div>

                            @if(count($assetAccounts) > 0)
                                @php
                                    $currentAssets = [];
                                    $nonCurrentAssets = [];

                                    foreach($assetAccounts as $asset) {
                                        if($asset['sub_type'] == 'Current Asset') {
                                            $currentAssets[] = $asset;
                                        } else {
                                            $nonCurrentAssets[] = $asset;
                                        }
                                    }
                                @endphp

                                {{-- Current Assets Section --}}
                                @if(count($currentAssets) > 0)
                                    <div class="account-main-inner py-2">
                                        <p class="fw-bold ps-2 mb-2">{{ __('Current Assets') }}</p>

                                        @foreach($currentAssets as $asset)
                                            @php
                                                $currentValue = $asset['is_depreciation'] ? 0 : $asset['balance'];
                                                $depreciation = $asset['is_depreciation'] ? abs($asset['balance']) : 0;
                                                $netValue = $currentValue - $depreciation;
                                            @endphp
                                            <div class="account-inner d-flex align-items-center justify-content-between ps-md-5 ps-3 border-bottom py-2">
                                                <p class="mb-2">
                                                    <a href="{{ route('report.ledger', $asset['account_id']) }}?account={{ $asset['account_id'] }}"
                                                       class="text-primary">{{ $asset['account_name'] }}</a>
                                                </p>
                                                <p class="mb-2 text-center">{{ $asset['account_code'] }}</p>
                                                <p class="mb-2 text-center">{{ $asset['sub_type'] }}</p>
                                                <p class="mb-2 text-center">
                                                    @if($asset['purchase_date'])
                                                        {{ date('d M Y', strtotime($asset['purchase_date'])) }}
                                                    @else
                                                        -
                                                    @endif
                                                </p>
                                                <p class="mb-2 text-center">{{ \Auth::user()->priceFormat($currentValue) }}</p>
                                                <p class="mb-2 text-center">{{ \Auth::user()->priceFormat($depreciation) }}</p>
                                                <p class="text-primary mb-2 text-end">{{ \Auth::user()->priceFormat($netValue) }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Non-Current Assets Section --}}
                                @if(count($nonCurrentAssets) > 0)
                                    <div class="account-main-inner py-2">
                                        <p class="fw-bold ps-2 mb-2">{{ __('Non-Current Assets') }}</p>

                                        @foreach($nonCurrentAssets as $asset)
                                            @php
                                                $currentValue = $asset['is_depreciation'] ? 0 : $asset['balance'];
                                                $depreciation = $asset['is_depreciation'] ? abs($asset['balance']) : 0;
                                                $netValue = $currentValue - $depreciation;
                                            @endphp
                                            <div class="account-inner d-flex align-items-center justify-content-between ps-md-5 ps-3 border-bottom py-2">
                                                <p class="mb-2">
                                                    <a href="{{ route('report.ledger', $asset['account_id']) }}?account={{ $asset['account_id'] }}"
                                                       class="text-primary">{{ $asset['account_name'] }}</a>
                                                </p>
                                                <p class="mb-2 text-center">{{ $asset['account_code'] }}</p>
                                                <p class="mb-2 text-center">{{ $asset['sub_type'] }}</p>
                                                <p class="mb-2 text-center">
                                                    @if($asset['purchase_date'])
                                                        {{ date('d M Y', strtotime($asset['purchase_date'])) }}
                                                    @else
                                                        -
                                                    @endif
                                                </p>
                                                <p class="mb-2 text-center">{{ \Auth::user()->priceFormat($currentValue) }}</p>
                                                <p class="mb-2 text-center">{{ \Auth::user()->priceFormat($depreciation) }}</p>
                                                <p class="text-primary mb-2 text-end">{{ \Auth::user()->priceFormat($netValue) }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Totals Section --}}
                                <div class="account-title d-flex align-items-center justify-content-between border-top border-bottom py-2 px-2 pe-0">
                                    <h6 class="fw-bold mb-0">{{ __('SUMMARY') }}</h6>
                                    <h6 class="fw-bold mb-0 text-end">{{ __('') }}</h6>
                                </div>

                                <div class="account-inner d-flex align-items-center justify-content-between ps-4 py-2">
                                    <p class="fw-bold mb-2">{{ __('Total Asset Value') }}</p>
                                    <p class="fw-bold mb-2 text-end">{{ \Auth::user()->priceFormat($totalAssetValue) }}</p>
                                </div>

                                <div class="account-inner d-flex align-items-center justify-content-between ps-4 py-2">
                                    <p class="fw-bold mb-2">{{ __('Total Depreciation') }}</p>
                                    <p class="fw-bold mb-2 text-end">{{ \Auth::user()->priceFormat($totalDepreciation) }}</p>
                                </div>

                                <div class="account-title d-flex align-items-center justify-content-between border-top border-bottom py-2 px-2 pe-0">
                                    <h6 class="fw-bold mb-0">{{ __('Net Asset Value') }}</h6>
                                    <h6 class="fw-bold mb-0 text-end">{{ \Auth::user()->priceFormat($netAssetValue) }}</h6>
                                </div>

                            @else
                                <div class="text-center py-4">
                                    <h6>{{ __('No assets found for the selected period.') }}</h6>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
