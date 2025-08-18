<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JournalItemResource extends JsonResource
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
            'journal' => $this->journal,
            'account' => $this->account,
            'chart_of_account' => new ChartOfAccountResource($this->whenLoaded('chartOfAccount')),
            'description' => $this->description,
            'debit' => $this->debit,
            'credit' => $this->credit,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
