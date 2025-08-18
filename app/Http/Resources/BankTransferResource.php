<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BankTransferResource extends JsonResource
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
            'from_account' => $this->from_account,
            'from_bank_account' => new BankAccountResource($this->whenLoaded('fromBankAccount')),
            'to_account' => $this->to_account,
            'to_bank_account' => new BankAccountResource($this->whenLoaded('toBankAccount')),
            'amount' => $this->amount,
            'date' => $this->date,
            'payment_method' => $this->payment_method,
            'reference' => $this->reference,
            'description' => $this->description,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
