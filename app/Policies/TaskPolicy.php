<?php
namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $task->project->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Task $task): bool
    {
        return $task->assignee_id === $user->id
            || $task->reporter_id === $user->id
            || $user->hasPermissionTo('tasks.edit');
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->reporter_id === $user->id
            || $user->hasPermissionTo('tasks.delete');
    }
}
