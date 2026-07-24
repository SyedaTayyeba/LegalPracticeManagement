<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'description' => $this->description,
            'minutes' => $this->minutes,
            'hourly_rate' => (float) $this->hourly_rate,
            'amount' => $this->amount(),
            'billable' => $this->billable,
            'entry_date' => $this->entry_date?->toDateString(),
            'is_invoiced' => $this->isInvoiced(),
            'case' => $this->whenLoaded('case', fn () => $this->case ? ['id' => $this->case->uuid, 'title' => $this->case->title] : null),
            'user' => $this->whenLoaded('user', fn () => ['id' => $this->user->uuid, 'name' => $this->user->name]),
        ];
    }
}
