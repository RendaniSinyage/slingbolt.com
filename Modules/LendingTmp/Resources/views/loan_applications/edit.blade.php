@extends('lendingtmp::layouts.master')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Edit Loan Application</h1>
                <hr>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('lendingtmp.loan-applications.update', $application->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('lendingtmp::loan_applications._form', ['application' => $application])
                </form>
            </div>
        </div>
    </div>
@endsection
