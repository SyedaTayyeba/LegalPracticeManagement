<?php

namespace Tests\Feature\Calendar;

use App\Models\CourtEvent;
use App\Models\Firm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourtCalendarTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return auth('api')->login($user);
    }

    public function test_lawyer_can_schedule_a_hearing(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/firm/calendar', [
                'title' => 'Initial Hearing',
                'event_type' => 'hearing',
                'starts_at' => now()->addDays(5)->setTime(10, 0)->toIso8601String(),
                'lead_lawyer_id' => $lawyer->uuid,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('court_events', ['title' => 'Initial Hearing']);
    }

    public function test_overlapping_events_for_the_same_lawyer_are_flagged_as_conflicts(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $start = now()->addDays(5)->setTime(10, 0);

        CourtEvent::factory()->for($firm)->create([
            'lead_lawyer_id' => $lawyer->id,
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
        ]);

        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/firm/calendar', [
                'title' => 'Conflicting Deposition',
                'event_type' => 'meeting',
                'starts_at' => $start->copy()->addMinutes(30)->toIso8601String(),
                'lead_lawyer_id' => $lawyer->uuid,
            ]);

        $response->assertStatus(409)->assertJsonPath('error_code', 'SCHEDULE_CONFLICT');
    }

    public function test_conflict_can_be_overridden_with_force_flag(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $start = now()->addDays(5)->setTime(10, 0);

        CourtEvent::factory()->for($firm)->create([
            'lead_lawyer_id' => $lawyer->id,
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
        ]);

        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/firm/calendar', [
                'title' => 'Double-booked but intentional',
                'event_type' => 'meeting',
                'starts_at' => $start->copy()->addMinutes(30)->toIso8601String(),
                'lead_lawyer_id' => $lawyer->uuid,
                'force' => true,
            ]);

        $response->assertCreated();
    }

    public function test_calendar_events_are_isolated_between_firms(): void
    {
        $firmA = Firm::factory()->create();
        $firmB = Firm::factory()->create();
        $lawyerA = User::factory()->lawyer()->for($firmA)->create();
        $eventInFirmB = CourtEvent::factory()->for($firmB)->create();

        $token = $this->tokenFor($lawyerA);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/firm/calendar/{$eventInFirmB->uuid}");

        $response->assertStatus(403);
    }
}
