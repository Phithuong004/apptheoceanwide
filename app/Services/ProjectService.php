<?php
namespace App\Services;

use App\Events\ProjectCreated;
use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectService
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepo
    ) {}

    public function create(array $data, User $owner): Project
    {
        return DB::transaction(function () use ($data, $owner) {

            // Đảm bảo có workspace_id
            $workspaceId = $data['workspace_id']
                ?? \App\Models\Workspace::where('owner_id', $owner->id)->value('id');

            if (!$workspaceId) {
                throw new \Exception('Không xác định được workspace.');
            }

            // Tạo slug unique
            $baseSlug = Str::slug($data['name']);
            $slug     = $baseSlug;
            $counter  = 1;
            while (Project::where('workspace_id', $workspaceId)->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $project = $this->projectRepo->create([
                ...$data,
                'owner_id'     => $owner->id,
                'workspace_id' => $workspaceId,
                'slug'         => $slug,
            ]);

            // Thêm owner làm manager
            $project->members()->attach($owner->id, [
                'role'      => 'manager',
                'joined_at' => now(),
            ]);

            // Tạo labels mặc định
            $this->createDefaultLabels($project);

            // Tạo Sprint 1 nếu là scrum
            if (($project->type ?? '') === 'scrum') {
                $project->sprints()->create([
                    'name'   => 'Sprint 1',
                    'status' => 'planning',
                ]);
            }

            event(new ProjectCreated($project));

            return $project;
        });
    }

    public function addMember(Project $project, User $user, string $role): void
    {
        $project->members()->syncWithoutDetaching([
            $user->id => ['role' => $role, 'joined_at' => now()],
        ]);
    }

    public function cloneFromTemplate(int $templateId, array $data, User $owner): Project
    {
        $template  = \App\Models\ProjectTemplate::findOrFail($templateId);
        $project   = $this->create($data, $owner);
        $structure = $template->structure ?? [];

        if (!empty($structure['labels'])) {
            foreach ($structure['labels'] as $label) {
                $project->labels()->create($label);
            }
        }

        return $project;
    }

    private function createDefaultLabels(Project $project): void
    {
        $defaults = [
            ['name' => 'Bug',      'color' => '#ef4444'],
            ['name' => 'Feature',  'color' => '#6366f1'],
            ['name' => 'Hotfix',   'color' => '#f59e0b'],
            ['name' => 'Refactor', 'color' => '#10b981'],
            ['name' => 'Docs',     'color' => '#8b5cf6'],
        ];

        foreach ($defaults as $label) {
            $project->labels()->create($label);
        }
    }
}
