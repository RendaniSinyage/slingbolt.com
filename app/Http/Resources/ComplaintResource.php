<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
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
            'complaint_from' => $this->complaint_from,
            'complaint_from_employee' => new EmployeeResource($this->whenLoaded('complaintFrom')),
            'complaint_against' => $this->complaint_against,
            'complaint_against_employee' => new EmployeeResource($this->whenLoaded('complaintAgainst')),
            'title' => $this->title,
            'complaint_date' => $this->complaint_date,
            'description' => $this->description,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
