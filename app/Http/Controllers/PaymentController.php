<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        // Default date range: past 1 month
        $dateFrom = $request->date_from ?? now()->subMonth()->format('Y-m-d');
        $dateTo   = $request->date_to   ?? now()->format('Y-m-d');

        $payments = Payment::with('invoice.client', 'creator')
            ->when($request->search, fn($q, $s) => $q->whereHas('invoice', fn($iq) => $iq->where('invoice_number', 'like', "%{$s}%")))
            ->when($request->client_id, fn($q, $c) => $q->whereHas('invoice', fn($iq) => $iq->where('client_id', $c)))
            ->when($request->payment_method, fn($q, $m) => $q->where('payment_method', $m))
            ->where('payment_date', '>=', $dateFrom)
            ->where('payment_date', '<=', $dateTo)
            ->latest()
            ->paginate(25);

        $clients = \App\Models\Client::active()->orderBy('name')->get();

        return view('transaction.payments.index', compact('payments', 'clients', 'dateFrom', 'dateTo'));
    }

    public function create(Request $request)
    {
        $invoices = Invoice::whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->with('client')
            ->latest()
            ->get();

        $selectedInvoice = $request->invoice_id ? Invoice::with('client')->find($request->invoice_id) : null;

        return view('transaction.payments.create', compact('invoices', 'selectedInvoice'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'payment_date' => 'required|date|before_or_equal:today',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:transfer,cash,giro',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        if ($request->amount > $invoice->remaining_amount) {
            return back()->withInput()->with('error', 'Jumlah pembayaran melebihi sisa tagihan.');
        }

        Payment::create([
            'invoice_id' => $invoice->id,
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        $invoice->updatePaymentStatus();

        // Update PO status if fully paid
        if ($invoice->status === 'paid' && $invoice->purchaseOrder) {
            $invoice->purchaseOrder->update(['status' => 'completed']);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;
        $payment->delete();
        $invoice->updatePaymentStatus();

        return back()->with('success', 'Pembayaran berhasil dihapus.');
    }
}
