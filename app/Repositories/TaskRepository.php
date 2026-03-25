<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Support\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function findById(int $id): Task
    {
        return Task::with(['assignee', 'reporter', 'labels', 'subtasks', 'attachments', 'timeLogs', 'watchers'])
            ->findOrFail($id);
    }

    public function getMyTasks(int $userId, int $workspaceId): Collection
    {
        return Task::with(['assignee', 'labels', 'project'])
            ->whereHas('project', fn($q) => $q->where('workspace_id', $workspaceId))
            ->where('assignee_id', $userId)
            ->whereNull('parent_id')
            ->orderBy('due_date')
            ->get();
    }

    public function getByProject(int $projectId, array $filters = []): Collection
    {
        $query = Task::with(['assignee', 'labels'])
            ->where('project_id', $projectId)
            ->whereNull('parent_id');

        if (!empty($filters['status']))   $query->where('status', $filters['status']);
        if (!empty($filters['priority'])) $query->where('priority', $filters['priority']);
        if (!empty($filters['assignee'])) $query->where('assignee_id', $filters['assignee']);
        if (!empty($filters['sprint']))   $query->where('sprint_id', $filters['sprint']);
        if (!empty($filters['search']))   $query->where('title', 'like', '%' . $filters['search'] . '%');

        return $query->orderBy('position')->get();
    }

    public function getBySprint(int $sprintId): Collection
    {
        return Task::with(['assignee', 'labels', 'subtasks'])
            ->where('sprint_id', $sprintId)
            ->orderBy('position')
            ->get();
    }

    public function getKanbanBoard(int $projectId): array
    {
        $statuses = ['backlog', 'todo', 'in_progress', 'in_review', 'done', 'blocked'];
        $board = [];

        foreach ($statuses as $status) {
            $board[$status] = Task::with(['assignee', 'labels', 'subtasks'])
                ->where('project_id', $projectId)
                ->where('status', $status)
                ->whereNull('parent_id')
                ->orderBy('position')
                ->get();
        }

        return $board;
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->fresh();
    }

    public function moveToStatus(Task $task, string $status, int $position): void
    {
        // Re-order existing tasks in target column
        Task::where('project_id', $task->project_id)
            ->where('status', $status)
            ->where('position', '>=', $position)
            ->increment('position');

        $task->update(['status' => $status, 'position' => $position]);

        if ($status === 'done' && !$task->completed_at) {
            $task->update(['completed_at' => now()]);
        }
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
