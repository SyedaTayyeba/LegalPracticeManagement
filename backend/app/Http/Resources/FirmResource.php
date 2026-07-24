<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FirmResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (! $this->resource) {
            return [];
        }

        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'email' => $this->email,
            'phone' => $this->phone,
            'plan' => $this->plan,
            'seat_limit' => $this->seat_limit,
            'storage_limit_mb' => $this->storage_limit_mb,
            'status' => $this->status,
            'staff_count' => $this->when(
                $this->relationLoaded('users') || $request->routeIs('firms.show'),
                fn () => $this->activeStaffCount()
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
