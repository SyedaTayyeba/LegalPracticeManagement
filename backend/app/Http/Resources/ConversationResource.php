<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'subject' => $this->subject,
            'case' => $this->whenLoaded('case', fn () => $this->case ? ['id' => $this->case->uuid, 'title' => $this->case->title] : null),
            'client' => $this->whenLoaded('client', fn () => $this->client ? ['id' => $this->client->uuid, 'display_name' => $this->client->display_name] : null),
            'participants' => $this->whenLoaded('participants', fn () => $this->participants->map(fn ($p) => ['id' => $p->uuid, 'name' => $p->name])),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
