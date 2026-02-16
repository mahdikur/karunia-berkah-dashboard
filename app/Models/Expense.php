<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_number', 'category', 'amount',
        'expense_date', 'description', 'receipt_file', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateExpenseNumber()
    {
        $prefix = 'EXP/' . date('Y') . '/' . date('m') . '/';
        $last = static::where('expense_number', 'like', $prefix . '%')
            ->orderBy('expense_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->expense_number, -3);
            return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return $prefix . '001';
    }

    public const CATEGORIES = [
        'bensin' => 'Bensin',
        'makan' => 'Makan',
        'rokok' => 'Rokok',
        'transportasi' => 'Transportasi',
        'operasional' => 'Operasional',
        'lain-lain' => 'Lain-lain',
    ];
}
