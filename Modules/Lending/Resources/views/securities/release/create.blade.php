@extends('lending::layouts.master')

@section('lending-content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Release Securities for Assignment #{{ $assignment->id }}</h1>
                <p>This form demonstrates a full release of all pledged securities.</p>
                <hr>

                <h4>Pledged Securities:</h4>
                <ul>
                    @foreach($assignment->pledges as $pledge)
                        <li>{{ $pledge->security->loan_security_name }} (Value: {{ number_format($pledge->quantity_pledged, 2) }})</li>
                    @endforeach
                </ul>

                <form action="{{ route('lending.security-assignments.releases.store', $assignment->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">Confirm Full Release</button>
                    <a href="{{ route('lending.loans.show', $assignment->assignable_id) }}" class="btn btn-default">Cancel</a>
                </form>
            </div>
        </div>
    </div>
@endsection
