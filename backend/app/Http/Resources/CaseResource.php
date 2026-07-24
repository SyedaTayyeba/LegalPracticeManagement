<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'case_number' => $this->case_number,
            'title' => $this->title,
            'case_type' => $this->case_type,
            'status' => $this->status,
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->uuid,
                'display_name' => $this->client->display_name,
            ]),
            'lead_lawyer' => $this->whenLoaded('leadLawyer', fn () => $this->leadLawyer ? [
                'id' => $this->leadLawyer->uuid,
                'name' => $this->leadLawyer->name,
            ] : null),
            'team' => $this->whenLoaded('team', fn () => $this->team->map(fn ($member) => [
                'id' => $member->uuid,
                'name' => $member->name,
                'role_on_case' => $member->pivot->role_on_case,
            ])),
            'opposing_party' => $this->opposing_party,
            'opposing_counsel' => $this->opposing_counsel,
            'court' => [
                'name' => $this->court_name,
                'case_number' => $this->court_case_number,
                'jurisdiction' => $this->court_jurisdiction,
            ],
            'opened_on' => $this->opened_on?->toDateString(),
            'filed_on' => $this->filed_on?->toDateString(),
            'closed_on' => $this->closed_on?->toDateString(),
            'description' => $this->when($request->routeIs('firm.cases.show'), $this->description),
            'notes' => CaseNoteResource::collection($this->whenLoaded('notes')),
            'status_history' => CaseStatusHistoryResource::collection($this->whenLoaded('statusHistory')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
