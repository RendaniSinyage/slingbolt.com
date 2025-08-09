@extends('lending::layouts.master')

@section('lending-content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Collections Report</h1>
                <hr>
                <form action="{{ route('lending.reports.collections') }}" method="GET" class="form-inline">
                    <div class="form-group mb-2">
                        <label for="start_date">Start Date:</label>
                        <input type="date" name="start_date" id="start_date" class="form-control mx-sm-3" value="{{ $startDate }}">
                    </div>
                    <div class="form-group mb-2">
                        <label for="end_date">End Date:</label>
                        <input type="date" name="end_date" id="end_date" class="form-control mx-sm-3" value="{{ $endDate }}">
                    </div>
                    <button type="submit" class="btn btn-primary mb-2">Filter</button>
                </form>
                <hr>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Repayment ID</th>
                            <th>Loan ID</th>
                            <th>Applicant</th>
                            <th>Payment Date</th>
                            <th>Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($repayments as $repayment)
                            <tr>
                                <td>{{ $repayment->id }}</td>
                                <td>{{ $repayment->loan_id }}</td>
                                <td>{{ $repayment->loan->applicant->name ?? 'N/A' }}</td>
                                <td>{{ $repayment->payment_date->format('Y-m-d') }}</td>
                                <td>{{ number_format($repayment->amount_paid, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No collections found for the selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-right">Total Collections:</th>
                            <th>{{ number_format($repayments->sum('amount_paid'), 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
