<?php
namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface SprintRepositoryInterface
{
    public function getAllForProject(int $projectId): Collection;
    public function findById(int $id): ?\App\Models\Sprint;
    public function create(array $data): \App\Models\Sprint;
    public function update(int $id, array $data): \App\Models\Sprint;
    public function delete(int $id): bool;
}
