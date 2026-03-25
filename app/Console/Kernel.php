<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Daily digest at 8 AM Vietnam time
        $schedule->command('digest:send')
                 ->dailyAt('08:00')
                 ->timezone('Asia/Ho_Chi_Minh')
                 ->withoutOverlapping();

        // Process recurring tasks every day at midnight
        $schedule->command('tasks:process-recurring')
                 ->dailyAt('00:00')
                 ->withoutOverlapping();

        // Archive old completed projects every Sunday
        $schedule->command('projects:archive --days=90')
                 ->weekly()
                 ->sundays()
                 ->at('02:00');

        // Generate weekly reports every Monday 7 AM
        $schedule->command('reports:generate --type=summary')
                 ->weeklyOn(1, '07:00');

        // Auto mark overdue invoices
        $schedule->call(function () {
            \App\Models\Invoice::where('status', 'sent')
                               ->where('due_date', '<', today())
                               ->update(['status' => 'overdue']);
        })->daily();

        // Send deadline reminders (tasks due in 1 day)
        $schedule->call(function () {
            $tasks = \App\Models\Task::whereDate('due_date', tomorrow())
                                     ->whereNotIn('status', ['done'])
                                     ->with('assignee')
                                     ->get();
            foreach ($tasks as $task) {
                if ($task->assignee) {
                    $task->assignee->notify(
                        new \App\Notifications\DeadlineReminderNotification($task, 1)
                    );
                }
            }
        })->dailyAt('09:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
