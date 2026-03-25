<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): User
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('member');
        $user->sendEmailVerificationNotification();

        Auth::login($user);
        return $user;
    }

    public function login(array $credentials, bool $remember = false): array
    {
        if (!Auth::attempt($credentials, $remember)) {
            return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng.'];
        }

        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            return ['success' => false, 'message' => 'Tài khoản đã bị khoá.'];
        }

        if ($user->two_factor_enabled) {
            Auth::logout();
            return ['success' => true, 'requires_2fa' => true, 'user' => $user];
        }

        $user->update(['last_active_at' => now()]);
        request()->session()->regenerate();

        return ['success' => true, 'requires_2fa' => false, 'user' => $user];
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
