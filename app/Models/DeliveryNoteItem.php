<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNoteItem extends Model
{
    protected $fillable = [
        'delivery_note_id', 'po_item_id', 'item_id',
        'quantity_delivered', 'unit', 'is_unavailable', 'unavailable_reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity_delivered' => 'decimal:2',
            'is_unavailable' => 'boolean',
        ];
    }

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function poItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function returnItems()
    {
        return $this->hasMany(ReturnNoteItem::class);
    }
}
