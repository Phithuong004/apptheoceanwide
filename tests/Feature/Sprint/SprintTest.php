<?php
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user      = User::factory()->create();
    $this->workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);
    $this->project   = Project::factory()->create([
        'workspace_id' => $this->workspace->id,
        'owner_id'     => $this->user->id,
        'type'         => 'scrum',
    ]);
    $this->workspace->members()->attach($this->user->id, ['role' => 'owner']);
    $this->project->members()->attach($this->user->id, ['role' => 'manager']);
});

test('can start a sprint', function () {
    $sprint = Sprint::factory()->create([
        'project_id' => $this->project->id,
        'status'     => 'planning',
    ]);

    $this->actingAs($this->user, 'sanctum')
         ->postJson("/api/v1/workspaces/{$this->workspace->id}/projects/{$this->project->id}/sprints/{$sprint->id}/start")
         ->assertOk();

    expect($sprint->fresh()->status)->toBe('active');
    expect($sprint->fresh()->started_at)->not->toBeNull();
});

test('cannot start two sprints simultaneously', function () {
    Sprint::factory()->create(['project_id' => $this->project->id, 'status' => 'active']);
    $second = Sprint::factory()->create(['project_id' => $this->project->id, 'status' => 'planning']);

    $this->actingAs($this->user, 'sanctum')
         ->postJson("/api/v1/workspaces/{$this->workspace->id}/projects/{$this->project->id}/sprints/{$second->id}/start")
         ->assertStatus(422);
});

test('completing sprint moves incomplete tasks to backlog', function () {
    $sprint = Sprint::factory()->create(['project_id' => $this->project->id, 'status' => 'active']);

    $incomplete = Task::factory()->create([
        'project_id'   => $this->project->id,
        'workspace_id' => $this->workspace->id,
        'reporter_id'  => $this->user->id,
        'sprint_id'    => $sprint->id,
        'status'       => 'in_progress',
    ]);

    $this->actingAs($this->user, 'sanctum')
         ->postJson("/api/v1/workspaces/{$this->workspace->id}/projects/{$this->project->id}/sprints/{$sprint->id}/complete")
         ->assertOk();

    expect($incomplete->fresh()->status)->toBe('backlog');
    expect($incomplete->fresh()->sprint_id)->toBeNull();
});
