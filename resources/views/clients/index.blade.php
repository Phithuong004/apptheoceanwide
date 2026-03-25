@extends('layouts.app')
@section('title', 'Khách hàng')

@section('content')
<div class="p-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Khách hàng</h1>
        <a href="{{ route('clients.create', $workspace->slug) }}"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
            + Thêm khách hàng
        </a>
    </div>

    {{-- Filter --}}
    <div class="mb-4">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Tìm tên, email, công ty..."
                class="bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm w-64">

            <select name="status"
                class="bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm">
                <option value="">Tất cả trạng thái</option>
                <option value="active"   @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                <option value="prospect" @selected(request('status') === 'prospect')>Prospect</option>
            </select>

            <button type="submit"
                class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm">
                Lọc
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl overflow-hidden border border-gray-700">
        <table class="w-full text-sm text-left text-gray-300">
            <thead class="bg-gray-700 text-gray-400 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Khách hàng</th>
                    <th class="px-4 py-3">Công ty</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Trạng thái</th>
                    <th class="px-4 py-3">Doanh thu</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($clients as $client)
                <tr class="hover:bg-gray-750">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $client->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($client->name).'&background=6366f1&color=fff' }}"
                                class="w-8 h-8 rounded-full">
                            <div class="font-medium text-white">{{ $client->name }}</div>
                        </div>
                    </td>
                    <td class="px-4 py-3">{{ $client->company ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $client->email ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $client->status === 'active'   ? 'bg-green-900 text-green-300'  :
                               ($client->status === 'inactive' ? 'bg-red-900 text-red-300'      :
                                'bg-yellow-900 text-yellow-300') }}">
                            {{ ucfirst($client->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        {{ number_format($client->total_billed, 0, ',', '.') }} {{ $client->currency }}
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('clients.show', [$workspace->slug, $client->id]) }}"
                            class="text-indigo-400 hover:text-indigo-300 text-xs">Chi tiết</a>
                        <a href="{{ route('clients.edit', [$workspace->slug, $client->id]) }}"
                            class="text-yellow-400 hover:text-yellow-300 text-xs">Sửa</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        Chưa có khách hàng nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $clients->links() }}
    </div>

</div>
@endsection
