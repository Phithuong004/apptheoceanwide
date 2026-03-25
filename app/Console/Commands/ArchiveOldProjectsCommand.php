<?php
namespace App\Console\Commands;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ArchiveOldProjectsCommand extends Command
{
    protected $signature   = 'projects:archive {--days=90}';
    protected $description = 'Archive các project hoàn thành sau N ngày';

    public function handle(): void
    {
        $days  = $this->option('days');
        $count = Project::where('status', 'completed')
                        ->where('updated_at', '<', now()->subDays($days))
                        ->update(['status' => 'archived']);

        $this->info("✅ Archived {$count} projects older than {$days} days.");
    }
}
