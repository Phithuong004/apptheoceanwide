<?php

use App\Http\Controllers\Task\TaskController;
use App\Http\Controllers\Task\TaskCommentController;
use App\Http\Controllers\Task\AttachmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('projects/{project:id}')->group(function () {
    // Tasks CRUD
    Route::get('/tasks',           [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/create',    [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks',          [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}',    [TaskController::class, 'show'])->name('tasks.show');
    Route::put('/tasks/{task}',    [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}',  [TaskController::class, 'update'])->name('tasks.patch'); // Dual support
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{task}/move', [TaskController::class, 'move'])->name('tasks.move');

    // Task Actions
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::patch('/tasks/{task}/assign', [TaskController::class, 'assign'])->name('tasks.assign');
    Route::get('/tasks/{task}/assign', fn($workspace, $project, $task) => 
        redirect()->route('tasks.show', compact('workspace', 'project', 'task')) 
    )->name('tasks.assign.get');

    // Comments
    Route::prefix('/tasks/{task}/comments')->group(function () {
        Route::post('/',                  [TaskCommentController::class, 'store'])->name('comments.store');
        Route::put('/{comment}',          [TaskCommentController::class, 'update'])->name('comments.update');
        Route::delete('/{comment}',       [TaskCommentController::class, 'destroy'])->name('comments.destroy');
    });

    // Attachments  
    Route::prefix('/tasks/{task}/attachments')->group(function () {
        Route::post('/',                    [AttachmentController::class, 'store'])->name('attachments.store');
        Route::delete('/{attachment}',      [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    });

});

// My Tasks (ngoài prefix)
Route::get('/tasks/my', [TaskController::class, 'myTasks'])->name('tasks.my');
