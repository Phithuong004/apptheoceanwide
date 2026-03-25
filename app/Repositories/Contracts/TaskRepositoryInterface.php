<?php
namespace App\Repositories\Contracts;

use App\Models\Task;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    public function findById(int $id): Task;
    public function getByProject(int $projectId, array $filters = []): Collection;
    public function getBySprint(int $sprintId): Collection;
    public function getKanbanBoard(int $projectId): array;
    public function create(array $data): Task;
    public function update(Task $task, array $data): Task;
    public function moveToStatus(Task $task, string $status, int $position): void;
    public function delete(Task $task): void;
    public function getMyTasks(int $userId, int $workspaceId): Collection;

}
