<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'assigned_to' => $this->assigned_to,
            'creator' => $this->whenLoaded('creator', fn () => new UserResource($this->creator)),
            'assignee' => $this->whenLoaded('assignee', fn () => $this->assignee ? new UserResource($this->assignee) : null),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'replies' => ReplyResource::collection($this->whenLoaded('replies')),
            'notes' => NoteResource::collection($this->whenLoaded('notes')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
