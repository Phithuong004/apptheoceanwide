<?php
namespace App\Http\Controllers\Sprint;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\Request;

class BacklogController extends Controller
{
    public function index(Workspace $workspace, Project $project)
    {
        $backlog  = Task::where('project_id', $project->id)
                        ->whereNull('sprint_id')
                        ->with(['assignee','labels'])
                        ->orderBy('position')
                        ->get();
        $sprints  = $project->sprints()->where('status','planning')->get();
        $members  = $project->members;

        return view('sprints.backlog', compact('workspace','project','backlog','sprints','members'));
    }

    public function moveToSprint(Request $request, Workspace $workspace, Project $project)
    {
        $request->validate([
            'task_ids'  => 'required|array',
            'sprint_id' => 'required|exists:sprints,id',
        ]);

        Task::whereIn('id', $request->task_ids)
            ->where('project_id', $project->id)
            ->update(['sprint_id' => $request->sprint_id, 'status' => 'todo']);

        return response()->json(['success' => true]);
    }

    public function removeFromSprint(Request $request, Workspace $workspace, Project $project, Task $task)
    {
        $task->update(['sprint_id' => null, 'status' => 'backlog']);
        return response()->json(['success' => true]);
    }
}
