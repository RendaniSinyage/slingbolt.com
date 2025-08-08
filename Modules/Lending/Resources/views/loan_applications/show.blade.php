@extends('lending::layouts.master')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Loan Application Details</h1>
                <hr>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>ID</th>
                            <td>{{ $application->id }}</td>
                        </tr>
                        <tr>
                            <th>Applicant</th>
                            <td>{{ $application->applicant->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Loan Product</th>
                            <td>{{ $application->loanProduct->product_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Loan Amount</th>
                            <td>{{ $application->loan_amount }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>{{ $application->status }}</td>
                        </tr>
                        <tr>
                            <th>Repayment Method</th>
                            <td>{{ $application->repayment_method }}</td>
                        </tr>
                        <tr>
                            <th>Repayment Periods</th>
                            <td>{{ $application->repayment_periods }}</td>
                        </tr>
                    </tbody>
                </table>
                <a href="{{ route('lending.loan-applications.index') }}" class="btn btn-default">Back to List</a>
            </div>
        </div>
    </div>
@endsection
