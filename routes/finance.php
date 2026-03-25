<?php

use App\Http\Controllers\Finance\{InvoiceController, BudgetController, ExpenseController};
use Illuminate\Support\Facades\Route;

Route::prefix('finance')->name('finance.')->middleware('permission:finance.view')->group(function () {

    // Invoices ✅ FIXED
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/',                       [InvoiceController::class, 'index'])->name('index');
        Route::post('/',                      [InvoiceController::class, 'store'])->name('store');
        Route::delete('/bulk-delete',         [InvoiceController::class, 'bulkDelete'])->name('bulkDelete');
        Route::get('/{invoice}',              [InvoiceController::class, 'show'])->name('show');
        
        // 🆕 NEW ROUTES (KHÔNG TRÙNG LẶP)
        Route::get('/{invoice}/pdf',          [InvoiceController::class, 'pdf'])->name('pdf');
        Route::post('/{invoice}/send',        [InvoiceController::class, 'send'])->name('send');
        
        // Existing routes
        Route::get('/{invoice}/download',     [InvoiceController::class, 'download'])->name('download');
        Route::post('/{invoice}/mark-paid',   [InvoiceController::class, 'markPaid'])->name('mark-paid');
        Route::patch('/{invoice}/items',      [InvoiceController::class, 'updateItems'])->name('update-items');
    }); // ✅ ĐÓNG INVOICES GROUP

    // Budgets
    Route::resource('budgets', BudgetController::class)->names('budgets');

    // Expenses
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/',                       [ExpenseController::class, 'index'])->name('index');
        Route::post('/',                      [ExpenseController::class, 'store'])->name('store');
        Route::post('/{expense}/approve',     [ExpenseController::class, 'approve'])->name('approve');
        Route::post('/{expense}/reject',      [ExpenseController::class, 'reject'])->name('reject');
    }); 

}); 
