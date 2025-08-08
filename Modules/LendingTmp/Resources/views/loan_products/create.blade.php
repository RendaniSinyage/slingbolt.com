@extends('lendingtmp::layouts.master')

@section('content')
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
                <form action="{{ route('lendingtmp.loan-products.store') }}" method="POST">
                    @csrf
                    @include('lendingtmp::loan_products._form')
                </form>
            </div>
        </div>
    </div>
@endsection
