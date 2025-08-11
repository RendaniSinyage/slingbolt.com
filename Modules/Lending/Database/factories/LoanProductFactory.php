<?php

namespace Modules\Lending\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class LoanProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Lending\Entities\LoanProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_code' => $this->faker->unique()->ean8,
            'product_name' => $this->faker->words(3, true),
            'rate_of_interest' => $this->faker->randomFloat(4, 5, 20),
            'created_by' => User::factory(),
            'disbursement_account_id' => 1,
            'payment_account_id' => 1,
            'loan_account_id' => 1,
            'interest_income_account_id' => 1,
            'penalty_income_account_id' => 1,
            'write_off_account_id' => 1,
            'interest_receivable_account_id' => 1,
            'penalty_receivable_account_id' => 1,
        ];
    }
}
