<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() || $user->isPlatformAdmin() || $user->isClient();
    }

    public function view(User $user, Document $document): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        if ($user->firm_id !== $document->firm_id) {
            return false;
        }

        if ($user->isClient()) {
            if (! $document->client_visible) {
                return false;
            }
            // Visible only if the document belongs to this client directly, or to a
            // case belonging to this client.
            $ownsDirectly = $document->client && $document->client->user_id === $user->id;
            $ownsViaCase = $document->case && $document->case->client && $document->case->client->user_id === $user->id;

            return $ownsDirectly || $ownsViaCase;
        }

        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Document $document): bool
    {
        return $user->isStaff() && $user->firm_id === $document->firm_id;
    }

    /** Deleting/replacing a document from the record is Firm Owner or the uploader only. */
    public function delete(User $user, Document $document): bool
    {
        if ($user->firm_id !== $document->firm_id) {
            return false;
        }

        return $user->isFirmOwner() || $document->uploaded_by === $user->id;
    }
}
