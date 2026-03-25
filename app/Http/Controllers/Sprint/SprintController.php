<?php
namespace App\Http\Controllers\Sprint;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sprint\StoreSprintRequest;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Workspace;
use App\Services\SprintService;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    public function __construct(private SprintService $sprintService) {}

    public function index(Workspace $workspace, Project $project)
    {
        $sprints = $project->sprints()->withCount(['tasks','tasks as completed_count' => fn($q) => $q->where('status','done')])->get();
        return view('sprints.index', compact('workspace','project','sprints'));
    }

    public function show(Workspace $workspace, Project $project, Sprint $sprint)
    {
        $sprint->load(['tasks.assignee','tasks.labels','tasks.subtasks']);
        $burndown = $this->sprintService->getBurndownData($sprint);
        $members  = $project->members;

        return view('sprints.show', compact('workspace','project','sprint','burndown','members'));
    }

    public function store(StoreSprintRequest $request, Workspace $workspace, Project $project)
    {
        $sprint = $project->sprints()->create($request->validated());
        return response()->json(['sprint' => $sprint]);
    }

    public function start(Workspace $workspace, Project $project, Sprint $sprint)
    {
        $this->authorize('manage', $sprint);
        try {
            $this->sprintService->startSprint($sprint);
            return back()->with('success', "Sprint '{$sprint->name}' đã bắt đầu!");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['sprint' => $e->getMessage()]);
        }
    }

    public function complete(Workspace $workspace, Project $project, Sprint $sprint)
    {
        $this->authorize('manage', $sprint);
        $result = $this->sprintService->completeSprint($sprint);
        return back()->with('success', "Sprint hoàn thành! Velocity: {$result['velocity']} points.");
    }

    public function burndown(Workspace $workspace, Project $project, Sprint $sprint)
    {
        $data = $this->sprintService->getBurndownData($sprint);
        return response()->json(['burndown' => $data]);
    }
}
