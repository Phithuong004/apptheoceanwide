<?php
namespace App\Providers;

use App\Events\{TaskCreated, TaskUpdated, TaskAssigned, SprintStarted, SprintCompleted, CommentPosted};
use App\Listeners\{
    SendTaskAssignmentNotification, SendCommentNotification,
    LogProjectActivity, UpdateSprintProgress, NotifyMentionedUsers
};
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TaskCreated::class => [
            LogProjectActivity::class . '@handleTaskCreated',
        ],
        TaskUpdated::class => [
            LogProjectActivity::class . '@handleTaskUpdated',
            UpdateSprintProgress::class,
        ],
        TaskAssigned::class => [
            SendTaskAssignmentNotification::class,
        ],
        CommentPosted::class => [
            SendCommentNotification::class,
            NotifyMentionedUsers::class,
        ],
        SprintStarted::class => [],
        SprintCompleted::class => [],
    ];
}
