@extends('lending::layouts.master')

@section('content')
    <h1>Upload Document for Loan Application #{{ $application->id }}</h1>

    <form action="{{ route('lending.loan-applications.documents.store', $application->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="document">Document (PDF only, max 5MB)</label>
            <input type="file" name="document" id="document" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
@endsection
