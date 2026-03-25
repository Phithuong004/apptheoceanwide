<?php
namespace App\Services;

use App\Events\SprintCompleted;
use App\Events\SprintStarted;
use App\Models\Sprint;
use App\Models\Task;

class SprintService
{
    public function startSprint(Sprint $sprint): void
    {
        if ($sprint->project->activeSprint) {
            throw new \RuntimeException('Đã có sprint đang chạy trong project này.');
        }

        $sprint->update([
            'status'     => 'active',
            'started_at' => now(),
        ]);

        event(new SprintStarted($sprint));
    }

    public function completeSprint(Sprint $sprint): array
    {
        $incompleteTasks = $sprint->tasks()
                                  ->whereNotIn('status', ['done'])
                                  ->get();

        // Move incomplete tasks to backlog
        foreach ($incompleteTasks as $task) {
            $task->update(['sprint_id' => null, 'status' => 'backlog']);
        }

        $sprint->update([
            'status'       => 'completed',
            'completed_at' => now(),
            'velocity'     => $sprint->tasks()->where('status','done')->sum('story_points'),
        ]);

        event(new SprintCompleted($sprint));

        return [
            'completed_tasks'   => $sprint->tasks()->where('status','done')->count(),
            'incomplete_tasks'  => $incompleteTasks->count(),
            'velocity'          => $sprint->velocity,
        ];
    }

    public function getBurndownData(Sprint $sprint): array
    {
        if (!$sprint->start_date || !$sprint->end_date) return [];

        $totalPoints = $sprint->total_points;
        $period      = \Carbon\CarbonPeriod::create($sprint->start_date, $sprint->end_date);
        $data        = [];

        foreach ($period as $date) {
            $completedOnDay = Task::where('sprint_id', $sprint->id)
                                  ->where('status', 'done')
                                  ->whereDate('completed_at', '<=', $date)
                                  ->sum('story_points');

            $data[] = [
                'date'      => $date->format('Y-m-d'),
                'remaining' => max(0, $totalPoints - $completedOnDay),
                'ideal'     => $this->getIdealBurndown($totalPoints, $sprint, $date),
            ];
        }

        return $data;
    }

    private function getIdealBurndown(int $total, Sprint $sprint, \Carbon\Carbon $date): float
    {
        $totalDays  = $sprint->start_date->diffInDays($sprint->end_date);
        $daysPassed = $sprint->start_date->diffInDays($date);
        if ($totalDays === 0) return 0;
        return round($total - ($total * $daysPassed / $totalDays), 2);
    }
}
