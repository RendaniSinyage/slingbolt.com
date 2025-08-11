@extends('lending::layouts.master')

@section('lending-content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Edit Loan</h1>
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
                <form action="{{ route('lending.loans.update', $loan->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('lending::loans._form', ['loan' => $loan])
                </form>
            </div>
        </div>
    </div>
@endsection
