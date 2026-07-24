<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'original_filename' => $this->original_filename,
            'category' => $this->category,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'version' => $this->version,
            'client_visible' => $this->client_visible,
            'download_count' => $this->download_count,
            'case' => $this->whenLoaded('case', fn () => $this->case ? [
                'id' => $this->case->uuid, 'title' => $this->case->title,
            ] : null),
            'client' => $this->whenLoaded('client', fn () => $this->client ? [
                'id' => $this->client->uuid, 'display_name' => $this->client->display_name,
            ] : null),
            'uploaded_by' => $this->whenLoaded('uploader', fn () => $this->uploader?->name),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
