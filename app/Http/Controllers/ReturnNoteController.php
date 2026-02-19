<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\ReturnNote;
use App\Models\ReturnNoteItem;
use Illuminate\Http\Request;

class ReturnNoteController extends Controller
{
    public function index(Request $request)
    {
        // Default date range: past 1 week
        $dateFrom = $request->date_from ?? now()->subWeek()->format('Y-m-d');
        $dateTo   = $request->date_to   ?? now()->format('Y-m-d');

        $returnNotes = ReturnNote::with('deliveryNote', 'purchaseOrder', 'client', 'creator')
            ->when($request->search, fn($q, $s) => $q->where('return_number', 'like', "%{$s}%"))
            ->when($request->client_id, fn($q, $c) => $q->where('client_id', $c))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->where('return_date', '>=', $dateFrom)
            ->where('return_date', '<=', $dateTo)
            ->latest()
            ->paginate(25);

        $clients = \App\Models\Client::active()->orderBy('name')->get();

        return view('transaction.return-notes.index', compact('returnNotes', 'clients', 'dateFrom', 'dateTo'));
    }

    public function create(Request $request)
    {
        $deliveryNotes = DeliveryNote::whereIn('status', ['sent', 'received'])
            ->with('purchaseOrder', 'client', 'items.item')
            ->latest()
            ->get();

        $selectedDn = $request->dn_id ? DeliveryNote::with('items.item', 'client', 'purchaseOrder')->find($request->dn_id) : null;

        return view('transaction.return-notes.create', compact('deliveryNotes', 'selectedDn'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'delivery_note_id' => 'required|exists:delivery_notes,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.delivery_note_item_id' => 'required|exists:delivery_note_items,id',
            'items.*.quantity_returned' => 'required|numeric|min:0.01',
        ]);

        $dn = DeliveryNote::findOrFail($request->delivery_note_id);

        $rn = ReturnNote::create([
            'return_number' => ReturnNote::generateReturnNumber(),
            'delivery_note_id' => $dn->id,
            'purchase_order_id' => $dn->purchase_order_id,
            'client_id' => $dn->client_id,
            'return_date' => $request->return_date,
            'status' => 'draft',
            'reason' => $request->reason,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        foreach ($request->items as $item) {
            if (($item['quantity_returned'] ?? 0) <= 0) continue;
            $dnItem = $dn->items()->findOrFail($item['delivery_note_item_id']);
            ReturnNoteItem::create([
                'return_note_id' => $rn->id,
                'delivery_note_item_id' => $item['delivery_note_item_id'],
                'item_id' => $dnItem->item_id,
                'quantity_returned' => $item['quantity_returned'],
                'unit' => $dnItem->unit,
                'reason' => $item['reason'] ?? null,
            ]);
        }

        return redirect()->route('return-notes.show', $rn)->with('success', 'Retur berhasil dibuat.');
    }

    public function show(ReturnNote $returnNote)
    {
        $returnNote->load('deliveryNote', 'purchaseOrder', 'client', 'creator', 'items.item', 'items.deliveryNoteItem');
        return view('transaction.return-notes.show', compact('returnNote'));
    }

    public function updateStatus(Request $request, ReturnNote $returnNote)
    {
        $request->validate(['status' => 'required|in:draft,confirmed,processed']);
        $returnNote->update(['status' => $request->status]);
        return back()->with('success', 'Status retur berhasil diperbarui.');
    }

    public function destroy(ReturnNote $returnNote)
    {
        if ($returnNote->status !== 'draft') {
            return back()->with('error', 'Hanya retur draft yang bisa dihapus.');
        }

        $returnNote->items()->delete();
        $returnNote->delete();

        return redirect()->route('return-notes.index')->with('success', 'Retur berhasil dihapus.');
    }
}
