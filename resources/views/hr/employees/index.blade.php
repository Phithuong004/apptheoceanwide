@extends('layouts.app')

@section('content')
    <div class="p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-white">Nhân sự</h1>
            <a href="{{ route('hr.employees.create', $workspace->slug) }}"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                + Thêm nhân viên
            </a>
        </div>

        {{-- Filter --}}
        <div class="mb-4 flex gap-3">
            <form method="GET" class="flex gap-3 w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm tên, email..."
                    class="bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm w-64">

                <select name="department_id"
                    class="bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm">
                    <option value="">Tất cả phòng ban</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm">
                    Lọc
                </button>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-gray-800 rounded-xl overflow-hidden border border-gray-700">
            <table class="w-full text-sm text-left text-gray-300">
                <thead class="bg-gray-700 text-gray-400 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Nhân viên</th>
                        <th class="px-4 py-3">Phòng ban</th>
                        <th class="px-4 py-3">Chức vụ</th>
                        <th class="px-4 py-3">Trạng thái</th>
                        <th class="px-4 py-3">Ngày vào</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($employees as $emp)
                        <tr class="hover:bg-gray-750">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $emp->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($emp->full_name) . '&background=6366f1&color=fff' }}"
                                        class="w-8 h-8 rounded-full">
                                    <div>
                                        <div class="font-medium text-white">{{ $emp->full_name }}</div>
                                        <div class="text-xs text-gray-400">{{ $emp->employee_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ $emp->department?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $emp->position?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $emp->status === 'active' ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300' }}">
                                    {{ $emp->status === 'active' ? 'Đang làm' : 'Đã nghỉ' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                {{ $emp->hired_date ? \Carbon\Carbon::parse($emp->hired_date)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">

                                <a href="{{ route('hr.employees.show', [$workspace->slug, $emp->id]) }}"
                                    class="text-indigo-400 hover:text-indigo-300 text-xs">
                                    Chi tiết
                                </a>

                                <a href="{{ route('hr.employees.edit', [$workspace->slug, $emp->id]) }}"
                                    class="text-yellow-400 hover:text-yellow-300 text-xs">
                                    Sửa
                                </a>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Chưa có nhân viên nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $employees->links() }}
        </div>
    </div>
@endsection
