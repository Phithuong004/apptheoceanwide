@extends('layouts.app')

@section('content')
<div class="p-6 max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('hr.employees.index', $workspace->slug) }}"
               class="text-gray-400 hover:text-white text-sm">← Nhân sự</a>
            <h1 class="text-2xl font-bold text-white">{{ $employee->full_name }}</h1>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-medium
            {{ $employee->status === 'active' ? 'bg-green-900 text-green-300' : 'bg-yellow-900 text-yellow-300' }}">
            {{ ucfirst($employee->status) }}
        </span>
    </div>

    <div class="grid grid-cols-3 gap-6">
        {{-- Thông tin chính --}}
        <div class="col-span-2 space-y-4">
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-400 uppercase mb-3">Thông tin cơ bản</h2>
                <div class="grid grid-cols-2 gap-3 text-sm text-gray-300">
                    <div>
                        <div class="text-gray-400 text-xs">Mã nhân viên</div>
                        <div class="font-medium">{{ $employee->employee_code }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Email</div>
                        <div>{{ $employee->email }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Điện thoại</div>
                        <div>{{ $employee->phone ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Phòng ban</div>
                        <div>{{ $employee->department?->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Chức vụ</div>
                        <div>{{ $employee->position?->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Quản lý</div>
                        <div>{{ $employee->manager?->full_name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Ngày vào làm</div>
                        <div>{{ $employee->hired_date ? $employee->hired_date->format('d/m/Y') : '—' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Kết thúc thử việc</div>
                        <div>{{ $employee->probation_end ? $employee->probation_end->format('d/m/Y') : '—' }}</div>
                    </div>
                </div>
            </div>

            {{-- Thống kê tháng này --}}
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-400 uppercase mb-3">
                    Thống kê tháng {{ now()->format('m/Y') }}
                </h2>
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div class="bg-gray-900 rounded-lg p-3">
                        <div class="text-gray-400 text-xs mb-1">Ngày làm</div>
                        <div class="text-xl font-semibold text-green-300">
                            {{ $statsThisMonth['work_days'] ?? 0 }}
                        </div>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3">
                        <div class="text-gray-400 text-xs mb-1">Ngày nghỉ</div>
                        <div class="text-xl font-semibold text-red-300">
                            {{ $statsThisMonth['leave_days'] ?? 0 }}
                        </div>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-3">
                        <div class="text-gray-400 text-xs mb-1">Giờ OT</div>
                        <div class="text-xl font-semibold text-indigo-300">
                            {{ $statsThisMonth['overtime'] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 text-center">
                <img src="{{ $employee->user?->avatar_url ?? $employee->avatar ? asset('storage/'.$employee->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($employee->full_name).'&background=6366f1&color=fff' }}"
                     class="w-20 h-20 rounded-full mx-auto mb-3">
                <div class="text-sm text-gray-300">{{ $employee->full_name }}</div>
                <div class="text-xs text-gray-400">{{ $employee->position?->name ?? '—' }}</div>
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 text-sm text-gray-300 space-y-2">
                <h2 class="text-sm font-semibold text-gray-400 uppercase mb-2">Thông tin lương</h2>
                <div class="flex justify-between">
                    <span class="text-gray-400 text-xs">Lương cơ bản</span>
                    <span>{{ number_format($employee->base_salary, 0, ',', '.') }} đ</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400 text-xs">Loại lương</span>
                    <span>{{ $employee->salary_type }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
