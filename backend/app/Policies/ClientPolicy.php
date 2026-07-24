<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        // Portal clients don't get a firm-wide client list — only staff do.
        return $user->isStaff() || $user->isPlatformAdmin();
    }

    public function view(User $user, Client $client): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        if ($user->firm_id !== $client->firm_id) {
            return false;
        }

        // A portal client may only view their own linked record.
        if ($user->isClient()) {
            return $client->user_id === $user->id;
        }

        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->isStaff() && $user->firm_id === $client->firm_id;
    }

    public function delete(User $user, Client $client): bool
    {
        // Archiving/removing a client from the CRM is an owner-level action —
        // lawyers/paralegals can edit, but not delete.
        return $user->isFirmOwner() && $user->firm_id === $client->firm_id;
    }
}
