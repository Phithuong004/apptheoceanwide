@extends('layouts.app')
@section('title', 'Backlog — ' . $project->name)

@section('content')
<div class="flex gap-6">
    {{-- Backlog Column --}}
    <div class="flex-1">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-white">
                Product Backlog
                <span class="text-sm text-gray-400 font-normal ml-2">({{ $backlog->count() }} tasks)</span>
            </h2>
            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm">
                + Thêm Task
            </button>
        </div>

        <div class="space-y-2" id="backlog-list">
            @forelse($backlog as $task)
            <div class="bg-gray-800 rounded-xl p-4 flex items-center gap-4 border border-gray-700
                        hover:border-gray-600 transition-colors"
                 data-task-id="{{ $task->id }}">
                <input type="checkbox" class="task-select w-4 h-4 rounded text-indigo-600"
                       value="{{ $task->id }}">

                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-1.5 py-0.5 rounded
                            @if($task->type === 'bug') bg-red-900/50 text-red-400
                            @elseif($task->type === 'epic') bg-purple-900/50 text-purple-400
                            @else bg-blue-900/50 text-blue-400 @endif">
                            {{ strtoupper($task->type) }}
                        </span>
                        <span class="text-white text-sm font-medium">{{ $task->title }}</span>
                    </div>
                    @if($task->labels->count())
                    <div class="flex gap-1 mt-1">
                        @foreach($task->labels as $label)
                        <span class="text-xs px-1.5 rounded" style="color: {{ $label->color }}">
                            {{ $label->name }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="flex items-center gap-3 text-sm text-gray-400">
                    @if($task->story_points)
                    <span class="bg-gray-700 px-2 py-0.5 rounded">{{ $task->story_points }} sp</span>
                    @endif

                    @if($task->assignee)
                    <img src="{{ $task->assignee->avatar_url }}"
                         class="w-6 h-6 rounded-full" title="{{ $task->assignee->name }}">
                    @endif

                    <select class="sprint-select bg-gray-700 border-0 text-xs text-gray-300 rounded px-2 py-1"
                            data-task-id="{{ $task->id }}">
                        <option value="">Chọn sprint...</option>
                        @foreach($sprints as $sprint)
                        <option value="{{ $sprint->id }}">{{ $sprint->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @empty
            <div class="text-center py-16 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p>Backlog trống. Tạo task mới để bắt đầu!</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Sprint Panel --}}
    <div class="w-80">
        <h2 class="text-xl font-bold text-white mb-4">Sprints</h2>
        @forelse($sprints as $sprint)
        <div class="bg-gray-800 rounded-xl p-4 mb-3 border border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold text-white">{{ $sprint->name }}</h3>
                <span class="text-xs px-2 py-0.5 rounded-full
                    @if($sprint->status === 'active') bg-green-900/50 text-green-400
                    @else bg-gray-700 text-gray-400 @endif">
                    {{ ucfirst($sprint->status) }}
                </span>
            </div>
            @if($sprint->start_date)
            <p class="text-xs text-gray-400">
                {{ $sprint->start_date->format('d/m') }} → {{ $sprint->end_date?->format('d/m/Y') }}
            </p>
            @endif
            <p class="text-xs text-gray-400 mt-1">
                {{ $sprint->tasks->count() }} tasks · {{ $sprint->total_points }} sp
            </p>

            @if($sprint->status === 'planning')
            <form method="POST"
                  action="{{ route('sprints.start', [$workspace->slug, $project->slug, $sprint->id]) }}"
                  class="mt-3">
                @csrf
                <button class="w-full py-1.5 bg-green-700 hover:bg-green-600 text-white text-sm rounded-lg">
                    🚀 Bắt đầu Sprint
                </button>
            </form>
            @elseif($sprint->status === 'active')
            <form method="POST"
                  action="{{ route('sprints.complete', [$workspace->slug, $project->slug, $sprint->id]) }}"
                  class="mt-3">
                @csrf
                <button class="w-full py-1.5 bg-blue-700 hover:bg-blue-600 text-white text-sm rounded-lg">
                    ✅ Hoàn thành Sprint
                </button>
            </form>
            @endif
        </div>
        @empty
        <p class="text-gray-500 text-sm">Chưa có sprint nào.</p>
        @endforelse
    </div>
</div>
@endsection
