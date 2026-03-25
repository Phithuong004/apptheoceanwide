@extends('layouts.app')
@section('title', 'Chỉnh sửa khách hàng')

@section('content')
<div class="max-w-3xl mx-auto p-6">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('clients.show', [$workspace->slug, $client->id]) }}"
           class="text-gray-400 hover:text-white transition">
            ← Quay lại
        </a>
        <span class="text-gray-600">/</span>
        <h1 class="text-xl font-bold text-white">
            Chỉnh sửa: {{ $client->name }}
        </h1>
    </div>

    {{-- Success --}}
    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-900 border border-green-700 text-green-300 text-sm rounded-xl">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- Errors --}}
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-900/50 border border-red-700 rounded-xl text-red-300 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM UPDATE --}}
    <form action="{{ route('clients.update', [$workspace->slug, $client->id]) }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf
        @method('PUT')

        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-5">

            {{-- Avatar --}}
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-gray-700 overflow-hidden flex items-center justify-center"
                     id="avatar-preview">

                    @if ($client->avatar)
                        <img src="{{ asset('storage/'.$client->avatar) }}"
                             class="w-full h-full object-cover">
                    @else
                        <img src="{{ $client->avatar_url }}"
                             class="w-full h-full object-cover">
                    @endif

                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">
                        Ảnh đại diện
                    </label>

                    <input type="file"
                           name="avatar"
                           accept="image/*"
                           onchange="previewAvatar(this)"
                           class="text-xs text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-gray-700 file:text-white hover:file:bg-gray-600 cursor-pointer">

                    <p class="text-xs text-gray-600 mt-1">
                        Để trống nếu không muốn thay đổi
                    </p>
                </div>
            </div>

            <hr class="border-gray-800">

            {{-- Name + Company --}}
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-xs text-gray-400 mb-1">
                        Tên khách hàng <span class="text-red-400">*</span>
                    </label>

                    <input type="text"
                           name="name"
                           value="{{ old('name',$client->name) }}"
                           required
                           class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none">
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">
                        Công ty
                    </label>

                    <input type="text"
                           name="company"
                           value="{{ old('company',$client->company) }}"
                           class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none">
                </div>

            </div>

            {{-- Email + Phone --}}
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-xs text-gray-400 mb-1">
                        Email
                    </label>

                    <input type="email"
                           name="email"
                           value="{{ old('email',$client->email) }}"
                           class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none">
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">
                        Số điện thoại
                    </label>

                    <input type="text"
                           name="phone"
                           value="{{ old('phone',$client->phone) }}"
                           class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none">
                </div>

            </div>

            {{-- Website + Country --}}
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-xs text-gray-400 mb-1">
                        Website
                    </label>

                    <input type="url"
                           name="website"
                           value="{{ old('website',$client->website) }}"
                           class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none">
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">
                        Quốc gia
                    </label>

                    <input type="text"
                           name="country"
                           value="{{ old('country',$client->country) }}"
                           class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none">
                </div>

            </div>

            {{-- Status + Currency --}}
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-xs text-gray-400 mb-1">
                        Trạng thái
                    </label>

                    <select name="status"
                            class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none">

                        <option value="active" @selected(old('status',$client->status)=='active')>Active</option>
                        <option value="prospect" @selected(old('status',$client->status)=='prospect')>Prospect</option>
                        <option value="inactive" @selected(old('status',$client->status)=='inactive')>Inactive</option>

                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">
                        Tiền tệ
                    </label>

                    <select name="currency"
                            class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none">

                        <option value="VND" @selected(old('currency',$client->currency)=='VND')>VND</option>
                        <option value="USD" @selected(old('currency',$client->currency)=='USD')>USD</option>
                        <option value="EUR" @selected(old('currency',$client->currency)=='EUR')>EUR</option>

                    </select>
                </div>

            </div>

            {{-- Address --}}
            <div>
                <label class="block text-xs text-gray-400 mb-1">
                    Địa chỉ
                </label>

                <textarea name="address"
                          rows="2"
                          class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none resize-none">{{ old('address',$client->address) }}</textarea>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs text-gray-400 mb-1">
                    Ghi chú
                </label>

                <textarea name="notes"
                          rows="3"
                          class="w-full bg-gray-800 border border-gray-700 focus:border-indigo-500 text-white text-sm rounded-lg px-3 py-2 outline-none resize-none">{{ old('notes',$client->notes) }}</textarea>
            </div>

        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-end gap-3 mt-4">

            <a href="{{ route('clients.show', [$workspace->slug,$client->id]) }}"
               class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded-lg">
                Huỷ
            </a>

            <button type="submit"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
                Lưu thay đổi
            </button>

        </div>

    </form>

    {{-- FORM DELETE (tách riêng hoàn toàn) --}}
    <form action="{{ route('clients.destroy', [$workspace->slug,$client->id]) }}"
          method="POST"
          class="mt-6"
          onsubmit="return confirm('Xoá khách hàng này? Hành động không thể hoàn tác.')">

        @csrf
        @method('DELETE')

        <button type="submit"
                class="px-4 py-2 bg-red-900/50 hover:bg-red-900 text-red-400 hover:text-red-300 text-sm rounded-lg">
            🗑 Xoá khách hàng
        </button>

    </form>

</div>

<script>
function previewAvatar(input){
    if(input.files && input.files[0]){
        const reader = new FileReader();

        reader.onload = function(e){
            document.getElementById('avatar-preview').innerHTML =
                `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        }

        reader.readAsDataURL(input.files[0]);
    }
}
</script>

@endsection