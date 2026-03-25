<?php
namespace App\Providers;

use App\Models\{Task, Project, Sprint};
use App\Observers\{TaskObserver, ProjectObserver, SprintObserver};
use App\Repositories\Contracts\{ProjectRepositoryInterface, TaskRepositoryInterface, SprintRepositoryInterface};
use App\Repositories\{ProjectRepository, TaskRepository, SprintRepository};
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(SprintRepositoryInterface::class, SprintRepository::class);
    }

    public function boot(): void
{
    // Observers — giữ nguyên
    Task::observe(TaskObserver::class);
    Project::observe(ProjectObserver::class);
    Sprint::observe(SprintObserver::class);

    // ✅ Thêm View Composer cho sidebar
    View::composer('components.sidebar', function ($view) {
        $workspace = request()->route('workspace');
        $view->with('workspace', $workspace);
        $view->with('slug', $workspace?->slug);
    });

}
}
