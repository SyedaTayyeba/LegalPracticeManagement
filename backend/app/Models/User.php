<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'firm_id', 'name', 'email', 'password', 'role',
        'phone', 'avatar_path', 'title', 'status',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            $user->uuid ??= (string) Str::uuid();
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

    /** Cases where this user is the lead lawyer — used by ReportService::lawyerWorkload(). */
    public function leadCases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CaseFile::class, 'lead_lawyer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | JWTSubject implementation
    |--------------------------------------------------------------------------
    */

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Custom claims embedded in the access token so every API request can be
     * tenant-scoped and role-checked WITHOUT an extra DB hit. On role or firm
     * change, existing tokens are invalidated (see AuthService::forceReauth()).
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'firm_id' => $this->firm_id,
            'role' => $this->role?->value,
            'uuid' => $this->uuid,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Role helpers
    |--------------------------------------------------------------------------
    */

    public function isPlatformAdmin(): bool
    {
        return $this->role === UserRole::PlatformAdmin;
    }

    public function isFirmOwner(): bool
    {
        return $this->role === UserRole::FirmOwner;
    }

    public function isClient(): bool
    {
        return $this->role === UserRole::Client;
    }

    public function isStaff(): bool
    {
        return $this->role?->isStaff() ?? false;
    }

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /** Belongs-to-tenant check used constantly by policies & middleware. */
    public function belongsToFirm(int $firmId): bool
    {
        return $this->firm_id === $firmId;
    }
}
