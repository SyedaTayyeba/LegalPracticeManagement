<?php

namespace Tests\Feature\Communication;

use App\Models\Client;
use App\Models\Firm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return auth('api')->login($user);
    }

    public function test_lawyer_can_start_a_conversation_with_a_client(): void
    {
        Notification::fake();

        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $portalUser = User::factory()->client()->for($firm)->create();
        $client = Client::factory()->for($firm)->create(['user_id' => $portalUser->id]);
        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/firm/conversations', [
                'client_id' => $client->uuid,
                'subject' => 'Document request',
                'body' => 'Could you upload the signed retainer when you have a moment?',
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('conversations', ['firm_id' => $firm->id, 'client_id' => $client->id]);
        $this->assertDatabaseHas('messages', ['body' => 'Could you upload the signed retainer when you have a moment?']);
        // The client's own portal user should be auto-added as a participant.
        $this->assertDatabaseHas('conversation_participants', ['user_id' => $portalUser->id]);
    }

    public function test_non_participant_cannot_read_a_conversation(): void
    {
        $firm = Firm::factory()->create();
        $lawyerA = User::factory()->lawyer()->for($firm)->create();
        $lawyerB = User::factory()->lawyer()->for($firm)->create();
        $client = Client::factory()->for($firm)->create();

        $tokenA = $this->tokenFor($lawyerA);
        $conversationId = $this->withHeader('Authorization', "Bearer {$tokenA}")->postJson('/api/v1/firm/conversations', [
            'client_id' => $client->uuid,
            'body' => 'Private note to self about this client.',
        ])->json('conversation.id');

        $tokenB = $this->tokenFor($lawyerB);
        $response = $this->withHeader('Authorization', "Bearer {$tokenB}")
            ->getJson("/api/v1/firm/conversations/{$conversationId}");

        $response->assertStatus(403);
    }

    public function test_firm_owner_can_read_any_conversation_for_oversight(): void
    {
        $firm = Firm::factory()->create();
        $owner = User::factory()->firmOwner()->for($firm)->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $client = Client::factory()->for($firm)->create();

        $token = $this->tokenFor($lawyer);
        $conversationId = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/firm/conversations', [
            'client_id' => $client->uuid,
            'body' => 'Case update.',
        ])->json('conversation.id');

        $ownerToken = $this->tokenFor($owner);
        $response = $this->withHeader('Authorization', "Bearer {$ownerToken}")
            ->getJson("/api/v1/firm/conversations/{$conversationId}");

        $response->assertOk();
    }
}
