<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\User;

class InvitationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isFirmOwner();
    }

    public function delete(User $user, Invitation $invitation): bool
    {
        return $user->isFirmOwner() && $user->firm_id === $invitation->firm_id;
    }
}
