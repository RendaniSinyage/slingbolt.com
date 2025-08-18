<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoicePaymentResource extends JsonResource
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
            'transaction_id' => $this->transaction_id,
            'invoice_id' => $this->invoice_id,
            'date' => $this->date,
            'amount' => $this->amount,
            'account_id' => $this->account_id,
            'bank_account' => new BankAccountResource($this->whenLoaded('bankAccount')),
            'payment_method' => $this->payment_method,
            'reference' => $this->reference,
            'description' => $this->description,
            'add_receipt' => $this->add_receipt,
        ];
    }
}
