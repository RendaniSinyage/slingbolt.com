@extends('lending::layouts.master')

@section('lending-content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Loans</h1>
                <a href="{{ route('lending.loans.create') }}" class="btn btn-primary">Create Loan</a>
                <hr>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Applicant</th>
                            <th>Loan Product</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Posting Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loans as $loan)
                            <tr>
                                <td>{{ $loan->id }}</td>
                                <td>{{ $loan->applicant->name ?? 'N/A' }}</td>
                                <td>{{ $loan->loanProduct->product_name ?? 'N/A' }}</td>
                                <td>{{ $loan->loan_amount }}</td>
                                <td>{{ $loan->status }}</td>
                                <td>{{ $loan->posting_date->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('lending.loans.show', $loan->id) }}" class="btn btn-info">View</a>
                                    <a href="{{ route('lending.loans.edit', $loan->id) }}" class="btn btn-warning">Edit</a>
                                    <form action="{{ route('lending.loans.destroy', $loan->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $loans->links() }}
            </div>
        </div>
    </div>
@endsection
