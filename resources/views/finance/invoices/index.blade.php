@extends('layouts.app')
@section('title', 'Invoices')

@section('content')
    <div class="max-w-7xl mx-auto p-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Invoices</h1>
                <p class="text-gray-400 text-sm mt-1">{{ $workspace->name }}</p>
            </div>
            <div class="flex items-center gap-2">
                {{-- Bulk Delete Bar (ẩn mặc định) --}}
                <div id="bulk-bar" class="hidden items-center gap-2">
                    <span id="bulk-count" class="text-sm text-gray-400">0 đã chọn</span>
                    <button onclick="confirmBulkDelete()"
                        class="flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-3 py-2 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Xóa đã chọn
                    </button>
                    <button onclick="clearSelection()"
                        class="text-sm text-gray-400 hover:text-white border border-white/10 px-3 py-2 rounded-lg transition">
                        Bỏ chọn
                    </button>
                </div>

                <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
                    class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tạo Invoice
                </button>
            </div>
        </div>

        {{-- Flash message --}}
        @if (session('success'))
            <div class="mb-4 bg-green-500/20 border border-green-500/40 text-green-400 text-sm px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-[#1e1e2e] border border-white/10 rounded-xl p-5">
                <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Chưa thanh toán</p>
                <p class="text-2xl font-bold text-white">${{ number_format($stats['total_outstanding'], 0, ',', '.') }}</p>
                <p class="text-yellow-400 text-xs mt-1">Sent + Partial</p>
            </div>
            <div class="bg-[#1e1e2e] border border-white/10 rounded-xl p-5">
                <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Đã thu tháng này</p>
                <p class="text-2xl font-bold text-white">${{ number_format($stats['total_paid_month'], 0, ',', '.') }}</p>
                <p class="text-green-400 text-xs mt-1">Tháng {{ now()->month }}/{{ now()->year }}</p>
            </div>
            <div class="bg-[#1e1e2e] border border-white/10 rounded-xl p-5">
                <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Quá hạn</p>
                <p class="text-2xl font-bold text-red-400">{{ $stats['overdue_count'] }}</p>
                <p class="text-red-400/60 text-xs mt-1">Invoice cần xử lý</p>
            </div>
        </div>


        {{-- Table --}}
        <div class="bg-[#1e1e2e] border border-white/10 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10 text-gray-400 text-xs uppercase tracking-wider">
                        {{-- Checkbox chọn tất cả --}}
                        <th class="px-5 py-3 w-10">
                            <input type="checkbox" id="check-all" onclick="toggleAll(this)"
                                class="w-4 h-4 rounded border-white/20 bg-transparent accent-indigo-500 cursor-pointer">
                        </th>
                        <th class="text-left px-5 py-3">Invoice #</th>
                        <th class="text-left px-5 py-3">Khách hàng</th>
                        <th class="text-left px-5 py-3">Project</th>
                        <th class="text-left px-5 py-3">Ngày phát</th>
                        <th class="text-left px-5 py-3">Hạn TT</th>
                        <th class="text-right px-5 py-3">Tổng tiền</th>
                        <th class="text-center px-5 py-3">Trạng thái</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-white/5 transition group" data-id="{{ $invoice->id }}">
                            <td class="px-5 py-4">
                                <input type="checkbox" value="{{ $invoice->id }}" onchange="updateSelection()"
                                    class="row-check w-4 h-4 rounded border-white/20 bg-transparent accent-indigo-500 cursor-pointer">
                            </td>
                            <td class="px-5 py-4 font-mono text-indigo-400 font-medium">
                                #{{ $invoice->invoice_number }}
                            </td>
                            <td class="px-5 py-4">
                                @if ($invoice->client)
                                    <div class="flex items-center gap-3">
                                        {{-- Avatar --}}
                                        <div
                                            class="w-10 h-10 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                            @if ($invoice->client->avatar)
                                                <img src="{{ $invoice->client->avatar }}"
                                                    alt="{{ $invoice->client->name }}"
                                                    class="w-full h-full object-cover rounded-full">
                                            @else
                                                <span class="text-white font-semibold text-sm uppercase">
                                                    {{ substr($invoice->client->name, 0, 2) }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Tên và Email --}}
                                        <div class="min-w-0 flex-1">
                                            <div class="text-white font-medium truncate">{{ $invoice->client->name }}</div>
                                            <div class="text-gray-400 text-xs truncate">{{ $invoice->client->email }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-gray-300">{{ $invoice->project?->name ?? '—' }}</td>
                            <td class="px-5 py-4 text-gray-400">{{ $invoice->issue_date?->format('d/m/Y') }}</td>
                            <td
                                class="px-5 py-4 text-gray-400 {{ $invoice->status === 'overdue' ? 'text-red-400 font-medium' : '' }}">
                                {{ $invoice->due_date?->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4 text-right text-white font-semibold">
                                ${{ number_format($invoice->total, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php
                                    $statusMap = [
                                        'draft' => ['label' => 'Draft', 'class' => 'bg-gray-500/20 text-gray-400'],
                                        'sent' => ['label' => 'Sent', 'class' => 'bg-blue-500/20 text-blue-400'],
                                        'partial' => [
                                            'label' => 'Partial',
                                            'class' => 'bg-yellow-500/20 text-yellow-400',
                                        ],
                                        'paid' => ['label' => 'Paid', 'class' => 'bg-green-500/20 text-green-400'],
                                        'overdue' => ['label' => 'Overdue', 'class' => 'bg-red-500/20 text-red-400'],
                                    ];
                                    $s = $statusMap[$invoice->status] ?? [
                                        'label' => $invoice->status,
                                        'class' => 'bg-gray-500/20 text-gray-400',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $s['class'] }}">
                                    {{ $s['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('finance.invoices.show', [$workspace->slug, $invoice->id]) }}"
                                    class="text-gray-500 hover:text-indigo-400 transition text-xs font-medium opacity-0 group-hover:opacity-100">
                                    Xem →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-16 text-center text-gray-500">
                                <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Chưa có invoice nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($invoices->hasPages())
                <div class="px-5 py-4 border-t border-white/10">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Form ẩn để submit bulk delete --}}
    <form id="bulk-delete-form" action="{{ route('finance.invoices.bulkDelete', $workspace->slug) }}" method="POST"
        class="hidden">
        @csrf
        @method('DELETE')
        <div id="bulk-ids-container"></div>
    </form>

    {{-- Modal xác nhận xóa --}}
    <div id="modal-confirm-delete"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-[#1e1e2e] border border-white/10 rounded-2xl w-full max-w-sm shadow-2xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-semibold">Xác nhận xóa</h3>
                    <p id="confirm-delete-text" class="text-gray-400 text-sm mt-0.5"></p>
                </div>
            </div>
            <div class="flex gap-3 justify-end">
                <button onclick="document.getElementById('modal-confirm-delete').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-gray-400 hover:text-white border border-white/10 rounded-lg transition">
                    Hủy
                </button>
                <button onclick="submitBulkDelete()"
                    class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                    Xóa
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Tạo Invoice --}}
    <div id="modal-create"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-[#1e1e2e] border border-white/10 rounded-2xl w-full max-w-2xl shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
                <h2 class="text-white font-semibold text-lg">Tạo Invoice mới</h2>
                <button onclick="document.getElementById('modal-create').classList.add('hidden')"
                    class="text-gray-500 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('finance.invoices.store', $workspace->slug) }}" method="POST" class="p-6 space-y-5">
                @csrf

                {{-- Client + Project + Due Date --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Client --}}
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Khách hàng <span
                                class="text-red-400">*</span></label>
                        <select name="client_id" id="client-select" required
                            class="w-full bg-[#13131f] border border-white/10 text-white text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:border-indigo-500 transition">
                            <option value="">-- Chọn khách hàng --</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}"
                                    {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Project --}}
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Project</label>
                        <select name="project_id" id="project-select"
                            class="w-full bg-[#13131f] border border-white/10 text-white text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:border-indigo-500 transition">
                            <option value="">-- Chọn project --</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" data-client="{{ $project->client_id }}"
                                    {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Due Date --}}
                    <div>
                        <label class="block text-xs text-gray-400 mb-1.5">Hạn thanh toán <span
                                class="text-red-400">*</span></label>
                        <input type="date" name="due_date" required min="{{ now()->addDay()->toDateString() }}"
                            class="w-full bg-[#13131f] border border-white/10 text-white text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:border-indigo-500 transition">
                    </div>
                </div>

                {{-- Line Items --}}
                <div>
                    <label class="block text-xs text-gray-400 mb-2">Danh mục dịch vụ <span
                            class="text-red-400">*</span></label>
                    <div id="items-container" class="space-y-2">
                        <div class="items-row grid grid-cols-12 gap-2 items-center">
                            <input type="text" name="items[0][description]" placeholder="Mô tả" required
                                class="col-span-6 bg-[#13131f] border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-indigo-500 transition">
                            <input type="number" name="items[0][quantity]" placeholder="SL" min="0.1"
                                step="0.1" required
                                class="col-span-2 bg-[#13131f] border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-indigo-500 transition">
                            <input type="number" name="items[0][unit_price]" placeholder="Đơn giá" min="0"
                                required
                                class="col-span-3 bg-[#13131f] border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-indigo-500 transition">
                            <div class="col-span-1"></div>
                        </div>
                    </div>
                    <button type="button" onclick="addItem()"
                        class="mt-2 text-xs text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Thêm dòng
                    </button>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-400 hover:text-white border border-white/10 rounded-lg transition">
                        Hủy
                    </button>
                    <button type="submit"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                        Tạo Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let itemIndex = 1;

        // ===== Filter Project theo Client =====
        document.getElementById('client-select').addEventListener('change', function() {
            const clientId = this.value;
            const projectSelect = document.getElementById('project-select');
            const allOptions = Array.from(projectSelect.children);

            // Reset project dropdown
            projectSelect.innerHTML = '<option value="">-- Chọn project --</option>';

            // Filter và thêm project của client đã chọn
            allOptions.forEach(option => {
                if (option.dataset.client == clientId) {
                    projectSelect.appendChild(option.cloneNode(true));
                }
            });

            // Clear project selection khi thay đổi client
            projectSelect.value = '';
        });

        // ===== Bulk Selection =====
        function getChecked() {
            return [...document.querySelectorAll('.row-check:checked')];
        }

        function updateSelection() {
            const checked = getChecked();
            const bar = document.getElementById('bulk-bar');
            const count = document.getElementById('bulk-count');
            const checkAll = document.getElementById('check-all');
            const total = document.querySelectorAll('.row-check').length;

            count.textContent = checked.length + ' đã chọn';
            bar.classList.toggle('hidden', checked.length === 0);
            bar.classList.toggle('flex', checked.length > 0);

            // Cập nhật trạng thái checkbox "chọn tất cả"
            checkAll.indeterminate = checked.length > 0 && checked.length < total;
            checkAll.checked = checked.length === total && total > 0;

            // Highlight hàng đang chọn
            document.querySelectorAll('.row-check').forEach(cb => {
                cb.closest('tr').classList.toggle('bg-indigo-500/5', cb.checked);
            });
        }

        function toggleAll(source) {
            document.querySelectorAll('.row-check').forEach(cb => {
                cb.checked = source.checked;
            });
            updateSelection();
        }

        function clearSelection() {
            document.getElementById('check-all').checked = false;
            document.querySelectorAll('.row-check').forEach(cb => cb.checked = false);
            updateSelection();
        }

        function confirmBulkDelete() {
            const checked = getChecked();
            if (!checked.length) return;

            document.getElementById('confirm-delete-text').textContent =
                `Bạn có chắc muốn xóa ${checked.length} invoice đã chọn? Hành động này không thể hoàn tác.`;
            document.getElementById('modal-confirm-delete').classList.remove('hidden');
        }

        function submitBulkDelete() {
            const checked = getChecked();
            const container = document.getElementById('bulk-ids-container');
            container.innerHTML = '';
            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });
            document.getElementById('bulk-delete-form').submit();
        }

        // ===== Add Invoice Item =====
        function addItem() {
            const container = document.getElementById('items-container');
            const i = itemIndex++;
            container.insertAdjacentHTML('beforeend', `
        <div class="items-row grid grid-cols-12 gap-2 items-center">
            <input type="text" name="items[${i}][description]" placeholder="Mô tả" required
                   class="col-span-6 bg-[#13131f] border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-indigo-500 transition">
            <input type="number" name="items[${i}][quantity]" placeholder="SL" min="0.1" step="0.1" required
                   class="col-span-2 bg-[#13131f] border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-indigo-500 transition">
            <input type="number" name="items[${i}][unit_price]" placeholder="Đơn giá" min="0" required
                   class="col-span-3 bg-[#13131f] border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-indigo-500 transition">
            <button type="button" onclick="this.closest('.items-row').remove()"
                    class="col-span-1 text-gray-600 hover:text-red-400 transition text-lg leading-none">×</button>
        </div>
    `);
        }
    </script>
@endsection
