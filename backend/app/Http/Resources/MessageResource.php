<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'body' => $this->body,
            'sender' => $this->whenLoaded('sender', fn () => ['id' => $this->sender->uuid, 'name' => $this->sender->name]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
