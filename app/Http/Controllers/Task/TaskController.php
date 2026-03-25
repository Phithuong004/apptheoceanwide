<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService,
        private TaskRepositoryInterface $taskRepo
    ) {}

    public function index(Workspace $workspace, Project $project, Request $request)
    {
        $tasks   = $this->taskRepo->getByProject($project->id, $request->only([
            'status', 'priority', 'assignee', 'sprint', 'search'
        ]));
        $members = $project->members;
        $labels  = $project->labels;
        $sprints = $project->sprints;

        return view('tasks.index', compact('workspace', 'project', 'tasks', 'members', 'labels', 'sprints'));
    }

    public function show(Workspace $workspace, Project $project, Task $task)
    {
        $this->authorize('view', $task);

        $task->load([
            'assignee',
            'reporter',
            'labels',
            'taskSubtasks',
            'attachments',
            'timeLogs.user',
            'comments.user',
            'comments.replies.user',
            'watchers',
            'dependencies',
            'collaborators.user',
            'project',
        ]);

        $members = $project->members()->get();

        return view('tasks.show', compact('workspace', 'project', 'task', 'members'));
    }

    public function store(StoreTaskRequest $request, Workspace $workspace, Project $project)
    {
        $data               = $request->validated();
        $data['project_id'] = $project->id;
        $task               = $this->taskService->create($data, auth()->user());

        if ($request->wantsJson()) {
            return response()->json(['task' => $task->load(['assignee', 'labels'])]);
        }

        return back()->with('success', 'Task đã được tạo!');
    }

    public function update(UpdateTaskRequest $request, Workspace $workspace, Project $project, Task $task)
    {
        $this->authorize('update', $task);
        $task = $this->taskService->update($task, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['task' => $task->load(['assignee', 'labels'])]);
        }

        return back()->with('success', 'Task đã được cập nhật!');
    }

    public function move(Request $request, Workspace $workspace, Project $project, Task $task)
    {
        $request->validate([
            'status'   => 'required|in:backlog,todo,in_progress,in_review,done,blocked',
            'position' => 'required|integer|min:0',
        ]);

        $this->taskService->moveTask($task, $request->status, $request->position);

        return response()->json(['success' => true]);
    }

    public function destroy(Workspace $workspace, Project $project, Task $task)
    {
        $this->authorize('delete', $task);
        $this->taskRepo->delete($task);

        return back()->with('success', 'Task đã được xoá.');
    }

    public function kanban(Workspace $workspace, Project $project)
    {
        $board   = $this->taskRepo->getKanbanBoard($project->id);
        $members = $project->members;
        $labels  = $project->labels;
        $sprints = $project->sprints;

        return view('tasks.kanban', compact('workspace', 'project', 'board', 'members', 'labels', 'sprints'));
    }

    public function updateStatus(Workspace $workspace, Project $project, Task $task, Request $request): RedirectResponse
    {
        $request->validate(['status' => 'required|in:backlog,todo,in_progress,in_review,done,blocked']);
        $this->taskRepo->update($task, ['status' => $request->status]);

        return back()->with('success', 'Cập nhật trạng thái thành công.');
    }

    public function assign(Workspace $workspace, Project $project, Task $task, Request $request)
    {
        $request->validate(['assignee_id' => 'nullable|exists:users,id']);
        $this->taskRepo->update($task, ['assignee_id' => $request->assignee_id]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Đã giao task.');
    }

    public function myTasks(Workspace $workspace)
    {
        $tasks = $this->taskRepo->getMyTasks(auth()->id(), $workspace->id);

        return view('tasks.my', compact('workspace', 'tasks'));
    }
}
