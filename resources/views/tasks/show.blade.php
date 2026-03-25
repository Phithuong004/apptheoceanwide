@extends('layouts.app')
@section('title', $task->title)

@push('styles')
    <style>
        .activity-line::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 24px;
            bottom: -12px;
            width: 1px;
            background: #374151;
        }

        .activity-item:last-child .activity-line::before {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="max-w-6xl mx-auto">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
            <a href="{{ route('projects.index', $workspace->slug) }}" class="hover:text-white transition">Dự án</a>
            <span>/</span>
            <a href="{{ route('projects.show', [$workspace->slug, $task->project->slug]) }}"
                class="hover:text-white transition">{{ $task->project->name }}</a>
            <span>/</span>
            <span class="text-gray-300">{{ Str::limit($task->title, 50) }}</span>
        </div>

        <div class="grid grid-cols-3 gap-6">

            {{-- ===== LEFT (col-span-2) ===== --}}
            <div class="col-span-2 space-y-4">
                {{-- Title card --}}
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-3">
                                {{-- Type badge --}}
                                <span
                                    class="text-xs px-2 py-0.5 rounded border
                                {{ $task->type === 'bug'
                                    ? 'border-red-500/40 text-red-400 bg-red-500/10'
                                    : ($task->type === 'epic'
                                        ? 'border-purple-500/40 text-purple-400 bg-purple-500/10'
                                        : ($task->type === 'feature'
                                            ? 'border-green-500/40 text-green-400 bg-green-500/10'
                                            : 'border-blue-500/40 text-blue-400 bg-blue-500/10')) }}">
                                    {{ $task->type === 'bug' ? '🐛' : ($task->type === 'epic' ? '⚡' : ($task->type === 'feature' ? '✨' : '📋')) }}
                                    {{ ucfirst($task->type ?? 'task') }}
                                </span>

                                {{-- Priority badge --}}
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full
                                {{ $task->priority === 'critical'
                                    ? 'bg-red-500/20 text-red-400'
                                    : ($task->priority === 'urgent'
                                        ? 'bg-orange-500/20 text-orange-400'
                                        : ($task->priority === 'high'
                                            ? 'bg-yellow-500/20 text-yellow-400'
                                            : ($task->priority === 'medium'
                                                ? 'bg-blue-500/20 text-blue-400'
                                                : 'bg-gray-700 text-gray-400'))) }}">
                                    {{ ucfirst($task->priority ?? 'low') }}
                                </span>

                                {{-- Tags --}}
                                @if ($task->tags)
                                    @foreach ((array) $task->tags as $tag)
                                        <span
                                            class="text-xs bg-gray-800 text-gray-400 px-2 py-0.5 rounded-full">#{{ $tag }}</span>
                                    @endforeach
                                @endif

                                {{-- Edit button --}}
                                <button onclick="openEditModal()"
                                    class="ml-auto text-xs text-gray-400 hover:text-white border border-gray-700 hover:border-gray-500 px-2.5 py-0.5 rounded-full transition flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                    Chỉnh sửa
                                </button>
                            </div>

                            <h1 class="text-2xl font-bold text-white leading-snug">{{ $task->title }}</h1>
                        </div>

                        {{-- Status selector --}}
                        <form method="POST"
                            action="{{ route('tasks.status', ['workspace' => $workspace->slug, 'project' => $task->project_id, 'task' => $task->id]) }}">
                            @csrf @method('PATCH')
                            <select name="status" onchange="this.form.submit()"
                                class="bg-gray-800 text-white text-sm rounded-lg px-3 py-1.5 border border-gray-700 focus:outline-none cursor-pointer">
                                @foreach (['backlog' => 'Backlog', 'todo' => 'To Do', 'in_progress' => 'In Progress', 'in_review' => 'In Review', 'done' => 'Done', 'blocked' => 'Blocked'] as $val => $lbl)
                                    <option value="{{ $val }}" @selected($task->status === $val)>{{ $lbl }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    {{-- Description --}}
                    <div class="prose prose-invert prose-sm max-w-none">
                        @if ($task->description)
                            <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-wrap">{{ $task->description }}
                            </p>
                        @else
                            <p class="text-gray-600 text-sm italic">Chưa có mô tả.</p>
                        @endif
                    </div>

                    {{-- Labels --}}
                    @if ($task->labels->count())
                        <div class="flex flex-wrap gap-1.5 mt-4 pt-4 border-t border-gray-800">
                            @foreach ($task->labels as $label)
                                <span class="text-xs px-2.5 py-1 rounded-full font-medium"
                                    style="background:{{ $label->color }}25;color:{{ $label->color }};border:1px solid {{ $label->color }}40">
                                    {{ $label->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Attachments --}}
                @if ($task->attachments->count())
                    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                        <h3 class="text-white font-semibold mb-3 flex items-center gap-2">
                            📎 File đính kèm
                            <span class="text-gray-500 text-sm font-normal">({{ $task->attachments->count() }})</span>
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($task->attachments as $attachment)
                                @php
                                    $isImage = str_starts_with($attachment->mime_type, 'image/');
                                    $ext = pathinfo(
                                        $attachment->original_name ?? $attachment->filename,
                                        PATHINFO_EXTENSION,
                                    );
                                    $iconMap = [
                                        'pdf' => '🔴',
                                        'doc' => '🔵',
                                        'docx' => '🔵',
                                        'xls' => '🟢',
                                        'xlsx' => '🟢',
                                        'zip' => '🟡',
                                    ];
                                    $icon = $iconMap[$ext] ?? '📄';
                                @endphp
                                <div
                                    class="flex items-center gap-3 bg-gray-800 rounded-lg p-3 group hover:bg-gray-750 transition">
                                    @if ($isImage)
                                        <img src="{{ asset('storage/' . $attachment->path) }}"
                                            class="w-10 h-10 rounded object-cover shrink-0">
                                    @else
                                        <div
                                            class="w-10 h-10 bg-gray-700 rounded flex items-center justify-center text-xl shrink-0">
                                            {{ $icon }}
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-white text-xs font-medium truncate">
                                            {{ $attachment->original_name ?? $attachment->filename }}</p>
                                        <p class="text-gray-500 text-xs">{{ number_format($attachment->size / 1024, 1) }}
                                            KB</p>
                                    </div>
                                    <a href="{{ asset('storage/' . $attachment->path) }}"
                                        download="{{ $attachment->original_name ?? $attachment->filename }}"
                                        class="text-gray-500 hover:text-white transition opacity-0 group-hover:opacity-100"
                                        title="Tải về">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Subtasks --}}
                @if (($task->taskSubtasks ?? collect())->count() > 0 || true)
                    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                        <h3 class="text-white font-semibold mb-3 flex items-center gap-2">
                            ✅ Subtasks
                            <span class="text-gray-500 text-sm font-normal">
                                {{ ($task->taskSubtasks ?? collect())->where('status', 'done')->count() }}/{{ ($task->taskSubtasks ?? collect())->count() }}
                            </span>
                        </h3>

                        {{-- FORM THÊM SUBTASK MỚI - LUÔN HIỂN THỊ --}}
                        <div class="mb-4 p-3 bg-gray-800 rounded-lg">
                            <form id="subtask-form" method="POST"
                                action="{{ route('projects.tasks.subtasks.store', ['workspace' => $workspace, 'project' => $task->project->slug, 'task' => $task->id]) }}"
                                class="flex gap-2">
                                @csrf
                                <input type="text" name="title" placeholder="Thêm subtask mới..."
                                    class="flex-1 bg-gray-700 text-white border border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 transition required">
                                <select name="assignee_id"
                                    class="bg-gray-700 text-white border border-gray-600 rounded-lg px-3 py-2 text-sm w-48 focus:outline-none focus:border-blue-500">
                                    <option value="">Không assign</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm transition font-medium whitespace-nowrap">
                                    Thêm
                                </button>
                            </form>
                        </div>

                        {{-- Progress bar - CHỈ HIỂN THỊ KHI CÓ SUBTASK --}}
                        @if (($task->taskSubtasks ?? collect())->count() > 0)
                            <div class="bg-gray-800 rounded-full h-1.5 mb-4">
                                <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-500"
                                    style="width: {{ ($task->taskSubtasks->where('status', 'done')->count() / $task->taskSubtasks->count()) * 100 }}%">
                                </div>
                            </div>
                        @endif

                        {{-- DANH SÁCH SUBTASKS - FINAL FIX --}}
                        <div class="space-y-2">
                            @forelse($task->taskSubtasks ?? [] as $sub)
                                <div class="flex items-center gap-3 py-2.5 border-b border-gray-800 last:border-b-0 hover:bg-gray-800/50 p-2 rounded-lg group transition-all"
                                    data-subtask-id="{{ $sub->id }}">
                                    {{-- Checkbox --}}
                                    <label
                                        class="flex items-center gap-2 cursor-pointer flex-1 group-hover:translate-x-1 transition-all">
                                        <input type="checkbox" {{ $sub->status == 'done' ? 'checked' : '' }}
                                            data-subtask-id="{{ $sub->id }}"
                                            data-url="{{ route('projects.tasks.subtasks.update', [
                                                'workspace' => $workspace,
                                                'project' => $project->slug,
                                                'task' => $task->id,
                                                'subtask' => $sub->id,
                                            ]) }}"
                                            onchange="toggleSubtaskDone(this)"
                                            class="subtask-checkbox w-4 h-4 rounded accent-blue-500 cursor-pointer shrink-0">

                                        <a href="{{ route('projects.tasks.subtasks.show', [
                                            'workspace' => $workspace,
                                            'project' => $project->slug,
                                            'task' => $task->id,
                                            'subtask' => $sub->id,
                                        ]) }}"
                                            class="subtask-title font-medium transition-colors line-clamp-1 flex-1
                                                {{ $sub->status == 'done' ? 'line-through text-gray-500' : 'text-gray-100 hover:text-blue-400' }}">
                                            {{ $sub->title }}
                                        </a>
                                    </label>

                                    {{-- Deadline Badge --}}
                                    @if ($sub->deadline)
                                        @php
                                            $deadlineDate = \Carbon\Carbon::parse($sub->deadline);
                                            $isOverdue = $deadlineDate->isPast();
                                            $formattedDate = $deadlineDate->format('d/m');
                                        @endphp
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full 
                               {{ $isOverdue ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-green-500/20 text-green-400 border border-green-500/30' }} 
                               flex items-center gap-1 whitespace-nowrap">
                                            {{ $formattedDate }}
                                            @if ($isOverdue)
                                                ⚠️
                                            @endif
                                        </span>
                                    @endif

                                    {{-- Assignee Avatar --}}
                                    @if ($sub->assignee_id)
                                        @php $assignee = $members->firstWhere('id', $sub->assignee_id); @endphp
                                        @if ($assignee)
                                            <div class="relative group/avatar">
                                                <img src="{{ $assignee->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($assignee->name) . '&background=3b82f6&color=fff&size=28' }}"
                                                    class="w-6 h-6 rounded-full border-2 border-gray-700 shrink-0"
                                                    title="{{ $assignee->name }}">
                                                {{-- Tooltip --}}
                                                <div
                                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover/avatar:block bg-gray-700 text-white text-xs px-2 py-1 rounded whitespace-nowrap z-10">
                                                    {{ $assignee->name }}
                                                </div>
                                            </div>
                                        @endif
                                    @endif

                                    {{-- Actions --}}
                                    <div
                                        class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all ml-auto">
                                        {{-- Đổi button edit → link đến trang chi tiết --}}
                                        <a href="{{ route('projects.tasks.subtasks.show', [
                                            'workspace' => $workspace,
                                            'project' => $project->slug,
                                            'task' => $task->id,
                                            'subtask' => $sub->id,
                                        ]) }}"
                                            class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-all"
                                            title="Chỉnh sửa subtask">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>

                                        {{-- Delete --}}
                                        <button type="button" data-subtask-id="{{ $sub->id }}"
                                            data-url="{{ route('projects.tasks.subtasks.destroy', [
                                                'workspace' => $workspace,
                                                'project' => $project->slug,
                                                'task' => $task->id,
                                                'subtask' => $sub->id,
                                            ]) }}"
                                            onclick="deleteSubtask(this)"
                                            class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-all"
                                            title="Xóa subtask">

                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500 text-sm bg-gray-800 rounded-lg">
                                    Chưa có subtask nào. Thêm ngay bên trên!
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
                {{-- Comments + Activity --}}
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
                    {{-- Tab header --}}
                    <div class="flex items-center gap-4 mb-5 border-b border-gray-800 pb-3">
                        <button onclick="switchTab('comments')" id="tab-comments"
                            class="text-sm font-medium text-white border-b-2 border-blue-500 pb-3 -mb-3 transition">
                            💬 Bình luận ({{ $task->comments->count() }})
                        </button>
                        <button onclick="switchTab('activity')" id="tab-activity"
                            class="text-sm font-medium text-gray-500 hover:text-white border-b-2 border-transparent pb-3 -mb-3 transition">
                            🕐 Lịch sử
                        </button>
                    </div>

                    {{-- Comments tab --}}
                    <div id="panel-comments">
                        <div id="comments-list" class="space-y-4 mb-5">
                            @forelse($task->comments->whereNull('parent_id') as $comment)
                                <div class="flex gap-3 group" id="comment-{{ $comment->id }}">
                                    <img src="{{ $comment->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name ?? 'U') . '&background=3b82f6&color=fff&size=32' }}"
                                        class="w-8 h-8 rounded-full shrink-0 mt-0.5">
                                    <div class="flex-1">
                                        <div class="bg-gray-800 rounded-xl px-4 py-3">
                                            <div class="flex justify-between items-start mb-1.5">
                                                <div>
                                                    <span
                                                        class="text-white text-sm font-medium">{{ $comment->user->name }}</span>
                                                    <span
                                                        class="text-gray-500 text-xs ml-2">{{ $comment->created_at?->diffForHumans() }}</span>
                                                    @if ($comment->is_edited)
                                                        <span class="text-gray-600 text-xs ml-1">(đã sửa)</span>
                                                    @endif
                                                </div>
                                                @if (auth()->id() === $comment->user_id)
                                                    <div
                                                        class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition">
                                                        <button
                                                            onclick="editComment({{ $comment->id }}, `{{ addslashes($comment->content) }}`)"
                                                            class="text-xs text-gray-400 hover:text-blue-400 px-2 py-0.5 rounded hover:bg-blue-500/10 transition">
                                                            Sửa
                                                        </button>
                                                        <button onclick="deleteComment({{ $comment->id }})"
                                                            class="text-xs text-gray-400 hover:text-red-400 px-2 py-0.5 rounded hover:bg-red-500/10 transition">
                                                            Xóa
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>

                                            <p
                                                class="text-gray-300 text-sm leading-relaxed comment-content-{{ $comment->id }}">
                                                {{ $comment->content }}</p>

                                            {{-- Edit form --}}
                                            <div id="edit-form-{{ $comment->id }}" class="hidden mt-2">
                                                <textarea id="edit-content-{{ $comment->id }}" rows="2"
                                                    class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 text-sm border border-gray-600 focus:border-blue-500 focus:outline-none resize-none">{{ $comment->content }}</textarea>
                                                <div class="flex gap-2 mt-1.5 justify-end">
                                                    <button onclick="cancelEdit({{ $comment->id }})"
                                                        class="text-xs text-gray-400 hover:text-white px-2 py-1 rounded transition">
                                                        Hủy
                                                    </button>
                                                    <button onclick="submitEdit({{ $comment->id }})"
                                                        class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded transition">
                                                        Lưu
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Attachments --}}
                                            @if ($comment->attachments && $comment->attachments->count())
                                                <div class="flex flex-wrap gap-2 mt-2 pt-2 border-t border-gray-700">
                                                    @foreach ($comment->attachments as $att)
                                                        @php $isImg = str_starts_with($att->mime_type ?? '', 'image/'); @endphp
                                                        @if ($isImg)
                                                            <a href="{{ asset('storage/' . $att->path) }}"
                                                                target="_blank"
                                                                class="block rounded-lg overflow-hidden border border-gray-700 hover:border-blue-500 transition"
                                                                style="max-width:180px">
                                                                <img src="{{ asset('storage/' . $att->path) }}"
                                                                    class="w-full h-auto object-cover max-h-32">
                                                                <div class="px-2 py-1 bg-gray-900 text-xs text-gray-400">
                                                                    {{ Str::limit($att->original_name, 22) }} ·
                                                                    {{ number_format($att->size / 1024, 1) }} KB
                                                                </div>
                                                            </a>
                                                        @else
                                                            <a href="{{ asset('storage/' . $att->path) }}"
                                                                download="{{ $att->original_name }}"
                                                                class="flex items-center gap-2 text-xs text-blue-400 hover:text-blue-300 bg-gray-700 hover:bg-gray-600 rounded-lg px-3 py-1.5 transition border border-gray-600">
                                                                {{ Str::limit($att->original_name, 22) }}
                                                                <span
                                                                    class="text-gray-500">{{ number_format($att->size / 1024, 1) }}
                                                                    KB</span>
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-600 text-sm text-center py-6" id="no-comments-msg">Chưa có bình luận
                                    nào.</p>
                            @endforelse

                        </div>

                        {{-- Comment form --}}
                        <div class="flex gap-3 mt-4 border-t border-gray-800 pt-4">
                            <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=3b82f6&color=fff&size=32' }}"
                                class="w-8 h-8 rounded-full shrink-0 mt-1">
                            <div class="flex-1">
                                <textarea id="commentInput" rows="2" placeholder="Thêm bình luận..."
                                    class="w-full bg-gray-800 text-white rounded-xl px-4 py-2.5 border border-gray-700 focus:border-blue-500 focus:outline-none text-sm resize-none transition"></textarea>

                                {{-- Toolbar --}}
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center gap-1">
                                        {{-- Đính kèm file --}}
                                        <label
                                            class="text-xs text-gray-400 hover:text-blue-400 px-2 py-1.5 rounded-lg hover:bg-blue-500/10 cursor-pointer transition"
                                            title="Đính kèm file">
                                            Đính kèm file
                                            <input type="file" id="commentFiles" multiple
                                                accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip" class="hidden"
                                                onchange="previewCommentFiles(this)">
                                        </label>

                                    </div>
                                    <button onclick="submitComment()"
                                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg transition flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                        Gửi
                                    </button>
                                </div>

                                {{-- File preview --}}
                                <div id="commentFilePreview" class="flex flex-wrap gap-1.5 mt-2"></div>
                            </div>
                        </div>
                    </div>


                    {{-- Activity tab --}}
                    <div id="panel-activity" class="hidden">
                        @if ($task->updated_at != $task->created_at || $task->comments->count())
                            <div class="space-y-0">
                                {{-- Tạo task --}}
                                <div class="activity-item relative flex gap-3 pb-4">
                                    <div class="activity-line relative">
                                        <div
                                            class="w-8 h-8 rounded-full bg-green-500/20 border border-green-500/30 flex items-center justify-center text-xs shrink-0">
                                            ✨
                                        </div>
                                    </div>
                                    <div class="flex-1 pt-1">
                                        <p class="text-sm text-gray-300">
                                            <span
                                                class="text-white font-medium">{{ $task->reporter->name ?? 'Unknown' }}</span>
                                            đã tạo task này
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ $task->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>

                                {{-- Assignee --}}
                                @if ($task->assignee)
                                    <div class="activity-item relative flex gap-3 pb-4">
                                        <div class="activity-line relative">
                                            <img src="{{ $task->reporter->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($task->reporter->name ?? 'U') . '&background=3b82f6&color=fff&size=32' }}"
                                                class="w-8 h-8 rounded-full shrink-0">
                                        </div>
                                        <div class="flex-1 pt-1">
                                            <p class="text-sm text-gray-300">
                                                Giao cho
                                                <span class="text-white font-medium">{{ $task->assignee->name }}</span>
                                            </p>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ $task->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                @endif

                                {{-- Comments as activity --}}
                                @foreach ($task->comments as $comment)
                                    <div class="activity-item relative flex gap-3 pb-4">
                                        <div class="activity-line relative">
                                            <img src="{{ $comment->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name ?? 'U') . '&background=6366f1&color=fff&size=32' }}"
                                                class="w-8 h-8 rounded-full shrink-0">
                                        </div>
                                        <div class="flex-1 pt-1">
                                            <p class="text-sm text-gray-300">
                                                <span class="text-white font-medium">{{ $comment->user->name }}</span>
                                                đã bình luận
                                                @if ($comment->is_edited)
                                                    <span class="text-gray-600 text-xs">(đã chỉnh sửa)</span>
                                                @endif
                                            </p>
                                            <p
                                                class="text-xs text-gray-500 bg-gray-800 rounded-lg px-3 py-2 mt-1 inline-block">
                                                "{{ Str::limit($comment->content, 80) }}"
                                            </p>
                                            <p class="text-xs text-gray-600 mt-1">
                                                {{ $comment->created_at?->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Cập nhật gần nhất --}}
                                @if ($task->updated_at != $task->created_at)
                                    <div class="activity-item relative flex gap-3 pb-4">
                                        <div class="activity-line relative">
                                            <div
                                                class="w-8 h-8 rounded-full bg-yellow-500/20 border border-yellow-500/30 flex items-center justify-center text-xs shrink-0">
                                                ✏️
                                            </div>
                                        </div>
                                        <div class="flex-1 pt-1">
                                            <p class="text-sm text-gray-300">Task được cập nhật lần cuối</p>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ $task->updated_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-600 text-sm text-center py-6">Chưa có hoạt động nào.</p>
                        @endif
                    </div>
                </div>

            </div>

            {{-- ===== RIGHT ===== --}}
            <div class="space-y-4">
                {{-- Cộng tác viên --}}
                @if ($task->collaborators->count())
                    <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
                        <h3 class="text-gray-400 text-xs uppercase tracking-wider mb-3">👥 Cộng tác viên</h3>
                        @foreach ($task->collaborators as $collab)
                            <div class="flex items-center gap-2.5 mb-2 last:mb-0">
                                <img src="{{ $collab->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($collab->user->name) . '&background=6366f1&color=fff&size=28' }}"
                                    class="w-7 h-7 rounded-full shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="text-white text-sm truncate">{{ $collab->user->name }}</p>
                                </div>
                                <span
                                    class="text-xs px-1.5 py-0.5 rounded
            {{ $collab->role === 'reviewer'
                ? 'bg-yellow-500/20 text-yellow-400'
                : ($collab->role === 'observer'
                    ? 'bg-gray-700 text-gray-400'
                    : 'bg-blue-500/20 text-blue-400') }}">
                                    {{ $collab->role === 'reviewer' ? 'Giám sát' : ($collab->role === 'observer' ? 'Theo dõi' : 'Hỗ trợ') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Người thực hiện --}}
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
                    <h3 class="text-gray-400 text-xs uppercase tracking-wider mb-3">Người thực hiện</h3>
                    @foreach ($members as $member)
                        <label
                            class="flex items-center gap-2.5 mb-1.5 cursor-pointer hover:bg-gray-800 rounded-lg px-2 py-1.5 transition">
                            <input type="radio" name="assignee_radio" value="{{ $member->id }}"
                                @checked($task->assignee_id == $member->id) class="assign-radio accent-blue-500">
                            <img src="{{ $member->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=3b82f6&color=fff&size=28' }}"
                                class="w-7 h-7 rounded-full">
                            <div>
                                <p class="text-white text-sm">{{ $member->name }}</p>
                                <p class="text-gray-500 text-xs">{{ $member->pivot->role ?? 'member' }}</p>
                            </div>
                            @if ($task->assignee_id == $member->id)
                                <span class="ml-auto text-xs text-blue-400">✓</span>
                            @endif
                        </label>
                    @endforeach
                    <label
                        class="flex items-center gap-2.5 mt-1 cursor-pointer hover:bg-gray-800 rounded-lg px-2 py-1.5 transition">
                        <input type="radio" name="assignee_radio" value="" @checked(!$task->assignee_id)
                            class="assign-radio accent-blue-500">
                        <div
                            class="w-7 h-7 rounded-full bg-gray-700 flex items-center justify-center text-gray-400 text-xs">
                            —</div>
                        <span class="text-gray-500 text-sm italic">Chưa giao</span>
                    </label>
                </div>

                {{-- Meta info --}}
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 space-y-3">
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-800">
                        <p class="text-gray-500 text-xs uppercase tracking-wider">Deadline</p>
                        <p class="text-sm font-medium {{ $task->isOverdue() ? 'text-red-400' : 'text-white' }}">
                            {{ $task->due_date?->format('d/m/Y') ?? '—' }}
                            @if ($task->isOverdue())
                                <span class="text-xs">(Quá hạn)</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-800">
                        <p class="text-gray-500 text-xs uppercase tracking-wider">Bắt đầu</p>
                        <p class="text-white text-sm">{{ $task->start_date?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-800">
                        <p class="text-gray-500 text-xs uppercase tracking-wider">Story Points</p>
                        <span class="text-xs bg-indigo-900/40 text-indigo-400 px-2 py-0.5 rounded font-medium">
                            {{ $task->story_points ?? '—' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-800">
                        <p class="text-gray-500 text-xs uppercase tracking-wider">Sprint</p>
                        <p class="text-white text-sm">{{ $task->sprint->name ?? '—' }}</p>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-800">
                        <p class="text-gray-500 text-xs uppercase tracking-wider">Reporter</p>
                        <div class="flex items-center gap-1.5">
                            <img src="{{ $task->reporter->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($task->reporter->name ?? 'U') . '&background=3b82f6&color=fff&size=20' }}"
                                class="w-5 h-5 rounded-full">
                            <p class="text-white text-sm">{{ $task->reporter->name ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-800">
                        <p class="text-gray-500 text-xs uppercase tracking-wider">Estimated</p>
                        <p class="text-white text-sm">{{ $task->estimated_hours ? $task->estimated_hours . 'h' : '—' }}
                        </p>
                    </div>
                    <div class="flex justify-between items-center py-1.5">
                        <p class="text-gray-500 text-xs uppercase tracking-wider">Tạo lúc</p>
                        <p class="text-white text-sm">{{ $task->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                {{-- Watchers --}}
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
                    <h3 class="text-gray-400 text-xs uppercase tracking-wider mb-3">
                        👁 Đang theo dõi ({{ $task->watchers->count() }})
                    </h3>
                    @if ($task->watchers->count())
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($task->watchers as $watcher)
                                <div class="relative group">
                                    <img src="{{ $watcher->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($watcher->name) . '&background=3b82f6&color=fff&size=28' }}"
                                        class="w-8 h-8 rounded-full border-2 border-gray-800 hover:border-blue-500 transition cursor-pointer"
                                        title="{{ $watcher->name }}">
                                    <div
                                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:block bg-gray-700 text-white text-xs px-2 py-1 rounded whitespace-nowrap z-10">
                                        {{ $watcher->name }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600 text-xs">Chưa có ai theo dõi.</p>
                    @endif
                </div>

                {{-- Time log --}}
                @if ($task->timeLogs->count())
                    <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
                        <h3 class="text-gray-400 text-xs uppercase tracking-wider mb-3">⏱ Time Logs</h3>
                        @foreach ($task->timeLogs->take(5) as $log)
                            <div class="flex items-center justify-between py-1.5 border-b border-gray-800 last:border-0">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $log->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($log->user->name) . '&background=3b82f6&color=fff&size=20' }}"
                                        class="w-5 h-5 rounded-full">
                                    <span class="text-gray-300 text-xs">{{ $log->user->name }}</span>
                                </div>
                                <span class="text-indigo-400 text-xs font-medium">{{ $log->hours }}h</span>
                            </div>
                        @endforeach
                        <div class="flex justify-between mt-2 pt-2 border-t border-gray-800">
                            <span class="text-gray-500 text-xs">Tổng thực tế</span>
                            <span class="text-white text-xs font-semibold">{{ $task->actual_hours }}h</span>
                        </div>
                    </div>
                @endif

                {{-- Back link --}}
                <a href="{{ route('projects.show', [$workspace->slug, $task->project->slug]) }}"
                    class="block text-center text-blue-400 hover:text-blue-300 text-sm py-2 transition">
                    ← Về dự án {{ $task->project->name }}
                </a>
            </div>
        </div>
    </div>

    {{-- ===== MODAL CHỈNH SỬA ===== --}}
    <div id="editTaskModal" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
        <div
            class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800 sticky top-0 bg-gray-900">
                <h2 class="text-white font-semibold text-lg">✏️ Chỉnh sửa Task</h2>
                <button onclick="closeEditModal()"
                    class="text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>
            <form method="POST"
                action="{{ route('tasks.update', ['workspace' => $workspace->slug, 'project' => $task->project_id, 'task' => $task->id]) }}"
                class="px-6 py-5 space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Tên task</label>
                    <input type="text" name="title" value="{{ old('title', $task->title) }}" required
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Mô tả</label>
                    <textarea name="description" rows="4"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition resize-none">{{ old('description', $task->description) }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Priority</label>
                        <select name="priority"
                            class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                            @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent', 'critical' => 'Critical'] as $v => $l)
                                <option value="{{ $v }}"
                                    {{ old('priority', $task->priority) === $v ? 'selected' : '' }}>{{ $l }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Type</label>
                        <select name="type"
                            class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                            @foreach (['task' => 'Task', 'bug' => 'Bug', 'feature' => 'Feature', 'story' => 'Story', 'epic' => 'Epic'] as $v => $l)
                                <option value="{{ $v }}"
                                    {{ old('type', $task->type) === $v ? 'selected' : '' }}>
                                    {{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Deadline</label>
                        <input type="date" name="due_date"
                            value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"
                            class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Ngày bắt đầu</label>
                        <input type="date" name="start_date"
                            value="{{ old('start_date', $task->start_date?->format('Y-m-d')) }}"
                            class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                    </div>
                </div>
                <div>
                    <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Story Points</label>
                    <input type="number" name="story_points" min="0" max="100"
                        value="{{ old('story_points', $task->story_points) }}"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                </div>
                {{-- Người hỗ trợ / Cộng tác viên --}}
                <div>
                    <label class="block text-gray-400 text-xs uppercase tracking-wider mb-2">
                        👥 Người cộng tác
                        <span class="text-gray-600 font-normal normal-case ml-1">(hỗ trợ, giám sát)</span>
                    </label>

                    <div class="space-y-2 mb-3">
                        @foreach ($task->collaborators as $collab)
                            <div class="flex items-center gap-2 bg-gray-800 rounded-lg px-3 py-2"
                                id="collab-row-{{ $collab->user_id }}">
                                <img src="{{ $collab->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($collab->user->name) . '&background=6366f1&color=fff&size=24' }}"
                                    class="w-6 h-6 rounded-full shrink-0">
                                <span class="text-white text-sm flex-1">{{ $collab->user->name }}</span>
                                <select name="collaborator_roles[{{ $collab->user_id }}]"
                                    class="bg-gray-700 border border-gray-600 rounded px-2 py-1 text-xs text-white focus:outline-none">
                                    @foreach (['supporter' => 'Hỗ trợ', 'reviewer' => 'Giám sát', 'observer' => 'Theo dõi'] as $rv => $rl)
                                        <option value="{{ $rv }}"
                                            {{ $collab->role === $rv ? 'selected' : '' }}>{{ $rl }}</option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="removeCollab({{ $collab->user_id }})"
                                    class="text-red-400 hover:text-red-300 text-xs px-1.5 py-1 rounded hover:bg-red-500/10 transition">
                                    ✕
                                </button>
                                <input type="hidden" name="remove_collaborators_check[{{ $collab->user_id }}]"
                                    value="0" id="remove-flag-{{ $collab->user_id }}">
                            </div>
                        @endforeach
                    </div>

                    {{-- Thêm mới --}}
                    <div class="flex gap-2">
                        <select id="newCollabUser"
                            class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500">
                            <option value="">-- Chọn thành viên --</option>
                            @foreach ($members as $member)
                                @if ($member->id !== $task->assignee_id)
                                    <option value="{{ $member->id }}" data-name="{{ $member->name }}">
                                        {{ $member->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <select id="newCollabRole"
                            class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500">
                            <option value="supporter">Hỗ trợ</option>
                            <option value="reviewer">Giám sát</option>
                            <option value="observer">Theo dõi</option>
                        </select>
                        <button type="button" onclick="addCollab()"
                            class="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg transition whitespace-nowrap">
                            + Thêm
                        </button>
                    </div>
                    <div id="newCollabContainer"></div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-800">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 text-sm text-gray-400 hover:text-white border border-gray-700 rounded-lg transition">Hủy</button>
                    <button type="submit"
                        class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">💾
                        Lưu</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT SUBTASK SIDEBAR --}}
    <div id="editSubtaskSidebar" class="hidden fixed inset-0 bg-black/70 z-50 flex justify-end p-4">
        <div id="subtaskSidebar"
            class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-2xl transform translate-x-full transition-transform duration-300">
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-800 sticky top-0 bg-gray-900 z-10">
                <h2 class="text-white font-semibold text-lg">Chỉnh sửa Subtask</h2>
                <button onclick="closeSubtaskSidebar()"
                    class="text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>
            <form method="POST" id="editSubtaskForm" class="p-6 space-y-4">
                @csrf @method('PATCH')
                <input type="hidden" name="subtask_id" id="edit_subtask_id">

                <div>
                    <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Tiêu đề</label>
                    <input type="text" name="title" id="edit_subtask_title" required
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Người phụ trách</label>
                    <select name="assignee_id" id="edit_subtask_assignee"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm">
                        <option value="">Không assign</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Deadline</label>
                    <input type="date" name="deadline" id="edit_subtask_deadline"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Ghi chú</label>
                    <textarea name="comments" id="edit_subtask_comments" rows="3"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm resize-none"></textarea>
                </div>

                {{-- File upload --}}
                <div>
                    <label class="block text-gray-400 text-xs uppercase tracking-wider mb-1">File đính kèm</label>
                    <input type="file" name="attachments[]" multiple
                        class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 py-2 text-sm">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-800">
                    <button type="button" onclick="closeSubtaskSidebar()"
                        class="px-4 py-2 text-sm text-gray-400 hover:text-white border border-gray-700 rounded-lg">Hủy</button>
                    <button type="submit"
                        class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast-area" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

@endsection

@push('scripts')
    <script>
        async function toggleSubtaskDone(checkbox) {
            const subtaskId = checkbox.dataset.subtaskId;
            const url = checkbox.dataset.url;
            const isDone = checkbox.checked;
            const row = document.querySelector(`div[data-subtask-id="${subtaskId}"]`);
            const titleEl = row?.querySelector('.subtask-title');

            // Hiệu ứng ngay lập tức
            if (isDone) {
                titleEl?.classList.add('line-through', 'text-gray-500');
                titleEl?.classList.remove('text-gray-100', 'hover:text-blue-400');
                row?.classList.add('opacity-60');
            } else {
                titleEl?.classList.remove('line-through', 'text-gray-500');
                titleEl?.classList.add('text-gray-100', 'hover:text-blue-400');
                row?.classList.remove('opacity-60');
            }

            // Gửi lên server
            try {
                const res = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        status: isDone ? 'done' : 'todo'
                    })
                });

                if (!res.ok) {
                    // Rollback nếu thất bại
                    checkbox.checked = !isDone;
                    toggleSubtaskDone(checkbox);
                    showToast('Cập nhật thất bại!', 'error');
                } else {
                    showToast(isDone ? '✅ Đã hoàn thành!' : 'Đã bỏ hoàn thành');
                }
            } catch (e) {
                checkbox.checked = !isDone;
                toggleSubtaskDone(checkbox);
                showToast('Lỗi kết nối!', 'error');
            }
        }

        document.getElementById('subtask-form')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Đang thêm...';

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: new FormData(form)
                });

                const data = await res.json();

                if (res.ok && data.subtask) {
                    appendSubtask(data.subtask);
                    form.reset();
                    showToast('Đã thêm subtask!');
                } else {
                    showToast(data.message ?? 'Thêm thất bại!', 'error');
                }
            } catch (err) {
                showToast('Lỗi kết nối!', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Thêm';
            }
        });

        function appendSubtask(sub) {
            const list = document.querySelector('.space-y-2');
            list.querySelector('.text-center.py-8')?.remove();

            const div = document.createElement('div');
            div.className =
                'flex items-center gap-3 py-2.5 border-b border-gray-800 last:border-b-0 hover:bg-gray-800/50 p-2 rounded-lg group transition-all';
            div.setAttribute('data-subtask-id', sub.id);
            div.innerHTML = `
        <label class="flex items-center gap-2 cursor-pointer flex-1">
            <input type="checkbox" class="w-5 h-5 rounded text-blue-500 focus:ring-blue-500">
            <a href="${sub.show_url}" class="font-medium text-gray-100 hover:text-blue-400 transition-colors line-clamp-1 flex-1">
                ${sub.title}
            </a>
        </label>
        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all ml-auto">
            <a href="${sub.show_url}"
                class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-all"
                title="Chỉnh sửa">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </a>
            <button type="button"
                data-subtask-id="${sub.id}"
                data-url="${sub.delete_url}"
                onclick="deleteSubtask(this)"
                class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-all"
                title="Xóa subtask">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </div>
    `;
            list.appendChild(div);
        }

        async function deleteSubtask(btn) {
            const subtaskId = btn.dataset.subtaskId;
            const url = btn.dataset.url;

            if (!confirm('Xóa subtask này?')) return;

            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });

                if (res.ok) {
                    // Tìm div row cha có data-subtask-id, bỏ qua chính button
                    const row = document.querySelector(`div[data-subtask-id="${subtaskId}"]`);
                    if (row) row.remove();
                    showToast('Đã xóa subtask!');
                } else {
                    showToast('Xóa thất bại!', 'error');
                }
            } catch (e) {
                showToast('Lỗi kết nối!', 'error');
            }
        }

        // Tab switch
        function switchTab(tab) {
            ['comments', 'activity'].forEach(t => {
                document.getElementById('panel-' + t).classList.toggle('hidden', t !== tab);
                const btn = document.getElementById('tab-' + t);
                btn.classList.toggle('text-white', t === tab);
                btn.classList.toggle('border-blue-500', t === tab);
                btn.classList.toggle('text-gray-500', t !== tab);
                btn.classList.toggle('border-transparent', t !== tab);
            });
        }

        // Assign radio
        document.querySelectorAll('.assign-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                fetch('{{ route('tasks.assign', ['workspace' => $workspace->slug, 'project' => $task->project_id, 'task' => $task->id]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        assignee_id: this.value,
                        _method: 'PATCH'
                    })
                }).then(r => {
                    if (r.ok) showToast('✅ Đã giao task!');
                    else showToast('❌ Thất bại!', 'error');
                });
            });
        });

        // Modal
        function openEditModal() {
            document.getElementById('editTaskModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            document.getElementById('editTaskModal').classList.add('hidden');
            document.body.style.overflow = '';
        }
        document.getElementById('editTaskModal').addEventListener('click', e => {
            if (e.target === document.getElementById('editTaskModal')) closeEditModal();
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeEditModal();
        });

        // Toast
        function showToast(msg, type = 'success') {
            const t = document.createElement('div');
            t.className =
                `px-4 py-2.5 rounded-lg text-sm text-white shadow-lg ${type==='error'?'bg-red-600':'bg-gray-800 border border-gray-700'}`;
            t.innerHTML = msg;
            document.getElementById('toast-area').appendChild(t);
            setTimeout(() => {
                t.style.opacity = '0';
                setTimeout(() => t.remove(), 300);
            }, 2500);
        }

        let collabCount = 0;

        function addCollab() {
            const sel = document.getElementById('newCollabUser');
            const role = document.getElementById('newCollabRole').value;
            const uid = sel.value;
            const name = sel.options[sel.selectedIndex]?.dataset.name;

            if (!uid) {
                alert('Vui lòng chọn thành viên!');
                return;
            }

            collabCount++;
            const container = document.getElementById('newCollabContainer');
            const div = document.createElement('div');
            div.className = 'flex items-center gap-2 bg-gray-800 rounded-lg px-3 py-2 mt-2';
            div.id = `new-collab-${collabCount}`;
            div.innerHTML = `
        <span class="text-white text-sm flex-1">${name}</span>
        <span class="text-xs text-indigo-400">${role}</span>
        <button type="button" onclick="this.closest('div').remove()"
                class="text-red-400 hover:text-red-300 text-xs px-1.5">✕</button>
        <input type="hidden" name="new_collaborators[${collabCount}][user_id]" value="${uid}">
        <input type="hidden" name="new_collaborators[${collabCount}][role]"    value="${role}">
    `;
            container.appendChild(div);
            sel.value = '';
        }

        function removeCollab(userId) {
            document.getElementById(`collab-row-${userId}`).style.opacity = '0.3';
            const flag = document.getElementById(`remove-flag-${userId}`);
            if (flag) flag.value = '1';
            // append hidden input to form
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'remove_collaborators[]';
            input.value = userId;
            document.querySelector('#editTaskModal form').appendChild(input);
        }

        const COMMENT_STORE_URL =
            '{{ route('comments.store', ['workspace' => $workspace->slug, 'project' => $task->project_id, 'task' => $task->id]) }}';
        const COMMENT_BASE_URL =
            '{{ url($workspace->slug . '/projects/' . $task->project_id . '/tasks/' . $task->id . '/comments') }}';
        const CSRF = '{{ csrf_token() }}';
        const AUTH_ID = {{ auth()->id() }};
        const AUTH_NAME = '{{ auth()->user()->name }}';
        const AUTH_AVATAR =
            '{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=3b82f6&color=fff&size=32' }}';

        // ===== SUBMIT COMMENT =====
        async function submitComment() {
            const content = document.getElementById('commentInput').value.trim();
            if (!content) return;

            const formData = new FormData();
            formData.append('content', content);
            formData.append('_token', CSRF);

            // Dùng selectedFiles thay vì input.files
            selectedFiles.forEach(file => formData.append('attachments[]', file));

            const btn = document.querySelector('[onclick="submitComment()"]');
            btn.disabled = true;
            btn.textContent = 'Đang gửi...';

            try {
                const res = await fetch(COMMENT_STORE_URL, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();

                if (data.comment) {
                    appendComment(data.comment);
                    document.getElementById('commentInput').value = '';
                    document.getElementById('commentFiles').value = '';
                    document.getElementById('commentFilePreview').innerHTML = '';
                    selectedFiles = []; // Reset danh sách
                    document.getElementById('no-comments-msg')?.remove();
                    showToast('Đã gửi bình luận');
                }
            } catch (e) {
                showToast('Lỗi khi gửi', 'error');
            }

            btn.disabled = false;
            btn.textContent = 'Gửi';
        }

        // ===== APPEND COMMENT DOM =====
        function appendComment(c) {
            const list = document.getElementById('comments-list');
            const div = document.createElement('div');
            div.className = 'flex gap-3 group';
            div.id = `comment-${c.id}`;

            // Build attachments HTML
            let attHtml = '';
            if (c.attachments && c.attachments.length > 0) {
                const items = c.attachments.map(att => {
                    const isImg = att.mime_type && att.mime_type.startsWith('image/');
                    const sizeKb = (att.size / 1024).toFixed(1);
                    const url = `/storage/${att.path}`;
                    if (isImg) {
                        return `<a href="${url}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-700 hover:border-blue-500 transition" style="max-width:180px">
                            <img src="${url}" class="w-full h-auto object-cover max-h-32">
                            <div class="px-2 py-1 bg-gray-750 text-xs text-gray-400">${escHtml(att.original_name)} · ${sizeKb} KB</div>
                        </a>`;
                    }
                    return `<a href="${url}" download="${escHtml(att.original_name)}"
                       class="flex items-center gap-2 text-xs text-blue-400 hover:text-blue-300 bg-gray-700 hover:bg-gray-600 rounded-lg px-3 py-1.5 transition border border-gray-600">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        ${escHtml(att.original_name)} <span class="text-gray-500">${sizeKb} KB</span>
                    </a>`;
                }).join('');
                attHtml = `<div class="flex flex-wrap gap-2 mt-2 pt-2 border-t border-gray-700">${items}</div>`;
            }

            div.innerHTML = `
        <img src="${AUTH_AVATAR}" class="w-8 h-8 rounded-full shrink-0 mt-0.5">
        <div class="flex-1">
            <div class="bg-gray-800 rounded-xl px-4 py-3">
                <div class="flex justify-between items-start mb-1.5">
                    <div>
                        <span class="text-white text-sm font-medium">${AUTH_NAME}</span>
                        <span class="text-gray-500 text-xs ml-2">vừa xong</span>
                    </div>
                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition">
                        <button onclick="editComment(${c.id}, \`${c.content.replace(/`/g,'\\`')}\`)"
                                class="text-xs text-gray-400 hover:text-blue-400 px-2 py-0.5 rounded hover:bg-blue-500/10 transition">Sửa</button>
                        <button onclick="deleteComment(${c.id})"
                                class="text-xs text-gray-400 hover:text-red-400 px-2 py-0.5 rounded hover:bg-red-500/10 transition">Xóa</button>
                    </div>
                </div>
                <p class="text-gray-300 text-sm leading-relaxed comment-content-${c.id}">${escHtml(c.content)}</p>
                ${attHtml}
                <div id="edit-form-${c.id}" class="hidden mt-2">
                    <textarea id="edit-content-${c.id}" rows="2"
                              class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 text-sm border border-gray-600 focus:border-blue-500 focus:outline-none resize-none"></textarea>
                    <div class="flex gap-2 mt-1.5 justify-end">
                        <button onclick="cancelEdit(${c.id})" class="text-xs text-gray-400 hover:text-white px-2 py-1 rounded transition">Hủy</button>
                        <button onclick="submitEdit(${c.id})" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded transition">Lưu</button>
                    </div>
                </div>
            </div>
        </div>`;

            list.appendChild(div);
            div.scrollIntoView({
                behavior: 'smooth',
                block: 'end'
            });
        }

        // ===== EDIT COMMENT =====
        function editComment(id, content) {
            document.querySelector(`.comment-content-${id}`).classList.add('hidden');
            document.getElementById(`edit-form-${id}`).classList.remove('hidden');
            document.getElementById(`edit-content-${id}`).value = content;
            document.getElementById(`edit-content-${id}`).focus();
        }

        function cancelEdit(id) {
            document.querySelector(`.comment-content-${id}`).classList.remove('hidden');
            document.getElementById(`edit-form-${id}`).classList.add('hidden');
        }

        async function submitEdit(id) {
            const content = document.getElementById(`edit-content-${id}`).value.trim();
            if (!content) return;

            try {
                const res = await fetch(`${COMMENT_BASE_URL}/${id}`, {
                    method: 'POST', // Laravel chấp nhận POST + _method
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        content: content,
                        _method: 'PUT'
                    })
                });

                const data = await res.json();
                if (data.comment) {
                    document.querySelector(`.comment-content-${id}`).textContent = data.comment.content;
                    cancelEdit(id);
                    showToast('Đã cập nhật bình luận');
                } else {
                    showToast('Lỗi: ' + (data.message ?? 'Không thể lưu'), 'error');
                }
            } catch (e) {
                showToast('Lỗi kết nối khi lưu', 'error');
            }
        }

        async function deleteComment(id) {
            if (!confirm('Xóa bình luận này?')) return;

            try {
                const res = await fetch(`${COMMENT_BASE_URL}/${id}`, {
                    method: 'POST', // POST + _method = DELETE
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        _method: 'DELETE'
                    })
                });

                if (res.ok) {
                    document.getElementById(`comment-${id}`)?.remove();
                    showToast('Đã xóa bình luận');
                } else {
                    const data = await res.json();
                    showToast('Lỗi: ' + (data.message ?? 'Không thể xóa'), 'error');
                }
            } catch (e) {
                showToast('Lỗi kết nối khi xóa', 'error');
            }
        }


        // ===== FILE PREVIEW =====
        // Danh sách file được chọn (quản lý thủ công)
        let selectedFiles = [];

        function previewCommentFiles(input) {
            // Gộp file mới vào danh sách hiện có (tránh trùng tên)
            [...input.files].forEach(newFile => {
                const exists = selectedFiles.some(f => f.name === newFile.name && f.size === newFile.size);
                if (!exists) selectedFiles.push(newFile);
            });

            // Reset input để có thể chọn lại cùng file
            input.value = '';

            renderFilePreview();
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            renderFilePreview();
        }

        function renderFilePreview() {
            const preview = document.getElementById('commentFilePreview');
            preview.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const sizeKb = (file.size / 1024).toFixed(1);
                const isImg = file.type.startsWith('image/');

                const item = document.createElement('div');
                item.className =
                    'flex items-center gap-2 bg-gray-700 border border-gray-600 rounded-lg px-3 py-1.5 text-xs text-gray-300';
                item.innerHTML = `
            ${isImg
                ? `<img src="${URL.createObjectURL(file)}" class="w-5 h-5 rounded object-cover shrink-0">`
                : `<span class="text-gray-500 shrink-0">
                                                                                                                                                                   <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                                                                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                                                                                                             d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                                                                                                                                   </svg>
                                                                                                                                                               </span>`
            }
            <span class="truncate max-w-[160px]">${escHtml(file.name)}</span>
            <span class="text-gray-500 shrink-0">${sizeKb} KB</span>
            <button onclick="removeFile(${index})"
                    class="ml-1 text-gray-500 hover:text-red-400 shrink-0 transition text-xs font-medium">
                Xóa
            </button>`;
                preview.appendChild(item);
            });
        }


        // ===== ENTER to submit =====
        document.getElementById('commentInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                submitComment();
            }
        });

        // ===== UTILS =====
        function escHtml(str) {
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
    </script>
@endpush
