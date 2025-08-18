<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
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
            'price' => $this->price,
            'phone' => $this->phone,
            'pipeline_id' => $this->pipeline_id,
            'pipeline' => new PipelineResource($this->whenLoaded('pipeline')),
            'stage_id' => $this->stage_id,
            'stage' => new StageResource($this->whenLoaded('stage')),
            'sources' => $this->sources,
            'products' => $this->products,
            'notes' => $this->notes,
            'labels' => $this->labels,
            'status' => $this->status,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'users' => UserResource::collection($this->whenLoaded('users')),
            'clients' => ClientResource::collection($this->whenLoaded('clients')),
            'discussions' => DiscussionResource::collection($this->whenLoaded('discussions')),
            'files' => DealFileResource::collection($this->whenLoaded('files')),
            'tasks' => DealTaskResource::collection($this->whenLoaded('tasks')),
        ];
    }
}
