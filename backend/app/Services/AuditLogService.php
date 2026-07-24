<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Record an action against the current tenant. Called from every module's
     * service layer (auth, invitations, and later cases/documents/billing)
     * so there is one consistent, queryable trail per firm.
     */
    public function log(
        string $action,
        ?User $actor = null,
        ?Model $subject = null,
        array $metadata = [],
        ?Request $request = null
    ): AuditLog {
        $request ??= request();

        return AuditLog::create([
            'firm_id' => $actor?->firm_id ?? $subject?->firm_id ?? null,
            'user_id' => $actor?->id,
            'action' => $action,
            'auditable_type' => $subject ? $subject::class : null,
            'auditable_id' => $subject?->getKey(),
            'metadata' => $metadata,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
