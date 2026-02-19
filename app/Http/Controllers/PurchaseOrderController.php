<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Item;
use App\Models\ItemPriceHistory;
use App\Models\Modal;
use App\Models\ModalAllocation;
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
            'items.*.purchase_price' => 'nullable|numeric|min:0',
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
                'purchase_price' => $item['purchase_price'] ?? 0,
                'selling_price' => $item['selling_price'],
                'notes' => $item['notes'] ?? null,
            ]);

            $this->trackPriceHistory($po->client_id, $item['item_id'], $item['purchase_price'] ?? 0, $item['selling_price'], 'po', $po->id);
        }

        $message = $po->status === 'pending_approval'
            ? 'PO berhasil dibuat dan diajukan untuk approval.'
            : 'PO berhasil disimpan sebagai draft.';

        return redirect()->route('purchase-orders.show', $po)->with('success', $message);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('client', 'creator', 'approver', 'items.item', 'deliveryNotes.items', 'invoice', 'modalAllocations.modal', 'returnNotes', 'expenses');
        
        // Get available modals for allocation
        $availableModals = Modal::with('allocations')->get()->filter(fn($m) => $m->remaining_amount > 0);
        
        return view('transaction.purchase-orders.show', compact('purchaseOrder', 'availableModals'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        // Allow editing PO at any status, but with different rules
        $canEditItems = in_array($purchaseOrder->status, ['draft', 'pending_approval', 'rejected']);
        $hasDeliveryNotes = $purchaseOrder->deliveryNotes()->count() > 0;
        $hasInvoice = $purchaseOrder->invoice !== null;

        $purchaseOrder->load('items.item', 'deliveryNotes.items', 'invoice');
        $clients = Client::active()->orderBy('name')->get();
        $items = Item::active()->with('category')->orderBy('name')->get();

        return view('transaction.purchase-orders.edit', compact('purchaseOrder', 'clients', 'items', 'canEditItems', 'hasDeliveryNotes', 'hasInvoice'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $canEditItems = in_array($purchaseOrder->status, ['draft', 'pending_approval', 'rejected']);
        $hasDeliveryNotes = $purchaseOrder->deliveryNotes()->count() > 0;
        $hasInvoice = $purchaseOrder->invoice !== null;

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'po_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string',
            'items.*.purchase_price' => 'nullable|numeric|min:0',
            'items.*.selling_price' => 'required|numeric|min:0',
        ]);

        // If PO is approved and has DN/Invoice, only update prices and non-structural fields
        if ($purchaseOrder->status === 'approved' && ($hasDeliveryNotes || $hasInvoice)) {
            $purchaseOrder->update([
                'delivery_date' => $request->delivery_date,
                'notes' => $request->notes,
                'last_edited_at' => now(),
            ]);

            // Only update prices for existing items
            foreach ($request->items as $item) {
                $existingItem = $purchaseOrder->items()->where('item_id', $item['item_id'])->first();
                if ($existingItem) {
                    $existingItem->update([
                        'purchase_price' => $item['purchase_price'] ?? 0,
                        'selling_price' => $item['selling_price'],
                    ]);
                    $this->trackPriceHistory($purchaseOrder->client_id, $item['item_id'], $item['purchase_price'] ?? 0, $item['selling_price'], 'po', $purchaseOrder->id);
                }
            }

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'PO berhasil diperbarui (hanya harga & info yang diubah karena ada SJ/Invoice terkait).');
        }

        // Normal edit for draft/pending/rejected
        $updateData = [
            'client_id' => $request->client_id,
            'po_date' => $request->po_date,
            'delivery_date' => $request->delivery_date,
            'notes' => $request->notes,
            'rejected_reason' => null,
            'last_edited_at' => now(),
        ];

        if ($canEditItems) {
            $updateData['status'] = $request->has('submit_approval') ? 'pending_approval' : 'draft';
        }

        $purchaseOrder->update($updateData);

        if ($canEditItems) {
            // Replace items
            $purchaseOrder->items()->delete();
            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'purchase_price' => $item['purchase_price'] ?? 0,
                    'selling_price' => $item['selling_price'],
                    'notes' => $item['notes'] ?? null,
                ]);

                $this->trackPriceHistory($purchaseOrder->client_id, $item['item_id'], $item['purchase_price'] ?? 0, $item['selling_price'], 'po', $purchaseOrder->id);
            }
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

            $this->trackPriceHistory($purchaseOrder->client_id, $poItem->item_id, $priceData['purchase_price'] ?? 0, $priceData['selling_price'], 'po', $purchaseOrder->id);
        }

        return back()->with('success', 'Harga berhasil diperbarui.');
    }

    public function setModal(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'modal_id' => 'required|exists:modals,id',
            'allocated_amount' => 'required|numeric|min:0.01',
        ]);

        $modal = Modal::findOrFail($request->modal_id);
        
        if ($request->allocated_amount > $modal->remaining_amount) {
            return back()->with('error', 'Jumlah alokasi melebihi sisa modal yang tersedia (Rp ' . number_format($modal->remaining_amount, 0, ',', '.') . ').');
        }

        ModalAllocation::create([
            'modal_id' => $request->modal_id,
            'purchase_order_id' => $purchaseOrder->id,
            'allocated_amount' => $request->allocated_amount,
        ]);

        return back()->with('success', 'Modal berhasil dialokasikan ke PO ini.');
    }

    public function addExpense(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        Expense::create([
            'expense_number' => Expense::generateExpenseNumber(),
            'category' => $request->category,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'description' => $request->description,
            'created_by' => auth()->id(),
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        return back()->with('success', 'Pengeluaran berhasil dicatat untuk PO ini.');
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

    private function trackPriceHistory($clientId, $itemId, $purchasePrice, $sellingPrice, $refType, $refId)
    {
        $latestPrice = ItemPriceHistory::where('client_id', $clientId)
            ->where('item_id', $itemId)
            ->latest('changed_at')
            ->first();

        $priceChanged = !$latestPrice || 
                        (float)$latestPrice->purchase_price !== (float)$purchasePrice || 
                        (float)$latestPrice->selling_price !== (float)$sellingPrice;

        if ($priceChanged) {
            ItemPriceHistory::create([
                'client_id' => $clientId,
                'item_id' => $itemId,
                'purchase_price' => $purchasePrice,
                'selling_price' => $sellingPrice,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'reference_type' => $refType,
                'reference_id' => $refId,
            ]);
        }
    }
}
