@extends('lending::layouts.master')

@section('lending-content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Create Loan Product</h1>
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
                <form action="{{ route('lending.loan-products.store') }}" method="POST">
                    @csrf
                    @include('lending::loan_products._form')
                </form>
            </div>
        </div>
    </div>
@endsection
