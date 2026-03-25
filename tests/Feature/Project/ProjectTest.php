<?php
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user      = User::factory()->create();
    $this->workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);
    $this->workspace->members()->attach($this->user->id, ['role' => 'owner']);
});

test('user can create a project', function () {
    $response = $this->actingAs($this->user, 'sanctum')
                     ->postJson("/api/v1/workspaces/{$this->workspace->id}/projects", [
                         'workspace_id' => $this->workspace->id,
                         'name'         => 'My Project',
                         'type'         => 'scrum',
                         'visibility'   => 'team',
                     ]);

    $response->assertStatus(201)
             ->assertJsonPath('data.name', 'My Project');

    $this->assertDatabaseHas('projects', ['name' => 'My Project']);
});

test('project has default labels after creation', function () {
    $project = Project::factory()->create([
        'workspace_id' => $this->workspace->id,
        'owner_id'     => $this->user->id,
    ]);

    $this->actingAs($this->user, 'sanctum')
         ->postJson("/api/v1/workspaces/{$this->workspace->id}/projects", [
             'workspace_id' => $this->workspace->id,
             'name'         => 'Label Test Project',
             'type'         => 'scrum',
             'visibility'   => 'team',
         ]);

    $created = Project::where('name', 'Label Test Project')->first();
    expect($created->labels)->not->toBeEmpty();
});

test('unauthorized user cannot delete project', function () {
    $other   = User::factory()->create();
    $project = Project::factory()->create([
        'workspace_id' => $this->workspace->id,
        'owner_id'     => $this->user->id,
    ]);

    $this->actingAs($other, 'sanctum')
         ->deleteJson("/api/v1/workspaces/{$this->workspace->id}/projects/{$project->id}")
         ->assertStatus(403);
});
