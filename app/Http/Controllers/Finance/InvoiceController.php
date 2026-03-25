<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Workspace;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function index(Workspace $workspace)
    {
        $invoices = Invoice::where('workspace_id', $workspace->id)
            ->with(['client', 'project'])
            ->latest()->paginate(20);

        $stats = [
            'total_outstanding' => Invoice::where('workspace_id', $workspace->id)
                ->whereIn('status', ['sent', 'partial'])
                ->sum('total'),
            'total_paid_month'  => Invoice::where('workspace_id', $workspace->id)
                ->where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->sum('total'),
            'overdue_count'     => Invoice::where('workspace_id', $workspace->id)
                ->where('status', 'overdue')
                ->count(),
        ];

        $clients = \App\Models\Client::where('workspace_id', $workspace->id)
            ->orderBy('name')
            ->get();

        $projects = \App\Models\Project::where('workspace_id', $workspace->id)
            ->orderBy('name')
            ->get();

        return view('finance.invoices.index', compact('workspace', 'invoices', 'stats', 'clients', 'projects'));
    }

    public function store(Request $request, Workspace $workspace)
    {
        $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'project_id'  => 'nullable|exists:projects,id', // ← THÊM
            'due_date'    => 'required|date|after:today',
            'items'       => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0.1',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        $invoice = $this->invoiceService->create(
            array_merge(
                $request->only(['client_id', 'project_id', 'due_date', 'items']), // ← THÊM project_id
                ['issue_date' => today()]
            ),
            $workspace->id
        );

        return redirect()->route('finance.invoices.show', [$workspace->slug, $invoice->id])
            ->with('success', "Invoice #{$invoice->invoice_number} đã được tạo!");
    }

    public function show(Workspace $workspace, Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'project', 'creator']);
        return view('finance.invoices.show', compact('workspace', 'invoice'));
    }

    public function download(Workspace $workspace, Invoice $invoice)
    {
        $path = $this->invoiceService->generatePdf($invoice);
        return response()->download(storage_path('app/public/' . $path));
    }

    public function markPaid(Request $request, Workspace $workspace, Invoice $invoice)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);
        $this->invoiceService->markAsPaid($invoice, $request->amount);
        return back()->with('success', 'Đã cập nhật thanh toán!');
    }

    public function bulkDelete(Request $request, Workspace $workspace)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:invoices,id']);

        Invoice::where('workspace_id', $workspace->id)
            ->whereIn('id', $request->ids)
            ->delete();

        return back()->with('success', 'Đã xóa ' . count($request->ids) . ' invoice!');
    }

    public function updateItems(Request $request, Workspace $workspace, Invoice $invoice)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:1000',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $invoice) {
            // Xóa items cũ
            $invoice->items()->delete();

            // Tạo items mới
            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $total = $itemData['quantity'] * $itemData['unit_price'];
                $invoice->items()->create([
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $total,
                ]);
                $subtotal += $total;
            }

            // Cập nhật invoice
            $invoice->subtotal = $subtotal;
            $invoice->tax_amount = $subtotal * ($invoice->tax_rate / 100);
            $invoice->total = $subtotal + $invoice->tax_amount - $invoice->discount;
            $invoice->save();
        });

        return redirect()->route('finance.invoices.show', [$workspace->slug, $invoice->id])
            ->with('success', 'Cập nhật items thành công!');
    }

    public function pdf(Workspace $workspace, Invoice $invoice)
    {
        // $this->authorize('view', $invoice);

        $pdf = Pdf::loadView('finance.invoices.pdf', compact('workspace', 'invoice'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $filename = 'Invoice-' . $invoice->invoice_number . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Gửi invoice qua Gmail mailto
     */
    public function send(Request $request, Workspace $workspace, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $client = $invoice->client;
        $subject = "Invoice #{$invoice->invoice_number} - Thanh toán";
        $body = "Kính gửi {$client->name},\r\n\r\n";
        $body .= "📄 Hóa đơn: #{$invoice->invoice_number}\r\n";
        $body .= "💰 Tổng tiền: $" . number_format($invoice->total, 0) . "\r\n";
        $body .= "📅 Hạn chót: {$invoice->due_date->format('d/m/Y')}\r\n";
        $body .= "💳 Còn nợ: $" . number_format($invoice->amount_due, 0) . "\r\n\r\n";
        $body .= "Trân trọng,\r\n" . config('app.name');

        $mailto = 'mailto:' . $client->email . '?' . http_build_query([
            'subject' => $subject,
            'body' => $body,
        ]);

        $invoice->update(['status' => 'sent']);
        return redirect()->away($mailto);
    }

}
