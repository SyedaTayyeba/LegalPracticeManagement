<?php

namespace App\Policies;

use App\Models\User;

/**
 * Not tied to a specific model — reports are firm-wide aggregates, so this
 * policy is checked via Gate::authorize('viewReports') rather than against
 * an Eloquent instance. Registered as a closure-based Gate (see
 * AuthServiceProvider::boot()) rather than a model policy.
 */
class ReportPolicy
{
    public function viewReports(User $user): bool
    {
        return $user->isFirmOwner() || $user->isPlatformAdmin();
    }
}
