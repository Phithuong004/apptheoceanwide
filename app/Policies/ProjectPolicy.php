<?php
namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        if ($project->visibility === 'public') return true;
        if ($project->visibility === 'team')
            return $project->workspace->hasMember($user);
        return $project->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('projects.create');
    }

    public function update(User $user, Project $project): bool
    {
        return $project->owner_id === $user->id
            || $project->members()->where('user_id', $user->id)
                                  ->wherePivot('role','manager')->exists()
            || $user->hasPermissionTo('projects.edit');
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->owner_id === $user->id
            || $user->hasPermissionTo('projects.delete');
    }
}
