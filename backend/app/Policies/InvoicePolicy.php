<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() || $user->isPlatformAdmin() || $user->isClient();
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }
        if ($user->firm_id !== $invoice->firm_id) {
            return false;
        }
        if ($user->isClient()) {
            return $invoice->client->user_id === $user->id;
        }

        return $user->isStaff();
    }

    /** Only the Firm Owner manages invoices/billing (spec: "Firm Owner: Manage billing"). */
    public function create(User $user): bool
    {
        return $user->isFirmOwner();
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->isFirmOwner() && $user->firm_id === $invoice->firm_id;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->isFirmOwner() && $user->firm_id === $invoice->firm_id && $invoice->status === 'draft';
    }
}
