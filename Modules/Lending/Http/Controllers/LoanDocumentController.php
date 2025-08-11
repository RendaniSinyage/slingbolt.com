<?php

namespace Modules\Lending\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lending\Entities\LoanApplication;
use Modules\Lending\Entities\LoanDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class LoanDocumentController extends Controller
{
    public function index(LoanApplication $application)
    {
        $documents = $application->documents;
        return view('lending::loan_documents.index', compact('application', 'documents'));
    }

    public function create(LoanApplication $application)
    {
        return view('lending::loan_documents.create', compact('application'));
    }

    public function store(Request $request, LoanApplication $application)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf|max:5120', // 5MB max
        ]);

        $file = $request->file('document');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('loan_documents/' . $application->id, $fileName, 'private');

        $application->documents()->create([
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('lending.loan-applications.documents.index', $application->id)
            ->with('success', 'Document uploaded successfully.');
    }

    public function download(LoanDocument $document)
    {
        // This assumes 'private' disk is configured in filesystems.php
        // and is not publicly accessible.
        return Storage::disk('private')->download($document->file_path, $document->file_name);
    }

    public function destroy(LoanDocument $document)
    {
        Storage::disk('private')->delete($document->file_path);
        $document->delete();

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }
}
