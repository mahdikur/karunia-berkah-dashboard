<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DeliveryNoteItem;
use App\Models\Expense;
use App\Models\InvoiceItem;
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
        // Default date range: past 1 week
        $dateFrom = $request->date_from ?? now()->subWeek()->format('Y-m-d');
        $dateTo   = $request->date_to   ?? now()->format('Y-m-d');

        $query = PurchaseOrder::with('client', 'creator')
            ->when($request->search, fn($q, $s) => $q->where('po_number', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->client_id, fn($q, $c) => $q->where('client_id', $c))
            ->where('po_date', '>=', $dateFrom)
            ->where('po_date', '<=', $dateTo);

        if (auth()->user()->isStaff()) {
            $query->where('created_by', auth()->id());
        }

        $purchaseOrders = $query->latest()->paginate(25);
        $clients = Client::active()->orderBy('name')->get();

        return view('transaction.purchase-orders.index', compact('purchaseOrders', 'clients', 'dateFrom', 'dateTo'));
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
        $dnCount     = $purchaseOrder->deliveryNotes()->count();
        $invoiceCount = $purchaseOrder->invoice !== null ? 1 : 0;

        // Allow item editing if draft/pending/rejected, OR if approved with at most 1 DN and 1 invoice
        $canEditItems = in_array($purchaseOrder->status, ['draft', 'pending_approval', 'rejected'])
            || ($purchaseOrder->status === 'approved' && $dnCount <= 1 && $invoiceCount <= 1);

        $hasDeliveryNotes = $dnCount > 0;
        $hasInvoice       = $invoiceCount > 0;
        $isApproved       = $purchaseOrder->status === 'approved';

        $purchaseOrder->load('items.item', 'deliveryNotes.items', 'invoice');
        $clients = Client::active()->orderBy('name')->get();
        $items   = Item::active()->with('category')->orderBy('name')->get();

        return view('transaction.purchase-orders.edit', compact(
            'purchaseOrder', 'clients', 'items', 'canEditItems',
            'hasDeliveryNotes', 'hasInvoice', 'isApproved', 'dnCount', 'invoiceCount'
        ));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $dnCount      = $purchaseOrder->deliveryNotes()->count();
        $invoiceCount = $purchaseOrder->invoice !== null ? 1 : 0;
        $isApproved   = $purchaseOrder->status === 'approved';

        // Allow item editing if draft/pending/rejected, OR if approved with at most 1 DN and 1 invoice
        $canEditItems = in_array($purchaseOrder->status, ['draft', 'pending_approval', 'rejected'])
            || ($isApproved && $dnCount <= 1 && $invoiceCount <= 1);

        $request->validate([
            'client_id'                  => 'required|exists:clients,id',
            'po_date'                    => 'required|date',
            'delivery_date'              => 'nullable|date',
            'notes'                      => 'nullable|string',
            'items'                      => 'required|array|min:1',
            'items.*.item_id'            => 'required|exists:items,id',
            'items.*.quantity'           => 'required|numeric|min:0.01',
            'items.*.unit'               => 'required|string',
            'items.*.purchase_price'     => 'nullable|numeric|min:0',
            'items.*.selling_price'      => 'required|numeric|min:0',
        ]);

        // Approved PO with >1 DN or >1 Invoice: only allow price/info update
        if ($isApproved && ($dnCount > 1 || $invoiceCount > 1)) {
            $purchaseOrder->update([
                'delivery_date' => $request->delivery_date,
                'notes'         => $request->notes,
                'last_edited_at' => now(),
            ]);

            foreach ($request->items as $item) {
                $existingItem = $purchaseOrder->items()->where('item_id', $item['item_id'])->first();
                if ($existingItem) {
                    $existingItem->update([
                        'purchase_price' => $item['purchase_price'] ?? 0,
                        'selling_price'  => $item['selling_price'],
                    ]);
                    $this->trackPriceHistory($purchaseOrder->client_id, $item['item_id'], $item['purchase_price'] ?? 0, $item['selling_price'], 'po', $purchaseOrder->id);
                }
            }

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'PO berhasil diperbarui (hanya harga & info yang diubah karena ada lebih dari 1 SJ/Invoice terkait).');
        }

        // Build update data – client_id/po_date/status only editable for non-approved POs
        $updateData = [
            'delivery_date'  => $request->delivery_date,
            'notes'          => $request->notes,
            'last_edited_at' => now(),
        ];

        if (!$isApproved) {
            $updateData['client_id']       = $request->client_id;
            $updateData['po_date']         = $request->po_date;
            $updateData['rejected_reason'] = null;
            $updateData['status']          = $request->has('submit_approval') ? 'pending_approval' : 'draft';
        }

        $purchaseOrder->update($updateData);

        if ($canEditItems) {
            // First: Delete DN and Invoice items to avoid FK constraint violation
            if ($dnCount === 1) {
                $dn = $purchaseOrder->deliveryNotes()->first();
                $dn->items()->delete();
            }
            if ($invoiceCount === 1) {
                $invoice = $purchaseOrder->invoice;
                $invoice->items()->delete();
            }

            // Now delete PO items (safe since dependent records are deleted)
            $purchaseOrder->items()->delete();
            $newPoItems = [];
            foreach ($request->items as $item) {
                $poItem = PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id'           => $item['item_id'],
                    'quantity'          => $item['quantity'],
                    'unit'              => $item['unit'],
                    'purchase_price'    => $item['purchase_price'] ?? 0,
                    'selling_price'     => $item['selling_price'],
                    'notes'             => $item['notes'] ?? null,
                ]);
                $newPoItems[] = $poItem;
                $this->trackPriceHistory($purchaseOrder->client_id, $item['item_id'], $item['purchase_price'] ?? 0, $item['selling_price'], 'po', $purchaseOrder->id);
            }

            // Auto-update the single DN if present
            if ($dnCount === 1) {
                $dn = $purchaseOrder->deliveryNotes()->first();
                foreach ($newPoItems as $poItem) {
                    DeliveryNoteItem::create([
                        'delivery_note_id'   => $dn->id,
                        'po_item_id'         => $poItem->id,
                        'item_id'            => $poItem->item_id,
                        'quantity_delivered' => $poItem->quantity,
                        'unit'               => $poItem->unit,
                        'is_unavailable'     => false,
                    ]);
                }
            }

            // Auto-update the single Invoice if present
            if ($invoiceCount === 1) {
                $invoice = $purchaseOrder->invoice;
                $subtotal = 0;
                foreach ($newPoItems as $poItem) {
                    $lineSubtotal = $poItem->quantity * $poItem->selling_price;
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'po_item_id' => $poItem->id,
                        'item_id'    => $poItem->item_id,
                        'quantity'   => $poItem->quantity,
                        'unit'       => $poItem->unit,
                        'unit_price' => $poItem->selling_price,
                        'subtotal'   => $lineSubtotal,
                    ]);
                    $subtotal += $lineSubtotal;
                }
                $discountAmount = $invoice->discount_type === 'percentage'
                    ? ($subtotal * $invoice->discount_value / 100)
                    : $invoice->discount_value;
                $afterDiscount = $subtotal - $discountAmount;
                $taxAmount     = $afterDiscount * $invoice->tax_percentage / 100;
                $totalAmount   = $afterDiscount + $taxAmount;
                $invoice->update([
                    'subtotal'         => $subtotal,
                    'discount_amount'  => $discountAmount,
                    'tax_amount'       => $taxAmount,
                    'total_amount'     => $totalAmount,
                    'remaining_amount' => $totalAmount - $invoice->paid_amount,
                ]);
            }
        }

        $message = 'PO berhasil diperbarui.';
        if ($isApproved && ($dnCount === 1 || $invoiceCount === 1)) {
            $updated = [];
            if ($dnCount === 1)    $updated[] = 'Surat Jalan';
            if ($invoiceCount === 1) $updated[] = 'Invoice';
            $message .= ' ' . implode(' & ', $updated) . ' otomatis diperbarui.';
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', $message);
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

    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('client', 'creator', 'approver', 'items.item');
        return view('transaction.purchase-orders.print', compact('purchaseOrder'));
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
