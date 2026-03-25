@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto p-6">

        <div class="flex items-center mb-6">
            <a href="{{ route('hr.employees.index', $workspace->slug) }}" class="text-gray-400 mr-4">
                ← Quay lại
            </a>

            <h1 class="text-2xl font-bold text-white">
                Chỉnh sửa nhân viên
            </h1>
        </div>


        <form method="POST" action="{{ route('hr.employees.update', [$workspace->slug, $employee->id]) }}"
            enctype="multipart/form-data">

            @csrf
            @method('PUT')


            <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 space-y-6">

                <h2 class="text-sm font-semibold text-gray-400 tracking-wider">
                    THÔNG TIN CƠ BẢN
                </h2>


                <div class="grid grid-cols-2 gap-4">

                    <div class="col-span-2">
                        <label class="text-sm text-gray-300">Họ và tên *</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $employee->full_name) }}"
                            class="input">
                    </div>


                    <div>
                        <label>Email *</label>
                        <input type="email" name="email" value="{{ old('email', $employee->email) }}" class="input">
                    </div>

                    <div>
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="input">
                    </div>


                    <div>
                        <label>Giới tính</label>

                        <select name="gender" class="input">
                            <option value="">-- Chọn --</option>
                            <option value="male" @selected($employee->gender == 'male')>Nam</option>
                            <option value="female" @selected($employee->gender == 'female')>Nữ</option>
                        </select>

                    </div>

                    <div>
                        <label>Ngày sinh</label>

                        <input type="date" name="birth_date" value="{{ $employee->birth_date?->format('Y-m-d') }}"
                            class="input">
                    </div>


                    <div class="col-span-2">
                        <label>Địa chỉ</label>

                        <input type="text" name="address" value="{{ old('address', $employee->address) }}" class="input">
                    </div>

                </div>
            </div>


            <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 mt-6 space-y-6">

                <h2 class="text-sm font-semibold text-gray-400 tracking-wider">
                    CÔNG VIỆC
                </h2>

                <div class="grid grid-cols-2 gap-4">

                    <div>
                        <label>Phòng ban</label>

                        <select name="department_id" class="input">

                            <option value="">-- Chọn --</option>

                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" @selected($employee->department_id == $dept->id)>
                                    {{ $dept->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>


                    <div>
                        <label>Chức vụ</label>

                        <select name="position_id" class="input">

                            <option value="">-- Chọn --</option>

                            @foreach ($positions as $pos)
                                <option value="{{ $pos->id }}" @selected($employee->position_id == $pos->id)>
                                    {{ $pos->name }}
                                </option>
                            @endforeach

                        </select>

                    </div>


                    <div>
                        <label>Quản lý trực tiếp</label>

                        <select name="manager_id" class="input">

                            <option value="">-- Không có --</option>

                            @foreach ($managers as $m)
                                <option value="{{ $m->id }}" @selected($employee->manager_id == $m->id)>
                                    {{ $m->full_name }}
                                </option>
                            @endforeach

                        </select>

                    </div>


                    <div>
                        <label>Trạng thái</label>

                        <select name="status" class="input">

                            <option value="active" @selected($employee->status == 'active')>
                                Đang làm
                            </option>

                            <option value="probation" @selected($employee->status == 'probation')>
                                Thử việc
                            </option>

                            <option value="terminated" @selected($employee->status == 'terminated')>
                                Đã nghỉ
                            </option>

                        </select>

                    </div>


                    <div>
                        <label>Ngày vào làm *</label>

                        <input type="date" name="hired_date" value="{{ $employee->hired_date?->format('Y-m-d') }}"
                            class="input">

                    </div>

                    <div>
                        <label>Kết thúc thử việc</label>

                        <input type="date" name="probation_end" value="{{ $employee->probation_end?->format('Y-m-d') }}"
                            class="input">

                    </div>

                </div>
            </div>


            <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 mt-6 space-y-6">

                <h2 class="text-sm font-semibold text-gray-400 tracking-wider">
                    LƯƠNG
                </h2>


                <div class="grid grid-cols-2 gap-4">

                    <div>
                        <label>Lương cơ bản</label>

                        <input type="number" name="base_salary" value="{{ $employee->base_salary }}" class="input">
                    </div>


                    <div>
                        <label>Loại lương</label>

                        <select name="salary_type" class="input">

                            <option value="monthly" @selected($employee->salary_type == 'month')>
                                Tháng
                            </option>

                            <option value="daily" @selected($employee->salary_type == 'day')>
                                Ngày
                            </option>

                            <option value="hourly" @selected($employee->salary_type == 'hour')>
                                Giờ
                            </option>

                        </select>

                    </div>


                    <div>
                        <label>CMND / CCCD</label>

                        <input type="text" name="id_card" value="{{ $employee->id_card }}" class="input">
                    </div>


                    <div>
                        <label>Mã số thuế</label>

                        <input type="text" name="tax_code" value="{{ $employee->tax_code }}" class="input">
                    </div>


                    <div>
                        <label>Số tài khoản</label>

                        <input type="text" name="bank_account" value="{{ $employee->bank_account }}" class="input">
                    </div>


                    <div>
                        <label>Ngân hàng</label>

                        <input type="text" name="bank_name" value="{{ $employee->bank_name }}" class="input">
                    </div>

                </div>


                <div>

                    <label>Kỹ năng</label>

                    <textarea name="skills" class="input h-24">{{ json_encode($employee->skills) }}</textarea>

                </div>


                <div>

                    <label>Ghi chú</label>

                    <textarea name="notes" class="input h-24">{{ $employee->notes }}</textarea>

                </div>


                <div>

                    <label>Ảnh đại diện</label>

                    <input type="file" name="avatar" class="input">

                    @if ($employee->avatar)
                        <img src="{{ $employee->avatar_url }}" class="w-16 h-16 rounded-full mt-2">
                    @endif

                </div>

            </div>


            <div class="mt-6 flex justify-end">

                <button class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                    Cập nhật nhân viên
                </button>

            </div>

        </form>
    </div>


    <style>
        .input {
            width: 100%;
            background: #0f172a;
            border: 1px solid #374151;
            padding: 10px;
            border-radius: 8px;
            color: white;
        }
    </style>
@endsection
