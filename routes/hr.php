<?php
use App\Http\Controllers\HR\{EmployeeController, DepartmentController, LeaveController, AttendanceController, PayrollController, PositionController};
use Illuminate\Support\Facades\Route;

Route::prefix('hr')->name('hr.')->middleware('permission:hr.view')->group(function () {
    // Employees
    Route::resource('employees', EmployeeController::class)->names('employees');

    // Departments
    Route::resource('departments', DepartmentController::class)->names('departments');

    // Positions
    Route::resource('positions', PositionController::class)->names('positions');

    // Leaves
    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/',                        [LeaveController::class, 'index'])->name('index');
        Route::post('/',                       [LeaveController::class, 'store'])->name('store');
        Route::post('/{leave}/approve',        [LeaveController::class, 'approve'])->name('approve');
        Route::post('/{leave}/reject',         [LeaveController::class, 'reject'])->name('reject');
    });

    // Attendance
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/',              [AttendanceController::class, 'index'])->name('index');
        Route::post('/check-in',    [AttendanceController::class, 'checkIn'])->name('check-in');
        Route::post('/check-out',   [AttendanceController::class, 'checkOut'])->name('check-out');
    });

    // Payroll
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/',                       [PayrollController::class, 'index'])->name('index');
        Route::post('/generate',              [PayrollController::class, 'generate'])->name('generate');
        Route::post('/{payroll}/confirm',     [PayrollController::class, 'confirm'])->name('confirm');
        Route::post('/{payroll}/mark-paid',   [PayrollController::class, 'markPaid'])->name('mark-paid');
    });
});
