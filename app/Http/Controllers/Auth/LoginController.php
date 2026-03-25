<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
{
    $result = $this->authService->login(
        $request->only('email', 'password'),
        $request->boolean('remember')
    );

    if (!$result['success']) {
        return back()->withErrors(['email' => $result['message']])->withInput();
    }

    if ($result['requires_2fa']) {
        session(['2fa_user_id' => $result['user']->id]);
        return redirect()->route('auth.two-factor');
    }

    // ✅ Sửa redirect đúng
    $workspace = Auth::user()->workspaces()->first();
    if ($workspace) {
        return redirect()->route('dashboard', $workspace->slug);
    }

    return redirect()->route('workspace.create');
}


    public function logout(): RedirectResponse
    {
        $this->authService->logout();
        return redirect()->route('login');
    }
}
