<?php

namespace Tests\Feature\Auth;

use App\Models\Firm;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_firm_owner_can_invite_a_lawyer(): void
    {
        Notification::fake();

        $firm = Firm::factory()->create(['seat_limit' => 15]);
        $owner = User::factory()->firmOwner()->for($firm)->create();
        $token = auth('api')->login($owner);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/firm/invitations', [
                'email' => 'newlawyer@test.test',
                'role' => 'lawyer',
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('invitations', ['email' => 'newlawyer@test.test', 'firm_id' => $firm->id]);
    }

    public function test_non_owner_cannot_invite_users(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $token = auth('api')->login($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/firm/invitations', [
                'email' => 'newlawyer@test.test',
                'role' => 'lawyer',
            ]);

        $response->assertStatus(403);
    }

    public function test_invitation_cannot_exceed_seat_limit(): void
    {
        $firm = Firm::factory()->create(['seat_limit' => 1]);
        $owner = User::factory()->firmOwner()->for($firm)->create();
        $token = auth('api')->login($owner);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/firm/invitations', [
                'email' => 'newlawyer@test.test',
                'role' => 'lawyer',
            ]);

        $response->assertStatus(422)->assertJsonPath('error_code', 'SEAT_LIMIT_REACHED');
    }

    public function test_invitee_can_accept_invitation_and_join_firm(): void
    {
        $firm = Firm::factory()->create();
        $owner = User::factory()->firmOwner()->for($firm)->create();
        $invitation = Invitation::factory()->for($firm)->create([
            'invited_by' => $owner->id,
            'email' => 'invitee@test.test',
            'role' => 'paralegal',
        ]);

        $response = $this->postJson('/api/v1/invitations/accept', [
            'token' => $invitation->token,
            'name' => 'New Paralegal',
            'password' => 'AcceptedPass!123',
            'password_confirmation' => 'AcceptedPass!123',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'email' => 'invitee@test.test',
            'firm_id' => $firm->id,
            'role' => 'paralegal',
        ]);
    }

    public function test_expired_invitation_cannot_be_accepted(): void
    {
        $firm = Firm::factory()->create();
        $owner = User::factory()->firmOwner()->for($firm)->create();
        $invitation = Invitation::factory()->for($firm)->create([
            'invited_by' => $owner->id,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->postJson('/api/v1/invitations/accept', [
            'token' => $invitation->token,
            'name' => 'Too Late',
            'password' => 'AcceptedPass!123',
            'password_confirmation' => 'AcceptedPass!123',
        ]);

        $response->assertStatus(422)->assertJsonPath('error_code', 'DOMAIN_ERROR');
    }
}
