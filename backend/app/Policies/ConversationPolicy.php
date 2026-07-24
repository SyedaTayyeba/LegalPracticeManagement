<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() || $user->isPlatformAdmin() || $user->isClient();
    }

    /** Only actual participants (or the Firm Owner, for oversight) may read a thread. */
    public function view(User $user, Conversation $conversation): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }
        if ($user->firm_id !== $conversation->firm_id) {
            return false;
        }
        if ($user->isFirmOwner()) {
            return true;
        }

        return $conversation->participants()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isStaff() || $user->isClient();
    }

    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
