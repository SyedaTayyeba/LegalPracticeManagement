<?php

namespace App\Console\Commands;

use App\Models\CourtEvent;
use App\Notifications\HearingReminderNotification;
use Illuminate\Console\Command;

class SendHearingReminders extends Command
{
    protected $signature = 'calendar:send-hearing-reminders';

    protected $description = 'Send reminder notifications for hearings/events happening within 24 hours';

    public function handle(): int
    {
        $events = CourtEvent::where('reminder_sent', false)
            ->whereBetween('starts_at', [now(), now()->addDay()])
            ->with('attendees')
            ->get();

        foreach ($events as $event) {
            foreach ($event->attendees as $attendee) {
                $attendee->notify(new HearingReminderNotification($event));
            }
            $event->update(['reminder_sent' => true]);
        }

        $this->info("Sent reminders for {$events->count()} upcoming calendar events.");

        return self::SUCCESS;
    }
}
