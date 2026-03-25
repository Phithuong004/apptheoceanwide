<?php

namespace App\Http\Controllers\Project;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\Workspace;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService,
        private ProjectRepositoryInterface $projectRepo
    ) {}

    public function index(Workspace $workspace)
    {
        $projects = $this->projectRepo->getAllForWorkspace($workspace->id, auth()->id());
        return view('projects.index', compact('workspace', 'projects'));
    }

    public function create(Workspace $workspace)
    {
        $this->authorize('create', Project::class);
        $clients   = $workspace->clients ?? collect();
        $templates = \App\Models\ProjectTemplate::where('workspace_id', $workspace->id)
            ->orWhere('is_public', true)->get();
        return view('projects.create', compact('workspace', 'clients', 'templates'));
    }

    // ← THÊM method edit với clients
    public function edit(Workspace $workspace, Project $project)
    {
        $this->authorize('update', $project);

        $clients = Client::where('workspace_id', $workspace->id)->orderBy('name')->get();
        $users = User::where('workspace_id', $workspace->id)  // ← THÊM: Load users cho Owner dropdown
            ->orderBy('name')->get();

        return view('projects.edit', compact('workspace', 'project', 'clients', 'users'));
    }


    public function store(StoreProjectRequest $request, Workspace $workspace)
    {
        $this->authorize('create', Project::class);

        $project = $this->projectService->create(
            array_merge($request->validated(), ['workspace_id' => $workspace->id]),
            auth()->user()
        );

        return redirect()->route('projects.show', [$workspace->slug, $project->slug])
            ->with('success', 'Dự án đã được tạo!');
    }

    public function show(Workspace $workspace, Project $project)
    {
        $this->authorize('view', $project);
        $board        = app(\App\Repositories\Contracts\TaskRepositoryInterface::class)
            ->getKanbanBoard($project->id);
        $activeSprint = $project->activeSprint;
        $members      = $project->members;
        $labels       = $project->labels;

        return view('projects.show', compact(
            'workspace',
            'project',
            'board',
            'activeSprint',
            'members',
            'labels'
        ));
    }

    public function update(UpdateProjectRequest $request, Workspace $workspace, Project $project)
    {
        $this->authorize('update', $project);

        // 1️⃣ Validate owner_id (nullable OK)
        $request->validate([
            'owner_id' => 'nullable|exists:users,id',
        ]);

        // 2️⃣ Chuẩn bị data update project (loại trừ member data)
        $updateData = $request->safe()->except([
            'member_roles',
            'remove_members',
            'new_members',
        ]);

        // 3️⃣ Ensure client_id & handle owner_id safely
        $updateData['client_id'] = $request->input('client_id');

        // ← FIX: Chỉ set owner_id nếu có giá trị, giữ nguyên nếu null
        if ($request->filled('owner_id')) {
            $updateData['owner_id'] = $request->input('owner_id');
        }
        // Không set owner_id = null → tránh lỗi NOT NULL

        // 4️⃣ Update project cơ bản (name, client_id, owner_id, etc...)
        $this->projectRepo->update($project, $updateData);

        // 5️⃣ Get fresh project để lấy owner_id mới nhất
        $project->refresh();

        // 6️⃣ Update role thành viên (skip owner)
        if ($request->filled('member_roles')) {
            foreach ($request->member_roles as $userId => $role) {
                if ($userId != $project->owner_id) {  // Skip owner role change
                    $project->members()->updateExistingPivot($userId, ['role' => $role]);
                }
            }
        }

        // 7️⃣ Remove members (protect owner)
        if ($request->filled('remove_members')) {
            $removeIds = collect($request->remove_members)
                ->reject(fn($id) => $id == $project->owner_id);  // Không xóa owner
            $project->members()->detach($removeIds);
        }

        // 8️⃣ Add new members
        if ($request->filled('new_members')) {
            $projectWorkspace = $project->workspace;

            foreach ($request->new_members as $member) {
                $user = \App\Models\User::where('email', $member['email'])->first();
                if (!$user) continue;

                // Add to workspace nếu chưa
                if (!$projectWorkspace->hasMember($user)) {
                    $projectWorkspace->members()->attach($user->id, [
                        'role' => 'member',
                        'joined_at' => now(),
                    ]);
                }

                $isNew = !$project->members()->where('user_id', $user->id)->exists();

                $project->members()->syncWithoutDetaching([
                    $user->id => [
                        'role' => $member['role'] ?? 'developer',
                        'joined_at' => now(),
                    ]
                ]);

                if ($isNew) {
                    $user->notify(new \App\Notifications\AddedToProject($project));
                }
            }
        }

        return back()->with('success', '✅ Dự án "' . $project->name . '" đã được cập nhật thành công!');
    }

    public function destroy(Workspace $workspace, Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return redirect()->route('projects.index', $workspace->slug)
            ->with('success', 'Dự án đã được xoá.');
    }
}
