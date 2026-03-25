<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <span class="text-4xl">🔐</span>
        <h1 class="text-2xl font-bold text-white mt-2">Đặt lại mật khẩu</h1>
        <p class="text-gray-400 mt-1">Nhập mật khẩu mới của bạn</p>
    </div>

    <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700 shadow-xl">

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            {{-- Email --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                <input type="email" name="email" value="{{ $email ?? old('email') }}" required
                       class="w-full bg-gray-700 border border-gray-600 rounded-xl px-4 py-3
                              text-white placeholder-gray-400 focus:outline-none
                              focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500
                              @error('email') border-red-500 @enderror">
                @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Mật khẩu mới</label>
                <input type="password" name="password" required
                       class="w-full bg-gray-700 border border-gray-600 rounded-xl px-4 py-3
                              text-white placeholder-gray-400 focus:outline-none
                              focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500
                              @error('password') border-red-500 @enderror">
                @error('password')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" required
                       class="w-full bg-gray-700 border border-gray-600 rounded-xl px-4 py-3
                              text-white placeholder-gray-400 focus:outline-none
                              focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <button type="submit"
                    class="w-full py-3 bg-indigo-600 hover:bg-indigo-500
                           text-white font-semibold rounded-xl transition-colors">
                Đặt lại mật khẩu
            </button>
        </form>

        <p class="text-center text-sm text-gray-400 mt-6">
            <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300">
                ← Quay lại đăng nhập
            </a>
        </p>
    </div>
</div>

</body>
</html>
