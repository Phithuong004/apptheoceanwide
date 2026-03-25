<?php

namespace App\Services;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\Task;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function create(array $data, int $workspaceId): Invoice
    {
        // Tách items ra khỏi data trước khi tạo invoice
        $items = $data['items'] ?? [];
        unset($data['items']);

        $invoice = Invoice::create([
            'client_id'      => $data['client_id'],
            'due_date'       => $data['due_date'],
            'issue_date'     => $data['issue_date'],
            'project_id'     => $data['project_id'] ?? null,
            'workspace_id'   => $workspaceId,
            'created_by'     => auth()->id(),
            'invoice_number' => Invoice::generateNumber($workspaceId),
            'status'         => 'draft',
            'total'          => 0,
            'amount_paid'    => 0,
        ]);

        foreach ($items as $i => $item) {
            $invoice->items()->create([
                'description'  => $item['description'],
                'quantity'     => (float) $item['quantity'],
                'unit_price'   => (float) $item['unit_price'],
                'total'        => (float) $item['quantity'] * (float) $item['unit_price'], // ← rõ ràng
                'sort_order'   => $i,
            ]);
        }

        $invoice->recalculate();
        return $invoice;
    }

    public function createFromTimelogs(int $projectId, array $userIds, float $hourlyRate): Invoice
    {
        $project = \App\Models\Project::findOrFail($projectId);
        $logs    = \App\Models\TaskTimeLog::whereHas('task', fn($q) => $q->where('project_id', $projectId))
            ->whereIn('user_id', $userIds)
            ->where('invoiced', false)
            ->with(['task', 'user'])
            ->get();

        $invoice = Invoice::create([
            'workspace_id'   => $project->workspace_id,
            'client_id'      => $project->client_id,
            'project_id'     => $projectId,
            'created_by'     => auth()->id(),
            'invoice_number' => Invoice::generateNumber($project->workspace_id),
            'issue_date'     => today(),
            'due_date'       => today()->addDays(30),
        ]);

        foreach ($logs->groupBy('task_id') as $taskId => $taskLogs) {
            $hours = $taskLogs->sum('hours');
            $invoice->items()->create([
                'description' => "Task: {$taskLogs->first()->task->title}",
                'quantity'    => $hours,
                'unit_price'  => $hourlyRate,
                'total'       => $hours * $hourlyRate,
            ]);
        }

        $invoice->recalculate();
        $logs->each->update(['invoiced' => true]);

        return $invoice;
    }

    public function sendToClient(Invoice $invoice): void
    {
        $pdf = $this->generatePdf($invoice);
        Mail::to($invoice->client->email)->queue(new InvoiceMail($invoice, $pdf));
        $invoice->update(['status' => 'sent', 'sent_at' => now()]);
    }

    public function generatePdf(Invoice $invoice): string
    {
        $invoice->load(['client', 'items', 'project', 'creator']);
        $pdf  = Pdf::loadView('finance.invoices.pdf', compact('invoice'))
            ->setPaper('a4');
        $path = "invoices/INV-{$invoice->invoice_number}.pdf";
        Storage::disk('public')->put($path, $pdf->output());
        return $path;
    }

    public function markAsPaid(Invoice $invoice, float $amount): void
    {
        $invoice->increment('amount_paid', $amount);
        $newAmount = $invoice->fresh()->amount_paid;

        $status = $newAmount >= $invoice->total ? 'paid' : 'partial';
        $invoice->update([
            'status'  => $status,
            'paid_at' => $status === 'paid' ? now() : null,
        ]);
    }
}
