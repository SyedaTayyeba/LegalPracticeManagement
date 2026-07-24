<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'email' => $this->email,
            'role' => $this->role,
            'invited_by' => $this->inviter?->name,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'accepted' => $this->isAccepted(),
            'expired' => $this->isExpired(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
