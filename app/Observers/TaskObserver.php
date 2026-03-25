<?php
namespace App\Observers;

use App\Models\Task;

class TaskObserver
{
    public function creating(Task $task): void
    {
        if (!$task->workspace_id && $task->project) {
            $task->workspace_id = $task->project->workspace_id;
        }
    }

    public function updating(Task $task): void
    {
        if ($task->isDirty('status') && $task->status === 'done' && !$task->completed_at) {
            $task->completed_at = now();
        }

        if ($task->isDirty('status') && $task->status !== 'done') {
            $task->completed_at = null;
        }
    }

    public function deleted(Task $task): void
    {
        // Delete subtasks
        $task->subtasks()->delete();
        // Remove from watchers
        $task->watchers()->detach();
    }
}
