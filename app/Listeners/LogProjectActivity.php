<?php
namespace App\Listeners;

use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Models\ActivityLog;

class LogProjectActivity
{
    public function handleTaskCreated(TaskCreated $event): void
    {
        ActivityLog::create([
            'user_id'      => $event->task->reporter_id,
            'subject_type' => Task::class,
            'subject_id'   => $event->task->id,
            'action'       => 'created',
            'description'  => "Created task: {$event->task->title}",
            'project_id'   => $event->task->project_id,
        ]);
    }

    public function handleTaskUpdated(TaskUpdated $event): void
    {
        ActivityLog::create([
            'user_id'      => auth()->id(),
            'subject_type' => Task::class,
            'subject_id'   => $event->task->id,
            'action'       => 'updated',
            'description'  => "Updated task: {$event->task->title}",
            'project_id'   => $event->task->project_id,
        ]);
    }
}
