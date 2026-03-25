<?php
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user      = User::factory()->create();
    $this->workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);
    $this->workspace->members()->attach($this->user->id, ['role' => 'owner']);
    $this->project   = Project::factory()->create([
        'workspace_id' => $this->workspace->id,
        'owner_id'     => $this->user->id,
    ]);
    $this->project->members()->attach($this->user->id, ['role' => 'manager']);
});

test('can create a task', function () {
    $res = $this->actingAs($this->user, 'sanctum')
                ->postJson("/api/v1/workspaces/{$this->workspace->id}/projects/{$this->project->id}/tasks", [
                    'title'    => 'Build Login Page',
                    'priority' => 'high',
                    'type'     => 'task',
                ]);

    $res->assertStatus(201)->assertJsonPath('data.title', 'Build Login Page');
    $this->assertDatabaseHas('tasks', ['title' => 'Build Login Page']);
});

test('task status changes to done sets completed_at', function () {
    $task = Task::factory()->create([
        'project_id'   => $this->project->id,
        'workspace_id' => $this->workspace->id,
        'reporter_id'  => $this->user->id,
        'status'       => 'in_progress',
    ]);

    $this->actingAs($this->user, 'sanctum')
         ->putJson("/api/v1/workspaces/{$this->workspace->id}/projects/{$this->project->id}/tasks/{$task->id}", [
             'status' => 'done',
         ]);

    $this->assertNotNull($task->fresh()->completed_at);
});

test('task can be moved to different status', function () {
    $task = Task::factory()->create([
        'project_id'   => $this->project->id,
        'workspace_id' => $this->workspace->id,
        'reporter_id'  => $this->user->id,
        'status'       => 'todo',
    ]);

    $this->actingAs($this->user, 'sanctum')
         ->postJson("/api/v1/workspaces/{$this->workspace->id}/projects/{$this->project->id}/tasks/{$task->id}/move", [
             'status'   => 'in_progress',
             'position' => 0,
         ])->assertOk();

    expect($task->fresh()->status)->toBe('in_progress');
});
