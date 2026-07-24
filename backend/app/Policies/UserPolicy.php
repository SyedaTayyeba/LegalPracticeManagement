<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $actor, User $target): bool
    {
        return $actor->isPlatformAdmin() || $actor->firm_id === $target->firm_id;
    }

    /** Only the Firm Owner can suspend/reactivate/change roles for staff and clients. */
    public function update(User $actor, User $target): bool
    {
        if ($actor->isPlatformAdmin()) {
            return true;
        }

        if (! $actor->isFirmOwner() || $actor->firm_id !== $target->firm_id) {
            return false;
        }

        // Firm Owners cannot suspend themselves via this endpoint.
        return $actor->id !== $target->id;
    }
}
