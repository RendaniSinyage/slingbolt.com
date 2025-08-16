<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
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
            'category_id' => $this->category_id,
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'amount' => $this->amount,
            'date' => $this->date,
            'project_id' => $this->project,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'description' => $this->description,
            'receipt' => $this->receipt,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
