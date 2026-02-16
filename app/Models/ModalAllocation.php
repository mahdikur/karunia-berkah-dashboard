<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModalAllocation extends Model
{
    protected $fillable = ['modal_id', 'purchase_order_id', 'allocated_amount'];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
        ];
    }

    public function modal()
    {
        return $this->belongsTo(Modal::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
