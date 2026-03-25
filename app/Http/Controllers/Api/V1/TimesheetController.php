<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TaskTimeLog;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    public function __construct(private TaskService $taskService) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'from'         => 'nullable|date',
            'to'           => 'nullable|date',
            'project_id'   => 'nullable|exists:projects,id',
        ]);

        $logs = TaskTimeLog::where('user_id', auth()->id())
                           ->when($request->from, fn($q) => $q->where('logged_date', '>=', $request->from))
                           ->when($request->to,   fn($q) => $q->where('logged_date', '<=', $request->to))
                           ->when($request->project_id, fn($q) =>
                               $q->whereHas('task', fn($t) => $t->where('project_id', $request->project_id))
                           )
                           ->with(['task.project'])
                           ->orderBy('logged_date', 'desc')
                           ->paginate(50);

        return response()->json([
            'data'        => $logs->items(),
            'total_hours' => TaskTimeLog::where('user_id', auth()->id())
                                        ->when($request->from, fn($q) => $q->where('logged_date', '>=', $request->from))
                                        ->when($request->to,   fn($q) => $q->where('logged_date', '<=', $request->to))
                                        ->sum('hours'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'task_id'     => 'required|exists:tasks,id',
            'hours'       => 'required|numeric|min:0.1|max:24',
            'description' => 'nullable|string|max:500',
            'date'        => 'nullable|date|before_or_equal:today',
        ]);

        $task = \App\Models\Task::findOrFail($request->task_id);
        $this->taskService->logTime($task, auth()->user(), $request->all());

        return response()->json(['success' => true, 'actual_hours' => $task->fresh()->actual_hours], 201);
    }
}
