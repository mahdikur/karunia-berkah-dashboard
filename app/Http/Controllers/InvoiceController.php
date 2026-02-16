<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ItemPriceHistory;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with('client', 'creator')
            ->when($request->search, fn($q, $s) => $q->where('invoice_number', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->client_id, fn($q, $c) => $q->where('client_id', $c))
            ->when($request->date_from, fn($q, $d) => $q->where('invoice_date', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->where('invoice_date', '<=', $d))
            ->latest()
            ->paginate(25);

        $clients = Client::active()->orderBy('name')->get();

        return view('transaction.invoices.index', compact('invoices', 'clients'));
    }

    public function create(Request $request)
    {
        $purchaseOrders = PurchaseOrder::where('status', 'approved')
            ->doesntHave('invoice')
            ->with('client', 'items.item')
            ->latest()
            ->get();

        $selectedPo = $request->po_id ? PurchaseOrder::with('items.item', 'client')->find($request->po_id) : null;

        return view('transaction.invoices.create', compact('purchaseOrders', 'selectedPo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $po = PurchaseOrder::findOrFail($request->purchase_order_id);

        if ($po->invoice) {
            return back()->with('error', 'PO ini sudah memiliki invoice.');
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($request->items as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $discountValue = $request->discount_value ?? 0;
        $discountAmount = $request->discount_type === 'percentage'
            ? ($subtotal * $discountValue / 100)
            : $discountValue;

        $afterDiscount = $subtotal - $discountAmount;
        $taxPercentage = $request->tax_percentage ?? 0;
        $taxAmount = $afterDiscount * $taxPercentage / 100;
        $totalAmount = $afterDiscount + $taxAmount;

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'purchase_order_id' => $po->id,
            'client_id' => $po->client_id,
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'subtotal' => $subtotal,
            'discount_type' => $request->discount_type,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'remaining_amount' => $totalAmount,
            'status' => 'unpaid',
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        foreach ($request->items as $item) {
            $poItem = $po->items()->findOrFail($item['po_item_id']);
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'po_item_id' => $item['po_item_id'],
                'item_id' => $poItem->item_id,
                'quantity' => $item['quantity'],
                'unit' => $poItem->unit,
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['quantity'] * $item['unit_price'],
            ]);

            // Save price history
            ItemPriceHistory::create([
                'client_id' => $po->client_id,
                'item_id' => $poItem->item_id,
                'selling_price' => $item['unit_price'],
                'purchase_price' => $poItem->purchase_price,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'reference_type' => 'invoice',
                'reference_id' => $invoice->id,
            ]);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice berhasil dibuat.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('purchaseOrder', 'client', 'creator', 'items.item', 'payments.creator');
        return view('transaction.invoices.show', compact('invoice'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load('purchaseOrder', 'client', 'creator', 'items.item');
        return view('transaction.invoices.print', compact('invoice'));
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->payments()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus invoice yang sudah ada pembayaran.');
        }

        $invoice->items()->delete();
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dihapus.');
    }
}
