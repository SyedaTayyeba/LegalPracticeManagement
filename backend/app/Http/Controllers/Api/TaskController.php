<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $tasks) {}

    /** GET /api/v1/firm/tasks?status=&priority=&case_id=&assigned_to_me=&overdue=&search= */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        return TaskResource::collection(
            $this->tasks->list($request->user(), $request->only([
                'status', 'priority', 'case_id', 'assigned_to_me', 'overdue', 'search', 'per_page',
            ]))
        );
    }

    /** POST /api/v1/firm/tasks */
    public function store(StoreTaskRequest $request)
    {
        $this->authorize('create', Task::class);

        $task = $this->tasks->create($request->user(), $request->validated());

        return response()->json(['message' => 'Task created.', 'task' => new TaskResource($task)], 201);
    }

    /** GET /api/v1/firm/tasks/{task} */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return new TaskResource($task->load(['assignee', 'case', 'creator']));
    }

    /** PATCH /api/v1/firm/tasks/{task} */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $updated = $this->tasks->update($request->user(), $task, $request->validated());

        return response()->json(['message' => 'Task updated.', 'task' => new TaskResource($updated)]);
    }

    /** DELETE /api/v1/firm/tasks/{task} */
    public function destroy(Request $request, Task $task)
    {
        $this->authorize('delete', $task);

        $this->tasks->delete($request->user(), $task);

        return response()->json(['message' => 'Task deleted.']);
    }
}
