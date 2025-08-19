<?php

namespace Modules\Lending\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_code' => $this->product_code,
            'product_name' => $this->product_name,
            'rate_of_interest' => $this->rate_of_interest,
            'interest_calculation_method' => $this->interest_calculation_method,
            'repayment_frequency' => $this->repayment_frequency,
            'minimum_loan_term' => $this->minimum_loan_term,
            'maximum_loan_term' => $this->maximum_loan_term,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
