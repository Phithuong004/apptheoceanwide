<?php
use App\Http\Controllers\Sprint\SprintController;
use Illuminate\Support\Facades\Route;

Route::prefix('projects/{project}/sprints')->name('sprints.')->group(function () {
    Route::get('/',                    [SprintController::class, 'index'])->name('index');
    Route::post('/',                   [SprintController::class, 'store'])->name('store');
    Route::get('/{sprint}',            [SprintController::class, 'show'])->name('show');
    Route::put('/{sprint}',            [SprintController::class, 'update'])->name('update');
    Route::delete('/{sprint}',         [SprintController::class, 'destroy'])->name('destroy');
    Route::post('/{sprint}/start',     [SprintController::class, 'start'])->name('start');
    Route::post('/{sprint}/complete',  [SprintController::class, 'complete'])->name('complete');
});
