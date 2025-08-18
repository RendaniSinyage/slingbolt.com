<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTaskResource extends JsonResource
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
            'description' => $this->description,
            'estimated_hrs' => $this->estimated_hrs,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'priority' => $this->priority,
            'priority_color' => $this->priority_color,
            'assign_to' => $this->assign_to,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'project_id' => $this->project_id,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'milestone_id' => $this->milestone_id,
            'milestone' => new MilestoneResource($this->whenLoaded('milestone')),
            'stage_id' => $this->stage_id,
            'stage' => new TaskStageResource($this->whenLoaded('stage')),
            'order' => $this->order,
            'is_favourite' => $this->is_favourite,
            'is_complete' => $this->is_complete,
            'marked_at' => $this->marked_at,
            'progress' => $this->progress,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'comments' => TaskCommentResource::collection($this->whenLoaded('comments')),
            'taskFiles' => TaskFileResource::collection($this->whenLoaded('taskFiles')),
            'checklist' => TaskChecklistResource::collection($this->whenLoaded('checklist')),
        ];
    }
}
