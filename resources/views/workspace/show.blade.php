@extends('layouts.app')

@section('title', $workspace->name)

@section('content')
<div class="max-w-4xl">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <img src="{{ $workspace->logo ? asset('storage/' . $workspace->logo) : 'https://ui-avatars.com/api/?name='.urlencode($workspace->name).'&background=6366f1&color=fff&size=64' }}"
                 class="w-16 h-16 rounded-xl">
            <div>
                <h1 class="text-3xl font-bold text-white">{{ $workspace->name }}</h1>
                <p class="text-gray-400 mt-1">{{ $workspace->description ?? 'Không có mô tả' }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <span class="px-3 py-1 bg-gray-800 text-gray-300 rounded-full text-sm">
                {{ $workspace->plan ?? 'Free' }}
            </span>
            <span class="px-3 py-1 bg-gray-800 text-gray-300 rounded-full text-sm">
                {{ $workspace->members->count() }} thành viên
            </span>
            <span class="px-3 py-1 bg-gray-800 text-gray-300 rounded-full text-sm">
                {{ $projects->count() }} dự án
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Thống kê --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">📊 Thống kê</h2>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <div class="text-3xl font-bold text-white">{{ $stats['total_members'] }}</div>
                    <div class="text-gray-400 text-sm mt-1">Thành viên</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-white">{{ $stats['total_projects'] }}</div>
                    <div class="text-gray-400 text-sm mt-1">Dự án</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-white">{{ $stats['active_tasks'] }}</div>
                    <div class="text-gray-400 text-sm mt-1">Task đang làm</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-white">{{ $stats['completed_tasks'] }}</div>
                    <div class="text-gray-400 text-sm mt-1">Task hoàn thành</div>
                </div>
            </div>
        </div>

        {{-- Thành viên --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">👥 Thành viên</h2>
            <div class="flex -space-x-2">
                @foreach($members->take(6) as $member)
                    <img src="{{ $member->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($member->name).'&background=6366f1&color=fff' }}"
                         class="w-10 h-10 rounded-full border-2 border-gray-900"
                         title="{{ $member->name }} ({{ $member->pivot->role }})">
                @endforeach
                @if($members->count() > 6)
                    <span class="w-10 h-10 rounded-full bg-gray-800 border-2 border-gray-900 flex items-center justify-center text-xs text-gray-300">
                        +{{ $members->count() - 6 }}
                    </span>
                @endif
            </div>
            <div class="mt-3">
                <a href="#" class="text-blue-400 text-sm hover:underline">
                    Xem tất cả {{ $members->count() }} thành viên
                </a>
            </div>
        </div>
    </div>

    {{-- Danh sách dự án --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <div class="p-6 border-b border-gray-800">
            <h2 class="text-lg font-semibold text-white">📁 Dự án</h2>
        </div>
        <div class="divide-y divide-gray-800">
            @forelse($projects as $project)
                <a href="{{ route('projects.show', [$workspace->slug, $project->slug]) }}"
                   class="flex items-center gap-4 p-6 hover:bg-gray-850 transition">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $project->color ?? 'from-gray-700 to-gray-800' }} flex items-center justify-center">
                        <span class="text-sm font-semibold">{{ strtoupper(substr($project->name, 0, 1)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-medium text-sm">{{ $project->name }}</p>
                        <p class="text-gray-400 text-xs">{{ $project->description ?: 'Không có mô tả' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 bg-gray-800 text-xs rounded-full text-gray-300">
                            {{ $project->status }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="p-12 text-center">
                    <p class="text-gray-500 text-sm mb-2">Chưa có dự án nào</p>
                    <a href="{{ route('projects.create', $workspace->slug) }}"
                       class="text-blue-400 hover:text-blue-300 text-sm">
                        Tạo dự án đầu tiên
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
