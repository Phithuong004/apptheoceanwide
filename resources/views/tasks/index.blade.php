@extends('layouts.app')
@section('title', 'Tasks — ' . $project->name)

@push('styles')
<style>
.task-row:hover { background-color: rgba(255,255,255,0.03); }
.filter-active  { border-color: #6366f1 !important; color: #a5b4fc !important; }
</style>
@endpush

@section('content')

{{-- ===== HEADER ===== --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <a href="{{ route('projects.index', $workspace->slug) }}" class="hover:text-white transition">Dự án</a>
            <span>/</span>
            <a href="{{ route('projects.show', [$workspace->slug, $project->slug]) }}" class="hover:text-white transition">{{ $project->name }}</a>
            <span>/</span>
            <span class="text-gray-300">Tasks</span>
        </div>
        <h1 class="text-white text-xl font-bold">{{ $project->name }}</h1>
    </div>
    <div class="flex items-center gap-2">
        {{-- View toggle --}}
        <div class="flex bg-gray-800 rounded-lg p-1 border border-gray-700">
            <a href="{{ route('tasks.index', [$workspace->slug, $project->slug]) }}"
               class="px-3 py-1.5 rounded-md text-xs font-medium bg-gray-700 text-white transition">
                ☰ List
            </a>
            <a href="{{ route('projects.show', [$workspace->slug, $project->slug]) }}"
               class="px-3 py-1.5 rounded-md text-xs font-medium text-gray-400 hover:text-white transition">
                ⬜ Board
            </a>
        </div>
        <button onclick="openCreateModal('todo')"
                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tạo Task
        </button>
    </div>
</div>

{{-- ===== FILTERS ===== --}}
<div class="flex items-center gap-3 mb-5 flex-wrap">
    <form method="GET" action="{{ route('tasks.index', [$workspace->slug, $project->slug]) }}"
          id="filterForm" class="flex items-center gap-3 flex-wrap flex-1">

        {{-- Search --}}
        <div class="relative flex-1 min-w-48 max-w-72">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm task..."
                   class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-10 pr-4 py-2 text-white text-sm focus:outline-none focus:border-indigo-500 transition"
                   onchange="document.getElementById('filterForm').submit()">
        </div>

        {{-- Status --}}
        <select name="status" onchange="this.form.submit()"
                class="bg-gray-800 border {{ request('status') ? 'border-indigo-500 text-indigo-400' : 'border-gray-700 text-gray-400' }} rounded-lg px-3 py-2 text-sm focus:outline-none transition">
            <option value="">Tất cả trạng thái</option>
            @foreach(['backlog'=>'Backlog','todo'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','done'=>'Done','blocked'=>'Blocked'] as $v => $l)
                <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>

        {{-- Priority --}}
        <select name="priority" onchange="this.form.submit()"
                class="bg-gray-800 border {{ request('priority') ? 'border-indigo-500 text-indigo-400' : 'border-gray-700 text-gray-400' }} rounded-lg px-3 py-2 text-sm focus:outline-none transition">
            <option value="">Tất cả priority</option>
            @foreach(['low'=>'🟢 Low','medium'=>'🔵 Medium','high'=>'🟡 High','urgent'=>'🟠 Urgent','critical'=>'🔴 Critical'] as $v => $l)
                <option value="{{ $v }}" {{ request('priority') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>

        {{-- Assignee --}}
        <select name="assignee" onchange="this.form.submit()"
                class="bg-gray-800 border {{ request('assignee') ? 'border-indigo-500 text-indigo-400' : 'border-gray-700 text-gray-400' }} rounded-lg px-3 py-2 text-sm focus:outline-none transition">
            <option value="">Tất cả assignee</option>
            @foreach($members as $member)
                <option value="{{ $member->id }}" {{ request('assignee') == $member->id ? 'selected' : '' }}>
                    {{ $member->name }}
                </option>
            @endforeach
        </select>

        {{-- Sprint --}}
        @if($sprints->count())
        <select name="sprint" onchange="this.form.submit()"
                class="bg-gray-800 border {{ request('sprint') ? 'border-indigo-500 text-indigo-400' : 'border-gray-700 text-gray-400' }} rounded-lg px-3 py-2 text-sm focus:outline-none transition">
            <option value="">Tất cả sprint</option>
            @foreach($sprints as $sprint)
                <option value="{{ $sprint->id }}" {{ request('sprint') == $sprint->id ? 'selected' : '' }}>
                    {{ $sprint->name }}
                </option>
            @endforeach
        </select>
        @endif

        {{-- Clear filters --}}
        @if(request()->hasAny(['status','priority','assignee','sprint','search']))
        <a href="{{ route('tasks.index', [$workspace->slug, $project->slug]) }}"
           class="text-xs text-gray-500 hover:text-white border border-gray-700 hover:border-gray-500 px-3 py-2 rounded-lg transition">
            ✕ Xóa filter
        </a>
        @endif
    </form>

    {{-- Task count --}}
    <span class="text-gray-500 text-sm whitespace-nowrap">{{ $tasks->count() }} tasks</span>
</div>

{{-- ===== TASK LIST ===== --}}
<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">

    {{-- Table header --}}
    <div class="grid grid-cols-12 gap-4 px-4 py-3 border-b border-gray-800 text-xs text-gray-500 uppercase tracking-wider">
        <div class="col-span-5">Tiêu đề</div>
        <div class="col-span-2">Assignee</div>
        <div class="col-span-1 text-center">Priority</div>
        <div class="col-span-1 text-center">Status</div>
        <div class="col-span-1 text-center">Sprint</div>
        <div class="col-span-1 text-center">Due</div>
        <div class="col-span-1 text-center">SP</div>
    </div>

    {{-- Task rows --}}
    @forelse($tasks as $task)
    <div class="task-row grid grid-cols-12 gap-4 px-4 py-3 border-b border-gray-800/50 last:border-0 items-center transition group">

        {{-- Title --}}
        <div class="col-span-5 flex items-center gap-3 min-w-0">
            {{-- Type icon --}}
            <span class="text-xs shrink-0
                {{ $task->type === 'bug'     ? 'text-red-400'    :
                  ($task->type === 'feature' ? 'text-green-400'  :
                  ($task->type === 'epic'    ? 'text-purple-400' : 'text-blue-400')) }}">
                {{ $task->type === 'bug' ? '🐛' : ($task->type === 'epic' ? '⚡' : ($task->type === 'feature' ? '✨' : '📋')) }}
            </span>

            <div class="min-w-0">
                <a href="{{ route('tasks.show', [$workspace->slug, $project->id, $task->id]) }}"
                   class="text-white text-sm font-medium hover:text-indigo-400 transition truncate block
                          {{ $task->status === 'done' ? 'line-through text-gray-500' : '' }}">
                    {{ $task->title }}
                </a>
                {{-- Labels --}}
                @if($task->labels->count())
                <div class="flex gap-1 mt-1 flex-wrap">
                    @foreach($task->labels->take(3) as $label)
                    <span class="text-xs px-1.5 py-0.5 rounded-full"
                          style="background:{{ $label->color }}20;color:{{ $label->color }}">
                        {{ $label->name }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Assignee --}}
        <div class="col-span-2">
            @if($task->assignee)
            <div class="flex items-center gap-2">
                <img src="{{ $task->assignee->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($task->assignee->name).'&background=3b82f6&color=fff&size=24' }}"
                     class="w-6 h-6 rounded-full shrink-0">
                <span class="text-gray-300 text-xs truncate">{{ $task->assignee->name }}</span>
            </div>
            @else
            <span class="text-gray-600 text-xs">—</span>
            @endif
        </div>

        {{-- Priority --}}
        <div class="col-span-1 text-center">
            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                {{ $task->priority === 'critical' ? 'bg-red-500/20 text-red-400'     :
                  ($task->priority === 'urgent'   ? 'bg-orange-500/20 text-orange-400' :
                  ($task->priority === 'high'     ? 'bg-yellow-500/20 text-yellow-400' :
                  ($task->priority === 'medium'   ? 'bg-blue-500/20 text-blue-400'   :
                                                    'bg-gray-700 text-gray-400'))) }}">
                {{ ucfirst($task->priority ?? 'low') }}
            </span>
        </div>

        {{-- Status --}}
        <div class="col-span-1 text-center">
            <span class="text-xs px-2 py-0.5 rounded-full
                {{ $task->status === 'done'        ? 'bg-green-500/20 text-green-400'   :
                  ($task->status === 'in_progress' ? 'bg-blue-500/20 text-blue-400'    :
                  ($task->status === 'blocked'     ? 'bg-red-500/20 text-red-400'      :
                  ($task->status === 'in_review'   ? 'bg-purple-500/20 text-purple-400':
                                                     'bg-gray-700 text-gray-400'))) }}">
                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
            </span>
        </div>

        {{-- Sprint --}}
        <div class="col-span-1 text-center">
            <span class="text-xs text-gray-500">{{ $task->sprint->name ?? '—' }}</span>
        </div>

        {{-- Due date --}}
        <div class="col-span-1 text-center">
            @if($task->due_date)
            <span class="text-xs {{ $task->due_date->isPast() && $task->status !== 'done' ? 'text-red-400' : 'text-gray-400' }}">
                {{ $task->due_date->format('d/m') }}
            </span>
            @else
            <span class="text-gray-600 text-xs">—</span>
            @endif
        </div>

        {{-- Story points --}}
        <div class="col-span-1 text-center">
            @if($task->story_points)
            <span class="text-xs bg-indigo-900/40 text-indigo-400 px-1.5 py-0.5 rounded">
                {{ $task->story_points }}
            </span>
            @else
            <span class="text-gray-600 text-xs">—</span>
            @endif
        </div>
    </div>
    @empty
    <div class="py-16 text-center">
        <p class="text-4xl mb-3">📭</p>
        <p class="text-gray-400 font-medium">Chưa có task nào</p>
        <p class="text-gray-600 text-sm mt-1">Tạo task đầu tiên để bắt đầu</p>
        <button onclick="openCreateModal('todo')"
                class="mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm transition">
            + Tạo Task
        </button>
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($tasks instanceof \Illuminate\Pagination\LengthAwarePaginator)
<div class="mt-4">
    {{ $tasks->appends(request()->query())->links() }}
</div>
@endif

{{-- ===== MODAL TẠO TASK ===== --}}
<div id="createModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-xl shadow-2xl max-h-[90vh] overflow-y-auto">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800 sticky top-0 bg-gray-900">
            <h2 class="text-lg font-bold text-white">Tạo Task Mới</h2>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
        </div>

        <form method="POST"
              action="{{ route('tasks.store', [$workspace->slug, $project->id]) }}"
              enctype="multipart/form-data"
              class="px-6 py-5 space-y-4">
            @csrf
            <input type="hidden" name="status" id="modalStatus" value="todo">

            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Tiêu đề *</label>
                <input type="text" name="title" required autofocus
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 transition">
            </div>

            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Mô tả</label>
                <textarea name="description" rows="3" placeholder="Mô tả chi tiết..."
                          class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 transition resize-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Assignee</label>
                    <select name="assignee_id"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500">
                        <option value="">-- Chưa giao --</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Priority</label>
                    <select name="priority"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500">
                        <option value="low">🟢 Low</option>
                        <option value="medium" selected>🔵 Medium</option>
                        <option value="high">🟡 High</option>
                        <option value="urgent">🟠 Urgent</option>
                        <option value="critical">🔴 Critical</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Type</label>
                    <select name="type"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500">
                        <option value="task">📋 Task</option>
                        <option value="bug">🐛 Bug</option>
                        <option value="feature">✨ Feature</option>
                        <option value="story">📖 Story</option>
                        <option value="epic">⚡ Epic</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Sprint</label>
                    <select name="sprint_id"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500">
                        <option value="">-- Không có --</option>
                        @foreach($sprints as $sprint)
                            <option value="{{ $sprint->id }}">{{ $sprint->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Due Date</label>
                    <input type="date" name="due_date"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Story Points</label>
                    <input type="number" name="story_points" min="0" max="100" placeholder="0"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Đính kèm file</label>
                <div class="border-2 border-dashed border-gray-700 rounded-lg p-4 text-center hover:border-indigo-500/50 transition cursor-pointer"
                     onclick="document.getElementById('attachFiles').click()">
                    <input type="file" id="attachFiles" name="attachments[]" multiple
                           accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip"
                           class="hidden" onchange="previewFiles(this)">
                    <p class="text-gray-500 text-sm">📎 Click để chọn file hoặc kéo thả</p>
                    <p class="text-gray-600 text-xs mt-1">Ảnh, PDF, Word, Excel, ZIP — tối đa 10MB/file</p>
                    <div id="filePreview" class="mt-3 space-y-1 text-left"></div>
                </div>
            </div>

            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Ghi chú ban đầu</label>
                <textarea name="initial_comment" rows="2" placeholder="Thêm context hoặc ghi chú cho task..."
                          class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 transition resize-none"></textarea>
            </div>

            <div class="flex gap-3 pt-2 border-t border-gray-800">
                <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-medium text-sm transition">
                    ✅ Tạo Task
                </button>
                <button type="button" onclick="closeCreateModal()"
                        class="px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">
                    Huỷ
                </button>
            </div>
        </form>
    </div>
</div>

<div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

@endsection

@push('scripts')
<script>
function openCreateModal(status = 'todo') {
    document.getElementById('modalStatus').value = status;
    document.getElementById('createModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function previewFiles(input) {
    const preview = document.getElementById('filePreview');
    preview.innerHTML = '';
    [...input.files].forEach(file => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 text-xs text-gray-400 bg-gray-700/50 rounded px-3 py-1.5';
        div.innerHTML = `<span>📄</span><span class="truncate">${file.name}</span><span class="ml-auto text-gray-500">${(file.size/1024).toFixed(1)}KB</span>`;
        preview.appendChild(div);
    });
}

document.getElementById('createModal').addEventListener('click', function(e) {
    if (e.target === this) closeCreateModal();
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeCreateModal();
});
</script>
@endpush
