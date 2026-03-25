<?php
namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Workspace;
use App\Services\ReportService;

class VelocityController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function show(Workspace $workspace, Project $project)
    {
        $data = $this->reportService->getVelocityChart($project);
        return view('reports.velocity', compact('workspace','project','data'));
    }
}
