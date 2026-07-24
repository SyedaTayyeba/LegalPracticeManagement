<?php

namespace Tests\Feature\Portal;

use App\Models\CaseFile;
use App\Models\Client;
use App\Models\Firm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_dashboard_returns_only_their_own_case_summary(): void
    {
        $firm = Firm::factory()->create();
        $portalUser = User::factory()->client()->for($firm)->create();
        $client = Client::factory()->for($firm)->create(['user_id' => $portalUser->id]);
        $otherClient = Client::factory()->for($firm)->create();

        CaseFile::factory()->forFirm($firm)->create(['client_id' => $client->id, 'status' => 'active']);
        CaseFile::factory()->forFirm($firm)->create(['client_id' => $otherClient->id, 'status' => 'active']);

        $token = auth('api')->login($portalUser);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/firm/portal/dashboard');

        $response->assertOk()->assertJsonPath('data.open_case_count', 1);
    }

    public function test_staff_cannot_access_the_client_portal_dashboard_endpoint(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $token = auth('api')->login($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/firm/portal/dashboard');

        $response->assertStatus(403);
    }
}
