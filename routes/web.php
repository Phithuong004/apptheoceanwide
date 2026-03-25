<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{LoginController, RegisterController, TwoFactorController};
use App\Http\Controllers\Workspace\WorkspaceController;
use App\Http\Controllers\Dashboard\DashboardController;

// ── Auth ─────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login',   [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/2fa',      [TwoFactorController::class, 'show'])->name('auth.two-factor');
    Route::post('/2fa',     [TwoFactorController::class, 'verify']);

    Route::get('/forgot-password',        [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password',       [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password',        [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── Workspace Invitation (public) ────────────────────────────
Route::get('/invitation/{token}', [WorkspaceController::class, 'acceptInvitation'])
    ->name('invitation.accept');

// ── Authenticated Routes ──────────────────────────────────────
Route::middleware(['auth', 'track.activity'])->group(function () {

    Route::get('/', fn() => redirect()->route('workspace.index'))->name('home');

    // Workspace (không scoped)
    Route::get('/workspaces',        [WorkspaceController::class, 'index'])->name('workspace.index');
    Route::get('/workspaces/create', [WorkspaceController::class, 'create'])->name('workspace.create');
    Route::post('/workspaces',       [WorkspaceController::class, 'store'])->name('workspace.store');

    // Notifications
    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.read');

    Route::post('/notifications/{id}/read', function (string $id) {
        auth()->user()->notifications()->where('id', $id)->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    })->name('notifications.read.one');

    // Workspace-scoped routes
    Route::prefix('{workspace:slug}')
        ->middleware('workspace.member')
        ->group(function () {

            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // Workspace management
            Route::get('/',    [WorkspaceController::class, 'show'])->name('workspace.show');
            Route::put('/',    [WorkspaceController::class, 'update'])->name('workspace.update'); // ← đã chuyển vào đây
            Route::post('/invite', [WorkspaceController::class, 'invite'])->name('workspace.invite');

            // Quản lý thành viên
            Route::post('/members',        [\App\Http\Controllers\Workspace\WorkspaceMemberController::class, 'store'])->name('workspace.members.store');
            Route::delete('/members/{user}',[\App\Http\Controllers\Workspace\WorkspaceMemberController::class, 'destroy'])->name('workspace.members.destroy');

            // Projects, Tasks, Sprints, HR...
            require base_path('routes/projects.php');
            require base_path('routes/tasks.php');
            require base_path('routes/sprints.php');
            require base_path('routes/hr.php');
            require base_path('routes/clients.php');
            require base_path('routes/finance.php');
            require base_path('routes/reports.php');
        });
});
