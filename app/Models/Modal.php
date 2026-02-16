<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modal extends Model
{
    protected $fillable = [
        'modal_number', 'total_amount', 'modal_date', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'modal_date' => 'date',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function allocations()
    {
        return $this->hasMany(ModalAllocation::class);
    }

    public function getAllocatedAmountAttribute()
    {
        return $this->allocations->sum('allocated_amount');
    }

    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->allocated_amount;
    }

    public static function generateModalNumber()
    {
        $prefix = 'MOD/' . date('Y') . '/' . date('m') . '/';
        $last = static::where('modal_number', 'like', $prefix . '%')
            ->orderBy('modal_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->modal_number, -3);
            return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return $prefix . '001';
    }
}
