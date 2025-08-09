@extends('lending::layouts.master')

@section('lending-content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Loan Applications</h1>
                <a href="{{ route('lending.loan-applications.create') }}" class="btn btn-primary">Create Loan Application</a>
                <hr>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Applicant</th>
                            <th>Loan Product</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loanApplications as $application)
                            <tr>
                                <td>{{ $application->id }}</td>
                                <td>{{ $application->applicant->name ?? 'N/A' }} ({{ $application->applicant_type }})</td>
                                <td>{{ $application->loanProduct->product_name ?? 'N/A' }}</td>
                                <td>{{ $application->loan_amount }}</td>
                                <td>{{ $application->status }}</td>
                                <td>
                                    <a href="{{ route('lending.loan-applications.show', $application->id) }}" class="btn btn-info">View</a>
                                    <a href="{{ route('lending.loan-applications.edit', $application->id) }}" class="btn btn-warning">Edit</a>
                                    <form action="{{ route('lending.loan-applications.destroy', $application->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $loanApplications->links() }}
            </div>
        </div>
    </div>
@endsection
