<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Item;
use App\Models\ItemPriceHistory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('client', 'creator')
            ->when($request->search, fn($q, $s) => $q->where('po_number', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->client_id, fn($q, $c) => $q->where('client_id', $c))
            ->when($request->date_from, fn($q, $d) => $q->where('po_date', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->where('po_date', '<=', $d));

        if (auth()->user()->isStaff()) {
            $query->where('created_by', auth()->id());
        }

        $purchaseOrders = $query->latest()->paginate(25);
        $clients = Client::active()->orderBy('name')->get();

        return view('transaction.purchase-orders.index', compact('purchaseOrders', 'clients'));
    }

    public function create()
    {
        $clients = Client::active()->orderBy('name')->get();
        $items = Item::active()->with('category')->orderBy('name')->get();

        return view('transaction.purchase-orders.create', compact('clients', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'po_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string',
            'items.*.selling_price' => 'required|numeric|min:0',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generatePoNumber(),
            'client_id' => $request->client_id,
            'created_by' => auth()->id(),
            'po_date' => $request->po_date,
            'delivery_date' => $request->delivery_date,
            'status' => $request->has('submit_approval') ? 'pending_approval' : 'draft',
            'notes' => $request->notes,
        ]);

        foreach ($request->items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'selling_price' => $item['selling_price'],
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $message = $po->status === 'pending_approval'
            ? 'PO berhasil dibuat dan diajukan untuk approval.'
            : 'PO berhasil disimpan sebagai draft.';

        return redirect()->route('purchase-orders.show', $po)->with('success', $message);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('client', 'creator', 'approver', 'items.item', 'deliveryNotes', 'invoice', 'modalAllocations.modal');
        return view('transaction.purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['draft', 'pending_approval', 'rejected'])) {
            return back()->with('error', 'PO tidak bisa diedit.');
        }

        $purchaseOrder->load('items.item');
        $clients = Client::active()->orderBy('name')->get();
        $items = Item::active()->with('category')->orderBy('name')->get();

        return view('transaction.purchase-orders.edit', compact('purchaseOrder', 'clients', 'items'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['draft', 'pending_approval', 'rejected'])) {
            return back()->with('error', 'PO tidak bisa diedit.');
        }

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'po_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string',
            'items.*.selling_price' => 'required|numeric|min:0',
        ]);

        $purchaseOrder->update([
            'client_id' => $request->client_id,
            'po_date' => $request->po_date,
            'delivery_date' => $request->delivery_date,
            'status' => $request->has('submit_approval') ? 'pending_approval' : 'draft',
            'notes' => $request->notes,
            'rejected_reason' => null,
        ]);

        // Replace items
        $purchaseOrder->items()->delete();
        foreach ($request->items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->id,
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'selling_price' => $item['selling_price'],
                'notes' => $item['notes'] ?? null,
            ]);
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'PO berhasil diperbarui.');
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending_approval') {
            return back()->with('error', 'PO tidak dalam status pending approval.');
        }

        $purchaseOrder->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'PO berhasil diapprove.');
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending_approval') {
            return back()->with('error', 'PO tidak dalam status pending approval.');
        }

        $request->validate(['rejected_reason' => 'required|string']);

        $purchaseOrder->update([
            'status' => 'rejected',
            'rejected_reason' => $request->rejected_reason,
        ]);

        return back()->with('success', 'PO berhasil ditolak.');
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'approved') {
            return back()->with('error', 'Hanya PO approved yang bisa dibatalkan.');
        }

        if ($purchaseOrder->deliveryNotes()->count() > 0) {
            return back()->with('error', 'Tidak bisa batal, sudah ada Surat Jalan.');
        }

        $request->validate(['cancelled_reason' => 'required|string']);

        $purchaseOrder->update([
            'status' => 'cancelled',
            'cancelled_reason' => $request->cancelled_reason,
        ]);

        return back()->with('success', 'PO berhasil dibatalkan.');
    }

    public function updatePrices(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'approved') {
            return back()->with('error', 'Hanya PO approved yang bisa diupdate harganya.');
        }

        $request->validate([
            'prices' => 'required|array',
            'prices.*.purchase_price' => 'nullable|numeric|min:0',
            'prices.*.selling_price' => 'required|numeric|min:0',
        ]);

        foreach ($request->prices as $itemId => $priceData) {
            $poItem = PurchaseOrderItem::findOrFail($itemId);
            $poItem->update([
                'purchase_price' => $priceData['purchase_price'] ?? null,
                'selling_price' => $priceData['selling_price'],
            ]);

            // Save price history
            ItemPriceHistory::create([
                'client_id' => $purchaseOrder->client_id,
                'item_id' => $poItem->item_id,
                'purchase_price' => $priceData['purchase_price'] ?? null,
                'selling_price' => $priceData['selling_price'],
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'reference_type' => 'po',
                'reference_id' => $purchaseOrder->id,
            ]);
        }

        return back()->with('success', 'Harga berhasil diperbarui.');
    }

    // API endpoint for getting latest price
    public function getItemPrice(Request $request)
    {
        $history = ItemPriceHistory::where('client_id', $request->client_id)
            ->where('item_id', $request->item_id)
            ->latest('changed_at')
            ->first();

        return response()->json([
            'selling_price' => $history ? $history->selling_price : 0,
            'purchase_price' => $history ? $history->purchase_price : 0,
        ]);
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['draft'])) {
            return back()->with('error', 'Hanya PO draft yang bisa dihapus.');
        }

        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')->with('success', 'PO berhasil dihapus.');
    }
}
