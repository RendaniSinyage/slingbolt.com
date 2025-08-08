@extends('lendingtmp::layouts.master')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Loan Details</h1>
                <hr>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>ID</th>
                            <td>{{ $loan->id }}</td>
                        </tr>
                        <tr>
                            <th>Applicant</th>
                            <td>{{ $loan->applicant->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Loan Product</th>
                            <td>{{ $loan->loanProduct->product_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Loan Amount</th>
                            <td>{{ $loan->loan_amount }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>{{ $loan->status }}</td>
                        </tr>
                        <tr>
                            <th>Posting Date</th>
                            <td>{{ $loan->posting_date->format('Y-m-d') }}</td>
                        </tr>
                    </tbody>
                </table>
                <a href="{{ route('lendingtmp.loans.index') }}" class="btn btn-default">Back to List</a>
            </div>
        </div>
    </div>
@endsection
