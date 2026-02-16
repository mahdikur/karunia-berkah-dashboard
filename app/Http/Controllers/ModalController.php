<?php

namespace App\Http\Controllers;

use App\Models\Modal;
use App\Models\ModalAllocation;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class ModalController extends Controller
{
    public function index(Request $request)
    {
        $modals = Modal::with('creator', 'allocations.purchaseOrder')
            ->when($request->search, fn($q, $s) => $q->where('modal_number', 'like', "%{$s}%"))
            ->latest()
            ->paginate(25);

        return view('transaction.modals.index', compact('modals'));
    }

    public function create()
    {
        $purchaseOrders = PurchaseOrder::where('status', 'approved')
            ->with('client')
            ->latest()
            ->get();

        return view('transaction.modals.create', compact('purchaseOrders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'total_amount' => 'required|numeric|min:0.01',
            'modal_date' => 'required|date',
            'notes' => 'nullable|string',
            'allocations' => 'nullable|array',
            'allocations.*.purchase_order_id' => 'required|exists:purchase_orders,id',
            'allocations.*.allocated_amount' => 'required|numeric|min:0.01',
        ]);

        // Validate total allocation <= total modal
        if ($request->allocations) {
            $totalAllocation = collect($request->allocations)->sum('allocated_amount');
            if ($totalAllocation > $request->total_amount) {
                return back()->withInput()->with('error', 'Total alokasi melebihi jumlah modal.');
            }
        }

        $modal = Modal::create([
            'modal_number' => Modal::generateModalNumber(),
            'total_amount' => $request->total_amount,
            'modal_date' => $request->modal_date,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        if ($request->allocations) {
            foreach ($request->allocations as $allocation) {
                ModalAllocation::create([
                    'modal_id' => $modal->id,
                    'purchase_order_id' => $allocation['purchase_order_id'],
                    'allocated_amount' => $allocation['allocated_amount'],
                ]);
            }
        }

        return redirect()->route('modals.index')->with('success', 'Modal berhasil ditambahkan.');
    }

    public function show(Modal $modal)
    {
        $modal->load('creator', 'allocations.purchaseOrder.client');
        return view('transaction.modals.show', compact('modal'));
    }

    public function destroy(Modal $modal)
    {
        $modal->allocations()->delete();
        $modal->delete();

        return redirect()->route('modals.index')->with('success', 'Modal berhasil dihapus.');
    }
}
