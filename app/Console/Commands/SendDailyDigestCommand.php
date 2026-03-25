<?php
namespace App\Console\Commands;

use App\Mail\DailyDigestMail;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyDigestCommand extends Command
{
    protected $signature   = 'digest:send';
    protected $description = 'Gửi email tóm tắt hàng ngày cho tất cả user';

    public function handle(): void
    {
        $users = User::where('status', 'active')
                     ->whereNotNull('email_verified_at')
                     ->get();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $tasks = Task::where('assignee_id', $user->id)
                         ->whereNotIn('status', ['done'])
                         ->with(['project','labels'])
                         ->orderBy('due_date')
                         ->take(10)
                         ->get();

            $overdue = $tasks->filter(fn($t) => $t->isOverdue());
            $dueToday = $tasks->filter(fn($t) => $t->due_date?->isToday());

            if ($tasks->count() > 0) {
                Mail::to($user->email)
                    ->queue(new DailyDigestMail($user, $tasks, $overdue, $dueToday));
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n✅ Daily digest sent to {$users->count()} users.");
    }
}
