<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to LegalCaseFlow')
            ->greeting("Welcome, {$notifiable->name}!")
            ->line('Your firm workspace has been created successfully.')
            ->line('You can now invite your team and start managing cases securely.')
            ->action('Go to Dashboard', config('app.frontend_url').'/dashboard')
            ->line('Thank you for choosing LegalCaseFlow.');
    }
}
