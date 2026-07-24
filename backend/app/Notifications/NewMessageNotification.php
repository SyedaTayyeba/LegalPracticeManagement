<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Message $message, public readonly User $sender) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New message from {$this->sender->name}")
            ->greeting("Hi {$notifiable->name},")
            ->line("{$this->sender->name} sent you a new message.")
            ->action('View Conversation', config('app.frontend_url').'/messages/'.$this->message->conversation->uuid);
    }
}
