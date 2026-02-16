<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    protected $fillable = [
        'dn_number', 'purchase_order_id', 'client_id',
        'dn_date', 'delivery_type', 'status', 'created_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'dn_date' => 'date',
        ];
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
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'sent' => '<span class="badge bg-info">Sent</span>',
            'received' => '<span class="badge bg-success">Received</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public static function generateDnNumber()
    {
        $prefix = 'SJ/' . date('Y') . '/' . date('m') . '/';
        $last = static::where('dn_number', 'like', $prefix . '%')
            ->orderBy('dn_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->dn_number, -3);
            return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return $prefix . '001';
    }
}
