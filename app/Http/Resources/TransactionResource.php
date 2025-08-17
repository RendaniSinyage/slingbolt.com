<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'user_id' => $this->user_id,
            'user_type' => $this->user_type,
            'type' => $this->type,
            'account' => $this->account,
            'bank_account' => new BankAccountResource($this->whenLoaded('bankAccount')),
            'amount' => $this->amount,
            'description' => $this->description,
            'date' => $this->date,
            'created_by' => $this->created_by,
            'payment_id' => $this->payment_id,
            'category' => $this->category,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
