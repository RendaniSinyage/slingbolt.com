@extends('lending::layouts.master')

@section('lending-content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Loan Securities</h1>
                <a href="{{ route('lending.loan-securities.create') }}" class="btn btn-primary">Create Loan Security</a>
                <hr>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($securities as $security)
                            <tr>
                                <td>{{ $security->id }}</td>
                                <td>{{ $security->loan_security_code }}</td>
                                <td>{{ $security->loan_security_name }}</td>
                                <td>{{ $security->loanSecurityType->name ?? 'N/A' }}</td>
                                <td>{{ number_format($security->original_security_value, 2) }}</td>
                                <td>
                                    {{-- Add show, edit, delete buttons here --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $securities->links() }}
            </div>
        </div>
    </div>
@endsection
