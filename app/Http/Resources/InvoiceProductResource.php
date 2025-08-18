<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceProductResource extends JsonResource
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
            'product_id' => $this->product_id,
            'product' => new ProductServiceResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'price' => $this->price,
            'description' => $this->description,
        ];
    }
}
