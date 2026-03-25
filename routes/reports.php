<?php
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Report\BurndownController;
use App\Http\Controllers\Report\VelocityController;
use Illuminate\Support\Facades\Route;

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');

    Route::get('/{project}/burndown/{sprint}',      [BurndownController::class, 'show'])->name('burndown');
    Route::get('/{project}/burndown/{sprint}/data', [BurndownController::class, 'data'])->name('burndown.data');
    Route::get('/{project}/velocity',               [VelocityController::class, 'show'])->name('velocity');
});
