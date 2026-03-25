<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private TaskService             $taskService,
        private TaskRepositoryInterface $taskRepo
    ) {}

    public function index(Request $request, Project $project): JsonResponse
    {
        $tasks = $this->taskRepo->getByProject($project->id, $request->only([
            'status','priority','assignee','sprint','search','type'
        ]));

        return response()->json([
            'data' => TaskResource::collection($tasks),
            'meta' => ['total' => $tasks->count()],
        ]);
    }

    public function kanban(Project $project): JsonResponse
    {
        $board = $this->taskRepo->getKanbanBoard($project->id);
        $result = [];
        foreach ($board as $status => $tasks) {
            $result[$status] = TaskResource::collection($tasks);
        }
        return response()->json(['data' => $result]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $request->validate([
            'title'           => 'required|string|max:500',
            'status'          => 'nullable|in:backlog,todo,in_progress,in_review,done,blocked',
            'priority'        => 'nullable|in:low,medium,high,urgent,critical',
            'type'            => 'nullable|in:epic,story,task,bug,sub_task',
            'assignee_id'     => 'nullable|exists:users,id',
            'sprint_id'       => 'nullable|exists:sprints,id',
            'story_points'    => 'nullable|integer|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
            'due_date'        => 'nullable|date',
            'labels'          => 'nullable|array',
        ]);

        $data               = $request->validated();
        $data['project_id'] = $project->id;
        $task               = $this->taskService->create($data, auth()->user());

        if (!empty($data['labels'])) {
            $task->labels()->sync($data['labels']);
        }

        return response()->json(['data' => new TaskResource($task->load(['assignee','labels']))], 201);
    }

    public function show(Project $project, Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        $task->load([
            'assignee','reporter','labels','subtasks.assignee',
            'attachments','timeLogs.user','comments.user',
            'watchers','dependencies.dependsOn',
        ]);

        return response()->json(['data' => new TaskResource($task)]);
    }

    public function update(Request $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        $task = $this->taskService->update($task, $request->only([
            'title','description','status','priority','assignee_id',
            'story_points','estimated_hours','due_date','sprint_id','tags',
        ]));

        if ($request->has('labels')) {
            $task->labels()->sync($request->labels);
        }

        return response()->json(['data' => new TaskResource($task->fresh(['assignee','labels']))]);
    }

    public function move(Request $request, Project $project, Task $task): JsonResponse
    {
        $request->validate([
            'status'   => 'required|in:backlog,todo,in_progress,in_review,done,blocked',
            'position' => 'required|integer|min:0',
        ]);

        $this->taskService->moveTask($task, $request->status, $request->position);

        return response()->json(['success' => true, 'task' => new TaskResource($task->fresh())]);
    }

    public function destroy(Project $project, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);
        $this->taskRepo->delete($task);

        return response()->json(null, 204);
    }
}
