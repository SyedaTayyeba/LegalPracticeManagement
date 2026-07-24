<?php

namespace App\Policies;

use App\Models\CourtEvent;
use App\Models\User;

class CourtEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() || $user->isPlatformAdmin() || $user->isClient();
    }

    public function view(User $user, CourtEvent $event): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }
        if ($user->firm_id !== $event->firm_id) {
            return false;
        }
        if ($user->isClient()) {
            return $event->case && $event->case->client && $event->case->client->user_id === $user->id;
        }

        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, CourtEvent $event): bool
    {
        if ($user->firm_id !== $event->firm_id || ! $user->isStaff()) {
            return false;
        }

        return $user->isFirmOwner() || $event->created_by === $user->id || $event->lead_lawyer_id === $user->id;
    }

    public function delete(User $user, CourtEvent $event): bool
    {
        return $this->update($user, $event);
    }
}
