<?php
namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Workspace;
use App\Services\SprintService;

class BurndownController extends Controller
{
    public function __construct(private SprintService $sprintService) {}

    public function show(Workspace $workspace, Project $project, Sprint $sprint)
    {
        $data = $this->sprintService->getBurndownData($sprint);
        return view('reports.burndown', compact('workspace','project','sprint','data'));
    }

    public function data(Workspace $workspace, Project $project, Sprint $sprint)
    {
        return response()->json($this->sprintService->getBurndownData($sprint));
    }
}
