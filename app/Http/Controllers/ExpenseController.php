<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::with('creator')
            ->when($request->search, fn($q, $s) => $q->where('description', 'like', "%{$s}%")->orWhere('expense_number', 'like', "%{$s}%"))
            ->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->when($request->date_from, fn($q, $d) => $q->where('expense_date', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->where('expense_date', '<=', $d))
            ->latest()
            ->paginate(25);

        return view('transaction.expenses.index', compact('expenses'));
    }

    public function create()
    {
        return view('transaction.expenses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $data = $request->only('category', 'amount', 'expense_date', 'description');
        $data['expense_number'] = Expense::generateExpenseNumber();
        $data['created_by'] = auth()->id();

        if ($request->hasFile('receipt_file')) {
            $data['receipt_file'] = $request->file('receipt_file')->store('receipts', 'public');
        }

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function show(Expense $expense)
    {
        return view('transaction.expenses.show', compact('expense'));
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_file) {
            Storage::disk('public')->delete($expense->receipt_file);
        }

        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
