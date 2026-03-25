@extends('layouts.app')
@section('title', $project->name)

@push('styles')
    <style>
        .sortable-ghost {
            opacity: 0.3;
            transform: scale(0.98);
        }

        .sortable-drag {
            opacity: 0.95;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        .column-active {
            border-color: #6366f1 !important;
            background-color: rgba(99, 102, 241, 0.05);
        }
    </style>
@endpush

@section('content')

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('projects.index', $workspace->slug) }}" class="text-gray-500 hover:text-white text-sm">← Dự
                án</a>
            <h1 class="text-white text-xl font-bold mt-1">{{ $project->name }}</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('projects.edit', [$workspace->slug, $project->slug]) }}"
                class="bg-gray-700 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded-lg transition">Chỉnh sửa</a>
            <button onclick="openCreateTask('todo')"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition">+ Tạo Task</button>
        </div>
    </div>

    {{-- Sprint active --}}
    @if ($activeSprint)
        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-4 mb-6 flex items-center justify-between">
            <div>
                <p class="text-blue-400 text-xs uppercase tracking-wider">Sprint đang chạy</p>
                <p class="text-white font-semibold mt-0.5">{{ $activeSprint->name }}</p>
            </div>
            <div class="text-right">
                <p class="text-gray-400 text-xs">Kết thúc: {{ $activeSprint->end_date?->format('d/m/Y') }}</p>
                <p class="text-blue-400 text-xs mt-0.5">{{ $activeSprint->tasks->count() }} tasks</p>
            </div>
        </div>
    @endif

    {{-- Kanban Board --}}
    <div class="overflow-x-auto pb-4">
        <div class="flex gap-4 min-w-max">
            @foreach ([
            'backlog' => ['label' => 'Backlog', 'color' => 'gray'],
            'todo' => ['label' => 'To Do', 'color' => 'slate'],
            'in_progress' => ['label' => 'In Progress', 'color' => 'blue'],
            'in_review' => ['label' => 'In Review', 'color' => 'purple'],
            'done' => ['label' => 'Done', 'color' => 'green'],
            'blocked' => ['label' => 'Blocked', 'color' => 'red'],
        ] as $status => $meta)
                <div class="w-72 bg-gray-900 border border-gray-800 rounded-xl flex flex-col">

                    {{-- Column header --}}
                    <div class="px-4 py-3 border-b border-gray-800 flex items-center justify-between">
                        <h3 class="text-white text-sm font-medium">{{ $meta['label'] }}</h3>
                        <div class="flex items-center gap-2">
                            <span class="column-count text-xs bg-gray-800 text-gray-400 px-2 py-0.5 rounded-full">
                                {{ collect($board[$status] ?? [])->count() }}
                            </span>
                            <button onclick="openCreateTask('{{ $status }}')"
                                class="text-gray-500 hover:text-white text-lg leading-none transition">+</button>
                        </div>
                    </div>

                    {{-- Task list (SortableJS target) --}}
                    <div class="kanban-column p-3 space-y-2 flex-1 min-h-32 transition-all"
                        data-status="{{ $status }}">

                        @forelse($board[$status] ?? [] as $task)
                            <div class="task-card bg-gray-800 border border-gray-700 hover:border-gray-600
                            rounded-lg p-3 transition-all hover:-translate-y-0.5 cursor-grab active:cursor-grabbing select-none"
                                data-id="{{ $task->id }}" data-status="{{ $status }}">

                                {{-- Labels --}}
                                @if ($task->labels->count())
                                    <div class="flex gap-1 flex-wrap mb-2">
                                        @foreach ($task->labels as $label)
                                            <span class="text-xs px-1.5 py-0.5 rounded-full"
                                                style="background:{{ $label->color }}20;color:{{ $label->color }}">
                                                {{ $label->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Title --}}
                                <a href="{{ route('tasks.show', ['workspace' => $workspace->slug, 'project' => $project->id, 'task' => $task->id]) }}"
                                    class="block text-white text-sm leading-snug hover:text-indigo-400 transition"
                                    onclick="event.stopPropagation()">
                                    {{ $task->title }}
                                </a>

                                {{-- Subtasks --}}
                                @if ($task->subtasks->count())
                                    <p class="text-gray-500 text-xs mt-1">
                                        {{ $task->subtasks->where('status', 'done')->count() }}/{{ $task->subtasks->count() }}
                                        subtasks
                                    </p>
                                @endif

                                <div class="flex items-center justify-between mt-3">
                                    <span
                                        class="text-xs px-1.5 py-0.5 rounded
                            {{ $task->priority === 'critical'
                                ? 'bg-red-500/20 text-red-400'
                                : ($task->priority === 'high'
                                    ? 'bg-orange-500/20 text-orange-400'
                                    : ($task->priority === 'medium'
                                        ? 'bg-yellow-500/20 text-yellow-400'
                                        : 'bg-gray-700 text-gray-400')) }}">
                                        {{ ucfirst($task->priority ?? 'low') }}
                                    </span>

                                    <div class="flex items-center gap-2">
                                        @if ($task->due_date)
                                            <span
                                                class="text-xs {{ $task->due_date->isPast() && $task->status !== 'done' ? 'text-red-400' : 'text-gray-500' }}">
                                                {{ $task->due_date->format('d/m') }}
                                            </span>
                                        @endif

                                        @if ($task->assignee)
                                            <img src="{{ $task->assignee->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($task->assignee->name) . '&background=3b82f6&color=fff&size=24' }}"
                                                class="w-6 h-6 rounded-full" title="{{ $task->assignee->name }}">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-gray-700 text-xs text-center py-6">Trống</div>
                        @endforelse

                    </div>

                    {{-- Add task --}}
                    <div class="p-3 border-t border-gray-800">
                        <button onclick="openCreateTask('{{ $status }}')"
                            class="w-full text-center text-gray-600 hover:text-gray-400 text-sm py-1 rounded hover:bg-gray-800 transition">
                            + Thêm task
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Members --}}
    <div class="mt-6 bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h3 class="text-white font-semibold mb-4">👥 Thành viên dự án ({{ $members->count() }})</h3>
        <div class="flex flex-wrap gap-3">
            @foreach ($members as $member)
                <div class="flex items-center gap-2 bg-gray-800 rounded-lg px-3 py-2">
                    <img src="{{ $member->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=3b82f6&color=fff&size=28' }}"
                        class="w-7 h-7 rounded-full">
                    <div>
                        <p class="text-white text-sm">{{ $member->name }}</p>
                        <p class="text-gray-500 text-xs">{{ $member->pivot->role ?? 'member' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ===== MODAL TẠO TASK ===== --}}
    <div id="createTaskModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-xl shadow-2xl max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800 sticky top-0 bg-gray-900 z-10">
                <h2 class="text-lg font-bold text-white">Tạo Task Mới</h2>
                <button onclick="closeCreateTask()"
                    class="text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>

            <form method="POST" action="{{ route('tasks.store', [$workspace->slug, $project->id]) }}"
                enctype="multipart/form-data" class="px-6 py-5 space-y-4">
                @csrf
                <input type="hidden" name="status" id="taskDefaultStatus" value="todo">

                {{-- Tiêu đề --}}
                <div>
                    <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Tiêu đề *</label>
                    <input type="text" name="title" required autofocus
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 transition">
                </div>

                {{-- Mô tả --}}
                <div>
                    <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Mô tả</label>
                    <textarea name="description" rows="3" placeholder="Mô tả chi tiết task..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 transition resize-none"></textarea>
                </div>

                {{-- Assignee + Priority --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Assignee</label>
                        <select name="assignee_id"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500">
                            <option value="">-- Chưa giao --</option>
                            @foreach ($members as $member)
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

                {{-- Type + Status --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Type</label>
                        <select name="type"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500">
                            <option value="task">📋 Task</option>
                            <option value="bug">🐛 Bug</option>
                            <option value="feature">✨ Feature</option>
                            <option value="improvement">⚡ Improvement</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Trạng thái</label>
                        <select name="status" id="taskDefaultStatus"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500">
                            <option value="backlog">Backlog</option>
                            <option value="todo" selected>To Do</option>
                            <option value="in_progress">In Progress</option>
                            <option value="in_review">In Review</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                </div>

                {{-- Due Date + Story Points --}}
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

                {{-- Đính kèm file --}}
                <div>
                    <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Đính kèm file</label>
                    <div class="border-2 border-dashed border-gray-700 rounded-lg p-4 text-center hover:border-gray-500 transition cursor-pointer"
                        onclick="document.getElementById('taskAttachments').click()">
                        <input type="file" id="taskAttachments" name="attachments[]" multiple
                            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip" class="hidden"
                            onchange="showFileNames(this)">
                        <p class="text-gray-500 text-sm">📎 Click để chọn file hoặc kéo thả vào đây</p>
                        <p class="text-gray-600 text-xs mt-1">Hỗ trợ: ảnh, PDF, Word, Excel, ZIP</p>
                        <div id="fileNameList" class="mt-2 space-y-1"></div>
                    </div>
                </div>

                {{-- Ghi chú / Comment ban đầu --}}
                <div>
                    <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Ghi chú ban đầu</label>
                    <textarea name="initial_comment" rows="2" placeholder="Thêm ghi chú hoặc context cho task này..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 transition resize-none"></textarea>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 pt-2 border-t border-gray-800">
                    <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-medium text-sm transition">
                        ✅ Tạo Task
                    </button>
                    <button type="button" onclick="closeCreateTask()"
                        class="px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">
                        Huỷ
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- Toast --}}
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const CSRF = document.querySelector('meta[name="csrf-token"]').content;
            const PROJECT_ID = {{ $project->id }};
            const WORKSPACE = '{{ $workspace->slug }}';

            // ===== SORTABLE =====
            document.querySelectorAll('.kanban-column').forEach(column => {
                Sortable.create(column, {
                    group: 'kanban',
                    animation: 200,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    delay: 80,
                    delayOnTouchOnly: true,

                    onStart() {
                        document.querySelectorAll('.kanban-column').forEach(c => {
                            c.closest('.flex-col').classList.add('ring-1', 'ring-gray-700');
                        });
                    },

                    onMove(evt) {
                        document.querySelectorAll('.kanban-column').forEach(c => {
                            c.closest('.flex-col').classList.remove('ring-indigo-500',
                                'column-active');
                        });
                        evt.to.closest('.flex-col').classList.add('column-active');
                    },

                    onEnd(evt) {
                        // Bỏ highlight tất cả cột
                        document.querySelectorAll('.kanban-column').forEach(c => {
                            c.closest('.flex-col').classList.remove('ring-1',
                                'ring-gray-700', 'ring-indigo-500', 'column-active');
                        });

                        const taskId = evt.item.dataset.id;
                        const fromStatus = evt.from.dataset.status;
                        const toStatus = evt.to.dataset.status;
                        const newPos = evt.newIndex;

                        // Cập nhật data-status trên card
                        evt.item.dataset.status = toStatus;

                        // Cập nhật badge đếm
                        updateCount(evt.from);
                        updateCount(evt.to);

                        // Gọi API
                        fetch(`/${WORKSPACE}/projects/${PROJECT_ID}/tasks/${taskId}/move`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF,
                            },
                            body: JSON.stringify({
                                status: toStatus,
                                position: newPos
                            })
                        }).then(res => {
                            if (res.ok) {
                                showToast('✅ Chuyển sang <b>' + toStatus.replace('_', ' ') +
                                    '</b>');
                            } else {
                                showToast('❌ Cập nhật thất bại!', 'error');
                                // Revert
                                evt.from.insertBefore(evt.item, evt.from.children[evt
                                    .oldIndex] || null);
                                updateCount(evt.from);
                                updateCount(evt.to);
                            }
                        }).catch(() => {
                            showToast('❌ Lỗi kết nối!', 'error');
                        });
                    }
                });
            });

            function updateCount(columnEl) {
                const count = columnEl.querySelectorAll('.task-card').length;
                const badge = columnEl.closest('.flex-col').querySelector('.column-count');
                if (badge) badge.textContent = count;
            }

            function showToast(msg, type = 'success') {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `px-4 py-2.5 rounded-lg text-sm text-white shadow-lg
            ${type === 'error' ? 'bg-red-600' : 'bg-gray-800 border border-gray-700'}`;
                toast.innerHTML = msg;
                container.appendChild(toast);
                setTimeout(() => {
                    toast.style.transition = 'opacity 0.3s';
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                }, 2500);
            }

            // ===== MODAL =====
            document.getElementById('createTaskModal').addEventListener('click', function(e) {
                if (e.target === this) closeCreateTask();
            });

            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeCreateTask();
            });
        });

        function openCreateTask(status) {
            document.getElementById('taskDefaultStatus').value = status;
            document.getElementById('createTaskModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeCreateTask() {
            document.getElementById('createTaskModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function showFileNames(input) {
            const list = document.getElementById('fileNameList');
            list.innerHTML = '';
            [...input.files].forEach(file => {
                const div = document.createElement('div');
                div.className = 'text-xs text-gray-400 bg-gray-700 rounded px-2 py-1';
                div.textContent = `📄 ${file.name} (${(file.size/1024).toFixed(1)} KB)`;
                list.appendChild(div);
            });
        }
    </script>
@endpush
