<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Named CaseFile, not Case — `Case` is a reserved word in PHP and cannot be
 * used as a class name. Maps to the `cases` table.
 */
class CaseFile extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cases';

    protected $fillable = [
        'firm_id', 'case_number', 'title', 'case_type', 'client_id', 'lead_lawyer_id',
        'opposing_party', 'opposing_counsel', 'court_name', 'court_case_number', 'court_jurisdiction',
        'status', 'opened_on', 'filed_on', 'closed_on', 'description', 'created_by',
    ];

    protected $casts = [
        'opened_on' => 'date',
        'filed_on' => 'date',
        'closed_on' => 'date',
    ];

    public const STATUSES = ['new', 'investigation', 'active', 'waiting', 'completed', 'closed'];

    protected static function booted(): void
    {
        static::creating(function (CaseFile $case) {
            $case->uuid ??= (string) Str::uuid();
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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function leadLawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_lawyer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Everyone assigned to work this case (lead + support), independent of leadLawyer(). */
    public function team(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'case_team')
            ->withPivot('role_on_case')
            ->withTimestamps();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CaseNote::class)->orderByDesc('pinned')->orderByDesc('created_at');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(CaseStatusHistory::class)->orderByDesc('created_at');
    }

    // documents(): MorphMany — wired once Module 5 (Document Management) lands.
    // tasks(): HasMany — wired once Module 6 (Task & Deadline Management) lands.
    // hearings(): HasMany — wired once Module 7 (Court Calendar) lands.

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->whereFullText(['title', 'case_number'], $term)
            ->orWhere('case_number', 'like', "%{$term}%");
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, ['completed', 'closed'], true);
    }
}
