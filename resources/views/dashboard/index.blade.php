@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    {{-- Back button --}}
    <div class="mb-4">
        <a href="{{ route('workspace.index') }}"
            class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 hover:text-white rounded-lg text-sm font-medium inline-flex items-center gap-1">
            ← Quay lại Workspaces
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        @foreach ([['label' => 'Task hôm nay', 'value' => data_get($stats, 'tasks_today', 0)], ['label' => 'Quá hạn', 'value' => data_get($stats, 'overdue', 0)], ['label' => 'Project active', 'value' => data_get($stats, 'active_projects', 0)], ['label' => 'Giờ tuần này', 'value' => data_get($stats, 'hours_week', 0) . 'h'], ['label' => 'Hoàn thành tuần', 'value' => data_get($stats, 'completed_week', 0)]] as $stat)
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
                <p class="text-gray-400 text-xs">{{ $stat['label'] }}</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Workspace hiện tại: quản lý thành viên --}}
    @if (isset($workspace))
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs uppercase text-gray-400">Workspace hiện tại</p>
                    <p class="text-lg font-semibold text-white">{{ $workspace->name }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('workspace.show', $workspace->slug) }}"
                        class="px-3 py-2 bg-gray-800 hover:bg-gray-700 text-white text-xs rounded-lg">
                        Vào workspace
                    </a>

                    {{-- Nút mở modal edit --}}
                    <button type="button" x-data @click="$dispatch('open-workspace-edit-modal')"
                        class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white text-xs rounded-lg">
                        ⚙️ Cài đặt
                    </button>

                    <button type="button" x-data @click="$dispatch('open-workspace-members-modal')"
                        class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-lg">
                        Quản lý thành viên
                    </button>
                </div>

            </div>

            {{-- Show một vài member tóm tắt --}}
            <div class="flex -space-x-2">
                @foreach ($workspace->members->take(5) as $member)
                    <img src="{{ $member->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=6366f1&color=fff' }}"
                        class="w-8 h-8 rounded-full border border-gray-900" title="{{ $member->name }}">
                @endforeach
                @if ($workspace->members->count() > 5)
                    <span
                        class="w-8 h-8 rounded-full bg-gray-800 border border-gray-900 flex items-center justify-center text-xs text-gray-300">
                        +{{ $workspace->members->count() - 5 }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Modal quản lý member workspace --}}
        <div x-data="{ open: false }" x-on:open-workspace-members-modal.window="open = true" x-show="open" x-cloak
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/50">
            <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-lg p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-white">Thành viên workspace</h2>
                    <button class="text-gray-400 hover:text-white text-xl" @click="open = false">&times;</button>
                </div>

                {{-- Form thêm thành viên --}}
                <form action="{{ route('workspace.members.store', $workspace->slug) }}" method="POST"
                    class="flex gap-2 mb-4">
                    @csrf
                    <input type="email" name="email" placeholder="Email thành viên"
                        class="flex-1 bg-gray-800 border border-gray-700 text-white text-xs rounded-lg px-3 py-2">
                    <select name="role"
                        class="bg-gray-800 border border-gray-700 text-white text-xs rounded-lg px-3 py-2">
                        <option value="member">Member</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                        <option value="guest">Guest</option>
                    </select>
                    <button type="submit"
                        class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-lg">
                        Thêm
                    </button>
                </form>

                {{-- Danh sách thành viên --}}
                <div class="max-h-72 overflow-y-auto divide-y divide-gray-800">
                    @foreach ($workspace->members as $member)
                        <div class="flex items-center justify-between py-2">
                            <div class="flex items-center gap-3">
                                <img src="{{ $member->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=6366f1&color=fff' }}"
                                    class="w-8 h-8 rounded-full">
                                <div>
                                    <p class="text-sm text-white">{{ $member->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $member->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full bg-gray-800 text-xs text-gray-300">
                                    {{ $member->pivot->role }}
                                </span>

                                @if ($member->id !== $workspace->owner_id)
                                    <form
                                        action="{{ route('workspace.members.destroy', [$workspace->slug, $member->id]) }}"
                                        method="POST" onsubmit="return confirm('Xoá thành viên này khỏi workspace?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-400 hover:text-red-300">
                                            Xoá
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Modal chỉnh sửa workspace --}}
        <div x-data="{ open: false }" x-on:open-workspace-edit-modal.window="open = true" x-show="open" x-cloak
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/50">
            <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-lg p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-white">Cài đặt Workspace</h2>
                    <button class="text-gray-400 hover:text-white text-xl" @click="open = false">&times;</button>
                </div>

                <form action="{{ route('workspace.update', $workspace->slug) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Tên workspace --}}
                    <div class="mb-4">
                        <label class="block text-xs text-gray-400 mb-1">Tên workspace</label>
                        <input type="text" name="name" value="{{ $workspace->name }}"
                            class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-indigo-500"
                            required>
                    </div>

                    {{-- Mô tả --}}
                    <div class="mb-4">
                        <label class="block text-xs text-gray-400 mb-1">Mô tả</label>
                        <textarea name="description" rows="3"
                            class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-indigo-500">{{ $workspace->description }}</textarea>
                    </div>

                    {{-- Màu đại diện --}}
                    <div class="mb-5">
                        <label class="block text-xs text-gray-400 mb-1">Màu đại diện</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="color" value="{{ $workspace->color ?? '#3b82f6' }}"
                                class="w-10 h-10 rounded cursor-pointer bg-transparent border-0">
                            <span class="text-xs text-gray-500">Chọn màu hiển thị cho workspace</span>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open = false"
                            class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-xs rounded-lg">
                            Huỷ
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-lg">
                            Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>

    @endif

    {{-- Body --}}
    <div class="grid grid-cols-3 gap-6">

        {{-- Task của tôi --}}
        <div class="col-span-2 bg-gray-900 border border-gray-800 rounded-xl p-5">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-white font-semibold">🎯 Task của tôi</h2>
                <a href="{{ route('tasks.my', $workspace->slug) }}" class="text-blue-400 text-xs hover:underline">Xem tất
                    cả</a>
            </div>
            @forelse($myTasks as $task)
                <a href="{{ route('tasks.show', [$workspace->slug, $task->project_id, $task->id]) }}"
                    class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-800 transition border-l-2
                    {{ $task->priority === 'high' ? 'border-red-500' : ($task->priority === 'medium' ? 'border-yellow-500' : 'border-gray-600') }} mb-2">
                    <div>
                        <p class="text-white text-sm">{{ $task->title }}</p>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $task->project->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-gray-400 text-xs">{{ $task->due_date?->format('d/m') }}</p>
                        <span
                            class="text-xs px-2 py-0.5 rounded-full mt-1 inline-block
                        {{ $task->status === 'done'
                            ? 'bg-green-500/20 text-green-400'
                            : ($task->status === 'in_progress'
                                ? 'bg-blue-500/20 text-blue-400'
                                : ($task->status === 'in_review'
                                    ? 'bg-purple-500/20 text-purple-400'
                                    : 'bg-gray-700 text-gray-400')) }}">
                            {{ str_replace('_', ' ', ucfirst($task->status)) }}
                        </span>
                    </div>
                </a>
            @empty
                <p class="text-gray-500 text-sm text-center py-8">Không có task nào 🎉</p>
            @endforelse
        </div>

        {{-- Hoạt động gần đây --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <h2 class="text-white font-semibold mb-4">📋 Hoạt động gần đây</h2>
            @forelse($recentActivity as $activity)
                <div class="flex gap-3 mb-4">
                    <img src="{{ $activity->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($activity->user->name ?? 'U') . '&background=3b82f6&color=fff&size=32' }}"
                        class="w-7 h-7 rounded-full shrink-0 mt-0.5">
                    <div>
                        <p class="text-gray-300 text-xs">{{ $activity->description }}</p>
                        <p class="text-gray-600 text-xs mt-0.5">{{ $activity->created_at?->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-sm text-center py-8">Chưa có hoạt động</p>
            @endforelse
        </div>

    </div>
@endsection
