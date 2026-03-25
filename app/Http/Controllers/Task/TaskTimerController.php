<?php
namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Workspace;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskTimerController extends Controller
{
    public function __construct(private TaskService $taskService) {}

    public function store(Request $request, Workspace $workspace, Task $task)
    {
        $request->validate([
            'hours'       => 'required|numeric|min:0.1|max:24',
            'description' => 'nullable|string|max:500',
            'date'        => 'nullable|date|before_or_equal:today',
        ]);

        $this->taskService->logTime($task, auth()->user(), $request->all());

        return response()->json([
            'success'      => true,
            'actual_hours' => $task->fresh()->actual_hours,
        ]);
    }

    public function index(Workspace $workspace, Task $task)
    {
        $logs = $task->timeLogs()->with('user')->orderBy('logged_date','desc')->get();
        return response()->json(['logs' => $logs]);
    }
}
