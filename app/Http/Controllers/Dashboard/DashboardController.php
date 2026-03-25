<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Workspace;
use App\Services\ReportService;

class DashboardController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(Workspace $workspace)
    {
        $user  = auth()->user();
        $stats = $this->reportService->getDashboardStats($workspace->id, $user->id);

        $myTasks = Task::whereHas('project', fn($q) =>
                            $q->where('workspace_id', $workspace->id)
                       )
                       ->where('assignee_id', $user->id)
                       ->whereNotIn('status', ['done'])
                       ->with(['project','labels'])
                       ->orderByRaw("CASE priority
                           WHEN 'critical' THEN 1
                           WHEN 'urgent'   THEN 2
                           WHEN 'high'     THEN 3
                           WHEN 'medium'   THEN 4
                           ELSE 5 END")
                       ->take(10)
                       ->get();

        $recentActivity = \App\Models\ActivityLog::where('project_id', function($sub) use ($workspace) {
                              $sub->select('id')->from('projects')
                                  ->where('workspace_id', $workspace->id);
                          })
                          ->with('user')
                          ->latest()
                          ->take(15)
                          ->get();

        return view('dashboard.index', compact('workspace','stats','myTasks','recentActivity'));
    }
}
