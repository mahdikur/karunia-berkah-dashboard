<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'item_id', 'quantity',
        'unit', 'purchase_price', 'selling_price', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
        ];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->selling_price;
    }

    public function getPurchaseSubtotalAttribute()
    {
        return $this->quantity * ($this->purchase_price ?? 0);
    }

    public function getProfitAttribute()
    {
        return $this->subtotal - $this->purchase_subtotal;
    }
}
