<?php

namespace Modules\Lending\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Modules\Lending\Entities\LoanProduct;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LoanApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_loan_application_and_upload_document()
    {
        // 1. Arrange
        $user = User::factory()->create();
        $this->actingAs($user);
        $loanProduct = LoanProduct::factory()->create(['created_by' => $user->id]);
        Storage::fake('private');

        // 2. Act
        $applicationData = [
            'applicant_type' => 'App\Models\Customer',
            'applicant_id' => \App\Models\Customer::factory()->create(['created_by' => $user->id])->id,
            'loan_product_id' => $loanProduct->id,
            'loan_amount' => 50000,
            'monthly_income' => 15000,
            'monthly_debt' => 3000,
            'reversed_debit_orders_last_3_months' => 0,
            'failed_debit_orders_last_3_months' => ['month1' => 0, 'month2' => 1, 'month3' => 0],
        ];

        $response = $this->post(route('lending.loan-applications.store'), $applicationData);

        // 3. Assert
        $response->assertRedirect(route('lending.loan-applications.index'));
        $this->assertDatabaseHas('loan_applications', ['loan_amount' => 50000]);
        $application = \Modules\Lending\Entities\LoanApplication::first();
        $this->assertEquals('Pending Review', $application->status);
        $this->assertEquals('eligible', $application->recommendation);

        // Test Document Upload
        $documentData = [
            'document' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')
        ];
        $response = $this->post(route('lending.loan-applications.documents.store', $application->id), $documentData);

        $response->assertRedirect(route('lending.loan-applications.documents.index', $application->id));
        $this->assertDatabaseHas('loan_documents', ['loan_application_id' => $application->id]);
        $document = \Modules\Lending\Entities\LoanDocument::first();
        Storage::disk('private')->assertExists($document->file_path);
    }
}
