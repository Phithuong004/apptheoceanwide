<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SprintResource;
use App\Models\Project;
use App\Models\Sprint;
use App\Services\SprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    public function __construct(private SprintService $sprintService) {}

    public function index(Project $project): JsonResponse
    {
        $sprints = $project->sprints()->withCount('tasks')->get();
        return response()->json(['data' => SprintResource::collection($sprints)]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'goal'       => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after:start_date',
            'capacity'   => 'nullable|integer|min:0',
        ]);

        $sprint = $project->sprints()->create($request->validated());
        return response()->json(['data' => new SprintResource($sprint)], 201);
    }

    public function show(Project $project, Sprint $sprint): JsonResponse
    {
        $sprint->load(['tasks.assignee','tasks.labels']);
        $burndown = $this->sprintService->getBurndownData($sprint);

        return response()->json([
            'data'     => new SprintResource($sprint),
            'burndown' => $burndown,
            'stats'    => [
                'total_points'     => $sprint->total_points,
                'completed_points' => $sprint->completed_points,
                'completion_rate'  => $sprint->completion_rate,
                'days_remaining'   => $sprint->days_remaining,
            ],
        ]);
    }

    public function start(Project $project, Sprint $sprint): JsonResponse
    {
        try {
            $this->sprintService->startSprint($sprint);
            return response()->json(['message' => 'Sprint started.', 'data' => new SprintResource($sprint->fresh())]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function complete(Project $project, Sprint $sprint): JsonResponse
    {
        $result = $this->sprintService->completeSprint($sprint);
        return response()->json(['message' => 'Sprint completed.', 'result' => $result]);
    }
}
