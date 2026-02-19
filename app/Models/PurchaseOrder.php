<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'po_number', 'client_id', 'created_by', 'approved_by',
        'po_date', 'delivery_date', 'approved_at',
        'status', 'notes', 'rejected_reason', 'cancelled_reason',
        'last_edited_at',
    ];

    protected function casts(): array
    {
        return [
            'po_date' => 'date',
            'delivery_date' => 'date',
            'approved_at' => 'datetime',
            'last_edited_at' => 'datetime',
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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function modalAllocations()
    {
        return $this->hasMany(ModalAllocation::class);
    }

    public function returnNotes()
    {
        return $this->hasMany(ReturnNote::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function getTotalSellingAttribute()
    {
        return $this->items->sum(fn($item) => $item->quantity * $item->selling_price);
    }

    public function getTotalAmountAttribute()
    {
        return $this->total_selling;
    }

    public function getTotalPurchaseAttribute()
    {
        return $this->items->sum(fn($item) => $item->quantity * ($item->purchase_price ?? 0));
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'pending_approval' => '<span class="badge bg-warning text-dark">Pending Approval</span>',
            'approved' => '<span class="badge bg-success">Approved</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>',
            'cancelled' => '<span class="badge bg-dark">Cancelled</span>',
            'completed' => '<span class="badge bg-info">Completed</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public static function generatePoNumber()
    {
        $prefix = 'PO/' . date('Y') . '/' . date('m') . '/';
        $lastPo = static::withTrashed()
            ->where('po_number', 'like', $prefix . '%')
            ->orderBy('po_number', 'desc')
            ->first();

        if ($lastPo instanceof self) {
            $lastNumber = (int) substr($lastPo->po_number, -3);
            return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return $prefix . '001';
    }
}
