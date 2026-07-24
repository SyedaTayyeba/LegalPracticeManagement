<?php

namespace Tests\Feature\Billing;

use App\Models\CaseFile;
use App\Models\Client;
use App\Models\Firm;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return auth('api')->login($user);
    }

    public function test_lawyer_can_log_billable_time_against_a_case(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $case = CaseFile::factory()->forFirm($firm)->create();
        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/firm/time-entries', [
                'case_id' => $case->uuid,
                'description' => 'Drafted motion to dismiss',
                'minutes' => 90,
                'hourly_rate' => 300,
                'entry_date' => now()->toDateString(),
            ]);

        $response->assertCreated()->assertJsonPath('time_entry.amount', 450.0);
    }

    public function test_lawyer_cannot_create_an_invoice_only_firm_owner_can(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $client = Client::factory()->for($firm)->create();
        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/firm/invoices', [
                'client_id' => $client->uuid,
                'due_date' => now()->addDays(30)->toDateString(),
            ]);

        $response->assertStatus(403);
    }

    public function test_firm_owner_can_generate_invoice_from_unbilled_time_entries(): void
    {
        $firm = Firm::factory()->create();
        $owner = User::factory()->firmOwner()->for($firm)->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $client = Client::factory()->for($firm)->create();
        $case = CaseFile::factory()->forFirm($firm)->create(['client_id' => $client->id]);

        $entry = TimeEntry::factory()->create([
            'firm_id' => $firm->id, 'case_id' => $case->id, 'user_id' => $lawyer->id,
            'minutes' => 120, 'hourly_rate' => 300,
        ]);

        $token = $this->tokenFor($owner);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/firm/invoices', [
                'client_id' => $client->uuid,
                'case_id' => $case->uuid,
                'due_date' => now()->addDays(30)->toDateString(),
                'tax_rate' => 8,
                'time_entry_ids' => [$entry->uuid],
            ]);

        $response->assertCreated();
        $this->assertEquals(600.0, $response->json('invoice.subtotal'));
        $this->assertEquals(648.0, $response->json('invoice.total'));
        $this->assertNotNull($entry->fresh()->invoice_line_item_id);
    }

    public function test_the_same_time_entry_cannot_be_invoiced_twice(): void
    {
        $firm = Firm::factory()->create();
        $owner = User::factory()->firmOwner()->for($firm)->create();
        $client = Client::factory()->for($firm)->create();
        $entry = TimeEntry::factory()->create(['firm_id' => $firm->id, 'case_id' => CaseFile::factory()->forFirm($firm)->create()->id, 'user_id' => $owner->id]);
        $token = $this->tokenFor($owner);

        // First invoice consumes the entry
        $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/firm/invoices', [
            'client_id' => $client->uuid,
            'due_date' => now()->addDays(30)->toDateString(),
            'time_entry_ids' => [$entry->uuid],
        ]);

        // Second attempt should produce an empty invoice (entry already billed, filtered out by whereNull check)
        $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/firm/invoices', [
            'client_id' => $client->uuid,
            'due_date' => now()->addDays(30)->toDateString(),
            'time_entry_ids' => [$entry->uuid],
        ]);

        $response->assertCreated();
        $this->assertEquals(0.0, $response->json('invoice.subtotal'));
    }
}
