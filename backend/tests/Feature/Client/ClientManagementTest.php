<?php

namespace Tests\Feature\Client;

use App\Models\Client;
use App\Models\Firm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return auth('api')->login($user);
    }

    public function test_lawyer_can_create_a_client(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/firm/clients', [
                'type' => 'individual',
                'first_name' => 'Elena',
                'last_name' => 'Marsh',
                'email' => 'elena.marsh@example.test',
                'phone' => '555-0100',
            ]);

        $response->assertCreated()->assertJsonPath('client.display_name', 'Elena Marsh');
        $this->assertDatabaseHas('clients', ['email' => 'elena.marsh@example.test', 'firm_id' => $firm->id]);
    }

    public function test_client_role_cannot_create_clients(): void
    {
        $firm = Firm::factory()->create();
        $clientUser = User::factory()->client()->for($firm)->create();
        $token = $this->tokenFor($clientUser);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/firm/clients', [
                'type' => 'individual',
                'first_name' => 'Elena',
                'last_name' => 'Marsh',
            ]);

        $response->assertStatus(403);
    }

    public function test_clients_are_isolated_between_firms(): void
    {
        $firmA = Firm::factory()->create();
        $firmB = Firm::factory()->create();

        $lawyerA = User::factory()->lawyer()->for($firmA)->create();
        $clientInFirmB = Client::factory()->for($firmB)->create();

        $token = $this->tokenFor($lawyerA);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/firm/clients/{$clientInFirmB->uuid}");

        $response->assertStatus(403);
    }

    public function test_search_filters_clients_by_name(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        Client::factory()->for($firm)->create(['first_name' => 'Winston', 'last_name' => 'Alden', 'display_name' => 'Winston Alden']);
        Client::factory()->for($firm)->create(['first_name' => 'Priya', 'last_name' => 'Nair', 'display_name' => 'Priya Nair']);

        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/firm/clients?search=Alden');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('display_name');
        $this->assertTrue($names->contains('Winston Alden'));
        $this->assertFalse($names->contains('Priya Nair'));
    }

    public function test_staff_can_add_a_timeline_note_to_a_client(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $client = Client::factory()->for($firm)->create();
        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/firm/clients/{$client->uuid}/notes", [
                'body' => 'Initial consultation completed; awaiting signed retainer.',
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('client_notes', ['client_id' => $client->id, 'author_id' => $lawyer->id]);
    }

    public function test_only_firm_owner_can_archive_a_client(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $client = Client::factory()->for($firm)->create();
        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/firm/clients/{$client->uuid}");

        $response->assertStatus(403);
    }
}
