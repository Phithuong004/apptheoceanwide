<?php

namespace App\Repositories;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function getAllForWorkspace(int $workspaceId, int $userId): \Illuminate\Support\Collection
    {
        return Project::with(['owner', 'members', 'activeSprint'])
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn($q) => $q->where('status', 'done'),
            ])
            ->where('workspace_id', $workspaceId)
            ->where(function ($q) use ($userId) {
                $q->whereHas('members', fn($q) => $q->where('user_id', $userId))
                    ->orWhere('owner_id', $userId)
                    ->orWhere('visibility', 'public');
            })
            ->orderBy('updated_at', 'desc')
            ->get();
    }


    public function findBySlug(int $workspaceId, string $slug): Project
    {
        return Project::where('workspace_id', $workspaceId)
            ->where('slug', $slug)
            ->with(['owner', 'members', 'sprints', 'labels'])
            ->firstOrFail();
    }

    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);
        return $project->fresh();
    }
}
