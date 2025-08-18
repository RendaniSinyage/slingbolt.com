<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReferralSettingResource extends JsonResource
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
            'percentage' => $this->percentage,
            'minimum_threshold_amount' => $this->minimum_threshold_amount,
            'is_enable' => $this->is_enable,
            'guideline' => $this->guideline,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
