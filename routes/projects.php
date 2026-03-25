<?php

use App\Http\Controllers\Project\{ProjectController, ProjectMemberController, ProjectSettingController};
use App\Http\Controllers\Task\{TaskController, TaskCommentController, TaskAttachmentController, TaskTimerController, SubtaskController};
use App\Http\Controllers\Sprint\{SprintController, BacklogController};
use Illuminate\Support\Facades\Route;
use App\Models\TaskSubtask;

// My Tasks (ngoài prefix projects)
Route::get('/tasks/my', [TaskController::class, 'myTasks'])->name('tasks.my');
Route::bind('subtask', fn($value) => TaskSubtask::findOrFail($value));

// Projects
Route::prefix('projects')->name('projects.')->group(function () {
    Route::get('/',         [ProjectController::class, 'index'])->name('index');
    Route::get('/create',   [ProjectController::class, 'create'])->name('create');
    Route::post('/',        [ProjectController::class, 'store'])->name('store');

    Route::prefix('{project:slug}')->group(function () {
        Route::get('/',         [ProjectController::class, 'show'])->name('show');
        Route::get('/edit',     [ProjectController::class, 'edit'])->name('edit');
        Route::put('/',         [ProjectController::class, 'update'])->name('update');
        Route::delete('/',      [ProjectController::class, 'destroy'])->name('destroy');
        Route::get('/settings', [ProjectSettingController::class, 'index'])->name('settings');

        // Members
        Route::post('/members',          [ProjectMemberController::class, 'store'])->name('members.add');
        Route::delete('/members/{user}', [ProjectMemberController::class, 'destroy'])->name('members.remove');

        // Tasks
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/',             [TaskController::class, 'index'])->name('index');
            Route::get('/kanban',       [TaskController::class, 'kanban'])->name('kanban');
            Route::post('/',            [TaskController::class, 'store'])->name('store');
            Route::get('/{task}',       [TaskController::class, 'show'])->name('show');
            Route::put('/{task}',       [TaskController::class, 'update'])->name('update');
            Route::delete('/{task}',    [TaskController::class, 'destroy'])->name('destroy');
            Route::post('/{task}/move', [TaskController::class, 'move'])->name('move');

            //Subtasks
            Route::post('/{task}/subtasks', [SubtaskController::class, 'store'])->name('subtasks.store');
            Route::get('/{task}/subtasks/{subtask}', [SubtaskController::class, 'show'])->name('subtasks.show');
            Route::delete('/{task}/subtasks/{subtask}', [SubtaskController::class, 'destroy'])->name('subtasks.destroy');
            Route::patch('/{task}/subtasks/{subtask}', [SubtaskController::class, 'update'])->name('subtasks.update'); 

            // Comments
            Route::post('/{task}/comments',           [TaskCommentController::class, 'store'])->name('comments.store');
            Route::put('/{task}/comments/{comment}',  [TaskCommentController::class, 'update'])->name('comments.update');
            Route::delete('/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('comments.destroy');

            // Attachments
            Route::post('/{task}/attachments',            [TaskAttachmentController::class, 'store'])->name('attachments.store');
            Route::delete('/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])->name('attachments.destroy');

            // Time logging
            Route::post('/{task}/time-logs', [TaskTimerController::class, 'store'])->name('timelog.store');
            Route::get('/{task}/time-logs',  [TaskTimerController::class, 'index'])->name('timelog.index');
        });

        // Sprints
        Route::prefix('sprints')->name('sprints.')->group(function () {
            Route::get('/',                   [SprintController::class, 'index'])->name('index');
            Route::post('/',                  [SprintController::class, 'store'])->name('store');
            Route::get('/{sprint}',           [SprintController::class, 'show'])->name('show');
            Route::post('/{sprint}/start',    [SprintController::class, 'start'])->name('start');
            Route::post('/{sprint}/complete', [SprintController::class, 'complete'])->name('complete');
            Route::get('/{sprint}/burndown',  [SprintController::class, 'burndown'])->name('burndown');
        });

        // Backlog
        Route::prefix('backlog')->name('backlog.')->group(function () {
            Route::get('/',                     [BacklogController::class, 'index'])->name('index');
            Route::post('/move-to-sprint',      [BacklogController::class, 'moveToSprint'])->name('move');
            Route::post('/tasks/{task}/remove', [BacklogController::class, 'removeFromSprint'])->name('remove');
        });
    });
});
