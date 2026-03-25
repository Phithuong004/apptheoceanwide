<?php
namespace App\Jobs;

use App\Models\Task;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateRecurringTasksJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function handle(TaskService $taskService): void
    {
        $recurringTasks = Task::where('is_recurring', true)
                              ->where('status', 'done')
                              ->get();

        foreach ($recurringTasks as $task) {
            $pattern = $task->recurrence_pattern;
            if (!$this->shouldCreate($task, $pattern)) continue;

            $newTask = $task->replicate(['status','completed_at','sprint_id','actual_hours']);
            $newTask->status       = 'todo';
            $newTask->completed_at = null;
            $newTask->actual_hours = 0;
            $newTask->due_date     = $this->getNextDueDate($task->due_date, $pattern);
            $newTask->save();

            $newTask->labels()->sync($task->labels->pluck('id'));
        }
    }

    private function shouldCreate(Task $task, array $pattern): bool
    {
        $freq = $pattern['frequency'] ?? 'weekly';
        $last = $task->completed_at ?? $task->updated_at;

        return match($freq) {
            'daily'    => $last->isYesterday(),
            'weekly'   => $last->diffInDays(now()) >= 7,
            'monthly'  => $last->diffInMonths(now()) >= 1,
            default    => false,
        };
    }

    private function getNextDueDate(?Carbon $current, array $pattern): Carbon
    {
        $base = $current ?? now();
        return match($pattern['frequency'] ?? 'weekly') {
            'daily'   => $base->copy()->addDay(),
            'weekly'  => $base->copy()->addWeek(),
            'monthly' => $base->copy()->addMonth(),
            default   => $base->copy()->addWeek(),
        };
    }
}
