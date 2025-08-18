<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FormBuilderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'is_active' => $this->is_active,
            'is_lead_active' => $this->is_lead_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'fields' => FormFieldResource::collection($this->whenLoaded('form_field')),
            'responses' => FormResponseResource::collection($this->whenLoaded('responses')),
        ];
    }
}
