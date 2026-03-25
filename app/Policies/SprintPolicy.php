<?php
namespace App\Policies;

use App\Models\Sprint;
use App\Models\User;

class SprintPolicy
{
    public function manage(User $user, Sprint $sprint): bool
    {
        return $sprint->project->owner_id === $user->id
            || $user->hasPermissionTo('sprints.manage');
    }
}
