@extends('lending::layouts.master')

@section('lending-content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Write Off Loan #{{ $loan->id }}</h1>
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
                <form action="{{ route('lending.loans.write-offs.store', $loan->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="write_off_date">Write-Off Date</label>
                        <input type="date" name="write_off_date" id="write_off_date" class="form-control" value="{{ old('write_off_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="remarks">Remarks</label>
                        <textarea name="remarks" id="remarks" class="form-control">{{ old('remarks') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-danger">Confirm Write-Off</button>
                </form>
            </div>
        </div>
    </div>
@endsection
