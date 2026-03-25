@props(['workspace' => null])

<aside class="w-56 bg-gray-900 border-r border-gray-800 flex flex-col h-full shrink-0">

    {{-- Logo --}}
    <div class="px-4 py-4 border-b border-gray-800 flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-sm">
            {{ strtoupper(substr($workspace->name ?? 'P', 0, 1)) }}
        </div>
        <div class="overflow-hidden">
            <p class="text-white text-sm font-semibold truncate">{{ $workspace->name ?? 'ProjectHub' }}</p>
            <p class="text-gray-500 text-xs">{{ $workspace->plan ?? 'Free' }}</p>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">

        <p class="text-xs text-gray-500 uppercase tracking-wider px-3 py-2">Main</p>
        @if(!empty($slug))
        <x-sidebar-item :href="route('dashboard', $slug)" label="Dashboard"/>
        <x-sidebar-item :href="route('projects.index', $slug)" label="Dự án"/>
        <x-sidebar-item :href="route('tasks.my', $slug)" label="Task của tôi"/>

        <p class="text-xs text-gray-500 uppercase tracking-wider px-3 py-2 mt-4">Quản lý</p>
        <x-sidebar-item :href="route('clients.index', $slug)" label="Khách hàng"/>
        <x-sidebar-item :href="route('hr.employees.index', $slug)" label="Nhân sự"/>
        <x-sidebar-item :href="route('finance.invoices.index', $slug)" label="Tài chính"/>
        <x-sidebar-item :href="route('reports.index', $slug)" label="Báo cáo"/>

        @can('manage-workspace')
        <p class="text-xs text-gray-500 uppercase tracking-wider px-3 py-2 mt-4">Admin</p>
        <x-sidebar-item :href="route('workspace.members', $slug)" label="Thành viên"/>
        <x-sidebar-item :href="route('workspace.settings', $slug)" label="Cài đặt"/>
        @endcan
        @endif
    </nav>

    {{-- User --}}
    <div class="px-4 py-3 border-t border-gray-800 flex items-center gap-3">
        <img src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&background=3b82f6&color=fff' }}"
             class="w-8 h-8 rounded-full object-cover">
        <div class="flex-1 overflow-hidden">
            <p class="text-white text-sm truncate">{{ Auth::user()->name }}</p>
            <p class="text-gray-500 text-xs truncate">{{ Auth::user()->email }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-gray-500 hover:text-red-400 text-xs">Out</button>
        </form>
    </div>

</aside>
