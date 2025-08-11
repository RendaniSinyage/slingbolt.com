<?php

namespace Modules\Lending\Console;

use Illuminate\Console\Command;
use Modules\Lending\Entities\LoanApplication;
use Illuminate\Support\Facades\Storage;

class CleanupLoanDocuments extends Command
{
    protected $signature = 'lending:cleanup-documents';
    protected $description = 'Deletes documents for rejected or cancelled loan applications.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Cleaning up loan documents...');

        $applications = LoanApplication::whereIn('status', ['Rejected', 'Cancelled'])->get();

        foreach ($applications as $application) {
            foreach ($application->documents as $document) {
                Storage::disk('private')->delete($document->file_path);
                $document->delete();
                $this->info("Deleted document: {$document->file_name}");
            }
            // Optional: once all documents are deleted, we could also delete the application itself
            // or mark it as archived. For now, we'll just delete the documents.
        }

        $this->info('Loan document cleanup complete.');
    }
}
