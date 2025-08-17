<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'date' => $this->date,
            'amount' => $this->amount,
            'account_id' => $this->account_id,
            'bank_account' => new BankAccountResource($this->whenLoaded('bankAccount')),
            'vender_id' => $this->vender_id,
            'vender' => new VenderResource($this->whenLoaded('vender')),
            'category_id' => $this->category_id,
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'payment_method' => $this->payment_method,
            'reference' => $this->reference,
            'description' => $this->description,
            'add_receipt' => $this->add_receipt,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
