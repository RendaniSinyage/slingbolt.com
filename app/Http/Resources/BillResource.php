<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
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
            'bill_id' => $this->bill_id,
            'vender_id' => $this->vender_id,
            'vender' => new VenderResource($this->whenLoaded('vender')),
            'bill_date' => $this->bill_date,
            'due_date' => $this->due_date,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'status_label' => $this->getStatus(),
            'category_id' => $this->category_id,
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => BillProductResource::collection($this->whenLoaded('items')),
            'payments' => BillPaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
