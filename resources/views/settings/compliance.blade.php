@extends('layouts.admin')

@section('page-title')
    {{__('Compliance Settings')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Compliance Settings')}}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Default Compliance Settings') }}</h5>
                <small class="text-muted">{{ __('These are the default settings that will be applied to all companies unless they provide their own overrides.') }}</small>
            </div>
            <div class="card-body">
                <form action="{{ route('compliance.settings.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="max_interest_rate" class="form-label">{{ __('Maximum Interest Rate (% per annum)') }}</label>
                        <input type="number" step="0.01" name="max_interest_rate" id="max_interest_rate" class="form-control" value="{{ old('max_interest_rate', $settings->max_interest_rate ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="max_initiation_fee" class="form-label">{{ __('Maximum Initiation Fee') }}</label>
                        <input type="number" step="0.01" name="max_initiation_fee" id="max_initiation_fee" class="form-control" value="{{ old('max_initiation_fee', $settings->max_initiation_fee ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="max_monthly_service_fee" class="form-label">{{ __('Maximum Monthly Service Fee') }}</label>
                        <input type="number" step="0.01" name="max_monthly_service_fee" id="max_monthly_service_fee" class="form-control" value="{{ old('max_monthly_service_fee', $settings->max_monthly_service_fee ?? '') }}">
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">{{ __('Save Settings') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
