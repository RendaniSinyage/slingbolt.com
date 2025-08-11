@extends('lending::layouts.master')

@section('lending-content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Restructure Loan #{{ $loan->id }}</h1>
                <hr>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('lending.loans.restructures.store', $loan->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="restructure_date">Restructure Date</label>
                        <input type="date" name="restructure_date" id="restructure_date" class="form-control" value="{{ old('restructure_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="new_rate_of_interest">New Rate of Interest (%)</label>
                        <input type="number" step="0.01" name="new_rate_of_interest" id="new_rate_of_interest" class="form-control" value="{{ old('new_rate_of_interest', $loan->rate_of_interest) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="new_repayment_periods">New Repayment Periods (Months)</label>
                        <input type="number" name="new_repayment_periods" id="new_repayment_periods" class="form-control" value="{{ old('new_repayment_periods', $loan->repayment_periods) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="reason">Reason for Restructuring</label>
                        <textarea name="reason" id="reason" class="form-control">{{ old('reason') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Restructure</button>
                </form>
            </div>
        </div>
    </div>
@endsection
