<?php

namespace App\Policies;

use App\Models\CaseFile;
use App\Models\User;

class CasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() || $user->isPlatformAdmin() || $user->isClient();
    }

    public function view(User $user, CaseFile $case): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        if ($user->firm_id !== $case->firm_id) {
            return false;
        }

        if ($user->isClient()) {
            // A portal client may view a case only if it belongs to their own client record.
            return $case->client->user_id === $user->id;
        }

        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /** Firm Owner, the case's lead lawyer, or any assigned team member may edit. */
    public function update(User $user, CaseFile $case): bool
    {
        if ($user->firm_id !== $case->firm_id || ! $user->isStaff()) {
            return false;
        }

        if ($user->isFirmOwner()) {
            return true;
        }

        return $case->lead_lawyer_id === $user->id
            || $case->team()->where('users.id', $user->id)->exists();
    }

    /** Closing/deleting a matter is reserved for the Firm Owner or the lead lawyer. */
    public function delete(User $user, CaseFile $case): bool
    {
        if ($user->firm_id !== $case->firm_id) {
            return false;
        }

        return $user->isFirmOwner() || $case->lead_lawyer_id === $user->id;
    }
}
