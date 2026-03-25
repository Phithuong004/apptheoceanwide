<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'sort_order',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Tự tính thành tiền
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }
}
