<?php

namespace Modules\Lending\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Modules\Lending\Entities\LoanProduct;

class LoanApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Lending\Entities\LoanApplication::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'applicant_type' => 'App\Models\Customer',
            'applicant_id' => \App\Models\Customer::factory(),
            'loan_product_id' => LoanProduct::factory(),
            'loan_amount' => $this->faker->numberBetween(1000, 100000),
            'status' => 'Incomplete',
            'created_by' => User::factory(),
        ];
    }
}
