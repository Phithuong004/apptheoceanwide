<?php
namespace App\Repositories\Contracts;

use App\Models\Project;
use Illuminate\Support\Collection;

interface ProjectRepositoryInterface
{
    public function getAllForWorkspace(int $workspaceId, int $userId): Collection;
    public function findBySlug(int $workspaceId, string $slug): Project;
    public function create(array $data): Project;
    public function update(Project $project, array $data): Project;
}
