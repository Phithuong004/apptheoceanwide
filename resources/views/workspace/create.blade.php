@extends('layouts.app')
@section('title', 'Tạo Workspace')

@section('content')
<div class="max-w-lg mx-auto">

    <div class="mb-6">
        <a href="{{ route('workspace.index') }}"
           class="text-gray-500 hover:text-white text-sm transition">← Về danh sách</a>
        <h1 class="text-white text-xl font-bold mt-1">Tạo workspace mới</h1>
    </div>

    @if($errors->any())
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 mb-4">
        @foreach($errors->all() as $error)
        <p class="text-red-400 text-sm">{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('workspace.store') }}"
          class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-5">
        @csrf

        {{-- Tên --}}
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Tên workspace *</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   placeholder="VD: Công ty ABC, Team Design..."
                   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 transition">
        </div>

        {{-- Slug --}}
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Đường dẫn (slug)</label>
            <div class="flex items-center gap-2">
                <span class="text-gray-500 text-sm">{{ url('/') }}/</span>
                <input type="text" name="slug" id="slugInput" value="{{ old('slug') }}"
                       placeholder="ten-cua-ban"
                       class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 transition">
            </div>
            <p class="text-gray-600 text-xs mt-1">Chỉ gồm chữ thường, số và dấu gạch ngang.</p>
        </div>

        {{-- Mô tả --}}
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Mô tả</label>
            <textarea name="description" rows="3" placeholder="Mô tả ngắn về workspace..."
                      class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 transition resize-none">{{ old('description') }}</textarea>
        </div>

        {{-- Màu --}}
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Màu sắc</label>
            <div class="flex items-center gap-3">
                @foreach(['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#ec4899','#06b6d4'] as $c)
                <label class="cursor-pointer">
                    <input type="radio" name="color" value="{{ $c }}" class="hidden color-radio"
                           {{ old('color', '#3b82f6') === $c ? 'checked' : '' }}>
                    <div class="w-7 h-7 rounded-full border-2 border-transparent transition color-swatch"
                         style="background-color: {{ $c }}"></div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium text-sm transition">
                Tạo workspace
            </button>
            <a href="{{ route('workspace.index') }}"
               class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition text-center">
                Hủy
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Auto-generate slug từ tên
document.querySelector('input[name="name"]').addEventListener('input', function () {
    const slug = this.value.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-');
    document.getElementById('slugInput').value = slug;
});

// Highlight màu được chọn
document.querySelectorAll('.color-radio').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.color-swatch').forEach(s => {
            s.style.borderColor = 'transparent';
        });
        this.nextElementSibling.style.borderColor = 'white';
    });

    // Init highlight
    if (radio.checked) {
        radio.nextElementSibling.style.borderColor = 'white';
    }
});
</script>
@endpush
