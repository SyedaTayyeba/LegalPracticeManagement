<?php

namespace Tests\Feature\Subscription;

use App\Models\Firm;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private function seedPlans(): void
    {
        SubscriptionPlan::create(['key' => 'solo', 'name' => 'Solo Lawyer', 'seat_limit' => 1, 'storage_limit_mb' => 2048, 'price_monthly' => 49]);
        SubscriptionPlan::create(['key' => 'professional', 'name' => 'Professional Firm', 'seat_limit' => 15, 'storage_limit_mb' => 20480, 'price_monthly' => 149]);
    }

    private function tokenFor(User $user): string
    {
        return auth('api')->login($user);
    }

    public function test_public_plans_endpoint_lists_active_plans(): void
    {
        $this->seedPlans();

        $response = $this->getJson('/api/v1/plans');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_firm_owner_can_upgrade_plan(): void
    {
        $this->seedPlans();
        $firm = Firm::factory()->create(['plan' => 'solo', 'seat_limit' => 1]);
        $owner = User::factory()->firmOwner()->for($firm)->create();
        $token = $this->tokenFor($owner);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson('/api/v1/firm/plan', ['plan_key' => 'professional']);

        $response->assertOk();
        $this->assertEquals('professional', $firm->fresh()->plan);
    }

    public function test_downgrade_is_blocked_if_seat_count_exceeds_new_limit(): void
    {
        $this->seedPlans();
        $firm = Firm::factory()->create(['plan' => 'professional', 'seat_limit' => 15]);
        $owner = User::factory()->firmOwner()->for($firm)->create();
        User::factory()->lawyer()->for($firm)->count(3)->create(); // 4 active staff total incl. owner
        $token = $this->tokenFor($owner);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson('/api/v1/firm/plan', ['plan_key' => 'solo']); // solo limit = 1

        $response->assertStatus(422)->assertJsonPath('error_code', 'DOMAIN_ERROR');
    }
}
