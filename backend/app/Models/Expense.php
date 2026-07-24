<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id', 'case_id', 'description', 'amount', 'incurred_on',
        'billable', 'invoice_line_item_id', 'created_by',
    ];

    protected $casts = ['amount' => 'decimal:2', 'incurred_on' => 'date', 'billable' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (Expense $expense) {
            $expense->uuid ??= (string) Str::uuid();
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

    public function invoiceLineItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceLineItem::class);
    }
}
