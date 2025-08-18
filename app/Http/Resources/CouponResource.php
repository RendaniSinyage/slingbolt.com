<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
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
            'discount' => $this->discount,
            'limit' => $this->limit,
            'is_active' => $this->is_active,
            'used_coupon' => $this->used_coupon(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
