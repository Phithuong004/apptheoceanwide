<?php
namespace App\Console\Commands;

use App\Jobs\GenerateReportJob;
use App\Models\Project;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateReportsCommand extends Command
{
    protected $signature   = 'reports:generate {--project=} {--type=summary} {--user=}';
    protected $description = 'Generate reports for projects';

    public function handle(): void
    {
        $projectId = $this->option('project');
        $type      = $this->option('type');
        $userId    = $this->option('user');

        $projects = $projectId
            ? Project::where('id', $projectId)->get()
            : Project::where('status', 'active')->get();

        $user = $userId ? User::find($userId) : User::first();

        foreach ($projects as $project) {
            GenerateReportJob::dispatch($project, $user, $type);
        }

        $this->info("✅ Report generation dispatched for {$projects->count()} projects.");
    }
}
