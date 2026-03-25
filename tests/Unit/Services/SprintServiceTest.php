<?php
use App\Models\Sprint;
use App\Models\Task;
use App\Services\SprintService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('burndown data has correct structure', function () {
    $sprint = Sprint::factory()->create([
        'start_date' => now()->subDays(5)->toDateString(),
        'end_date'   => now()->addDays(5)->toDateString(),
        'status'     => 'active',
    ]);

    $service = new SprintService();
    $data    = $service->getBurndownData($sprint);

    expect($data)->not->toBeEmpty();
    expect($data[0])->toHaveKeys(['date','remaining','ideal']);
    expect($data[0]['ideal'])->toBeGreaterThanOrEqual(0);
});

test('velocity is calculated correctly after sprint complete', function () {
    $sprint = Sprint::factory()->create(['status' => 'active']);

    Task::factory()->count(3)->create([
        'sprint_id'    => $sprint->id,
        'project_id'   => $sprint->project_id,
        'status'       => 'done',
        'story_points' => 5,
    ]);

    Task::factory()->create([
        'sprint_id'    => $sprint->id,
        'project_id'   => $sprint->project_id,
        'status'       => 'in_progress',
        'story_points' => 8,
    ]);

    $service = new SprintService();
    $result  = $service->completeSprint($sprint);

    expect($result['velocity'])->toBe(15); // 3 × 5 = 15
    expect($result['incomplete_tasks'])->toBe(1);
});
