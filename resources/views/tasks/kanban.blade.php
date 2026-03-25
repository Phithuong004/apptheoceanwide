@extends('layouts.app')
@section('title', 'Kanban — ' . $project->name)

@push('styles')
<style>
.kanban-column { min-height: 200px; }
.task-card { cursor: grab; }
.task-card:active { cursor: grabbing; }
.sortable-ghost { opacity: 0.3; transform: scale(0.98); }
.sortable-drag { opacity: 0.9; box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
.column-drop-active { ring: 2px solid #6366f1; background-color: rgba(99,102,241,0.05); }
</style>
@endpush

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-white">{{ $project->name }}</h1>
        <p class="text-gray-400 text-sm mt-1">Kanban Board</p>
    </div>
    <div class="flex items-center gap-3">
        @if($sprints->count())
        <select class="rounded-lg bg-gray-800 border border-gray-700 text-white text-sm px-3 py-2 focus:outline-none">
            <option>Tất cả sprint</option>
            @foreach($sprints as $sprint)
                <option value="{{ $sprint->id }}">{{ $sprint->name }}</option>
            @endforeach
        </select>
        @endif

        <button onclick="openCreateTask('todo')"
                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tạo Task
        </button>
    </div>
</div>

{{-- Kanban Board --}}
<div class="flex gap-4 overflow-x-auto pb-6" id="kanban-board">
    @php
    $columns = [
        'backlog'     => ['label' => 'Backlog',     'color' => 'gray'],
        'todo'        => ['label' => 'To Do',        'color' => 'blue'],
        'in_progress' => ['label' => 'In Progress',  'color' => 'yellow'],
        'in_review'   => ['label' => 'In Review',    'color' => 'purple'],
        'done'        => ['label' => 'Done',          'color' => 'green'],
        'blocked'     => ['label' => 'Blocked',       'color' => 'red'],
    ];
    @endphp

    @foreach($columns as $status => $col)
    <div class="flex-shrink-0 w-72">

        {{-- Column Header --}}
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-{{ $col['color'] }}-500"></div>
                <h3 class="font-semibold text-sm text-gray-200">{{ $col['label'] }}</h3>
                <span class="column-count text-xs bg-gray-700 text-gray-400 rounded-full px-2 py-0.5">
                    {{ $board[$status]->count() }}
                </span>
            </div>
            <button class="text-gray-500 hover:text-white text-lg leading-none transition"
                    onclick="openCreateTask('{{ $status }}')">+</button>
        </div>

        {{-- Task List --}}
        <div class="kanban-column space-y-3 rounded-xl bg-gray-800/50 p-3 transition-all"
             data-status="{{ $status }}"
             id="column-{{ $status }}">

            @foreach($board[$status] as $index => $task)
            <div class="task-card bg-gray-800 rounded-xl p-4 shadow-sm hover:shadow-md
                        hover:-translate-y-0.5 transition-all border border-gray-700
                        hover:border-gray-600 select-none"
                 data-id="{{ $task->id }}"
                 data-status="{{ $status }}">

                {{-- Labels --}}
                @if($task->labels->count())
                <div class="flex flex-wrap gap-1 mb-2">
                    @foreach($task->labels as $label)
                    <span class="px-2 py-0.5 text-xs rounded-full font-medium"
                          style="background-color:{{ $label->color }}33; color:{{ $label->color }}">
                        {{ $label->name }}
                    </span>
                    @endforeach
                </div>
                @endif

                {{-- Title --}}
                <a href="{{ route('tasks.show', [$workspace->slug, $task->project_id, $task->id]) }}"
                   class="block text-sm font-medium text-gray-100 hover:text-indigo-400 mb-2">
                    {{ $task->title }}
                </a>

                {{-- Meta --}}
                <div class="flex items-center justify-between mt-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs px-1.5 py-0.5 rounded font-medium
                            @if($task->priority === 'critical') bg-red-900/50 text-red-400
                            @elseif($task->priority === 'urgent') bg-orange-900/50 text-orange-400
                            @elseif($task->priority === 'high') bg-yellow-900/50 text-yellow-400
                            @elseif($task->priority === 'medium') bg-blue-900/50 text-blue-400
                            @else bg-gray-700 text-gray-400
                            @endif">
                            {{ ucfirst($task->priority) }}
                        </span>

                        @if($task->story_points)
                        <span class="text-xs bg-indigo-900/50 text-indigo-400 px-1.5 py-0.5 rounded">
                            {{ $task->story_points }}sp
                        </span>
                        @endif

                        @if($task->due_date)
                        <span class="text-xs {{ $task->isOverdue() ? 'text-red-400' : 'text-gray-500' }}">
                            {{ $task->due_date->format('d/m') }}
                        </span>
                        @endif
                    </div>

                    @if($task->assignee)
                    <img src="{{ $task->assignee->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($task->assignee->name).'&background=3b82f6&color=fff&size=28' }}"
                         class="w-6 h-6 rounded-full object-cover flex-shrink-0"
                         title="{{ $task->assignee->name }}">
                    @endif
                </div>

                {{-- Subtask progress --}}
                @if($task->subtasks->count() > 0)
                <div class="mt-3">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Subtasks</span>
                        <span>{{ $task->subtasks->where('status','done')->count() }}/{{ $task->subtasks->count() }}</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-1">
                        <div class="bg-indigo-500 h-1 rounded-full"
                             style="width:{{ $task->subtask_progress }}%"></div>
                    </div>
                </div>
                @endif
            </div>
            @endforeach

        </div>
    </div>
    @endforeach
</div>

{{-- ===== MODAL TẠO TASK ===== --}}
<div id="createTaskModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-lg shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
            <h2 class="text-lg font-bold text-white">Tạo Task Mới</h2>
            <button onclick="closeCreateTask()" class="text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
        </div>
        <form method="POST"
              action="{{ route('tasks.store', [$workspace->slug, $project->slug]) }}"
              class="px-6 py-5 space-y-4">
            @csrf
            <input type="hidden" name="status" id="taskDefaultStatus" value="todo">

            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Tiêu đề *</label>
                <input type="text" name="title" required autofocus
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 transition">
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
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                        <option value="critical">Critical</option>
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
                    <input type="number" name="story_points" min="0" max="100"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <div class="flex gap-3 pt-2 border-t border-gray-800">
                <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-medium text-sm transition">
                    Tạo Task
                </button>
                <button type="button" onclick="closeCreateTask()"
                        class="px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">
                    Huỷ
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Toast container --}}
<div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
const MOVE_URL_TEMPLATE = "{{ url($workspace->slug . '/projects/' . $project->id . '/tasks/__ID__/move') }}";
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ===== SORTABLE INIT =====
document.querySelectorAll('.kanban-column').forEach(column => {
    Sortable.create(column, {
        group:         'kanban',
        animation:     200,
        ghostClass:    'sortable-ghost',
        dragClass:     'sortable-drag',
        delay:         80,
        delayOnTouchOnly: true,

        onStart() {
            // Highlight tất cả cột khi bắt đầu kéo
            document.querySelectorAll('.kanban-column').forEach(c => {
                c.classList.add('ring-1', 'ring-gray-600');
            });
        },

        onEnd(evt) {
            // Bỏ highlight
            document.querySelectorAll('.kanban-column').forEach(c => {
                c.classList.remove('ring-1', 'ring-gray-600', 'ring-indigo-500');
            });

            const taskId    = evt.item.dataset.id;
            const fromStatus = evt.from.dataset.status;
            const toStatus  = evt.to.dataset.status;
            const newPos    = evt.newIndex;

            // Cập nhật data-status trên card
            evt.item.dataset.status = toStatus;

            // Cập nhật badge đếm cả 2 cột
            updateColumnCount(evt.from);
            updateColumnCount(evt.to);

            // Gọi API
            moveTask(taskId, toStatus, newPos, fromStatus, evt);
        },

        onMove(evt) {
            // Highlight cột đang hover
            document.querySelectorAll('.kanban-column').forEach(c => {
                c.classList.remove('ring-indigo-500', 'ring-1');
            });
            evt.to.classList.add('ring-1', 'ring-indigo-500');
        }
    });
});

