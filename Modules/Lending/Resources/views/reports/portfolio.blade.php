@extends('lending::layouts.master')

@section('lending-content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Loan Portfolio Report</h1>
                <p>Showing all active loans.</p>
                <hr>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Loan ID</th>
                            <th>Applicant</th>
                            <th>Loan Product</th>
                            <th>Status</th>
                            <th>Loan Amount</th>
                            <th>Disbursement Date</th>
                            {{-- Add more columns as needed --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                            <tr>
                                <td>{{ $loan->id }}</td>
                                <td>{{ $loan->applicant->name ?? 'N/A' }}</td>
                                <td>{{ $loan->loanProduct->product_name ?? 'N/A' }}</td>
                                <td>{{ $loan->status }}</td>
                                <td>{{ number_format($loan->loan_amount, 2) }}</td>
                                <td>{{ $loan->disbursement_date ? $loan->disbursement_date->format('Y-m-d') : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No active loans found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
