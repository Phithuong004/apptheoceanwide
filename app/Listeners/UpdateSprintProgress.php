<?php
namespace App\Listeners;

use App\Events\TaskUpdated;

class UpdateSprintProgress
{
    public function handle(TaskUpdated $event): void
    {
        $task = $event->task;
        if (!$task->sprint_id) return;

        $sprint   = $task->sprint;
        $velocity = $sprint->tasks()->where('status','done')->sum('story_points');
        $sprint->update(['velocity' => $velocity]);
    }
}
