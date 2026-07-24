<?php

namespace App\Services;

use App\Models\CaseFile;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function list(User $actor, array $filters): LengthAwarePaginator
    {
        $query = Task::query()->where('firm_id', $actor->firm_id)
            ->with(['assignee:id,uuid,name', 'case:id,uuid,title']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (! empty($filters['case_id'])) {
            $query->whereHas('case', fn ($q) => $q->where('uuid', $filters['case_id']));
        }
        if (! empty($filters['assigned_to_me'])) {
            $query->where('assigned_to', $actor->id);
        }
        if (! empty($filters['overdue'])) {
            $query->whereDate('due_date', '<', now())->whereNotIn('status', ['completed', 'cancelled']);
        }
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->orderBy('due_date')->orderByDesc('priority')->paginate($filters['per_page'] ?? 20);
    }

    public function create(User $actor, array $data): Task
    {
        $caseId = null;
        if (! empty($data['case_id'])) {
            $caseId = CaseFile::where('firm_id', $actor->firm_id)->where('uuid', $data['case_id'])->value('id');
        }

        $assigneeId = null;
        if (! empty($data['assigned_to'])) {
            $assigneeId = User::where('firm_id', $actor->firm_id)->where('uuid', $data['assigned_to'])->value('id');
        }

        $task = Task::create([
            'firm_id' => $actor->firm_id,
            'case_id' => $caseId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'assigned_to' => $assigneeId,
            'created_by' => $actor->id,
            'priority' => $data['priority'] ?? 'medium',
            'due_date' => $data['due_date'] ?? null,
        ]);

        $this->auditLog->log('task.created', $actor, $task, ['title' => $task->title]);

        if ($assigneeId && $assigneeId !== $actor->id) {
            $task->assignee?->notify(new TaskAssignedNotification($task));
        }

        return $task->load(['assignee', 'case']);
    }

    public function update(User $actor, Task $task, array $data): Task
    {
        if (($data['status'] ?? null) === 'completed' && $task->status !== 'completed') {
            $data['completed_at'] = now();
        }

        $task->update($data);

        $this->auditLog->log('task.updated', $actor, $task, ['fields' => array_keys($data)]);

        return $task->fresh(['assignee', 'case']);
    }

    public function delete(User $actor, Task $task): void
    {
        $task->delete();
        $this->auditLog->log('task.deleted', $actor, $task);
    }
}
