<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Dispatched by the scheduled command `tasks:send-due-reminders` (see
 * app/Console/Commands/SendTaskDueReminders.php) for tasks due within 24 hours.
 */
class TaskDueReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Task $task) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Reminder: \"{$this->task->title}\" is due soon")
            ->greeting("Hi {$notifiable->name},")
            ->line("This is a reminder that \"{$this->task->title}\" is due on {$this->task->due_date->format('M j, Y')}.")
            ->action('View Task', config('app.frontend_url').'/tasks/'.$this->task->uuid);
    }
}
