<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'incurred_on' => $this->incurred_on?->toDateString(),
            'billable' => $this->billable,
            'is_invoiced' => $this->invoice_line_item_id !== null,
            'case' => $this->whenLoaded('case', fn () => $this->case ? ['id' => $this->case->uuid, 'title' => $this->case->title] : null),
        ];
    }
}
