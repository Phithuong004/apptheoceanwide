@extends('layouts.app')

@section('title', $subtask->title)

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('projects.show', [$workspace->slug, $project->slug]) }}" class="hover:text-white">
            {{ $project->name }}
        </a>
        <span>/</span>
        <a href="{{ route('tasks.show', [$workspace->slug, $project->id, $task->id]) }}" class="hover:text-white">
            {{ Str::limit($task->title, 40) }}
        </a>
        <span>/</span>
        <span class="text-gray-300">{{ Str::limit($subtask->title, 40) }}</span>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <h1 class="text-xl font-bold text-white mb-6">Chi tiết Subtask</h1>

        <form method="POST" action="{{ route('projects.tasks.subtasks.update', [
            'workspace' => $workspace->slug,
            'project'   => $project->slug,
            'task'      => $task->id,
            'subtask'   => $subtask->id,
        ]) }}">
            @csrf
            @method('PATCH')

            <div class="space-y-4">
                {{-- Tiêu đề --}}
                <div>
                    <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Tiêu đề</label>
                    <input type="text" name="title" value="{{ old('title', $subtask->title) }}" required
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>

                {{-- Người được assign --}}
                <div>
                    <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Người phụ trách</label>
                    <select name="assignee_id"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                        <option value="">Không assign</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" {{ $subtask->assignee_id == $member->id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Deadline --}}
                <div>
                    <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Deadline</label>
                    <input type="date" name="deadline"
                        value="{{ old('deadline', $subtask->deadline ? \Carbon\Carbon::parse($subtask->deadline)->format('Y-m-d') : '') }}"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Trạng thái</label>
                    <select name="status"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                        @foreach (['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done'] as $val => $lbl)
                            <option value="{{ $val }}" {{ $subtask->status == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Ghi chú --}}
                <div>
                    <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Ghi chú</label>
                    <textarea name="comments" rows="4"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 resize-none">{{ old('comments', $subtask->comments) }}</textarea>
                </div>
            </div>

            <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-800">
                <a href="{{ route('tasks.show', [$workspace->slug, $project->id, $task->id]) }}"
                    class="text-sm text-gray-400 hover:text-white transition">
                    ← Quay lại task
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg font-medium transition">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
