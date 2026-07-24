<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'invoice_number' => $this->invoice_number,
            'status' => $this->status,
            'issue_date' => $this->issue_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'paid_on' => $this->paid_on?->toDateString(),
            'subtotal' => (float) $this->subtotal,
            'tax_rate' => (float) $this->tax_rate,
            'tax_amount' => (float) $this->tax_amount,
            'total' => (float) $this->total,
            'is_overdue' => $this->isOverdue(),
            'notes' => $this->notes,
            'client' => $this->whenLoaded('client', fn () => $this->client ? ['id' => $this->client->uuid, 'display_name' => $this->client->display_name] : null),
            'case' => $this->whenLoaded('case', fn () => $this->case ? ['id' => $this->case->uuid, 'title' => $this->case->title] : null),
            'line_items' => $this->whenLoaded('lineItems', fn () => $this->lineItems->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'amount' => (float) $item->amount,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
