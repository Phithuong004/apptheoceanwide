<?php
namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Workspace;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(Workspace $workspace)
    {
        $stats = $this->reportService->getDashboardStats($workspace->id, auth()->id());
        return view('reports.index', compact('workspace','stats'));
    }
}
