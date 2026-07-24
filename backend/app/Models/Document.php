<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'firm_id', 'case_id', 'client_id', 'folder_id', 'category', 'name', 'original_filename',
        'disk', 'path', 'mime_type', 'size_bytes', 'root_document_id', 'version',
        'is_latest_version', 'client_visible', 'uploaded_by',
    ];

    protected $casts = [
        'client_visible' => 'boolean',
        'is_latest_version' => 'boolean',
        'size_bytes' => 'integer',
        'version' => 'integer',
    ];

    public const CATEGORIES = ['contract', 'agreement', 'evidence', 'court_file', 'client_document', 'other'];

    protected static function booted(): void
    {
        static::creating(function (Document $document) {
            $document->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(DocumentFolder::class, 'folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function rootDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'root_document_id');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->whereFullText(['name', 'original_filename'], $term)
            ->orWhere('name', 'like', "%{$term}%");
    }
}
