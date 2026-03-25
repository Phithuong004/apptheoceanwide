@extends('layouts.app')
@section('title', 'Invoice #' . $invoice->invoice_number)

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- BACK --}}
        <div class="mb-8">
            <a href="{{ route('finance.invoices.index', $workspace->slug) }}"
               class="inline-flex items-center px-5 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white rounded-xl text-sm font-medium border border-gray-700 transition">
               ← Quay lại danh sách hóa đơn
            </a>
        </div>

        <div class="bg-gray-900/50 rounded-3xl border border-gray-700 shadow-xl overflow-hidden">

            {{-- HEADER --}}
            <div class="p-8 border-b border-gray-800">
                <div class="flex justify-between items-start gap-6">
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-white">
                            Invoice #{{ $invoice->invoice_number }}
                        </h1>
                        <span class="inline-block mt-2 px-3 py-1 text-sm rounded-full font-medium
                            @if ($invoice->status === 'paid') bg-green-600/20 text-green-400 border border-green-500/30
                            @elseif($invoice->status === 'overdue') bg-red-600/20 text-red-400 border border-red-500/30
                            @elseif($invoice->status === 'sent') bg-blue-600/20 text-blue-400 border border-blue-500/30
                            @else bg-gray-600/20 text-gray-300 border border-gray-600/30 @endif">
                            {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                        </span>
                    </div>

                    {{-- 🆕 ACTION BUTTONS --}}
                    <div class="flex gap-3 flex-shrink-0">
                        {{-- Print PDF --}}
                        <a href="{{ route('finance.invoices.pdf', [$workspace->slug, $invoice->id]) }}"
                           target="_blank" 
                           class="flex items-center gap-2 px-4 py-2.5 bg-green-600/80 hover:bg-green-500 text-white text-sm rounded-xl font-medium border border-green-500/50 transition shadow-lg hover:shadow-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            In PDF
                        </a>

                        {{-- Send Email --}}
                        @if(!in_array($invoice->status, ['paid', 'cancelled']))
                        <form method="POST" action="{{ route('finance.invoices.send', [$workspace->slug, $invoice->id]) }}" class="inline">
                            @csrf
                            <button type="submit"
                                    class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm rounded-xl font-medium border border-blue-500/50 transition shadow-lg hover:shadow-xl"
                                    onclick="return confirm('Gửi hóa đơn tới {{ $invoice->client->email }}?')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.27 4.84A2 2 0 0110.73 11H3v2h7.73a2 2 0 01.73.44l7.27 4.84A2 2 0 0121 16V4a2 2 0 00-3.73-.89L12.73 7.56a2 2 0 01-.73.44H3v2z"></path>
                                </svg>
                                Gửi Email
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <div class="mt-4 text-sm text-gray-400">
                    <div>Ngày xuất: <span class="text-white font-medium">{{ $invoice->issue_date->format('d/m/Y') }}</span></div>
                    <div>Hạn thanh toán: <span class="text-white font-medium">{{ $invoice->due_date->format('d/m/Y') }}</span></div>
                </div>
            </div>

            {{-- CLIENT & PROJECT INFO --}}
            <div class="p-8 border-b border-gray-800 bg-gray-800/30">
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-4">Thông tin khách hàng & dự án</p>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Client --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-300 mb-3 uppercase tracking-wider">Khách hàng</h3>
                        <div class="space-y-1">
                            <p class="text-2xl font-bold text-white">{{ $invoice->client->name }}</p>
                            @if($invoice->client->company)
                                <p class="text-lg text-gray-300">{{ $invoice->client->company }}</p>
                            @endif
                            <p class="text-gray-400">{{ $invoice->client->email }}</p>
                            @if($invoice->client->phone)
                                <p class="text-gray-400">ĐT: {{ $invoice->client->phone }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Project --}}
                    @if($invoice->project)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-300 mb-3 uppercase tracking-wider">Dự án</h3>
                        <div class="space-y-1">
                            <p class="text-xl font-semibold text-white">{{ $invoice->project->name }}</p>
                            @if($invoice->project->client)
                                <p class="text-gray-300">{{ $invoice->project->client->name }}</p>
                            @endif
                            @if($invoice->project->status)
                                <span class="inline-block px-3 py-1 text-xs rounded-full bg-blue-500/20 text-blue-400 font-medium border border-blue-500/30">
                                    {{ ucfirst($invoice->project->status) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ITEMS --}}
            <div class="p-8">
                <h3 class="text-2xl font-bold text-white mb-6">Chi tiết sản phẩm / dịch vụ</h3>

                @if($invoice->status === 'draft')
                    {{-- Draft: Editable items --}}
                    <button onclick="addLineItem()" class="mb-6 px-6 py-3 bg-emerald-600/80 hover:bg-emerald-500 text-white text-sm rounded-xl font-semibold border border-emerald-500/50 shadow-lg hover:shadow-xl transition">
                        ➕ Thêm dòng sản phẩm
                    </button>

                    <form method="POST" action="{{ route('finance.invoices.update-items', [$workspace->slug, $invoice->id]) }}">
                        @csrf @method('PATCH')
                        <div class="overflow-x-auto border border-gray-700 rounded-2xl shadow-inner">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-800/50 backdrop-blur-sm text-gray-300 sticky top-0">
                                    <tr>
                                        <th class="p-6 text-left w-2/5 font-semibold">Mô tả dịch vụ</th>
                                        <th class="p-6 text-right w-1/6 font-semibold">Số lượng</th>
                                        <th class="p-6 text-right w-1/6 font-semibold">Đơn giá</th>
                                        <th class="p-6 text-right w-1/6 font-semibold">Thành tiền</th>
                                        <th class="p-6 w-16"></th>
                                    </tr>
                                </thead>
                                <tbody id="lineItemsContainer">
                                    @forelse($invoice->items as $index => $item)
                                        <tr class="line-item hover:bg-gray-800/50 border-b border-gray-700/50 transition" data-index="{{ $index }}">
                                            <td class="p-6">
                                                <input type="text" name="items[{{ $index }}][description]" value="{{ $item->description }}"
                                                       class="w-full bg-gray-800/50 border border-gray-700/50 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-blue-500 focus:bg-gray-800 transition backdrop-blur-sm" required>
                                            </td>
                                            <td class="p-6 text-right">
                                                <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="1" step="0.01"
                                                       class="qty w-24 text-right bg-gray-800/50 border border-gray-700/50 rounded-xl px-3 py-3 text-white focus:border-blue-500 transition" required>
                                            </td>
                                            <td class="p-6 text-right">
                                                <input type="number" name="items[{{ $index }}][unit_price]" value="{{ $item->unit_price }}" min="0" step="0.01"
                                                       class="price w-28 text-right bg-gray-800/50 border border-gray-700/50 rounded-xl px-3 py-3 text-white focus:border-blue-500 transition" required>
                                            </td>
                                            <td class="p-6 text-right">
                                                <span class="total font-semibold text-white text-lg">${{ number_format($item->quantity * $item->unit_price, 0) }}</span>
                                            </td>
                                            <td class="p-6">
                                                <button type="button" onclick="removeLineItem(this)" class="w-full h-11 bg-red-600/80 hover:bg-red-500 text-white rounded-xl font-medium transition shadow-md hover:shadow-lg">
                                                    Xóa
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        {{-- Empty state --}}
                                        <tr class="line-item border-b border-gray-700/50" data-index="0">
                                            <td class="p-6">
                                                <input type="text" name="items[0][description]" placeholder="Nhập tên dịch vụ..."
                                                       class="w-full bg-gray-800/50 border border-gray-700/50 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-blue-500 transition" required>
                                            </td>
                                            <td class="p-6 text-right">
                                                <input type="number" name="items[0][quantity]" value="1" min="1" step="0.01"
                                                       class="qty w-24 text-right bg-gray-800/50 border border-gray-700/50 rounded-xl px-3 py-3 text-white focus:border-blue-500 transition" required>
                                            </td>
                                            <td class="p-6 text-right">
                                                <input type="number" name="items[0][unit_price]" value="0" min="0" step="0.01"
                                                       class="price w-28 text-right bg-gray-800/50 border border-gray-700/50 rounded-xl px-3 py-3 text-white focus:border-blue-500 transition" required>
                                            </td>
                                            <td class="p-6 text-right">
                                                <span class="total font-semibold text-white text-lg">$0</span>
                                            </td>
                                            <td class="p-6">
                                                <button type="button" onclick="removeLineItem(this)" class="w-full h-11 bg-red-600/80 hover:bg-red-500 text-white rounded-xl font-medium transition shadow-md hover:shadow-lg">
                                                    Xóa
                                                </button>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-8 text-right">
                            <button type="submit" class="px-10 py-4 bg-indigo-600 hover:bg-indigo-500 text-white text-lg rounded-2xl font-bold shadow-2xl hover:shadow-3xl transition-all duration-200">
                                💾 Lưu thay đổi
                            </button>
                        </div>
                    </form>
                @else
                    {{-- Non-draft: Read-only table --}}
                    <div class="overflow-x-auto border border-gray-700 rounded-2xl shadow-inner">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-800/50 backdrop-blur-sm text-gray-300">
                                <tr>
                                    <th class="p-6 text-left w-2/5 font-semibold">Mô tả dịch vụ</th>
                                    <th class="p-6 text-right w-1/6 font-semibold">Số lượng</th>
                                    <th class="p-6 text-right w-1/6 font-semibold">Đơn giá</th>
                                    <th class="p-6 text-right w-1/6 font-semibold">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                    <tr class="border-b border-gray-700/50 hover:bg-gray-800/30 transition">
                                        <td class="p-6 font-medium text-white">{{ $item->description }}</td>
                                        <td class="p-6 text-right text-gray-300">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="p-6 text-right text-gray-300">${{ number_format($item->unit_price, 0) }}</td>
                                        <td class="p-6 text-right font-semibold text-white">${{ number_format($item->quantity * $item->unit_price, 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- TOTAL --}}
            <div class="p-8 border-t border-gray-800 bg-gradient-to-r from-gray-900/50 to-gray-800/50">
                <div class="max-w-md ml-auto space-y-4 text-right">
                    <div class="flex justify-between text-lg text-gray-300 py-2">
                        <span>Subtotal</span>
                        <span id="liveSubtotal">${{ number_format($invoice->subtotal, 0) }}</span>
                    </div>
                    <div class="flex justify-between text-lg text-gray-300 py-2">
                        <span>VAT ({{ $invoice->tax_rate }}%)</span>
                        <span id="liveVatAmount">${{ number_format($invoice->tax_amount, 0) }}</span>
                    </div>
                    <div class="border-t-2 border-gray-700 pt-4">
                        <div class="flex justify-between text-2xl font-bold text-white">
                            <span>TỔNG CỘNG</span>
                            <span id="liveTotal" class="text-3xl">${{ number_format($invoice->total, 0) }}</span>
                        </div>
                        @if($invoice->amount_due < $invoice->total)
                            <div class="text-sm text-emerald-400 mt-1">Đã thanh toán: ${{ number_format($invoice->total - $invoice->amount_due, 0) }}</div>
                        @endif
                        <div class="text-2xl font-bold text-emerald-400 mt-2">
                            Còn nợ: <span id="amountDue">${{ number_format($invoice->amount_due, 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PAYMENT --}}
            @if(!in_array($invoice->status, ['paid', 'cancelled']))
            <div class="p-8 border-t border-gray-800 bg-gradient-to-r from-emerald-500/5 to-green-500/5">
                <div class="max-w-lg mx-auto">
                    <h4 class="text-lg font-semibold text-emerald-400 mb-4 text-center">Thanh toán hóa đơn</h4>
                    <form method="POST" action="{{ route('finance.invoices.mark-paid', [$workspace->slug, $invoice->id]) }}" class="flex items-end gap-4 p-6 bg-gray-900/50 rounded-2xl border border-emerald-500/20">
                        @csrf
                        <div class="flex-1">
                            <label class="block text-sm text-gray-300 mb-2">Số tiền thanh toán</label>
                            <input type="number" name="amount" step="0.01" min="0" max="{{ $invoice->amount_due }}"
                                   value="{{ $invoice->amount_due }}" placeholder="0.00"
                                   class="w-full bg-gray-900 border border-emerald-500/30 rounded-2xl px-6 py-4 text-right text-2xl font-bold text-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/50 transition-all">
                            <p class="text-xs text-gray-500 mt-1">Tối đa: ${{ number_format($invoice->amount_due, 0) }}</p>
                        </div>
                        <button type="submit" class="h-16 px-8 bg-emerald-600 hover:bg-emerald-500 text-white text-lg font-bold rounded-2xl shadow-2xl hover:shadow-3xl transition-all duration-200">
                            ✅ Đánh dấu đã trả
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            let itemIndex = {{ count($invoice->items) ?: 1 }};
            const TAX_RATE = {{ $invoice->tax_rate }}; // Lấy từ PHP (phải là số, ví dụ: 10)

            // Format tiền tệ
            const formatCurrency = (num) => `$${Math.round(num).toLocaleString('en-US')}`;

            // Tính tổng tất cả rows và cập nhật live totals
            function updateInvoiceTotals() {
                let subtotal = 0;
                document.querySelectorAll('.line-item').forEach(row => {
                    const qty = parseFloat(row.querySelector('.qty').value) || 0;
                    const price = parseFloat(row.querySelector('.price').value) || 0;
                    subtotal += qty * price;
                });

                const vatAmount = Math.round(subtotal * TAX_RATE / 100);
                const total = subtotal + vatAmount;

                document.getElementById('liveSubtotal').textContent = formatCurrency(subtotal);
                document.getElementById('liveVatAmount').textContent = formatCurrency(vatAmount);
                document.getElementById('liveTotal').textContent = formatCurrency(total);
            }

            // Cập nhật total của 1 row VÀ toàn bộ invoice
            function updateRowTotal(row) {
                const qty = parseFloat(row.querySelector('.qty').value) || 0;
                const price = parseFloat(row.querySelector('.price').value) || 0;
                row.querySelector('.total').textContent = formatCurrency(qty * price);
                updateInvoiceTotals();
            }

            function addLineItem() {
                const container = document.getElementById('lineItemsContainer');
                const row = document.createElement('tr');
                row.className = "line-item border-b border-gray-800";
                row.dataset.index = itemIndex;
                row.innerHTML = `
                <td class="p-4">
                    <input type="text" name="items[${itemIndex}][description]"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white" required>
                </td>
                <td class="p-4 text-right">
                    <input type="number" name="items[${itemIndex}][quantity]" value="1"
                           class="qty w-20 text-right bg-gray-800 border border-gray-700 rounded-lg px-2 py-2 text-white" required>
                </td>
                <td class="p-4 text-right">
                    <input type="number" name="items[${itemIndex}][unit_price]" value="0"
                           class="price w-24 text-right bg-gray-800 border border-gray-700 rounded-lg px-2 py-2 text-white" required>
                </td>
                <td class="p-4 text-right">
                    <span class="total font-semibold text-white">$0</span>
                </td>
                <td class="p-4 text-center">
                    <button type="button" onclick="removeLineItem(this)"
                            class="px-3 py-1 bg-red-600 hover:bg-red-500 text-white text-xs rounded">Xóa</button>
                </td>
            `;
                container.appendChild(row);

                // Attach listeners cho row mới
                row.querySelector('.qty').addEventListener('input', handleInputChange);
                row.querySelector('.price').addEventListener('input', handleInputChange);

                itemIndex++;
                updateInvoiceTotals();
            }

            function removeLineItem(btn) {
                if (document.querySelectorAll('.line-item').length > 1) {
                    btn.closest('tr').remove();
                    updateInvoiceTotals();
                } else {
                    alert('Phải có ít nhất 1 dòng!');
                }
            }

            function handleInputChange(e) {
                const row = e.target.closest('tr');
                updateRowTotal(row);
            }

            // Init khi load page
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.qty, .price').forEach(input => {
                    input.addEventListener('input', handleInputChange);
                });
                updateInvoiceTotals();
            });
        </script>
    @endpush
@endsection
