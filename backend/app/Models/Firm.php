<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Firm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'email', 'phone',
        'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country',
        'plan', 'seat_limit', 'storage_limit_mb', 'trial_ends_at', 'status', 'owner_id',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'seat_limit' => 'integer',
        'storage_limit_mb' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Firm $firm) {
            $firm->uuid ??= (string) Str::uuid();
            $firm->slug ??= Str::slug($firm->name).'-'.Str::lower(Str::random(6));
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function activeStaffCount(): int
    {
        return $this->users()->whereIn('role', ['firm_owner', 'lawyer', 'paralegal'])
            ->where('status', 'active')->count();
    }

    public function hasReachedSeatLimit(): bool
    {
        return $this->activeStaffCount() >= $this->seat_limit;
    }
}
