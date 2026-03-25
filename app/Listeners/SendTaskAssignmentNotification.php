<?php
namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Notifications\TaskAssignedNotification;

class SendTaskAssignmentNotification
{
    public function handle(TaskAssigned $event): void
    {
        $assignee = $event->task->assignee;
        if ($assignee) {
            $assignee->notify(new TaskAssignedNotification($event->task));
        }
    }
}
