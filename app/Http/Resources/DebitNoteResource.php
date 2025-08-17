<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DebitNoteResource extends JsonResource
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
            'bill' => $this->bill,
            'bill_details' => new BillResource($this->whenLoaded('bills')),
            'vender' => $this->vender,
            'vender_details' => new VenderResource($this->whenLoaded('venders')),
            'date' => $this->date,
            'amount' => $this->amount,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
