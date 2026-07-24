<?php

namespace App\Policies;

use App\Models\TimeEntry;
use App\Models\User;

class TimeEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, TimeEntry $entry): bool
    {
        return $user->firm_id === $entry->firm_id && $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /** Owner may edit anyone's entries; a lawyer/paralegal may only edit their own,
     *  and only while it hasn't already been billed on an invoice. */
    public function update(User $user, TimeEntry $entry): bool
    {
        if ($user->firm_id !== $entry->firm_id || $entry->isInvoiced()) {
            return false;
        }

        return $user->isFirmOwner() || $entry->user_id === $user->id;
    }

    public function delete(User $user, TimeEntry $entry): bool
    {
        return $this->update($user, $entry);
    }
}
