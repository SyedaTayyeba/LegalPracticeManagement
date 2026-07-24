<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date?->toDateString(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'is_overdue' => $this->isOverdue(),
            'assignee' => $this->whenLoaded('assignee', fn () => $this->assignee ? [
                'id' => $this->assignee->uuid, 'name' => $this->assignee->name,
            ] : null),
            'case' => $this->whenLoaded('case', fn () => $this->case ? [
                'id' => $this->case->uuid, 'title' => $this->case->title,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
