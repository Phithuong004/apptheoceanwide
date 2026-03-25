@extends('layouts.app')
@section('title', $client->name)

@section('content')
    <div class="p-6 space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="{{ $client->avatar_url }}" class="w-16 h-16 rounded-full">
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $client->name }}</h1>
                    <p class="text-gray-400">{{ $client->company ?? '—' }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('clients.edit', [$workspace->slug, $client->id]) }}"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg">
                    Sửa
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Stats --}}
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 text-center">
                <p class="text-2xl font-bold text-white">{{ $stats['total_projects'] }}</p>
                <p class="text-gray-400 text-sm">Tổng dự án</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 text-center">
                <p class="text-2xl font-bold text-white">{{ $stats['active_projects'] }}</p>
                <p class="text-gray-400 text-sm">Dự án đang hoạt động</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 text-center">
                <p class="text-2xl font-bold text-white">{{ number_format($stats['total_billed'], 0, ',', '.') }}
                    {{ $client->currency }}</p>
                <p class="text-gray-400 text-sm">Đã thu</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 text-center">
                <p class="text-2xl font-bold text-red-400">{{ number_format($stats['outstanding'], 0, ',', '.') }}
                    {{ $client->currency }}</p>
                <p class="text-gray-400 text-sm">Còn nợ</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Thông tin liên hệ --}}
            <div class="lg:col-span-1 bg-gray-900 border border-gray-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-4">Thông tin liên hệ</h2>

                @if ($client->email)
                    <div class="flex items-center gap-3 mb-3 p-3 bg-gray-800 rounded-lg">
                        <div class="w-10 h-10 bg-blue-900/50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.27 4.84A2 2 0 0012 11a2 2 0 012-2 2 2 0 001.73.84L21 8m-9 7v4a1 1 0 01-1 1H6a1 1 0 01-1-1v-4a1 1 0 011-1h6a1 1 0 011 1z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white text-sm">Email</p>
                            <p class="text-gray-400 text-xs">{{ $client->email }}</p>
                        </div>
                    </div>
                @endif

                @if ($client->phone)
                    <div class="flex items-center gap-3 mb-3 p-3 bg-gray-800 rounded-lg">
                        <div class="w-10 h-10 bg-green-900/50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white text-sm">Điện thoại</p>
                            <p class="text-gray-400 text-xs">{{ $client->phone }}</p>
                        </div>
                    </div>
                @endif

                @if ($client->website)
                    <div class="flex items-center gap-3 mb-3 p-3 bg-gray-800 rounded-lg">
                        <div class="w-10 h-10 bg-purple-900/50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white text-sm">Website</p>
                            <a href="{{ $client->website }}" target="_blank"
                                class="text-indigo-400 text-xs hover:text-indigo-300">{{ $client->website }}</a>
                        </div>
                    </div>
                @endif

                @if ($client->primaryContact)
                    <div class="border-t border-gray-800 pt-4 mt-4">
                        <h3 class="text-sm font-semibold text-white mb-2">Liên hệ chính</h3>
                        <div class="flex items-center gap-3 p-3 bg-indigo-900/30 rounded-lg">
                            <div
                                class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-xs font-bold text-white">
                                {{ substr($client->primaryContact->name, 0, 2) }}
                            </div>
                            <div>
                                <p class="text-white font-medium">{{ $client->primaryContact->name }}</p>
                                @if ($client->primaryContact->email)
                                    <p class="text-xs text-gray-300">{{ $client->primaryContact->email }}</p>
                                @endif
                                @if ($client->primaryContact->position)
                                    <p class="text-xs text-gray-400">{{ $client->primaryContact->position }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Địa chỉ & Ghi chú --}}
            <div class="lg:col-span-1
                        bg-gray-900 border border-gray-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-4">Chi tiết</h2>

                @if ($client->address)
                    <div class="mb-4 p-3 bg-gray-800 rounded-lg">
                        <p class="text-xs text-gray-400 mb-1">Địa chỉ</p>
                        <p class="text-sm text-white">{{ $client->address }}</p>
                    </div>
                @endif

                @if ($client->country)
                    <div class="mb-4 p-3 bg-gray-800 rounded-lg">
                        <p class="text-xs text-gray-400 mb-1">Quốc gia</p>
                        <p class="text-sm text-white">{{ $client->country }}</p>
                    </div>
                @endif

                @if ($client->notes)
                    <div class="p-3 bg-gray-800 rounded-lg">
                        <p class="text-xs text-gray-400 mb-2">Ghi chú</p>
                        <p class="text-sm text-white whitespace-pre-wrap">{{ $client->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Dự án gần đây --}}
            <div class="lg:col-span-1 bg-gray-900 border border-gray-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-4 flex items-center justify-between">
                    Dự án
                    <span class="text-xs bg-gray-800 px-2 py-1 rounded-full">{{ $client->projects->count() }}</span>
                </h2>

                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @forelse($client->projects->take(5) as $project)
                        <a href="{{ route('projects.show', [$workspace->slug, $project->id]) }}"
                            class="flex items-center gap-3 p-3 bg-gray-800 rounded-lg hover:bg-gray-750 transition">
                            <div
                                class="w-2 h-10 bg-gradient-to-b {{ $project->status === 'active' ? 'from-green-400 to-green-600' : 'from-gray-500 to-gray-700' }} rounded">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-white font-medium truncate">{{ $project->name }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ $project->status }}</p>
                            </div>
                            @if ($project->activeSprint)
                                <span class="px-2 py-1 bg-blue-900/50 text-blue-300 text-xs rounded-full">
                                    Sprint
                                </span>
                            @endif
                        </a>
                    @empty
                        <p class="text-gray-500 text-sm text-center py-8">Chưa có dự án nào</p>
                    @endforelse

                    @if ($client->projects->count() > 5)
                        <a href="#" class="text-xs text-indigo-400 hover:text-indigo-300 block text-center py-2">
                            Xem tất cả {{ $client->projects->count() }} dự án
                        </a>
                    @endif
                </div>
            </div>

        </div>

        {{-- Hóa đơn gần đây --}}
        @if ($client->invoices->count() > 0)
            <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                <div class="bg-gray-800 px-6 py-4 border-b border-gray-700">
                    <h2 class="text-white font-semibold">Hóa đơn gần đây</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-300">
                        <thead class="text-xs uppercase text-gray-400 bg-gray-950">
                            <tr>
                                <th class="px-6 py-4">Số hóa đơn</th>
                                <th class="px-6 py-4">Ngày</th>
                                <th class="px-6 py-4">Số tiền</th>
                                <th class="px-6 py-4">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach ($client->invoices->take(10)->reverse() as $invoice)
                                <tr class="hover:bg-gray-950">
                                    <td class="px-6 py-4 font-mono text-sm">#{{ $invoice->number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ $invoice->created_at?->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 font-medium">
                                        {{ number_format($invoice->total ?? 0, 0, ',', '.') }} {{ $client->currency }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $invoice->status === 'paid'
                                    ? 'bg-green-900 text-green-300'
                                    : ($invoice->status === 'sent'
                                        ? 'bg-yellow-900 text-yellow-300'
                                        : ($invoice->status === 'partial'
                                            ? 'bg-blue-900 text-blue-300'
                                            : 'bg-gray-900 text-gray-300')) }}">
                                            {{ ucfirst($invoice->status ?? 'draft') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
