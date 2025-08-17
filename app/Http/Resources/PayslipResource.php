<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PayslipResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'net_payble' => $this->net_payble,
            'salary_month' => $this->salary_month,
            'status' => $this->status,
            'basic_salary' => $this->basic_salary,
            'allowance' => json_decode($this->allowance),
            'commission' => json_decode($this->commission),
            'loan' => json_decode($this->loan),
            'saturation_deduction' => json_decode($this->saturation_deduction),
            'other_payment' => json_decode($this->other_payment),
            'overtime' => json_decode($this->overtime),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
