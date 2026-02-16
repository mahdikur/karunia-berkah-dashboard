<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with('invoice.client', 'creator')
            ->when($request->search, fn($q, $s) => $q->whereHas('invoice', fn($iq) => $iq->where('invoice_number', 'like', "%{$s}%")))
            ->when($request->payment_method, fn($q, $m) => $q->where('payment_method', $m))
            ->when($request->date_from, fn($q, $d) => $q->where('payment_date', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->where('payment_date', '<=', $d))
            ->latest()
            ->paginate(25);

        return view('transaction.payments.index', compact('payments'));
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
