<?php
use App\Http\Controllers\Api\V1;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ── Public ───────────────────────────────────────────────
    Route::post('/auth/login',    [V1\AuthController::class, 'login'])->name('auth.login');
    Route::post('/auth/register', [V1\AuthController::class, 'register'])->name('auth.register');

    // ── Authenticated ─────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/auth/logout',  [V1\AuthController::class, 'logout']);
        Route::get('/auth/me',       [V1\AuthController::class, 'me']);
        Route::post('/auth/refresh', [V1\AuthController::class, 'refreshToken']);

        // Users
        Route::get('/users',       [V1\UserController::class, 'index']);
        Route::get('/users/{user}',[V1\UserController::class, 'show']);
        Route::put('/users/me',    [V1\UserController::class, 'updateProfile']);

        // Notifications
        Route::get('/notifications',                        [V1\NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read',             [V1\NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all',              [V1\NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications/{id}',                [V1\NotificationController::class, 'destroy']);

        // Timesheet
        Route::get('/timesheet',  [V1\TimesheetController::class, 'index']);
        Route::post('/timesheet', [V1\TimesheetController::class, 'store']);

        // ── Workspace-scoped ──────────────────────────────────
        Route::prefix('workspaces/{workspace}')
             ->middleware('workspace.member')
             ->group(function () {

            // Projects
            Route::apiResource('projects', V1\ProjectController::class);
            Route::get('projects/{project}/kanban', [V1\TaskController::class, 'kanban']);

            // Tasks (nested under project)
            Route::prefix('projects/{project}')->group(function () {
                Route::apiResource('tasks', V1\TaskController::class);
                Route::post('tasks/{task}/move',    [V1\TaskController::class, 'move']);

                // Comments
                Route::apiResource('tasks/{task}/comments', V1\CommentController::class)
                     ->only(['index','store','update','destroy']);

                // Sprints
                Route::apiResource('sprints', V1\SprintController::class);
                Route::post('sprints/{sprint}/start',    [V1\SprintController::class, 'start']);
                Route::post('sprints/{sprint}/complete', [V1\SprintController::class, 'complete']);
            });

            // Clients
            Route::apiResource('clients', V1\ClientController::class);

            // Team
            Route::get('team',          [V1\TeamController::class, 'index']);
            Route::post('team/invite',  [V1\TeamController::class, 'invite']);
            Route::delete('team/{user}',[V1\TeamController::class, 'remove']);

            // Reports
            Route::get('reports/summary',  [V1\ReportController::class, 'summary']);
            Route::get('reports/timelog',  [V1\ReportController::class, 'timelog']);
            Route::get('reports/velocity', [V1\ReportController::class, 'velocity']);

            // Webhooks
            Route::apiResource('webhooks', V1\WebhookController::class);
        });
    });
});

// ── Fallback ──────────────────────────────────────────────────
Route::fallback(fn() => response()->json(['message' => 'API endpoint not found.'], 404));
