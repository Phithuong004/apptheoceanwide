<?php
namespace App\Services;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\TaskTimeLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    public function getProjectSummary(Project $project): array
    {
        $tasks = $project->tasks()->whereNull('parent_id');

        return [
            'total_tasks'     => $tasks->count(),
            'completed_tasks' => (clone $tasks)->where('status','done')->count(),
            'overdue_tasks'   => (clone $tasks)->overdue()->count(),
            'in_progress'     => (clone $tasks)->where('status','in_progress')->count(),
            'total_hours'     => $project->tasks()->sum('actual_hours'),
            'estimated_hours' => $project->tasks()->sum('estimated_hours'),
            'completion_rate' => $project->progress,
            'by_priority'     => (clone $tasks)->selectRaw('priority, count(*) as count')
                                               ->groupBy('priority')->pluck('count','priority'),
            'by_assignee'     => (clone $tasks)->with('assignee')
                                               ->selectRaw('assignee_id, count(*) as count')
                                               ->groupBy('assignee_id')
                                               ->get()
                                               ->map(fn($t) => [
                                                   'name'  => $t->assignee?->name ?? 'Unassigned',
                                                   'count' => $t->count,
                                               ]),
        ];
    }

    public function getVelocityChart(Project $project, int $lastNSprints = 6): array
    {
        return Sprint::where('project_id', $project->id)
                     ->where('status', 'completed')
                     ->latest()
                     ->take($lastNSprints)
                     ->get()
                     ->reverse()
                     ->map(fn($s) => [
                         'sprint'           => $s->name,
                         'committed_points' => $s->total_points,
                         'completed_points' => $s->completed_points,
                         'velocity'         => $s->velocity,
                     ])
                     ->values()
                     ->toArray();
    }

    public function getTimelogReport(int $workspaceId, Carbon $from, Carbon $to): Collection
    {
        return TaskTimeLog::with(['user','task.project'])
                          ->whereHas('task.project', fn($q) => $q->where('workspace_id', $workspaceId))
                          ->whereBetween('logged_date', [$from, $to])
                          ->get()
                          ->groupBy('user_id')
                          ->map(fn($logs, $userId) => [
                              'user'         => $logs->first()->user,
                              'total_hours'  => $logs->sum('hours'),
                              'by_project'   => $logs->groupBy('task.project_id')
                                                     ->map(fn($pl) => [
                                                         'project' => $pl->first()->task->project->name,
                                                         'hours'   => $pl->sum('hours'),
                                                     ])->values(),
                          ]);
    }

    public function getDashboardStats(int $workspaceId, int $userId): array
    {
        $projectIds = Project::where('workspace_id', $workspaceId)
                             ->whereHas('members', fn($q) => $q->where('user_id', $userId))
                             ->pluck('id');

        return [
            'my_tasks_due_today'   => Task::whereIn('project_id', $projectIds)
                                          ->where('assignee_id', $userId)
                                          ->whereDate('due_date', today())
                                          ->whereNotIn('status',['done'])->count(),
            'my_overdue_tasks'     => Task::whereIn('project_id', $projectIds)
                                          ->where('assignee_id', $userId)
                                          ->overdue()->count(),
            'active_projects'      => Project::where('workspace_id', $workspaceId)
                                             ->whereHas('members', fn($q) => $q->where('user_id', $userId))
                                             ->where('status','active')->count(),
            'hours_this_week'      => TaskTimeLog::where('user_id', $userId)
                                                 ->whereBetween('logged_date', [
                                                     now()->startOfWeek(),
                                                     now()->endOfWeek()
                                                 ])->sum('hours'),
            'tasks_completed_week' => Task::whereIn('project_id', $projectIds)
                                          ->where('assignee_id', $userId)
                                          ->where('status','done')
                                          ->whereBetween('completed_at', [
                                              now()->startOfWeek(), now()->endOfWeek()
                                          ])->count(),
        ];
    }
}
