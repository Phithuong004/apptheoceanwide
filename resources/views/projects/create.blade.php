@extends('layouts.app')
@section('title', 'Tạo dự án')
@section('content')

    <div class="max-w-2xl mx-auto">
        <h2 class="text-white text-xl font-bold mb-6">Tạo dự án mới</h2>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <form method="POST" action="{{ route('projects.store', $workspace->slug) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-gray-400 text-sm">Tên dự án *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full mt-1 bg-gray-800 text-white rounded-lg px-4 py-2.5 border border-gray-700 focus:border-blue-500 focus:outline-none">
                    @error('name')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="text-gray-400 text-sm">Mô tả</label>
                    <textarea name="description" rows="3"
                        class="w-full mt-1 bg-gray-800 text-white rounded-lg px-4 py-2.5 border border-gray-700 focus:border-blue-500 focus:outline-none">{{ old('description') }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-gray-400 text-sm">Ngày bắt đầu</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}"
                            class="w-full mt-1 bg-gray-800 text-white rounded-lg px-4 py-2.5 border border-gray-700 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-gray-400 text-sm">Deadline</label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}"
                            class="w-full mt-1 bg-gray-800 text-white rounded-lg px-4 py-2.5 border border-gray-700 focus:border-blue-500 focus:outline-none">
                    </div>
                </div>
                {{-- Type + Visibility (required) --}}
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Loại dự án</label>
                        <select name="type"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500">
                            <option value="scrum">Scrum</option>
                            <option value="kanban">Kanban</option>
                            <option value="waterfall">Waterfall</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Hiển thị</label>
                        <select name="visibility"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500">
                            <option value="team">Team</option>
                            <option value="private">Private</option>
                            <option value="public">Public</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 justify-end pt-2">
                    <a href="{{ route('projects.index', $workspace->slug) }}"
                        class="text-gray-400 hover:text-white px-4 py-2 text-sm">Huỷ</a>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm">Tạo dự án</button>
                </div>
            </form>
        </div>
    </div>
@endsection
