@extends('layouts.app')
@section('title', 'Thêm khách hàng')

@section('content')
<div class="max-w-3xl mx-auto p-6">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('clients.index', $workspace->slug) }}"
           class="text-gray-400 hover:text-white transition">
            ← Quay lại
        </a>
        <span class="text-gray-600">/</span>
        <h1 class="text-xl font-bold text-white">Thêm khách hàng mới</h1>
    </div>

    @if($errors->any())
    <div class="mb-4 p-4 bg-red-900/50 border border-red-700 rounded-xl text-red-300 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('clients.store', $workspace->slug) }}"
          method="POST" enctype="multipart/form-data">
        @csrf

        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-5">

            {{-- Avatar --}}
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-gray-700 flex items-center justify-center text-2xl text-gray-400 overflow-hidden"
                     id="avatar-preview">
                    👤
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Ảnh đại diện</label>
                    <input type="file" name="avatar" accept="image/*"
                           onchange="previewAvatar(this)"
                           class="text-xs text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-gray-700 file:text-white file:text-xs hover:file:bg-gray-600 cursor-pointer">
                </div>
            </div>

            <hr class="border-gray-800">

            {{-- Tên + Công ty --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Tên khách hàng <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none transition"
                           placeholder="Nguyễn Văn A" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Công ty</label>
                    <input type="text" name="company" value="{{ old('company') }}"
                           class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none transition"
                           placeholder="Tên công ty">
                </div>
            </div>

            {{-- Email + Phone --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none transition"
                           placeholder="email@example.com">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Số điện thoại</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none transition"
                           placeholder="+84 xxx xxx xxx">
                </div>
            </div>

            {{-- Website + Country --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Website</label>
                    <input type="url" name="website" value="{{ old('website') }}"
                           class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none transition"
                           placeholder="https://example.com">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Quốc gia</label>
                    <input type="text" name="country" value="{{ old('country') }}"
                           class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none transition"
                           placeholder="Việt Nam">
                </div>
            </div>

            {{-- Status + Currency --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Trạng thái</label>
                    <select name="status"
                            class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none transition">
                        <option value="active"   @selected(old('status') === 'active')>Active</option>
                        <option value="prospect" @selected(old('status') === 'prospect')>Prospect</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Tiền tệ</label>
                    <select name="currency"
                            class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none transition">
                        <option value="VND" @selected(old('currency','VND') === 'VND')>VND</option>
                        <option value="USD" @selected(old('currency') === 'USD')>USD</option>
                        <option value="EUR" @selected(old('currency') === 'EUR')>EUR</option>
                    </select>
                </div>
            </div>

            {{-- Address --}}
            <div>
                <label class="block text-xs text-gray-400 mb-1">Địa chỉ</label>
                <textarea name="address" rows="2"
                          class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none transition resize-none"
                          placeholder="Địa chỉ đầy đủ...">{{ old('address') }}</textarea>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs text-gray-400 mb-1">Ghi chú</label>
                <textarea name="notes" rows="3"
                          class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none transition resize-none"
                          placeholder="Ghi chú nội bộ về khách hàng...">{{ old('notes') }}</textarea>
            </div>

        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3 mt-4">
            <a href="{{ route('clients.index', $workspace->slug) }}"
               class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded-lg transition">
                Huỷ
            </a>
            <button type="submit"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Lưu khách hàng
            </button>
        </div>

    </form>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('avatar-preview');
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
