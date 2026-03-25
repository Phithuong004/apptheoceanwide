<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Models\TaskSubtask;

class SubtaskController extends Controller
{
    public function store(Request $request, Workspace $workspace, Project $project, Task $task)
    {
        $request->validate(['title' => 'required|string|max:255']);

        $subtask = TaskSubtask::create([
            'task_id'     => $task->id,
            'title'       => $request->title,
            'assignee_id' => $request->assignee_id,
            'status'      => 'todo',
            'sort_order'  => TaskSubtask::where('task_id', $task->id)->max('sort_order') + 1,
        ]);

        $subtask->show_url = route('projects.tasks.subtasks.show', [
            'workspace' => $workspace->slug,
            'project'   => $project->slug,
            'task'      => $task->id,
            'subtask'   => $subtask->id,
        ]);

        $subtask->delete_url = route('projects.tasks.subtasks.destroy', [
            'workspace' => $workspace->slug,
            'project'   => $project->slug,
            'task'      => $task->id,
            'subtask'   => $subtask->id,
        ]);

        return response()->json(['subtask' => $subtask]);
    }

    public function show(Workspace $workspace, Project $project, Task $task, TaskSubtask $subtask)
    {
        abort_if($subtask->task_id !== $task->id, 404);

        $members = $workspace->members()->get();

        return view('projects.subtasks.show', compact('workspace', 'project', 'task', 'subtask', 'members'));
    }

    public function update(Request $request, Workspace $workspace, Project $project, Task $task, TaskSubtask $subtask)
    {
        abort_if($subtask->task_id !== $task->id, 404);

        $request->validate([
            'title'       => 'required|string|max:255',
            'assignee_id' => 'nullable|exists:users,id',
            'deadline'    => 'nullable|date',
            'status'      => 'nullable|in:todo,in_progress,done',
            'comments'    => 'nullable|string',
        ]);

        $subtask->update($request->only('title', 'assignee_id', 'deadline', 'status', 'comments'));

        return redirect()
            ->route('tasks.show', [$workspace->slug, $project->id, $task->id])
            ->with('success', 'Đã cập nhật subtask!');
    }


    public function destroy(Workspace $workspace, Project $project, Task $task, TaskSubtask $subtask)
    {
        abort_if($subtask->task_id !== $task->id, 404);

        $subtask->delete();
        return response()->json(['success' => true]);
    }
}
