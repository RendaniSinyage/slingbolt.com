<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AppraisalResource extends JsonResource
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
            'branch' => $this->branch,
            'branches' => new BranchResource($this->whenLoaded('branches')),
            'employee' => $this->employee,
            'employees' => new EmployeeResource($this->whenLoaded('employees')),
            'appraisal_date' => $this->appraisal_date,
            'rating' => json_decode($this->rating),
            'remark' => $this->remark,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
