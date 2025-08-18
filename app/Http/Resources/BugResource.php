<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BugResource extends JsonResource
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
            'bug_id' => $this->bug_id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'priority' => $this->priority,
            'start_date' => $this->start_date,
            'due_date' => $this->due_date,
            'description' => $this->description,
            'status_id' => $this->status,
            'status' => new BugStatusResource($this->whenLoaded('bugStatus')),
            'assign_to' => $this->assign_to,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
