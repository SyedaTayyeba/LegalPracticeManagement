<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'firm_id', 'user_id', 'type', 'display_name', 'first_name', 'last_name',
        'organization_name', 'email', 'phone', 'secondary_phone',
        'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country',
        'status', 'intake_notes', 'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            $client->uuid ??= (string) Str::uuid();

            // Derive the denormalized display_name if the caller didn't set one explicitly.
            if (empty($client->display_name)) {
                $client->display_name = $client->type === 'organization'
                    ? $client->organization_name
                    : trim("{$client->first_name} {$client->last_name}");
            }
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

    /** The portal login this client uses, if any (see Module 8: Client Portal). */
    public function portalUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ClientNote::class)->orderByDesc('pinned')->orderByDesc('created_at');
    }

    public function cases(): HasMany
    {
        return $this->hasMany(CaseFile::class, 'client_id');
    }

    // documents(): MorphMany — wired once Module 5 (Document Management) lands.

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->whereFullText(['display_name', 'email'], $term)
            ->orWhere('phone', 'like', "%{$term}%");
    }
}
