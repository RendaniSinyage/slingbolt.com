@extends('lending::layouts.master')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                {{-- ... Loan Details and Repayment Button ... --}}

                <h2 class="mt-5">Pledged Securities</h2>
                @forelse($loan->securityAssignments as $assignment)
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between">
                            <span>Assignment #{{ $assignment->id }} - Status: {{ $assignment->status }}</span>
                            @if($assignment->status == 'Pledged')
                                <a href="{{ route('lending.security-assignments.releases.create', $assignment->id) }}" class="btn btn-sm btn-warning">Release Securities</a>
                            @endif
                        </div>
                        <div class="card-body">
                            <ul>
                                @foreach($assignment->pledges as $pledge)
                                    <li>{{ $pledge->security->loan_security_name }} (Value: {{ number_format($pledge->quantity_pledged, 2) }})</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @empty
                    <p>No securities pledged for this loan.</p>
                @endforelse

                @if($loan->schedule)
                    {{-- ... Repayment Schedule Table ... --}}
                @endif

                {{-- ... Repayments History Table ... --}}
            </div>
        </div>
    </div>
@endsection
