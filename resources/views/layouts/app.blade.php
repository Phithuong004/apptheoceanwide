<!DOCTYPE html>
<html lang="vi" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ProjectHub') — {{ $workspace->name ?? '' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-950 text-white flex h-screen overflow-hidden">

    <x-sidebar :workspace="$workspace ?? null" />

    <div class="flex flex-col flex-1 overflow-hidden">

        {{-- Topbar --}}
        <header class="h-14 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-6 shrink-0">
            <div class="flex items-center gap-3">
                <span class="text-white font-semibold text-sm">@yield('title', 'ProjectHub')</span>
            </div>

            <div class="flex items-center gap-3">
                {{-- Notification Bell --}}
                @auth
                    @php $notifications = auth()->user()->unreadNotifications->take(10); @endphp
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="relative p-2 text-gray-400 hover:text-white transition rounded-lg hover:bg-gray-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if ($notifications->count() > 0)
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                            @endif
                        </button>

                        {{-- Dropdown --}}
                        <div x-show="open" x-transition @click.outside="open = false"
                            class="absolute right-0 mt-2 w-80 bg-gray-900 border border-gray-700 rounded-xl shadow-2xl z-50">

                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-800">
                                <span class="text-white text-sm font-semibold">
                                    Thông báo
                                    @if ($notifications->count() > 0)
                                        <span class="ml-1.5 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">
                                            {{ $notifications->count() }}
                                        </span>
                                    @endif
                                </span>
                                @if ($notifications->count() > 0)
                                    <form method="POST" action="{{ route('notifications.read') }}">
                                        @csrf
                                        <button class="text-xs text-blue-400 hover:text-blue-300 transition">
                                            Đánh dấu đã đọc
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-800">
                                @forelse($notifications as $n)
                                    @php $data = $n->data; @endphp
                                    <a href="{{ $data['action'] ?? '#' }}"
                                       class="flex gap-3 px-4 py-3 hover:bg-gray-800 transition"
                                       onclick="markRead('{{ $n->id }}')">
                                        {{-- Icon --}}
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-white text-xs font-bold">
                                            @if(isset($data['title']) && $data['title'] === 'Mời tham gia workspace')
                                                <span class="bg-green-600">W</span>
                                            @elseif(str_contains($data['title'] ?? '', 'task'))
                                                <span class="bg-blue-600">T</span>
                                            @elseif(str_contains($data['title'] ?? '', 'project'))
                                                <span class="bg-indigo-600">P</span>
                                            @else
                                                <span class="bg-gray-600">N</span>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-white leading-snug">
                                                {{ $data['title'] ?? 'Thông báo mới' }}
                                            </p>
                                            <p class="text-xs text-gray-400 mt-0.5">
                                                {{ $data['message'] ?? 'Có hoạt động mới' }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                                        </div>
                                    </a>
                                @empty
                                    <div class="px-4 py-8 text-center text-gray-500 text-sm">
                                        Không có thông báo mới
                                    </div>
                                @endforelse
                            </div>
                            
                        </div>
                    </div>
                @endauth

                {{-- User info --}}
                @auth
                    <div class="flex items-center gap-2 text-sm text-gray-400">
                        <div
                            class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden md:block">{{ auth()->user()->name }}</span>
                    </div>
                @endauth
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            @if (session('success'))
                <div class="mb-4 bg-green-500/20 border border-green-500 text-green-400 px-4 py-3 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-500/20 border border-red-500 text-red-400 px-4 py-3 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
    <script>
        function markRead(id) {
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            });
        }
    </script>
</body>

</html>
