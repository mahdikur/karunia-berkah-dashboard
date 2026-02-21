<x-app-layout>
    <x-slot name="title">Invoice {{ $invoice->invoice_number }}</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>{{ $invoice->invoice_number }}</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoice</a></li><li class="breadcrumb-item active">Detail</li></ol></nav>
        </div>
        <div class="d-flex gap-2">
            @if(in_array($invoice->status, ['unpaid','partial','overdue']))
                <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success"><i class="bi bi-cash me-1"></i>Catat Pembayaran</a>
            @endif
            @if($invoice->status !== 'paid')
                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
                <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#regenerateModal"><i class="bi bi-arrow-repeat me-1"></i>Regenerate</button>
            @endif
            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>Print</a>
            @if($invoice->paid_amount == 0)
            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" id="del-invoice-form">
                @csrf @method('DELETE')
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete('del-invoice-form')"><i class="bi bi-trash me-1"></i>Hapus</button>
            </form>
            @endif
        </div>
    </div>

    {{-- Regenerate Modal --}}
    <div class="modal fade" id="regenerateModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-arrow-repeat me-1"></i>Regenerate Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('invoices.regenerate', $invoice) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted small">Pilih sumber data untuk regenerate item invoice ini. Data item invoice lama akan digantikan.</p>
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="regenerate_from" id="reg_po" value="po" checked>
                                <label class="form-check-label" for="reg_po">
                                    <strong>Dari PO</strong><br>
                                    <small class="text-muted">{{ $invoice->purchaseOrder->po_number }}</small>
                                </label>
                            </div>
                        </div>
                        @if($invoice->purchaseOrder->deliveryNotes->count() > 0)
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="regenerate_from" id="reg_dn" value="dn">
                                <label class="form-check-label" for="reg_dn">
                                    <strong>Dari Surat Jalan</strong><br>
                                    <small class="text-muted">{{ $invoice->purchaseOrder->deliveryNotes->last()?->dn_number }} (terbaru)</small>
                                </label>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Regenerate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 text-center">{!! $invoice->status_badge !!}</div>
                    <table class="table table-borderless mb-0" style="font-size: 13px;">
                        <tr><td class="text-muted">Client</td><td class="fw-semibold">{{ $invoice->client->name }}</td></tr>
                        <tr><td class="text-muted">PO</td><td><a href="{{ route('purchase-orders.show', $invoice->purchaseOrder) }}">{{ $invoice->purchaseOrder->po_number }}</a></td></tr>
                        <tr><td class="text-muted">Tgl Invoice</td><td>{{ $invoice->invoice_date->format('d/m/Y') }}</td></tr>
                        <tr><td class="text-muted">Jatuh Tempo</td><td class="{{ $invoice->due_date < now() && $invoice->status !== 'paid' ? 'text-danger fw-bold' : '' }}">{{ $invoice->due_date->format('d/m/Y') }}</td></tr>
                        <tr><td class="text-muted">Dibuat</td><td>{{ $invoice->creator->name ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <table class="table table-borderless mb-0" style="font-size: 13px;">
                        <tr><td class="text-muted">Subtotal</td><td class="text-end">{{ \App\Helpers\FormatHelper::rupiah($invoice->subtotal) }}</td></tr>
                        <tr><td class="text-muted">Diskon</td><td class="text-end text-danger">- {{ \App\Helpers\FormatHelper::rupiah($invoice->discount_amount) }}</td></tr>
                        <tr><td class="text-muted">PPN ({{ $invoice->tax_percentage }}%)</td><td class="text-end">{{ \App\Helpers\FormatHelper::rupiah($invoice->tax_amount) }}</td></tr>
                        <tr class="border-top"><td class="fw-bold">Total</td><td class="text-end fw-bold">{{ \App\Helpers\FormatHelper::rupiah($invoice->total_amount) }}</td></tr>
                        <tr><td class="text-success">Terbayar</td><td class="text-end text-success">{{ \App\Helpers\FormatHelper::rupiah($invoice->paid_amount) }}</td></tr>
                        <tr><td class="text-danger fw-bold">Sisa</td><td class="text-end text-danger fw-bold">{{ \App\Helpers\FormatHelper::rupiah($invoice->remaining_amount) }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Item Invoice</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>#</th><th>Item</th><th>Qty</th><th>Satuan</th><th>Harga</th><th>Subtotal</th></tr></thead>
                        <tbody>
                            @foreach($invoice->items as $i => $invItem)
                                <tr><td>{{ $i+1 }}</td><td>{{ $invItem->item->name ?? '-' }}</td><td>{{ number_format($invItem->quantity, 2) }}</td><td>{{ $invItem->unit }}</td><td>{{ \App\Helpers\FormatHelper::rupiah($invItem->unit_price) }}</td><td class="fw-semibold">{{ \App\Helpers\FormatHelper::rupiah($invItem->subtotal) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-cash-coin me-2"></i>Riwayat Pembayaran</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Tanggal</th><th>Jumlah</th><th>Metode</th><th>Referensi</th><th>Oleh</th><th></th></tr></thead>
                        <tbody>
                            @forelse($invoice->payments as $pay)
                                <tr>
                                    <td>{{ $pay->payment_date->format('d/m/Y') }}</td>
                                    <td class="fw-semibold text-success">{{ \App\Helpers\FormatHelper::rupiah($pay->amount) }}</td>
                                    <td>{!! $pay->payment_method_label !!}</td>
                                    <td>{{ $pay->reference_number ?? '-' }}</td>
                                    <td>{{ $pay->creator->name ?? '-' }}</td>
                                    <td>
                                        <form action="{{ route('payments.destroy', $pay) }}" method="POST" id="del-pay-{{ $pay->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('del-pay-{{ $pay->id }}')"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-3 text-muted">Belum ada pembayaran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
