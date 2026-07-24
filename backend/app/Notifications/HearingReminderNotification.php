<?php

namespace App\Notifications;

use App\Models\CourtEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HearingReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly CourtEvent $event) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Reminder: {$this->event->title} tomorrow")
            ->greeting("Hi {$notifiable->name},")
            ->line("You have a {$this->event->event_type} scheduled: \"{$this->event->title}\".")
            ->line("When: {$this->event->starts_at->format('M j, Y g:i A')}")
            ->when($this->event->location, fn ($m) => $m->line("Where: {$this->event->location}"))
            ->action('View Calendar', config('app.frontend_url').'/calendar');
    }
}
