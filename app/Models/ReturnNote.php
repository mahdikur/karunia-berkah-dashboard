<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnNote extends Model
{
    protected $fillable = [
        'return_number', 'delivery_note_id', 'purchase_order_id', 'client_id',
        'return_date', 'status', 'reason', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
        ];
    }

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
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
        return $this->hasMany(ReturnNoteItem::class);
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'confirmed' => '<span class="badge bg-warning text-dark">Dikonfirmasi</span>',
            'processed' => '<span class="badge bg-success">Diproses</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public static function generateReturnNumber()
    {
        $prefix = 'RTN/' . date('Y') . '/' . date('m') . '/';
        $last = static::where('return_number', 'like', $prefix . '%')
            ->orderBy('return_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->return_number, -3);
            return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return $prefix . '001';
    }
}
