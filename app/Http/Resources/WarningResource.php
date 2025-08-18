<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WarningResource extends JsonResource
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
            'warning_by' => $this->warning_by,
            'warning_by_employee' => new EmployeeResource($this->whenLoaded('warningBy')),
            'warning_to' => $this->warning_to,
            'warning_to_employee' => new EmployeeResource($this->whenLoaded('warningTo')),
            'subject' => $this->subject,
            'warning_date' => $this->warning_date,
            'description' => $this->description,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
