<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Task $task) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("New task assigned: {$this->task->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("You've been assigned a new task: \"{$this->task->title}\".");

        if ($this->task->due_date) {
            $message->line("Due: {$this->task->due_date->format('M j, Y')}");
        }

        return $message->action('View Task', config('app.frontend_url').'/tasks/'.$this->task->uuid);
    }
}
