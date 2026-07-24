<?php

namespace App\Services;

use App\Models\Firm;
use App\Models\SubscriptionPlan;
use App\Models\User;

class SubscriptionService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    /**
     * Downgrades are blocked if the firm's current active staff count would
     * exceed the new plan's seat limit — the Firm Owner must deactivate
     * seats first. Upgrades are always allowed.
     */
    public function changePlan(User $actor, string $planKey): Firm
    {
        $plan = SubscriptionPlan::where('key', $planKey)->where('is_active', true)->firstOrFail();
        $firm = $actor->firm;

        $currentStaffCount = $firm->activeStaffCount();

        if ($currentStaffCount > $plan->seat_limit) {
            throw new \DomainException(
                "Cannot switch to {$plan->name}: your firm has {$currentStaffCount} active staff members, ".
                "which exceeds this plan's limit of {$plan->seat_limit}. Deactivate seats first."
            );
        }

        $firm->update([
            'plan' => $plan->key,
            'seat_limit' => $plan->seat_limit,
            'storage_limit_mb' => $plan->storage_limit_mb,
        ]);

        $this->auditLog->log('firm.plan_changed', $actor, $firm, ['new_plan' => $plan->key]);

        return $firm->fresh();
    }
}
