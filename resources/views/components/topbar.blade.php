<header class="bg-gray-900 border-b border-gray-800 px-6 py-3 flex items-center justify-between shrink-0">
    <h1 class="text-white font-semibold">@yield('title', 'Dashboard')</h1>
    <div class="flex items-center gap-4 text-gray-400 text-sm">
        <span>{{ now()->format('d/m/Y') }}</span>
        <span class="bg-blue-600/20 text-blue-400 px-2 py-1 rounded text-xs">
            {{ Auth::user()->getRoleNames()->first() ?? 'member' }}
        </span>
    </div>
</header>
