@extends('lending::layouts.master')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Loan Product Details</h1>
                <hr>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>ID</th>
                            <td>{{ $product->id }}</td>
                        </tr>
                        <tr>
                            <th>Product Code</th>
                            <td>{{ $product->product_code }}</td>
                        </tr>
                        <tr>
                            <th>Product Name</th>
                            <td>{{ $product->product_name }}</td>
                        </tr>
                        <tr>
                            <th>Rate of Interest</th>
                            <td>{{ $product->rate_of_interest }}%</td>
                        </tr>
                        <tr>
                            <th>Penalty Interest Rate</th>
                            <td>{{ $product->penalty_interest_rate }}%</td>
                        </tr>
                        <tr>
                            <th>Maximum Loan Amount</th>
                            <td>{{ $product->maximum_loan_amount }}</td>
                        </tr>
                        <tr>
                            <th>Is Term Loan?</th>
                            <td>{{ $product->is_term_loan ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr>
                            <th>Disabled?</th>
                            <td>{{ $product->disabled ? 'Yes' : 'No' }}</td>
                        </tr>
                    </tbody>
                </table>
                <a href="{{ route('lending.loan-products.index') }}" class="btn btn-default">Back to List</a>
            </div>
        </div>
    </div>
@endsection
