<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 14px; 
            line-height: 1.6; 
            color: #333; 
            background: white; 
        }
        .container { max-width: 800px; margin: 0 auto; padding: 40px 30px; }
        
        /* Header */
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            margin-bottom: 40px; 
            border-bottom: 4px solid #1e40af; 
            padding-bottom: 25px; 
        }
        .invoice-title { 
            font-size: 42px; 
            font-weight: bold; 
            color: #1e40af; 
            margin-bottom: 10px; 
        }
        .status { 
            display: inline-block; 
            padding: 10px 20px; 
            border-radius: 25px; 
            font-weight: bold; 
            font-size: 13px; 
            text-transform: uppercase; 
            letter-spacing: 1px;
        }
        .status.paid { background: #10b981; color: white; }
        .status.sent { background: #3b82f6; color: white; }
        .status.draft { background: #6b7280; color: white; }
        .status.overdue { background: #ef4444; color: white; }
        
        /* Dates */
        .dates { text-align: right; }
        .dates div { margin-bottom: 8px; font-size: 15px; }
        .date-value { font-weight: bold; color: #1f2937; font-size: 18px; }
        
        /* Info sections */
        .info-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 50px; 
            margin-bottom: 50px; 
        }
        .section-title { 
            font-size: 16px; 
            color: #374151; 
            margin-bottom: 15px; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            font-weight: 600; 
        }
        .client-name { 
            font-size: 28px; 
            font-weight: bold; 
            color: #1f2937; 
            margin-bottom: 10px; 
        }
        .client-detail { 
            color: #6b7280; 
            margin-bottom: 5px; 
        }
        
        /* Items table */
        table.items { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 40px 0; 
            background: white; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
        th { 
            background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%); 
            color: white; 
            padding: 18px 15px; 
            text-align: left; 
            font-weight: 600; 
            text-transform: uppercase; 
            font-size: 13px; 
            letter-spacing: 0.5px; 
        }
        td { 
            padding: 20px 15px; 
            border-bottom: 1px solid #e5e7eb; 
            vertical-align: top; 
        }
        .amount { text-align: right; font-weight: 600; color: #374151; }
        
        /* Totals */
        .totals { 
            width: 50%; 
            margin-left: auto; 
            margin-top: 30px; 
        }
        .total-row { 
            font-size: 18px; 
            font-weight: bold; 
            padding: 15px 0; 
            border-top: 3px solid #10b981; 
        }
        .grand-total { 
            color: #10b981 !important; 
            font-size: 28px !important; 
        }
        .amount-due { 
            color: #ef4444; 
            font-weight: bold; 
        }
        
        /* Footer */
        .footer { 
            margin-top: 60px; 
            padding-top: 40px; 
            border-top: 3px dashed #d1d5db; 
            text-align: center; 
            color: #6b7280; 
        }
        
        @page { margin: 20mm; }
        @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- HEADER -->
        <div class="header">
            <div>
                <div class="invoice-title">Invoice #{{ $invoice->invoice_number }}</div>
                <div class="status status-{{ $invoice->status }}">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</div>
            </div>
            
            <div class="dates">
                <div>Ngày xuất <span class="date-value">{{ $invoice->issue_date->format('d/m/Y') }}</span></div>
                <div>Hạn thanh toán <span class="date-value">{{ $invoice->due_date->format('d/m/Y') }}</span></div>
            </div>
        </div>

        <!-- CLIENT & PROJECT INFO -->
        <div class="info-grid">
            <div>
                <div class="section-title">Khách hàng</div>
                <div class="client-name">{{ $invoice->client->name }}</div>
                @if($invoice->client->company)
                    <div class="client-detail">{{ $invoice->client->company }}</div>
                @endif
                <div class="client-detail">{{ $invoice->client->email }}</div>
                @if($invoice->client->phone)
                    <div class="client-detail">ĐT: {{ $invoice->client->phone }}</div>
                @endif
            </div>

            @if($invoice->project)
            <div>
                <div class="section-title">Dự án</div>
                <div class="client-name">{{ $invoice->project->name }}</div>
                @if($invoice->project->client)
                    <div class="client-detail">Khách hàng: {{ $invoice->project->client->name }}</div>
                @endif
            </div>
            @endif
        </div>

        <!-- ITEMS TABLE -->
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 55%;">Mô tả sản phẩm / dịch vụ</th>
                    <th style="width: 12%; text-align: right;">SL</th>
                    <th style="width: 16%; text-align: right;">Đơn giá (USD)</th>
                    <th style="width: 17%; text-align: right;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="amount">{{ number_format($item->quantity, 2) }}</td>
                    <td class="amount">${{ number_format($item->unit_price, 0) }}</td>
                    <td class="amount">${{ number_format($item->quantity * $item->unit_price, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- TOTALS -->
        <table class="totals">
            <tr>
                <td style="width: 70%; padding-right: 25px;">Tạm tính</td>
                <td class="amount">${{ number_format($invoice->subtotal, 0) }}</td>
            </tr>
            <tr>
                <td style="width: 70%; padding-right: 25px;">Thuế VAT ({{ $invoice->tax_rate }}%)</td>
                <td class="amount">${{ number_format($invoice->tax_amount, 0) }}</td>
            </tr>
            <tr class="total-row">
                <td style="width: 70%; padding-right: 25px; padding-top: 15px;"><strong>TỔNG CỘNG</strong></td>
                <td class="grand-total" style="padding-top: 15px;">${{ number_format($invoice->total, 0) }}</td>
            </tr>
            @if(isset($invoice->amount_due) && $invoice->amount_due < $invoice->total)
            <tr>
                <td style="width: 70%; padding-right: 25px;">Số tiền còn lại</td>
                <td class="amount-due">${{ number_format($invoice->amount_due, 0) }}</td>
            </tr>
            @endif
        </table>

        <!-- FOOTER -->
        <div class="footer">
            <p style="font-size: 16px; margin-bottom: 15px; color: #1f2937;">
                <strong>Cảm ơn quý khách đã sử dụng dịch vụ!</strong>
            </p>
            <p>Vui lòng thanh toán trước hạn để tránh phí trễ hạn.</p>
            <p style="margin-top: 25px; font-size: 12px;">
                Hóa đơn này được tạo bởi <strong>{{ config('app.name') }}</strong><br>
                {{ config('app.url') }}
            </p>
        </div>

    </div>
</body>
</html>
