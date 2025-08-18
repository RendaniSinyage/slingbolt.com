<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'invoice_id' => $this->invoice_id,
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'send_date' => $this->send_date,
            'category_id' => $this->category_id,
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'ref_number' => $this->ref_number,
            'status' => $this->status,
            'status_label' => $this->getStatus(),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => InvoiceProductResource::collection($this->whenLoaded('items')),
            'payments' => InvoicePaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
