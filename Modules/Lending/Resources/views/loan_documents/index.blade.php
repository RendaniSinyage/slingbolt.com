@extends('lending::layouts.master')

@section('content')
    <h1>Documents for Loan Application #{{ $application->id }}</h1>

    <a href="{{ route('lending.loan-applications.documents.create', $application->id) }}" class="btn btn-primary mb-3">Upload Document</a>

    <table class="table">
        <thead>
            <tr>
                <th>File Name</th>
                <th>File Size</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documents as $document)
                <tr>
                    <td>{{ $document->file_name }}</td>
                    <td>{{ round($document->file_size / 1024, 2) }} KB</td>
                    <td>
                        <a href="{{ route('lending.loan-applications.documents.download', $document->id) }}" class="btn btn-sm btn-success">Download</a>
                        <form action="{{ route('lending.loan-applications.documents.destroy', $document->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
