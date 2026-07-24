<?php

namespace Tests\Feature\Cases;

use App\Models\CaseFile;
use App\Models\Client;
use App\Models\Firm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseManagementTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return auth('api')->login($user);
    }

    public function test_lawyer_can_open_a_new_case_for_a_client(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $client = Client::factory()->for($firm)->create();
        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/firm/cases', [
                'title' => 'Whitmore v. Alden Constructions',
                'case_type' => 'Litigation',
                'client_id' => $client->uuid,
                'lead_lawyer_id' => $lawyer->uuid,
                'opposing_party' => 'Alden Constructions LLC',
            ]);

        $response->assertCreated()
            ->assertJsonPath('case.title', 'Whitmore v. Alden Constructions')
            ->assertJsonPath('case.status', 'new');

        $this->assertDatabaseHas('cases', ['title' => 'Whitmore v. Alden Constructions', 'firm_id' => $firm->id]);
        $this->assertDatabaseHas('case_status_histories', ['to_status' => 'new']);
        $this->assertDatabaseHas('case_team', ['role_on_case' => 'lead']);
    }

    public function test_case_numbers_are_sequential_and_unique_per_firm(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $client = Client::factory()->for($firm)->create();
        $token = $this->tokenFor($lawyer);

        $first = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/firm/cases', [
            'title' => 'Case One', 'case_type' => 'Litigation', 'client_id' => $client->uuid,
        ])->json('case.case_number');

        $second = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/firm/cases', [
            'title' => 'Case Two', 'case_type' => 'Litigation', 'client_id' => $client->uuid,
        ])->json('case.case_number');

        $this->assertNotEquals($first, $second);
        $this->assertStringEndsWith('0001', $first);
        $this->assertStringEndsWith('0002', $second);
    }

    public function test_cases_are_isolated_between_firms(): void
    {
        $firmA = Firm::factory()->create();
        $firmB = Firm::factory()->create();

        $lawyerA = User::factory()->lawyer()->for($firmA)->create();
        $caseInFirmB = CaseFile::factory()->forFirm($firmB)->create();

        $token = $this->tokenFor($lawyerA);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/firm/cases/{$caseInFirmB->uuid}");

        $response->assertStatus(403);
    }

    public function test_a_client_portal_user_only_sees_their_own_cases(): void
    {
        $firm = Firm::factory()->create();
        $portalUser = User::factory()->client()->for($firm)->create();
        $ownClient = Client::factory()->for($firm)->create(['user_id' => $portalUser->id]);
        $otherClient = Client::factory()->for($firm)->create();

        CaseFile::factory()->forFirm($firm)->create(['client_id' => $ownClient->id, 'title' => 'My Case']);
        CaseFile::factory()->forFirm($firm)->create(['client_id' => $otherClient->id, 'title' => 'Not My Case']);

        $token = $this->tokenFor($portalUser);

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/firm/cases');

        $titles = collect($response->json('data'))->pluck('title');
        $this->assertTrue($titles->contains('My Case'));
        $this->assertFalse($titles->contains('Not My Case'));
    }

    public function test_lead_lawyer_can_transition_case_status_and_history_is_recorded(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $case = CaseFile::factory()->forFirm($firm)->create(['lead_lawyer_id' => $lawyer->id, 'status' => 'new']);
        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/firm/cases/{$case->uuid}/status", [
                'status' => 'active',
                'note' => 'Retainer signed, discovery underway.',
            ]);

        $response->assertOk()->assertJsonPath('case.status', 'active');
        $this->assertDatabaseHas('case_status_histories', [
            'case_id' => $case->id,
            'from_status' => 'new',
            'to_status' => 'active',
        ]);
    }

    public function test_unrelated_lawyer_cannot_edit_a_case_they_are_not_assigned_to(): void
    {
        $firm = Firm::factory()->create();
        $assignedLawyer = User::factory()->lawyer()->for($firm)->create();
        $otherLawyer = User::factory()->lawyer()->for($firm)->create();
        $case = CaseFile::factory()->forFirm($firm)->create(['lead_lawyer_id' => $assignedLawyer->id]);

        $token = $this->tokenFor($otherLawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/firm/cases/{$case->uuid}", ['title' => 'Hijacked Title']);

        $response->assertStatus(403);
    }

    public function test_firm_owner_can_assign_a_team_member_to_a_case(): void
    {
        $firm = Firm::factory()->create();
        $owner = User::factory()->firmOwner()->for($firm)->create();
        $paralegal = User::factory()->paralegal()->for($firm)->create();
        $case = CaseFile::factory()->forFirm($firm)->create();

        $token = $this->tokenFor($owner);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/firm/cases/{$case->uuid}/team", [
                'user_id' => $paralegal->uuid,
                'role_on_case' => 'support',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('case_team', ['case_id' => $case->id, 'user_id' => $paralegal->id, 'role_on_case' => 'support']);
    }

    public function test_staff_can_add_an_internal_case_note(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $case = CaseFile::factory()->forFirm($firm)->create(['lead_lawyer_id' => $lawyer->id]);
        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/firm/cases/{$case->uuid}/notes", [
                'body' => 'Filed motion for summary judgment.',
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('case_notes', ['case_id' => $case->id, 'author_id' => $lawyer->id]);
    }
}
