<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() || $user->isPlatformAdmin();
    }

    public function view(User $user, Task $task): bool
    {
        return $user->isPlatformAdmin() || $user->firm_id === $task->firm_id;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /** Owner, the task creator, or the assignee may update/complete a task. */
    public function update(User $user, Task $task): bool
    {
        if ($user->firm_id !== $task->firm_id || ! $user->isStaff()) {
            return false;
        }

        return $user->isFirmOwner() || $task->created_by === $user->id || $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->firm_id === $task->firm_id && ($user->isFirmOwner() || $task->created_by === $user->id);
    }
}
