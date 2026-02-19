<x-app-layout>
    <x-slot name="title">Edit Invoice - {{ $invoice->invoice_number }}</x-slot>
    <div class="page-header">
        <h1>Edit Invoice: {{ $invoice->invoice_number }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoice</a></li><li class="breadcrumb-item active">Edit</li></ol></nav>
    </div>

    @if($invoice->paid_amount > 0)
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>Invoice ini sudah memiliki pembayaran sebesar <strong>{{ \App\Helpers\FormatHelper::rupiah($invoice->paid_amount) }}</strong>. Perubahan total akan menyesuaikan sisa tagihan secara otomatis.
    </div>
    @endif

    <form action="{{ route('invoices.update', $invoice) }}" method="POST" id="invoiceForm">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Informasi Invoice</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">No. Invoice</label>
                            <input type="text" class="form-control" value="{{ $invoice->invoice_number }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">PO</label>
                            <input type="text" class="form-control" value="{{ $invoice->purchaseOrder->po_number }} - {{ $invoice->purchaseOrder->client->name }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tgl Invoice <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="invoice_date" id="invoiceDate" value="{{ $invoice->invoice_date->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jatuh Tempo <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="due_date" id="dueDate" value="{{ $invoice->due_date->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diskon</label>
                            <div class="input-group">
                                <select class="form-select" name="discount_type" id="discountType" style="max-width:120px;">
                                    <option value="percentage" {{ $invoice->discount_type === 'percentage' ? 'selected' : '' }}>%</option>
                                    <option value="fixed" {{ $invoice->discount_type === 'fixed' ? 'selected' : '' }}>Rp</option>
                                </select>
                                <input type="number" class="form-control" name="discount_value" id="discountValue" value="{{ $invoice->discount_value }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">PPN (%)</label>
                            <input type="number" class="form-control" name="tax_percentage" id="taxPercent" value="{{ $invoice->tax_percentage }}" min="0" max="100" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="notes" rows="2">{{ $invoice->notes }}</textarea>
                        </div>
                        @if($invoice->paid_amount > 0)
                        <div class="alert alert-success py-2 mb-0" style="font-size: 12px;">
                            <strong>Terbayar:</strong> {{ \App\Helpers\FormatHelper::rupiah($invoice->paid_amount) }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">Item Invoice</div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                        <table class="table mb-0">
                            <thead class="sticky-top bg-white" style="z-index:2;"><tr><th width="40">#</th><th>Item</th><th>Qty</th><th>Satuan</th><th>Harga</th><th>Subtotal</th></tr></thead>
                            <tbody id="invItemsBody">
                                @foreach($invoice->purchaseOrder->items as $i => $poItem)
                                @php
                                    $invItem = $invoice->items->firstWhere('po_item_id', $poItem->id);
                                @endphp
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        {{ $poItem->item->name }}
                                        <input type="hidden" name="items[{{ $i }}][po_item_id]" value="{{ $poItem->id }}">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm inv-qty" name="items[{{ $i }}][quantity]" value="{{ $invItem ? $invItem->quantity : $poItem->quantity }}" min="0.01" step="0.01" required>
                                    </td>
                                    <td>{{ $poItem->unit }}</td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm inv-price" name="items[{{ $i }}][unit_price]" value="{{ $invItem ? $invItem->unit_price : $poItem->selling_price }}" min="0" step="1" required>
                                    </td>
                                    <td class="inv-subtotal fw-semibold">Rp 0</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <table class="table table-borderless mb-0" style="font-size: 14px;">
                            <tr><td class="text-end text-muted">Subtotal</td><td width="200" class="text-end fw-semibold" id="subtotalDisplay">Rp 0</td></tr>
                            <tr><td class="text-end text-muted">Diskon</td><td class="text-end text-danger" id="discountDisplay">- Rp 0</td></tr>
                            <tr><td class="text-end text-muted">PPN</td><td class="text-end" id="taxDisplay">Rp 0</td></tr>
                            <tr class="border-top"><td class="text-end fw-bold">Total</td><td class="text-end fw-bold fs-5" id="totalDisplay">Rp 0</td></tr>
                            @if($invoice->paid_amount > 0)
                            <tr><td class="text-end text-success">Terbayar</td><td class="text-end text-success">{{ \App\Helpers\FormatHelper::rupiah($invoice->paid_amount) }}</td></tr>
                            <tr><td class="text-end text-danger fw-bold">Sisa Setelah Edit</td><td class="text-end text-danger fw-bold" id="remainingDisplay">Rp 0</td></tr>
                            @endif
                        </table>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        const paidAmount = {{ $invoice->paid_amount }};

        function formatRupiah(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n)); }

        function recalc() {
            let sub = 0;
            document.querySelectorAll('#invItemsBody tr').forEach(r => {
                const q = parseFloat(r.querySelector('.inv-qty')?.value) || 0;
                const p = parseFloat(r.querySelector('.inv-price')?.value) || 0;
                const s = q * p;
                const cell = r.querySelector('.inv-subtotal');
                if (cell) cell.textContent = formatRupiah(s);
                sub += s;
            });
            document.getElementById('subtotalDisplay').textContent = formatRupiah(sub);

            const dType = document.getElementById('discountType').value;
            const dVal = parseFloat(document.getElementById('discountValue').value) || 0;
            const disc = dType === 'percentage' ? (sub * dVal / 100) : dVal;
            document.getElementById('discountDisplay').textContent = '- ' + formatRupiah(disc);

            const afterDisc = sub - disc;
            const tax = afterDisc * (parseFloat(document.getElementById('taxPercent').value) || 0) / 100;
            document.getElementById('taxDisplay').textContent = formatRupiah(tax);

            const total = afterDisc + tax;
            document.getElementById('totalDisplay').textContent = formatRupiah(total);

            const remainEl = document.getElementById('remainingDisplay');
            if (remainEl) {
                remainEl.textContent = formatRupiah(total - paidAmount);
            }
        }

        document.querySelectorAll('.inv-qty, .inv-price, #discountValue, #discountType, #taxPercent').forEach(el => el.addEventListener('input', recalc));
        document.getElementById('discountType').addEventListener('change', recalc);
        recalc();
    </script>
    @endpush
</x-app-layout>
