<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id', 'case_id', 'user_id', 'description', 'minutes', 'hourly_rate',
        'billable', 'entry_date', 'invoice_line_item_id',
    ];

    protected $casts = [
        'billable' => 'boolean',
        'entry_date' => 'date',
        'hourly_rate' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (TimeEntry $entry) {
            $entry->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceLineItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceLineItem::class);
    }

    public function amount(): float
    {
        return round(($this->minutes / 60) * (float) $this->hourly_rate, 2);
    }

    public function isInvoiced(): bool
    {
        return $this->invoice_line_item_id !== null;
    }
}
