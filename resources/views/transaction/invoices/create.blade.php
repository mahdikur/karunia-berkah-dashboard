<x-app-layout>
    <x-slot name="title">Buat Invoice</x-slot>
    <div class="page-header">
        <h1>Buat Invoice</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoice</a></li><li class="breadcrumb-item active">Buat</li></ol></nav>
    </div>

    <form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
        @csrf
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Informasi Invoice</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Purchase Order <span class="text-danger">*</span></label>
                            <select class="form-select" name="purchase_order_id" id="poSelect" required>
                                <option value="">Pilih PO</option>
                                @foreach($purchaseOrders as $po)
                                    <option value="{{ $po->id }}" {{ ($selectedPo?->id ?? old('purchase_order_id')) == $po->id ? 'selected' : '' }}
                                        data-client="{{ $po->client->name }}" data-terms="{{ $po->client->payment_terms }}"
                                        data-items="{{ json_encode($po->items->map(fn($i) => ['id'=>$i->id,'name'=>$i->item->name,'qty'=>$i->quantity,'unit'=>$i->unit,'price'=>$i->selling_price])) }}">
                                        {{ $po->po_number }} - {{ $po->client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client</label>
                            <input type="text" class="form-control" id="clientName" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tgl Invoice <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="invoice_date" id="invoiceDate" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jatuh Tempo <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="due_date" id="dueDate" value="{{ old('due_date') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diskon</label>
                            <div class="input-group">
                                <select class="form-select" name="discount_type" id="discountType" style="max-width:120px;">
                                    <option value="percentage">%</option>
                                    <option value="fixed">Rp</option>
                                </select>
                                <input type="number" class="form-control" name="discount_value" id="discountValue" value="0" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">PPN (%)</label>
                            <input type="number" class="form-control" name="tax_percentage" id="taxPercent" value="11" min="0" max="100" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">Item Invoice</div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead><tr><th>Item</th><th>Qty</th><th>Satuan</th><th>Harga</th><th>Subtotal</th></tr></thead>
                            <tbody id="invItemsBody">
                                @if($selectedPo)
                                    @foreach($selectedPo->items as $poItem)
                                    <tr>
                                        <td>{{ $poItem->item->name }}
                                            <input type="hidden" name="items[{{ $loop->index }}][po_item_id]" value="{{ $poItem->id }}">
                                        </td>
                                        <td><input type="number" class="form-control form-control-sm inv-qty" name="items[{{ $loop->index }}][quantity]" value="{{ $poItem->quantity }}" min="0.01" step="0.01" required></td>
                                        <td>{{ $poItem->unit }}</td>
                                        <td><input type="number" class="form-control form-control-sm inv-price" name="items[{{ $loop->index }}][unit_price]" value="{{ $poItem->selling_price }}" min="0" step="1" required></td>
                                        <td class="inv-subtotal fw-semibold">Rp 0</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr id="emptyRow"><td colspan="5" class="text-center py-3 text-muted">Pilih PO terlebih dahulu</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <table class="table table-borderless mb-0" style="font-size: 14px;">
                            <tr><td class="text-end text-muted">Subtotal</td><td width="200" class="text-end fw-semibold" id="subtotalDisplay">Rp 0</td></tr>
                            <tr><td class="text-end text-muted">Diskon</td><td class="text-end text-danger" id="discountDisplay">- Rp 0</td></tr>
                            <tr><td class="text-end text-muted">PPN</td><td class="text-end" id="taxDisplay">Rp 0</td></tr>
                            <tr class="border-top"><td class="text-end fw-bold">Total</td><td class="text-end fw-bold fs-5" id="totalDisplay">Rp 0</td></tr>
                        </table>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Invoice</button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function formatRupiah(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n)); }

        function recalc() {
            let sub = 0;
            document.querySelectorAll('#invItemsBody tr:not(#emptyRow)').forEach(r => {
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
            document.getElementById('totalDisplay').textContent = formatRupiah(afterDisc + tax);
        }

        document.getElementById('poSelect').addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            document.getElementById('clientName').value = opt.dataset.client || '';
            const terms = parseInt(opt.dataset.terms) || 0;
            const invDate = new Date(document.getElementById('invoiceDate').value || Date.now());
            const due = new Date(invDate.getTime() + terms * 86400000);
            document.getElementById('dueDate').value = due.toISOString().split('T')[0];

            const body = document.getElementById('invItemsBody');
            if (!this.value) { body.innerHTML = '<tr id="emptyRow"><td colspan="5" class="text-center py-3 text-muted">Pilih PO</td></tr>'; recalc(); return; }
            const items = JSON.parse(opt.dataset.items || '[]');
            body.innerHTML = items.map((it, i) => `<tr><td>${it.name}<input type="hidden" name="items[${i}][po_item_id]" value="${it.id}"></td><td><input type="number" class="form-control form-control-sm inv-qty" name="items[${i}][quantity]" value="${it.qty}" min="0.01" step="0.01" required></td><td>${it.unit}</td><td><input type="number" class="form-control form-control-sm inv-price" name="items[${i}][unit_price]" value="${it.price}" min="0" step="1" required></td><td class="inv-subtotal fw-semibold">Rp 0</td></tr>`).join('');
            body.querySelectorAll('.inv-qty, .inv-price').forEach(el => el.addEventListener('input', recalc));
            recalc();
        });

        document.querySelectorAll('.inv-qty, .inv-price, #discountValue, #discountType, #taxPercent').forEach(el => el.addEventListener('input', recalc));
        document.getElementById('discountType').addEventListener('change', recalc);
        recalc();
    </script>
    @endpush
</x-app-layout>
