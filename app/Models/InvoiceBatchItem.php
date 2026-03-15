<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceBatchItem extends Model
{
    protected $fillable = [
        'invoice_batch_id', 'invoice_id', 'discount_amount', 'nett_amount',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'nett_amount'     => 'decimal:2',
        ];
    }

    public function batch()
    {
        return $this->belongsTo(InvoiceBatch::class, 'invoice_batch_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
