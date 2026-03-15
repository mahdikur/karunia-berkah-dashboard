<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Models\InvoiceBatchItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceBatchController extends Controller
{
    /**
     * Daftar semua batch invoice
     */
    public function index(Request $request)
    {
        $query = InvoiceBatch::with('client', 'creator')
            ->when($request->search, fn($q, $s) => $q->where(function ($wq) use ($s) {
                $wq->where('batch_number', 'like', "%{$s}%")
                   ->orWhere('batch_name', 'like', "%{$s}%");
            }))
            ->when($request->client_id, fn($q, $c) => $q->where('client_id', $c))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest();

        $batches = $query->paginate(20);
        $clients = Client::active()->orderBy('name')->get();

        return view('transaction.invoices.batch-list', compact('batches', 'clients'));
    }

    /**
     * Form buat batch baru
     */
    public function create()
    {
        $clients = Client::active()->whereHas('invoices', function ($q) {
            // hanya client yang punya invoice belum di-batch
            $q->whereDoesntHave('batchItem');
        })->orderBy('name')->get();

        return view('transaction.invoices.batch-create', compact('clients'));
    }

    /**
     * Simpan batch baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'batch_name'   => 'required|string|max:255',
            'client_id'    => 'required|exists:clients,id',
            'invoice_ids'  => 'required|array|min:1',
            'invoice_ids.*' => 'required|exists:invoices,id',
            'discounts'    => 'nullable|array',
        ]);

        // Pastikan semua invoice milik client & belum di-batch
        $invoiceIds = $request->invoice_ids;
        $invoices = Invoice::whereIn('id', $invoiceIds)
            ->where('client_id', $request->client_id)
            ->whereDoesntHave('batchItem')
            ->get();

        if ($invoices->count() !== count($invoiceIds)) {
            return back()->withErrors(['invoice_ids' => 'Beberapa invoice tidak valid atau sudah masuk batch lain.'])->withInput();
        }

        $discounts = $request->discounts ?? [];
        $totalAmount  = 0;
        $totalDiscount = 0;
        $grandTotal   = 0;

        foreach ($invoices as $inv) {
            $disc = isset($discounts[$inv->id]) ? (float) $discounts[$inv->id] : 0;
            $totalAmount   += (float) $inv->total_amount;
            $totalDiscount += $disc;
            $grandTotal    += (float) $inv->total_amount - $disc;
        }

        $batch = null;

        DB::transaction(function () use ($request, $invoices, $discounts, $totalAmount, $totalDiscount, $grandTotal, &$batch) {
            $batch = InvoiceBatch::create([
                'batch_number'     => InvoiceBatch::generateBatchNumber(),
                'batch_name'       => $request->batch_name,
                'client_id'        => $request->client_id,
                'total_amount'     => $totalAmount,
                'total_discount'   => $totalDiscount,
                'grand_total'      => $grandTotal,
                'paid_amount'      => 0,
                'remaining_amount' => $grandTotal,
                'status'           => 'unpaid',
                'notes'            => $request->notes,
                'created_by'       => auth()->id(),
            ]);

            foreach ($invoices as $inv) {
                $disc = isset($discounts[$inv->id]) ? (float) $discounts[$inv->id] : 0;
                InvoiceBatchItem::create([
                    'invoice_batch_id' => $batch->id,
                    'invoice_id'       => $inv->id,
                    'discount_amount'  => $disc,
                    'nett_amount'      => (float) $inv->total_amount - $disc,
                ]);
            }
        });

        return redirect()->route('invoice-batches.show', $batch)
            ->with('success', 'Batch invoice "' . $batch->batch_name . '" berhasil disimpan!');
    }

    /**
     * Detail batch
     */
    public function show(InvoiceBatch $invoiceBatch)
    {
        $invoiceBatch->load('client', 'creator', 'items.invoice.purchaseOrder');
        return view('transaction.invoices.batch-show', compact('invoiceBatch'));
    }

    /**
     * Print batch invoice
     */
    public function print(InvoiceBatch $invoiceBatch)
    {
        $invoiceBatch->load('client', 'creator', 'items.invoice.purchaseOrder.items', 'items.invoice.items.item');
        
        $invoices = $invoiceBatch->items->map(function ($item) {
            $inv = $item->invoice;
            $inv->batch_discount = $item->discount_amount;
            $inv->batch_nett = $item->nett_amount;
            return $inv;
        });

        $grandSubtotal = $invoiceBatch->total_amount;
        $grandDiscount = $invoiceBatch->total_discount;
        $grandTotal    = $invoiceBatch->grand_total;
        $client        = $invoiceBatch->client;

        return view('transaction.invoices.batch-print', compact(
            'invoices', 'client', 'grandSubtotal', 'grandDiscount', 'grandTotal', 'invoiceBatch'
        ));
    }

    /**
     * Bayar batch invoice (otomatis update masing-masing invoice)
     */
    public function pay(Request $request, InvoiceBatch $invoiceBatch)
    {
        if ($invoiceBatch->status === 'paid') {
            return back()->with('error', 'Batch ini sudah lunas.');
        }

        $request->validate([
            'payment_date'     => 'required|date',
            'amount'           => 'required|numeric|min:0.01',
            'payment_method'   => 'required|in:transfer,cash,giro',
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
        ]);

        $amount = (float) $request->amount;
        $remaining = (float) $invoiceBatch->remaining_amount;

        if ($amount > $remaining) {
            return back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan batch (Rp ' . number_format($remaining, 0, ',', '.') . ').');
        }

        DB::transaction(function () use ($request, $invoiceBatch, $amount) {
            // Update batch paid_amount & status
            $newPaid = (float) $invoiceBatch->paid_amount + $amount;
            $invoiceBatch->paid_amount      = $newPaid;
            $invoiceBatch->remaining_amount = (float) $invoiceBatch->grand_total - $newPaid;

            if ($newPaid >= (float) $invoiceBatch->grand_total) {
                $invoiceBatch->status = 'paid';
            } elseif ($newPaid > 0) {
                $invoiceBatch->status = 'partial';
            }
            $invoiceBatch->save();

            // Distribusikan pembayaran ke masing-masing invoice berdasarkan proporsi nett
            $remainingPayment = $amount;
            $items = $invoiceBatch->items()->with('invoice')->orderBy('id')->get();

            foreach ($items as $batchItem) {
                if ($remainingPayment <= 0) break;

                $inv = $batchItem->invoice;
                $invRemaining = (float) $inv->remaining_amount;

                if ($invRemaining <= 0) continue;

                $payThisInv = min($remainingPayment, $invRemaining);
                $remainingPayment -= $payThisInv;

                // Catat payment ke invoice satuan
                $payment = $inv->payments()->create([
                    'payment_date'     => $request->payment_date,
                    'amount'           => $payThisInv,
                    'payment_method'   => $request->payment_method,
                    'reference_number' => $request->reference_number,
                    'notes'            => '[Batch: ' . $invoiceBatch->batch_number . '] ' . ($request->notes ?? ''),
                    'created_by'       => auth()->id(),
                ]);

                // Update status invoice
                $inv->updatePaymentStatus();
            }
        });

        return back()->with('success', 'Pembayaran batch berhasil dicatat dan invoice satuan telah diperbarui.');
    }

    /**
     * API: get invoices available for new batch (exclude already batched)
     */
    public function getAvailableInvoices(Request $request)
    {
        $request->validate(['client_id' => 'required|exists:clients,id']);

        $allowedStatuses = ['unpaid', 'partial', 'overdue', 'paid'];
        $statuses = collect($request->statuses ?? [])
            ->filter(fn($s) => in_array($s, $allowedStatuses))
            ->values()
            ->toArray();

        if (empty($statuses)) {
            $statuses = ['unpaid', 'partial', 'overdue'];
        }

        $invoices = Invoice::where('client_id', $request->client_id)
            ->whereIn('status', $statuses)
            ->whereDoesntHave('batchItem') // <<< EXCLUDE yang sudah masuk batch
            ->when($request->date_from, fn($q, $d) => $q->where('invoice_date', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->where('invoice_date', '<=', $d))
            ->with('purchaseOrder.items', 'items')
            ->latest()
            ->get()
            ->map(function ($inv) {
                return [
                    'id'             => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'invoice_date'   => $inv->invoice_date ? $inv->invoice_date->format('d/m/Y') : '-',
                    'po_number'      => $inv->purchaseOrder->po_number ?? '-',
                    'po_date'        => $inv->purchaseOrder->po_date ? $inv->purchaseOrder->po_date->format('d/m/Y') : '-',
                    'total_items'    => $inv->purchaseOrder->items->sum('quantity'),
                    'total_amount'   => $inv->total_amount,
                    'remaining_amount' => $inv->remaining_amount,
                    'status'         => $inv->status,
                ];
            });

        return response()->json($invoices);
    }

    /**
     * Delete batch (hanya jika belum ada pembayaran)
     */
    public function destroy(InvoiceBatch $invoiceBatch)
    {
        if ((float) $invoiceBatch->paid_amount > 0) {
            return back()->with('error', 'Tidak bisa menghapus batch yang sudah ada pembayaran.');
        }

        DB::transaction(function () use ($invoiceBatch) {
            $invoiceBatch->items()->delete();
            $invoiceBatch->delete();
        });

        return redirect()->route('invoice-batches.index')
            ->with('success', 'Batch invoice berhasil dihapus.');
    }
}
