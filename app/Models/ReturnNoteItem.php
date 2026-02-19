<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnNoteItem extends Model
{
    protected $fillable = [
        'return_note_id', 'delivery_note_item_id', 'item_id',
        'quantity_returned', 'unit', 'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity_returned' => 'decimal:2',
        ];
    }

    public function returnNote()
    {
        return $this->belongsTo(ReturnNote::class);
    }

    public function deliveryNoteItem()
    {
        return $this->belongsTo(DeliveryNoteItem::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
