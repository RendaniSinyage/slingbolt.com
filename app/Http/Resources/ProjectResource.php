<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'project_name' => $this->project_name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'project_image' => $this->project_image,
            'budget' => $this->budget,
            'description' => $this->description,
            'status' => $this->status,
            'estimated_hrs' => $this->estimated_hrs,
            'tags' => $this->tags,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'client' => new ClientResource($this->whenLoaded('client')),
            'users' => UserResource::collection($this->whenLoaded('users')),
            'tasks' => ProjectTaskResource::collection($this->whenLoaded('tasks')),
            'milestones' => MilestoneResource::collection($this->whenLoaded('milestones')),
            'expenses' => ExpenseResource::collection($this->whenLoaded('expenses')),
            'bugs' => BugResource::collection($this->whenLoaded('bugs')),
        ];
    }
}
