@extends('layouts.app')
@section('title', 'Dự án')
@section('content')

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-white text-xl font-bold">Dự án</h2>
        <a href="{{ route('projects.create', $workspace->slug) }}"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">+ Tạo dự án</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($projects as $project)
            <a href="{{ route('projects.show', [$workspace->slug, $project]) }}"
                class="group relative bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-700 hover:border-blue-500/50 hover:shadow-2xl hover:shadow-blue-500/10 rounded-2xl p-6 transition-all duration-300 hover:-translate-y-1 hover:scale-[1.02] overflow-hidden">

                {{-- Background decoration --}}
                <div
                    class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                </div>

                {{-- Status Badge --}}
                <div class="absolute top-4 right-4">
                    <span
                        class="text-xs px-3 py-1.5 rounded-full font-medium backdrop-blur-sm
                {{ $project->status === 'active'
                    ? 'bg-green-500/20 text-green-400 border border-green-500/30'
                    : ($project->status === 'completed'
                        ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                        : 'bg-gray-700/50 text-gray-400 border border-gray-700/50') }}">
                        {{ ucfirst($project->status) }}
                    </span>
                </div>

                {{-- Project Name --}}
                <h3
                    class="text-white font-bold text-lg mb-3 relative z-10 line-clamp-1 group-hover:text-blue-300 transition-colors">
                    {{ $project->name }}
                </h3>

                {{-- Client & Owner - Compact horizontal layout --}}
                <div class="flex items-center gap-3 mb-4 relative z-10">

                    {{-- Client --}}
                    @if ($project->client)
                        <div
                            class="flex items-center gap-2 bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl px-3 py-2 flex-1 min-w-0 mb-1">
                            <div
                                class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-indigo-500/30 flex items-center justify-center flex-shrink-0">
                                @if ($project->client->avatar)
                                    <img src="{{ $project->client->avatar }}" alt="{{ $project->client->name }}"
                                        class="w-full h-full object-cover rounded-lg">
                                @else
                                    <span class="text-indigo-300 font-bold text-sm uppercase tracking-wider">
                                        {{ substr($project->client->name, 0, 2) }}
                                    </span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-indigo-300 text-sm font-medium truncate">{{ $project->client->name }}</p>
                                <p class="text-xs text-gray-500 tracking-wider font-medium opacity-80 mt-0.5">
                                    Client</p>
                            </div>
                        </div>
                    @endif

                    {{-- Owner --}}
                    @if ($project->owner)
                        <div
                            class="flex items-center gap-2 bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl px-3 py-2 flex-shrink-0">
                            <img src="{{ $project->owner->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($project->owner->name) . '&background=6366f1&color=fff&size=24' }}"
                                alt="{{ $project->owner->name }}" class="w-8 h-8 rounded-lg">
                            <div class="min-w-0 flex-1">
                                <p class="text-gray-200 text-sm font-medium truncate">{{ $project->owner->name }}</p>
                                <p class="text-xs text-gray-500 tracking-wider font-medium opacity-80 mt-0.5">
                                    Owner</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Description --}}
                <p class="text-gray-400 text-sm mb-4 line-clamp-2 relative z-10 leading-relaxed">
                    {{ $project->description ?? 'Chưa có mô tả.' }}</p>

                {{-- Stats Bar --}}
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <span class="text-xs text-gray-500 font-medium bg-gray-900/50 px-2.5 py-1 rounded-lg backdrop-blur-sm">
                        {{ $project->tasks_count ?? 0 }} tasks
                    </span>
                    <span class="text-xs text-gray-500">
                        {{ $project->end_date?->format('d/m/Y') ?? 'No deadline' }}
                    </span>
                </div>

                {{-- Progress Bar --}}
                @if ($project->tasks_count > 0)
                    <div class="relative z-10">
                        <div
                            class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-full h-2 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-2 rounded-full transition-all duration-700 ease-out relative overflow-hidden"
                                style="width: {{ max(5, ($project->completed_tasks_count / $project->tasks_count) * 100) }}%">
                                <div
                                    class="absolute inset-0 bg-gradient-to-r from-blue-400/50 to-indigo-400/50 animate-pulse opacity-0 group-hover:opacity-100 transition-opacity">
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 text-right font-mono">
                            {{ $project->completed_tasks_count ?? 0 }}/{{ $project->tasks_count }}
                        </p>
                    </div>
                @endif
            </a>
        @empty
            <div class="col-span-full text-center py-20 text-gray-500 bg-gray-900/50 border border-gray-800 rounded-2xl">
                <div
                    class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-800 to-gray-700 rounded-2xl flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-400 mb-2">Chưa có dự án nào</h3>
                <p class="text-gray-500 mb-6">Bắt đầu bằng cách tạo dự án đầu tiên của bạn.</p>
                <a href="{{ route('projects.create', $workspace->slug) }}"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-medium transition-all shadow-lg hover:shadow-xl hover:shadow-blue-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tạo dự án đầu tiên
                </a>
            </div>
        @endforelse
    </div>

@endsection
