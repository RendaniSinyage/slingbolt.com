@extends('lending::layouts.master')

@section('lending-content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Loan Products</h1>
                <a href="{{ route('lending.loan-products.create') }}" class="btn btn-primary">Create Loan Product</a>
                <hr>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Interest Rate</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loanProducts as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>{{ $product->product_code }}</td>
                                <td>{{ $product->product_name }}</td>
                                <td>{{ $product->rate_of_interest }}%</td>
                                <td>
                                    <a href="{{ route('lending.loan-products.show', $product->id) }}" class="btn btn-info">View</a>
                                    <a href="{{ route('lending.loan-products.edit', $product->id) }}" class="btn btn-warning">Edit</a>
                                    <form action="{{ route('lending.loan-products.destroy', $product->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $loanProducts->links() }}
            </div>
        </div>
    </div>
@endsection
