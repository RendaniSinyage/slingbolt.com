<?php

namespace Modules\Lending\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Lending\Services\EligibilityService;
use Modules\Lending\Entities\LoanApplication;
use App\Models\User;
use Modules\Lending\Entities\LoanProduct;

class EligibilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $eligibilityService;

    public function setUp(): void
    {
        parent::setUp();
        $this->eligibilityService = new EligibilityService();
    }

    private function createApplication(array $attributes = [])
    {
        $user = User::factory()->create();
        $loanProduct = LoanProduct::factory()->create(['created_by' => $user->id]);

        return LoanApplication::factory()->create(array_merge([
            'created_by' => $user->id,
            'loan_product_id' => $loanProduct->id,
            'monthly_income' => 10000,
            'monthly_debt' => 2000,
            'reversed_debit_orders_last_3_months' => 0,
            'failed_debit_orders_last_3_months' => ['month1' => 0, 'month2' => 0, 'month3' => 0],
        ], $attributes));
    }

    public function test_it_recommends_eligible_for_good_application()
    {
        $application = $this->createApplication();
        $this->eligibilityService->checkEligibility($application);
        $this->assertEquals('eligible', $application->recommendation);
    }

    public function test_it_recommends_ineligible_for_reversed_debit_orders()
    {
        $application = $this->createApplication(['reversed_debit_orders_last_3_months' => 1]);
        $this->eligibilityService->checkEligibility($application);
        $this->assertEquals('ineligible', $application->recommendation);
    }

    public function test_it_recommends_needs_review_for_failed_debit_orders()
    {
        $application = $this->createApplication(['failed_debit_orders_last_3_months' => ['month1' => 2, 'month2' => 0, 'month3' => 0]]);
        $this->eligibilityService->checkEligibility($application);
        $this->assertEquals('needs_review', $application->recommendation);
    }

    public function test_it_recommends_ineligible_for_low_income()
    {
        $application = $this->createApplication(['monthly_income' => 4000]);
        $this->eligibilityService->checkEligibility($application);
        $this->assertEquals('ineligible', $application->recommendation);
    }

    public function test_it_recommends_ineligible_for_high_dti()
    {
        $application = $this->createApplication(['monthly_income' => 10000, 'monthly_debt' => 5000]);
        $this->eligibilityService->checkEligibility($application);
        $this->assertEquals('ineligible', $application->recommendation);
    }
}
