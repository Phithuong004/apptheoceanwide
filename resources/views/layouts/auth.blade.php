<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Đăng nhập') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <span class="text-5xl">🚀</span>
        <h1 class="text-2xl font-bold text-white mt-3">{{ config('app.name') }}</h1>
        <p class="text-gray-400 mt-1">@yield('subtitle', 'Chào mừng trở lại')</p>
    </div>

    {{-- Card --}}
    <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700 shadow-xl">

        {{-- Status (password reset success...) --}}
        @if(session('status'))
        <div class="mb-4 p-3 bg-green-900/50 border border-green-700 text-green-300 rounded-lg text-sm">
            ✅ {{ session('status') }}
        </div>
        @endif

        {{-- Errors --}}
        @if($errors->any())
        <div class="mb-4 p-3 bg-red-900/50 border border-red-700 text-red-300 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </div>

    {{-- Footer links --}}
    <div class="text-center mt-6 text-sm text-gray-500">
        @yield('footer')
    </div>

</div>

@stack('scripts')
</body>
</html>