async function moveTask(taskId, toStatus, position, fromStatus, evt) {
    const url = MOVE_URL_TEMPLATE.replace('__ID__', taskId);

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ status: toStatus, position })
        });

        if (res.ok) {
            showToast(`✅ Chuyển sang <strong>${toStatus.replace('_', ' ')}</strong>`);
        } else {
            // Revert DOM nếu lỗi
            showToast('❌ Cập nhật thất bại, thử lại!', 'error');
            evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] || null);
            updateColumnCount(evt.from);
            updateColumnCount(evt.to);
        }
    } catch (e) {
        showToast('❌ Lỗi kết nối!', 'error');
    }
}

// ===== CẬP NHẬT SỐ LƯỢNG CỘT =====
function updateColumnCount(columnEl) {
    const status = columnEl.dataset.status;
    const count  = columnEl.querySelectorAll('.task-card').length;
    const header = columnEl.closest('.flex-shrink-0').querySelector('.column-count');
    if (header) header.textContent = count;
}

// ===== TOAST =====
function showToast(msg, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast     = document.createElement('div');
    toast.className = `px-4 py-2.5 rounded-lg text-sm text-white shadow-lg transition-all
        ${type === 'error' ? 'bg-red-600' : 'bg-gray-800 border border-gray-700'}`;
    toast.innerHTML = msg;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

// ===== MODAL =====
function openCreateTask(status) {
    document.getElementById('taskDefaultStatus').value = status;
    document.getElementById('createTaskModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCreateTask() {
    document.getElementById('createTaskModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Đóng modal khi click backdrop
document.getElementById('createTaskModal').addEventListener('click', function(e) {
    if (e.target === this) closeCreateTask();
});

// ESC để đóng modal
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeCreateTask();
});
</script>
@endpush
