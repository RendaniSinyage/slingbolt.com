<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'pipeline_id' => $this->pipeline_id,
            'pipeline' => new PipelineResource($this->whenLoaded('pipeline')),
            'stage_id' => $this->stage_id,
            'stage' => new LeadStageResource($this->whenLoaded('stage')),
            'sources' => $this->sources,
            'products' => $this->products,
            'notes' => $this->notes,
            'labels' => $this->labels,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'is_converted' => $this->is_converted,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'users' => UserResource::collection($this->whenLoaded('users')),
            'discussions' => DiscussionResource::collection($this->whenLoaded('discussions')),
            'files' => LeadFileResource::collection($this->whenLoaded('files')),
        ];
    }
}
