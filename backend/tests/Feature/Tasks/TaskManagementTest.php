<?php

namespace Tests\Feature\Tasks;

use App\Models\Firm;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return auth('api')->login($user);
    }

    public function test_paralegal_can_create_and_assign_a_task(): void
    {
        Notification::fake();

        $firm = Firm::factory()->create();
        $paralegal = User::factory()->paralegal()->for($firm)->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $token = $this->tokenFor($paralegal);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/firm/tasks', [
                'title' => 'File motion for extension',
                'assigned_to' => $lawyer->uuid,
                'priority' => 'high',
                'due_date' => now()->addDays(3)->toDateString(),
            ]);

        $response->assertCreated()->assertJsonPath('task.priority', 'high');
        $this->assertDatabaseHas('tasks', ['title' => 'File motion for extension', 'assigned_to' => $lawyer->id]);
    }

    public function test_completing_a_task_stamps_completed_at(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $task = Task::factory()->for($firm)->create(['assigned_to' => $lawyer->id, 'created_by' => $lawyer->id]);
        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/firm/tasks/{$task->uuid}", ['status' => 'completed']);

        $response->assertOk();
        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_unrelated_staff_member_cannot_update_someone_elses_task(): void
    {
        $firm = Firm::factory()->create();
        $creator = User::factory()->lawyer()->for($firm)->create();
        $bystander = User::factory()->paralegal()->for($firm)->create();
        $task = Task::factory()->for($firm)->create(['created_by' => $creator->id, 'assigned_to' => $creator->id]);
        $token = $this->tokenFor($bystander);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/firm/tasks/{$task->uuid}", ['status' => 'completed']);

        $response->assertStatus(403);
    }

    public function test_tasks_can_be_filtered_by_overdue(): void
    {
        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        Task::factory()->for($firm)->create(['due_date' => now()->subDays(2), 'title' => 'Overdue task']);
        Task::factory()->for($firm)->create(['due_date' => now()->addDays(5), 'title' => 'Future task']);
        $token = $this->tokenFor($lawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/firm/tasks?overdue=1');

        $titles = collect($response->json('data'))->pluck('title');
        $this->assertTrue($titles->contains('Overdue task'));
        $this->assertFalse($titles->contains('Future task'));
    }
}
