<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FirmInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Invitation $invitation) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = config('app.frontend_url')."/accept-invitation?token={$this->invitation->token}";

        return (new MailMessage)
            ->subject("You've been invited to join {$this->invitation->firm->name} on LegalCaseFlow")
            ->greeting('Hello,')
            ->line("{$this->invitation->inviter->name} has invited you to join {$this->invitation->firm->name} as a {$this->invitation->role} on LegalCaseFlow.")
            ->action('Accept Invitation', $url)
            ->line('This invitation expires in 7 days.');
    }
}
