<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentFolderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'case_id' => $this->case?->uuid,
            'parent_folder_id' => $this->parent?->uuid,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
