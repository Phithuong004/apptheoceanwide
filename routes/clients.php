<?php
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Client\ClientPortalController;
use Illuminate\Support\Facades\Route;

Route::prefix('clients')->name('clients.')->group(function () {
    Route::get('/',           [ClientController::class, 'index'])->name('index');
    Route::get('/create',     [ClientController::class, 'create'])->name('create');
    Route::post('/',          [ClientController::class, 'store'])->name('store');
    Route::get('/{client}',   [ClientController::class, 'show'])->name('show');
    Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit');
    Route::put('/{client}',   [ClientController::class, 'update'])->name('update');
    Route::delete('/{client}',[ClientController::class, 'destroy'])->name('destroy');
});