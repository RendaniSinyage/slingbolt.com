@extends('lending::layouts.master')

@section('lending-content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Compliance Settings</h1>
                <p>Here you can override the default system compliance settings for your company.</p>
                <hr>
                <form action="{{ route('lending.settings.compliance.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="max_interest_rate" class="form-label">{{ __('Maximum Interest Rate (% per annum)') }}</label>
                        <input type="number" step="0.01" name="max_interest_rate" id="max_interest_rate" class="form-control" placeholder="Default: {{ $defaults->max_interest_rate ?? 'Not Set' }}" value="{{ old('max_interest_rate', $settings->max_interest_rate ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="max_initiation_fee" class="form-label">{{ __('Maximum Initiation Fee') }}</label>
                        <input type="number" step="0.01" name="max_initiation_fee" id="max_initiation_fee" class="form-control" placeholder="Default: {{ $defaults->max_initiation_fee ?? 'Not Set' }}" value="{{ old('max_initiation_fee', $settings->max_initiation_fee ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="max_monthly_service_fee" class="form-label">{{ __('Maximum Monthly Service Fee') }}</label>
                        <input type="number" step="0.01" name="max_monthly_service_fee" id="max_monthly_service_fee" class="form-control" placeholder="Default: {{ $defaults->max_monthly_service_fee ?? 'Not Set' }}" value="{{ old('max_monthly_service_fee', $settings->max_monthly_service_fee ?? '') }}">
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">{{ __('Save Settings') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
