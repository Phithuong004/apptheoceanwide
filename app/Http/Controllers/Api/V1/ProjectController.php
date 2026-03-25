<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Workspace;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService             $projectService,
        private ProjectRepositoryInterface $projectRepo
    ) {}

    public function index(Request $request): JsonResponse
    {
        $workspace = Workspace::where('slug', $request->header('X-Workspace'))->firstOrFail();
        $projects  = $this->projectRepo->getAllForWorkspace($workspace->id, auth()->id());

        return response()->json([
            'data' => ProjectResource::collection($projects),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'workspace_id' => 'required|exists:workspaces,id',
            'name'         => 'required|string|max:150',
            'type'         => 'required|in:scrum,kanban,waterfall',
            'visibility'   => 'required|in:private,team,public',
        ]);

        $project = $this->projectService->create($request->validated(), auth()->user());

        return response()->json(['data' => new ProjectResource($project)], 201);
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        $project->load(['owner','members','activeSprint','labels']);

        return response()->json([
            'data'  => new ProjectResource($project),
            'stats' => [
                'progress'        => $project->progress,
                'total_tasks'     => $project->tasks()->count(),
                'completed_tasks' => $project->tasks()->where('status','done')->count(),
                'members_count'   => $project->members()->count(),
            ],
        ]);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        $project->update($request->only(['name','description','status','color','start_date','end_date']));

        return response()->json(['data' => new ProjectResource($project->fresh())]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();

        return response()->json(['message' => 'Project deleted.'], 204);
    }
}
