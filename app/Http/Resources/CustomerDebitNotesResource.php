<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerDebitNotesResource extends JsonResource
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
            'debit_id' => $this->debit_id,
            'bill' => $this->bill,
            'bill_product' => $this->bill_product,
            'date' => $this->date,
            'amount' => $this->amount,
            'status' => $this->status,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'bill_details' => new BillResource($this->whenLoaded('bills')),
        ];
    }
}
