<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id', 'payment_date', 'amount',
        'payment_method', 'reference_number', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPaymentMethodLabelAttribute()
    {
        return match($this->payment_method) {
            'transfer' => 'Transfer Bank',
            'cash' => 'Cash',
            'giro' => 'Giro',
            default => ucfirst($this->payment_method),
        };
    }
}
