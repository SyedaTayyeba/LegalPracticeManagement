<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourtEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'title' => $this->title,
            'event_type' => $this->event_type,
            'location' => $this->location,
            'notes' => $this->notes,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'case' => $this->whenLoaded('case', fn () => $this->case ? [
                'id' => $this->case->uuid, 'title' => $this->case->title,
            ] : null),
            'lead_lawyer' => $this->whenLoaded('leadLawyer', fn () => $this->leadLawyer ? [
                'id' => $this->leadLawyer->uuid, 'name' => $this->leadLawyer->name,
            ] : null),
            'attendees' => $this->whenLoaded('attendees', fn () => $this->attendees->map(fn ($a) => [
                'id' => $a->uuid, 'name' => $a->name,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
