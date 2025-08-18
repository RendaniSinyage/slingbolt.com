<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            'name' => $this->name,
            'price' => $this->price,
            'duration' => $this->duration,
            'max_users' => $this->max_users,
            'max_customers' => $this->max_customers,
            'max_venders' => $this->max_venders,
            'storage_limit' => $this->storage_limit,
            'enable_project' => $this->project,
            'enable_crm' => $this->crm,
            'enable_hrm' => $this->hrm,
            'enable_account' => $this->account,
            'enable_pos' => $this->pos,
            'enable_chatgpt' => $this->chatgpt,
            'enable_tenders' => $this->tenders,
            'trial' => $this->trial,
            'trial_days' => $this->trial_days,
            'is_disable' => $this->is_disable,
            'image' => $this->image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
