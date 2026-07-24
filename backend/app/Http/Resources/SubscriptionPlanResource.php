<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'seat_limit' => $this->seat_limit,
            'storage_limit_mb' => $this->storage_limit_mb,
            'price_monthly' => (float) $this->price_monthly,
            'features' => $this->features,
        ];
    }
}
