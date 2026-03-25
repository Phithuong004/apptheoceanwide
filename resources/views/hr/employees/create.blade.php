@extends('layouts.app')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('hr.employees.index', $workspace->slug) }}"
           class="text-gray-400 hover:text-white text-sm">← Quay lại</a>
        <h1 class="text-2xl font-bold text-white">Thêm nhân viên</h1>
    </div>

    @if($errors->any())
    <div class="mb-4 p-4 bg-red-900/50 border border-red-700 rounded-lg text-red-300 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('hr.employees.store', $workspace->slug) }}"
          method="POST" enctype="multipart/form-data"
          class="bg-gray-800 rounded-xl border border-gray-700 p-6 space-y-5">
        @csrf

        {{-- Thông tin cơ bản --}}
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Thông tin cơ bản</h2>

        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm text-gray-400 mb-1">Họ và tên <span class="text-red-400">*</span></label>
                <input type="text" name="full_name" value="{{ old('full_name') }}"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Email <span class="text-red-400">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Số điện thoại</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Giới tính</label>
                <select name="gender" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
                    <option value="">-- Chọn --</option>
                    <option value="male" @selected(old('gender')=='male')>Nam</option>
                    <option value="female" @selected(old('gender')=='female')>Nữ</option>
                    <option value="other" @selected(old('gender')=='other')>Khác</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Ngày sinh</label>
                <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="col-span-2">
                <label class="block text-sm text-gray-400 mb-1">Địa chỉ</label>
                <input type="text" name="address" value="{{ old('address') }}"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
            </div>
        </div>

        {{-- Công việc --}}
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider pt-2">Công việc</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Phòng ban</label>
                <select name="department_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
                    <option value="">-- Chọn --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id')==$dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Chức vụ</label>
                <select name="position_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
                    <option value="">-- Chọn --</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos->id }}" @selected(old('position_id')==$pos->id)>{{ $pos->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Quản lý trực tiếp</label>
                <select name="manager_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
                    <option value="">-- Không có --</option>
                    @foreach($managers as $mgr)
                        <option value="{{ $mgr->id }}" @selected(old('manager_id')==$mgr->id)>{{ $mgr->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Trạng thái</label>
                <select name="status" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
                    <option value="probation" @selected(old('status','probation')=='probation')>Thử việc</option>
                    <option value="active" @selected(old('status')=='active')>Đang làm</option>
                    <option value="inactive" @selected(old('status')=='inactive')>Tạm nghỉ</option>
                    <option value="terminated" @selected(old('status')=='terminated')>Đã nghỉ</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Ngày vào làm <span class="text-red-400">*</span></label>
                <input type="date" name="hired_date" value="{{ old('hired_date') }}"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Kết thúc thử việc</label>
                <input type="date" name="probation_end" value="{{ old('probation_end') }}"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
            </div>
        </div>

        {{-- Lương --}}
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider pt-2">Lương</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Lương cơ bản</label>
                <input type="number" name="base_salary" value="{{ old('base_salary', 0) }}"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Loại lương</label>
                <select name="salary_type" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
                    <option value="monthly" @selected(old('salary_type','monthly')=='monthly')>Tháng</option>
                    <option value="hourly" @selected(old('salary_type')=='hourly')>Giờ</option>
                    <option value="contract" @selected(old('salary_type')=='contract')>Hợp đồng</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">CMND/CCCD</label>
                <input type="text" name="id_card" value="{{ old('id_card') }}"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Mã số thuế</label>
                <input type="text" name="tax_code" value="{{ old('tax_code') }}"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Số tài khoản</label>
                <input type="text" name="bank_account" value="{{ old('bank_account') }}"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Ngân hàng</label>
                <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
            </div>
        </div>

        {{-- Khác --}}
        <div>
            <label class="block text-sm text-gray-400 mb-1">Kỹ năng</label>
            <textarea name="skills" rows="2"
                      class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">{{ old('skills') }}</textarea>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Ghi chú</label>
            <textarea name="notes" rows="2"
                      class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">{{ old('notes') }}</textarea>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Ảnh đại diện</label>
            <input type="file" name="avatar" accept="image/*"
                   class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm">
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('hr.employees.index', $workspace->slug) }}"
               class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm">Hủy</a>
            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                Lưu nhân viên
            </button>
        </div>
    </form>
</div>
@endsection
