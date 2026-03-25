<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-950 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-gray-900 rounded-2xl p-8 border border-gray-800">
        <h1 class="text-2xl font-bold text-white mb-2">Đăng nhập</h1>
        <p class="text-gray-400 text-sm mb-6">Chào mừng trở lại 👋</p>

        @if($errors->any())
            <div class="bg-red-500/20 border border-red-500 text-red-400 px-4 py-3 rounded-lg text-sm mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-gray-400 text-sm">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full mt-1 bg-gray-800 text-white rounded-lg px-4 py-2.5 border border-gray-700 focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="text-gray-400 text-sm">Mật khẩu</label>
                <input type="password" name="password" required
                       class="w-full mt-1 bg-gray-800 text-white rounded-lg px-4 py-2.5 border border-gray-700 focus:border-blue-500 focus:outline-none">
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded"> Ghi nhớ
                </label>
                <a href="{{ route('password.request') }}" class="text-blue-400 text-sm hover:underline">Quên mật khẩu?</a>
            </div>
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition">
                Đăng nhập
            </button>
        </form>

        <p class="text-center text-gray-500 text-sm mt-6">
            Chưa có tài khoản?
            <a href="{{ route('register') }}" class="text-blue-400 hover:underline">Đăng ký</a>
        </p>
    </div>
</body>
</html>
