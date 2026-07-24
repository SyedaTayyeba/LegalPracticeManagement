<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDueReminderNotification;
use Illuminate\Console\Command;

class SendTaskDueReminders extends Command
{
    protected $signature = 'tasks:send-due-reminders';

    protected $description = 'Send reminder notifications for tasks due within the next 24 hours';

    public function handle(): int
    {
        $tasks = Task::whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('assigned_to')
            ->where('reminder_sent', false)
            ->whereDate('due_date', '<=', now()->addDay()->toDateString())
            ->whereDate('due_date', '>=', now()->toDateString())
            ->with('assignee')
            ->get();

        foreach ($tasks as $task) {
            $task->assignee?->notify(new TaskDueReminderNotification($task));
            $task->update(['reminder_sent' => true]);
        }

        $this->info("Sent {$tasks->count()} task due-date reminders.");

        return self::SUCCESS;
    }
}
