@extends('lending::layouts.master')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Record Repayment for Loan #{{ $loan->id }}</h1>
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
                <form action="{{ route('lending.loans.repayments.store', $loan->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="amount_paid">Amount Paid</label>
                        <input type="number" step="0.01" name="amount_paid" id="amount_paid" class="form-control" value="{{ old('amount_paid') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="payment_date">Payment Date</label>
                        <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="remarks">Remarks</label>
                        <textarea name="remarks" id="remarks" class="form-control">{{ old('remarks') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Repayment</button>
                </form>
            </div>
        </div>
    </div>
@endsection
