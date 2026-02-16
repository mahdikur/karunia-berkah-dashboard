<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'address', 'email', 'phone',
        'pic_name', 'pic_phone', 'npwp',
        'latitude', 'longitude', 'logo',
        'payment_terms', 'credit_limit', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'payment_terms' => 'integer',
            'credit_limit' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function priceHistories()
    {
        return $this->hasMany(ItemPriceHistory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getOutstandingAmount()
    {
        return $this->invoices()
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum('remaining_amount');
    }

    public function getPaymentTermsLabelAttribute()
    {
        return match($this->payment_terms) {
            0 => 'COD',
            default => "NET {$this->payment_terms}",
        };
    }
}
