@extends('layouts.app')
@section('title', 'Workspaces')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-white text-xl font-bold">Workspaces của bạn</h2>
        <a href="{{ route('workspace.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition">
            + Tạo workspace
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($workspaces ?? [] as $workspace)
        <a href="{{ route('dashboard', $workspace->slug) }}"
           class="bg-gray-900 border border-gray-800 rounded-xl p-5 hover:border-blue-500 transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold text-lg"
                     style="background-color: {{ $workspace->color ?? '#3b82f6' }}">
                    {{ strtoupper(substr($workspace->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="text-white font-semibold group-hover:text-blue-400 transition">{{ $workspace->name }}</h3>
                    <p class="text-gray-500 text-xs">{{ $workspace->members_count ?? 0 }} thành viên</p>
                </div>
            </div>
            <p class="text-gray-400 text-sm line-clamp-2">{{ $workspace->description ?? 'Chưa có mô tả.' }}</p>
        </a>
        @empty
        <div class="col-span-2 text-center py-16 text-gray-500">
            <p class="text-4xl mb-3">🏢</p>
            <p>Chưa có workspace nào.</p>
            <a href="{{ route('workspace.create') }}" class="text-blue-400 hover:underline text-sm mt-2 inline-block">
                Tạo workspace đầu tiên
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection
