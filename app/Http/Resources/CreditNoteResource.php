<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CreditNoteResource extends JsonResource
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
            'invoice' => $this->invoice,
            'invoice_details' => new InvoiceResource($this->whenLoaded('invoices')),
            'customer' => $this->customer,
            'customer_details' => new CustomerResource($this->whenLoaded('customers')),
            'date' => $this->date,
            'amount' => $this->amount,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
