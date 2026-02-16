<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class DeliveryNoteController extends Controller
{
    public function index(Request $request)
    {
        $deliveryNotes = DeliveryNote::with('purchaseOrder', 'client', 'creator')
            ->when($request->search, fn($q, $s) => $q->where('dn_number', 'like', "%{$s}%"))
            ->when($request->client_id, fn($q, $c) => $q->where('client_id', $c))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(25);

        return view('transaction.delivery-notes.index', compact('deliveryNotes'));
    }

    public function create(Request $request)
    {
        $purchaseOrders = PurchaseOrder::where('status', 'approved')
            ->with('client', 'items.item')
            ->latest()
            ->get();

        $selectedPo = $request->po_id ? PurchaseOrder::with('items.item', 'client')->find($request->po_id) : null;

        return view('transaction.delivery-notes.create', compact('purchaseOrders', 'selectedPo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'dn_date' => 'required|date',
            'delivery_type' => 'required|in:full,partial',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_delivered' => 'required|numeric|min:0.01',
        ]);

        $po = PurchaseOrder::findOrFail($request->purchase_order_id);

        $dn = DeliveryNote::create([
            'dn_number' => DeliveryNote::generateDnNumber(),
            'purchase_order_id' => $po->id,
            'client_id' => $po->client_id,
            'dn_date' => $request->dn_date,
            'delivery_type' => $request->delivery_type,
            'status' => 'draft',
            'created_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        foreach ($request->items as $item) {
            $poItem = $po->items()->findOrFail($item['po_item_id']);
            DeliveryNoteItem::create([
                'delivery_note_id' => $dn->id,
                'po_item_id' => $item['po_item_id'],
                'item_id' => $poItem->item_id,
                'quantity_delivered' => $item['quantity_delivered'],
                'unit' => $poItem->unit,
            ]);
        }

        return redirect()->route('delivery-notes.show', $dn)->with('success', 'Surat Jalan berhasil dibuat.');
    }

    public function show(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load('purchaseOrder', 'client', 'creator', 'items.item', 'items.poItem');
        return view('transaction.delivery-notes.show', compact('deliveryNote'));
    }

    public function print(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load('purchaseOrder', 'client', 'creator', 'items.item');
        return view('transaction.delivery-notes.print', compact('deliveryNote'));
    }

    public function updateStatus(Request $request, DeliveryNote $deliveryNote)
    {
        $request->validate(['status' => 'required|in:draft,sent,received']);
        $deliveryNote->update(['status' => $request->status]);
        return back()->with('success', 'Status Surat Jalan berhasil diperbarui.');
    }

    public function destroy(DeliveryNote $deliveryNote)
    {
        if ($deliveryNote->status !== 'draft') {
            return back()->with('error', 'Hanya SJ draft yang bisa dihapus.');
        }

        $deliveryNote->items()->delete();
        $deliveryNote->delete();

        return redirect()->route('delivery-notes.index')->with('success', 'Surat Jalan berhasil dihapus.');
    }
}
