<?php
namespace App\Console\Commands;

use App\Jobs\CreateRecurringTasksJob;
use Illuminate\Console\Command;

class ProcessRecurringTasksCommand extends Command
{
    protected $signature   = 'tasks:process-recurring';
    protected $description = 'Xử lý và tạo lại các recurring tasks';

    public function handle(): void
    {
        CreateRecurringTasksJob::dispatch();
        $this->info('✅ Recurring tasks job dispatched.');
    }
}
