@extends('lending::layouts.master')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1>Loan Details #{{ $loan->id }}</h1>
                    <a href="{{ route('lending.loans.repayments.create', $loan->id) }}" class="btn btn-success">Record Repayment</a>
                </div>
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
                            <td>{{ number_format($loan->loan_amount, 2) }}</td>
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
                <a href="{{ route('lending.loans.index') }}" class="btn btn-default">Back to List</a>

                @if($loan->schedule)
                    <h2 class="mt-5">Repayment Schedule</h2>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Payment Date</th>
                                <th>Principal</th>
                                <th>Interest</th>
                                <th>Penalty</th>
                                <th>Total Payment</th>
                                <th>Outstanding Principal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loan->schedule->installments as $installment)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $installment->payment_date->format('Y-m-d') }}</td>
                                    <td>{{ number_format($installment->principal_amount, 2) }}</td>
                                    <td>{{ number_format($installment->interest_amount, 2) }}</td>
                                    <td>{{ number_format($installment->penalty_amount, 2) }}</td>
                                    <td>{{ number_format($installment->total_amount + $installment->penalty_amount, 2) }}</td>
                                    <td>{{ number_format($installment->outstanding_principal_balance, 2) }}</td>
                                    <td>{{ $installment->is_paid ? 'Paid' : 'Unpaid' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <h2 class="mt-5">Repayments History</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount Paid</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loan->repayments as $repayment)
                            <tr>
                                <td>{{ $repayment->payment_date->format('Y-m-d') }}</td>
                                <td>{{ number_format($repayment->amount_paid, 2) }}</td>
                                <td>{{ $repayment->remarks }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">No repayments recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
