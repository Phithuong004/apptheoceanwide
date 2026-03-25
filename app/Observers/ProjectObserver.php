<?php
namespace App\Observers;

use App\Models\Project;
use Illuminate\Support\Str;

class ProjectObserver
{
    public function creating(Project $project): void
    {
        if (!$project->slug) {
            $project->slug = Str::slug($project->name) . '-' . Str::random(4);
        }
    }

    public function deleting(Project $project): void
    {
        // Archive instead of delete if has tasks
        if ($project->tasks()->exists()) {
            $project->update(['status' => 'archived']);
            return;
        }
        $project->tasks()->delete();
        $project->sprints()->delete();
    }
}
