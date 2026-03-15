<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceBatch extends Model
{
    protected $fillable = [
        'batch_number', 'batch_name', 'client_id',
        'total_amount', 'total_discount', 'grand_total',
        'paid_amount', 'remaining_amount',
        'status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'total_amount'     => 'decimal:2',
            'total_discount'   => 'decimal:2',
            'grand_total'      => 'decimal:2',
            'paid_amount'      => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(InvoiceBatchItem::class);
    }

    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, InvoiceBatchItem::class, 'invoice_batch_id', 'id', 'id', 'invoice_id');
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'unpaid'  => '<span class="badge bg-warning text-dark">Unpaid</span>',
            'partial' => '<span class="badge bg-info">Partial</span>',
            'paid'    => '<span class="badge bg-success">Paid</span>',
            default   => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function updatePaymentStatus()
    {
        $totalPaid = $this->paid_amount;
        $grandTotal = $this->grand_total;

        if ($totalPaid >= $grandTotal && $grandTotal > 0) {
            $this->status = 'paid';
        } elseif ($totalPaid > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'unpaid';
        }

        $this->remaining_amount = $grandTotal - $totalPaid;
        $this->save();
    }

    public static function generateBatchNumber()
    {
        $prefix = 'BATCH/' . date('Y') . '/' . date('m') . '/';
        $last = static::where('batch_number', 'like', $prefix . '%')
            ->orderBy('batch_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->batch_number, -3);
            return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return $prefix . '001';
    }
}
