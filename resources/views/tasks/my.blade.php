@extends('layouts.app')
@section('title', 'Task của tôi')
@section('content')

<div x-data="{ view: 'kanban' }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-white text-xl font-bold">Task của tôi</h2>

        {{-- Toggle view --}}
        <div class="flex gap-1 bg-gray-800 border border-gray-700 rounded-lg p-1">
            <button @click="view = 'kanban'"
                :class="view === 'kanban' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white'"
                class="px-3 py-1.5 rounded-md text-xs font-medium transition">
                ⬛ Kanban
            </button>
            <button @click="view = 'list'"
                :class="view === 'list' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white'"
                class="px-3 py-1.5 rounded-md text-xs font-medium transition">
                ☰ Danh sách
            </button>
        </div>
    </div>

    {{-- ── KANBAN VIEW ── --}}
    <div x-show="view === 'kanban'" x-transition>
        <div class="grid grid-cols-4 gap-4">
            @foreach(['todo'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','done'=>'Done'] as $status => $label)
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
                <h3 class="text-gray-400 text-sm font-medium mb-3">
                    {{ $label }}
                    <span class="ml-1 bg-gray-800 text-gray-300 text-xs px-1.5 rounded-full">
                        {{ $tasks->where('status', $status)->count() }}
                    </span>
                </h3>
                @foreach($tasks->where('status', $status) as $task)
                <a href="{{ route('tasks.show', [$workspace->slug, $task->project_id, $task->id]) }}"
                   class="block bg-gray-800 hover:bg-gray-750 border border-gray-700 rounded-lg p-3 mb-2 transition">
                    {{-- Priority bar --}}
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-1.5 h-1.5 rounded-full
                            {{ $task->priority === 'high' ? 'bg-red-500' :
                               ($task->priority === 'medium' ? 'bg-yellow-500' : 'bg-gray-500') }}">
                        </span>
                        <p class="text-white text-sm">{{ $task->title }}</p>
                    </div>
                    <p class="text-gray-500 text-xs mt-1">{{ $task->project->name }}</p>
                    @if($task->due_date)
                    <p class="text-xs mt-2 {{ $task->due_date->isPast() && $task->status !== 'done' ? 'text-red-400' : 'text-gray-500' }}">
                        📅 {{ $task->due_date->format('d/m/Y') }}
                    </p>
                    @endif
                </a>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── LIST VIEW ── --}}
    <div x-show="view === 'list'" x-transition>
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-left text-gray-300">
                <thead class="bg-gray-800 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">Tên task</th>
                        <th class="px-4 py-3">Dự án</th>
                        <th class="px-4 py-3">Trạng thái</th>
                        <th class="px-4 py-3">Ưu tiên</th>
                        <th class="px-4 py-3">Deadline</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($tasks as $task)
                    <tr class="hover:bg-gray-800 transition">
                        <td class="px-4 py-3">
                            <a href="{{ route('tasks.show', [$workspace->slug, $task->project_id, $task->id]) }}"
                               class="text-white hover:text-indigo-400 font-medium">
                                {{ $task->title }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $task->project->name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $task->status === 'done'        ? 'bg-green-900 text-green-300'  :
                                   ($task->status === 'in_progress' ? 'bg-blue-900 text-blue-300'    :
                                   ($task->status === 'in_review'   ? 'bg-purple-900 text-purple-300' : 'bg-gray-700 text-gray-400')) }}">
                                {{ str_replace('_', ' ', ucfirst($task->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="flex items-center gap-1 text-xs
                                {{ $task->priority === 'high'   ? 'text-red-400'    :
                                   ($task->priority === 'medium' ? 'text-yellow-400' : 'text-gray-400') }}">
                                <span class="w-1.5 h-1.5 rounded-full
                                    {{ $task->priority === 'high'   ? 'bg-red-500'    :
                                       ($task->priority === 'medium' ? 'bg-yellow-500' : 'bg-gray-500') }}">
                                </span>
                                {{ ucfirst($task->priority ?? 'low') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs
                            {{ $task->due_date?->isPast() && $task->status !== 'done' ? 'text-red-400' : 'text-gray-400' }}">
                            {{ $task->due_date ? $task->due_date->format('d/m/Y') : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                            Không có task nào 🎉
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
