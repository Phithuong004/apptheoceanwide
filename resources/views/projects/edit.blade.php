@extends('layouts.app')
@section('title', 'Chỉnh sửa — ' . $project->name)

@section('content')
    <div class="max-w-3xl mx-auto">

        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('projects.show', [$workspace->slug, $project->slug]) }}"
               class="text-gray-500 hover:text-white text-sm transition">← Về dự án</a>
            <h1 class="text-white text-xl font-bold mt-1">Chỉnh sửa: {{ $project->name }}</h1>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4 mb-6">
                <p class="text-green-400 text-sm">{{ session('success') }}</p>
            </div>
        @endif

        {{-- ===== FORM UPDATE PROJECT ===== --}}
        <form method="POST" id="form-update-project" action="{{ route('projects.update', [$workspace->slug, $project->slug]) }}">
            @csrf
            @method('PUT')

            {{-- THÔNG TIN CƠ BẢN --}}
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 mb-4">
                <h2 class="text-white font-semibold mb-4">📋 Thông tin cơ bản</h2>

                {{-- Tên + Client --}}
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Tên dự án *</label>
                        <input type="text" name="name" value="{{ old('name', $project->name) }}" required
                               class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 transition">
                        @error('name')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Client --}}
                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Khách hàng</label>
                        <select name="client_id"
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 transition">
                            <option value="">-- Không có khách hàng --</option>
                            @forelse($clients as $client)
                                <option value="{{ $client->id }}"
                                        {{ old('client_id', $project->client_id) == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @empty
                                <option disabled>Chưa có khách hàng nào</option>
                            @endforelse
                        </select>
                        @error('client_id')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Mô tả --}}
                <div class="mb-4">
                    <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Mô tả</label>
                    <textarea name="description" rows="3"
                              class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 transition resize-none">{{ old('description', $project->description) }}</textarea>
                    @error('description')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Màu sắc --}}
                <div class="mb-4">
                    <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Màu sắc</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="color" value="{{ old('color', $project->color) }}"
                               class="w-10 h-10 rounded-lg bg-gray-800 border border-gray-700 cursor-pointer p-1">
                        <input type="text" id="colorHex" value="{{ old('color', $project->color) }}"
                               class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500"
                               readonly>
                    </div>
                </div>

                {{-- Status + Type + Visibility --}}
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Trạng thái</label>
                        <select name="status" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 transition">
                            @foreach (['planning' => 'Planning', 'active' => 'Active', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'archived' => 'Archived'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $project->status) === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Loại dự án</label>
                        <select name="type" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 transition">
                            @foreach (['scrum' => 'Scrum', 'kanban' => 'Kanban', 'waterfall' => 'Waterfall'] as $val => $label)
                                <option value="{{ $val }}" {{ old('type', $project->type) === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Hiển thị</label>
                        <select name="visibility" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 transition">
                            @foreach (['private' => '🔒 Private', 'team' => '👥 Team', 'public' => '🌐 Public'] as $val => $label)
                                <option value="{{ $val }}" {{ old('visibility', $project->visibility) === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- THỜI GIAN + NGÂN SÁCH --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
                    <h2 class="text-white font-semibold mb-4">📅 Thời gian</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Ngày bắt đầu</label>
                            <input type="date" name="start_date"
                                   value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"
                                   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Deadline</label>
                            <input type="date" name="end_date"
                                   value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}"
                                   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 transition">
                        </div>
                    </div>
                </div>

                <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
                    <h2 class="text-white font-semibold mb-4">💰 Ngân sách</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Ngân sách</label>
                            <input type="number" name="budget" min="0" step="1000"
                                   value="{{ old('budget', $project->budget) }}" placeholder="0"
                                   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Đơn vị tiền tệ</label>
                            <select name="currency" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 transition">
                                @foreach (['VND', 'USD', 'EUR', 'JPY', 'SGD'] as $cur)
                                    <option value="{{ $cur }}" {{ old('currency', $project->currency) === $cur ? 'selected' : '' }}>
                                        {{ $cur }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- THÀNH VIÊN --}}
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 mb-6">
                <h2 class="text-white font-semibold mb-4">👥 Thành viên dự án</h2>

                {{-- 🆕 ADMIN ONLY: Owner Selection --}}
                @if(auth()->user()->hasRole('admin') || auth()->id() === $project->owner_id)
                <div class="mb-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-xl">
                    <label class="block text-sm text-blue-300 font-medium mb-3">👑 Đổi Owner (Admin)</label>
                    <div class="flex gap-3 items-end">
                        <select id="ownerSelect" class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500">
                            <option value="">-- Giữ nguyên {{ $project->owner?->name ?? 'owner' }} --</option>
                            @foreach($project->members as $member)
                                <option value="{{ $member->id }}"
                                        data-name="{{ $member->name }}"
                                        data-avatar="{{ $member->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=3b82f6&color=fff&size=32' }}"
                                        {{ $project->owner_id == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }} ({{ $member->email }})
                                </option>
                            @endforeach
                        </select>
                        <button type="button" onclick="setNewOwner()" 
                                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition whitespace-nowrap">
                            Đổi Owner
                        </button>
                    </div>
                    <div id="ownerPreview" class="mt-2 hidden flex items-center gap-2 p-2 bg-gray-800 rounded-lg">
                        <img id="ownerAvatar" class="w-6 h-6 rounded-full" src="">
                        <span id="ownerName" class="text-white text-sm font-medium"></span>
                        <span class="text-xs text-yellow-400">👑 Owner mới</span>
                    </div>
                    {{-- Hidden input cho controller xử lý --}}
                    <input type="hidden" name="owner_id" id="ownerInput" value="{{ $project->owner_id }}">
                </div>
                @endif

                {{-- Existing Members --}}
                <div class="space-y-2 mb-4 max-h-96 overflow-y-auto">
                    @forelse($project->members as $member)
                        <div class="flex items-center justify-between bg-gray-800 rounded-lg px-4 py-3 member-row" data-member-id="{{ $member->id }}">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <img src="{{ $member->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=3b82f6&color=fff&size=32' }}"
                                     class="w-8 h-8 rounded-full flex-shrink-0" alt="{{ $member->name }}">
                                <div class="min-w-0 flex-1">
                                    <p class="text-white text-sm font-medium truncate">{{ $member->name }}</p>
                                    <p class="text-gray-500 text-xs truncate">{{ $member->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <select name="member_roles[{{ $member->id }}]"
                                        class="bg-gray-700 border border-gray-600 rounded-lg px-2 py-1.5 text-white text-xs focus:outline-none focus:border-blue-500">
                                    @foreach (['manager', 'developer', 'designer', 'tester', 'viewer'] as $role)
                                        <option value="{{ $role }}" {{ $member->pivot->role === $role ? 'selected' : '' }}>
                                            {{ ucfirst($role) }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($member->id == $project->owner_id)
                                    <span class="text-xs text-yellow-400 border border-yellow-500/30 px-3 py-1.5 rounded-lg flex items-center gap-1">
                                        👑 Owner
                                    </span>
                                @else
                                    <button type="button" onclick="removeMember({{ $member->id }})"
                                            class="text-red-400 hover:text-red-300 text-xs border border-red-500/30 hover:border-red-400 px-2 py-1.5 rounded-lg transition">
                                        Xóa
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            Chưa có thành viên nào
                        </div>
                    @endforelse
                </div>

                {{-- Add New Member --}}
                <div class="border-t border-gray-800 pt-4">
                    <label class="block text-xs text-gray-400 uppercase tracking-wider mb-2">Thêm thành viên mới</label>
                    <div class="flex gap-2">
                        <input type="email" id="newMemberEmail" placeholder="nhập@email.com"
                               class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 transition">
                        <select id="newMemberRole" class="w-32 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500">
                            @foreach (['developer', 'designer', 'tester', 'viewer', 'manager'] as $role)
                                <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="addMember()"
                                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition">
                            + Thêm
                        </button>
                    </div>
                    <div id="newMembersContainer" class="mt-3 space-y-2"></div>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="flex gap-3 mb-8">
                <button type="submit"
                        class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition shadow-lg hover:shadow-xl">
                    💾 Lưu thay đổi
                </button>
                <a href="{{ route('projects.show', [$workspace->slug, $project->slug]) }}"
                   class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm transition shadow-lg">
                    Hủy
                </a>
            </div>
        </form>

        {{-- ===== FORM DELETE (TÁCH RIÊNG) ===== --}}
        <div class="bg-red-500/5 border-2 border-red-500/20 rounded-xl p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-red-400 font-semibold text-lg">⚠️ Xóa dự án vĩnh viễn</h3>
                    <p class="text-gray-400 text-sm mt-1">Xóa toàn bộ tasks, comments, files. **Không thể khôi phục**.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('projects.destroy', [$workspace->slug, $project->slug]) }}" 
                  class="inline-block" onsubmit="return confirm('⚠️ XÓA VĨNH VIỄN dự án \"{{ $project->name }}\"?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition shadow-lg hover:shadow-xl border border-red-500/50">
                    🗑️ Xóa dự án
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // 1️⃣ Color picker sync
    document.querySelector('input[name="color"]').addEventListener('input', function() {
        document.getElementById('colorHex').value = this.value;
    });

    let memberCount = 0;

    // 2️⃣ 🆕 Admin: Set new owner (dùng field `owner_id` của controller)
    function setNewOwner() {
        const select = document.getElementById('ownerSelect');
        const preview = document.getElementById('ownerPreview');
        const input = document.getElementById('ownerInput');
        
        if (select.value) {
            input.value = select.value;
            const option = select.options[select.selectedIndex];
            document.getElementById('ownerAvatar').src = option.dataset.avatar;
            document.getElementById('ownerName').textContent = option.dataset.name;
            preview.classList.remove('hidden');
        } else {
            input.value = "{{ $project->owner_id }}"; // Giữ nguyên
            preview.classList.add('hidden');
        }
    }

    // 3️⃣ Add new member
    function addMember() {
        const email = document.getElementById('newMemberEmail').value.trim();
        const role = document.getElementById('newMemberRole').value;

        if (!email) {
            alert('❌ Vui lòng nhập email!');
            return;
        }

        memberCount++;
        const container = document.getElementById('newMembersContainer');
        const div = document.createElement('div');
        div.id = `new-member-${memberCount}`;
        div.className = 'flex items-center justify-between bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-3';
        div.innerHTML = `
            <div class="flex items-center gap-3 flex-1">
                <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-xs text-gray-400">${email.charAt(0).toUpperCase()}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-white text-sm font-medium truncate">${email}</p>
                    <span class="text-xs text-blue-400">${role}</span>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <select class="bg-gray-700 border border-gray-600 rounded px-2 py-1 text-white text-xs" onchange="updateMemberRole(${memberCount}, this.value)">
                    @foreach(['manager','developer','designer','tester','viewer'] as $role)
                        <option value="{{ $role }}" ${role === '${role}' ? 'selected' : ''}>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
                <button type="button" onclick="removeNewMember(${memberCount})" class="text-red-400 hover:text-red-300 text-xs px-2 py-1 rounded transition">✕</button>
            </div>
            <input type="hidden" name="new_members[${memberCount}][email]" value="${email}">
            <input type="hidden" name="new_members[${memberCount}][role]" id="new-member-role-${memberCount}" value="${role}">
        `;
        container.appendChild(div);
        document.getElementById('newMemberEmail').value = '';
    }

    function updateMemberRole(count, role) {
        document.getElementById(`new-member-role-${count}`).value = role;
    }

    function removeNewMember(count) {
        document.getElementById(`new-member-${count}`).remove();
    }

    // 4️⃣ Remove existing member
    function removeMember(userId) {
        if (!confirm('Xóa thành viên này khỏi dự án?')) return;

        const form = document.getElementById('form-update-project');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remove_members[]';
        input.value = userId;
        form.appendChild(input);

        document.querySelector(`[data-member-id="${userId}"]`).remove();
    }

    // 5️⃣ Enter để add member
    document.getElementById('newMemberEmail').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addMember();
        }
    });
</script>
@endpush
