<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupportResource extends JsonResource
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
            'subject' => $this->subject,
            'priority' => $this->priority,
            'end_date' => $this->end_date,
            'ticket_code' => $this->ticket_code,
            'status' => $this->status,
            'attachment' => $this->attachment,
            'description' => $this->description,
            'created_by' => $this->created_by,
            'created_by_user' => new UserResource($this->whenLoaded('createdBy')),
            'user' => $this->user,
            'assign_user' => new UserResource($this->whenLoaded('assignUser')),
            'ticket_created' => $this->ticket_created,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'replies' => SupportReplyResource::collection($this->whenLoaded('replies')),
        ];
    }
}
