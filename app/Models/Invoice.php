<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'client_id',
        'project_id',
        'created_by',
        'invoice_number',
        'status',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount',
        'total',
        'amount_paid',
        'currency',
        'notes',
        'terms',
        'sent_at',
        'paid_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date'   => 'date',
        'sent_at'    => 'datetime',
        'paid_at'    => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function recalculate(): void
    {
        $subtotal   = $this->items()->sum(\DB::raw('quantity * unit_price'));
        $tax_amount = round($subtotal * ($this->tax_rate / 100), 2);
        $total      = $subtotal + $tax_amount - $this->discount;

        $this->update(compact('subtotal', 'tax_amount', 'total'));
    }

    public function getAmountDueAttribute(): float
    {
        return max(0, $this->total - $this->amount_paid);
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && !in_array($this->status, ['paid', 'cancelled']);
    }

    public static function generateNumber(int $workspaceId): string
    {
        $year = now()->year;

        // Đếm trực tiếp trên DB, bỏ qua mọi global scope / soft delete
        $count = \DB::table('invoices')
            ->where('workspace_id', $workspaceId)
            ->whereYear('created_at', $year)
            ->count();

        $next = $count + 1;

        while (true) {
            $candidate = sprintf('INV-%d-%04d', $year, $next);

            $exists = \DB::table('invoices')
                ->where('invoice_number', $candidate)
                ->exists();

            if (! $exists) {
                return $candidate;
            }

            $next++;
        }
    }
}
