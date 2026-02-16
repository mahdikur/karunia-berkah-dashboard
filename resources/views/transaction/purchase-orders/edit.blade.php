<x-app-layout>
    <x-slot name="title">Edit Purchase Order</x-slot>
    <div class="page-header">
        <h1>Edit PO: {{ $purchaseOrder->po_number }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">PO</a></li><li class="breadcrumb-item active">Edit</li></ol></nav>
    </div>

    <form action="{{ route('purchase-orders.update', $purchaseOrder) }}" method="POST" id="poForm">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Informasi PO</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">No. PO</label>
                            <input type="text" class="form-control" value="{{ $purchaseOrder->po_number }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <select class="form-select" name="client_id" id="clientSelect" required>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ $purchaseOrder->client_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal PO <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="po_date" value="{{ $purchaseOrder->po_date->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Pengiriman</label>
                            <input type="date" class="form-control" name="delivery_date" value="{{ $purchaseOrder->delivery_date?->format('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="notes" rows="3">{{ $purchaseOrder->notes }}</textarea>
                        </div>
                        @if($purchaseOrder->status === 'rejected' && $purchaseOrder->rejected_reason)
                            <div class="alert alert-danger" style="font-size: 13px;">
                                <strong>Alasan ditolak:</strong> {{ $purchaseOrder->rejected_reason }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Daftar Item</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItem"><i class="bi bi-plus me-1"></i>Tambah Item</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0" id="itemsTable">
                                <thead><tr><th>Item</th><th width="100">Qty</th><th width="80">Satuan</th><th width="180">Harga Jual</th><th width="180">Subtotal</th><th width="50"></th></tr></thead>
                                <tbody id="itemsBody">
                                    @foreach($purchaseOrder->items as $i => $poItem)
                                    <tr class="item-row" data-index="{{ $i }}">
                                        <td>
                                            <select class="form-select form-select-sm item-select" name="items[{{ $i }}][item_id]" required>
                                                <option value="">Pilih Item</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->id }}" data-unit="{{ $item->unit }}" {{ $poItem->item_id == $item->id ? 'selected' : '' }}>{{ $item->code }} - {{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" class="form-control form-control-sm qty-input" name="items[{{ $i }}][quantity]" value="{{ $poItem->quantity }}" min="0.01" step="0.01" required></td>
                                        <td><input type="text" class="form-control form-control-sm unit-input" name="items[{{ $i }}][unit]" value="{{ $poItem->unit }}" required></td>
                                        <td><input type="number" class="form-control form-control-sm price-input" name="items[{{ $i }}][selling_price]" value="{{ $poItem->selling_price }}" min="0" step="1" required></td>
                                        <td class="subtotal-cell fw-semibold">Rp 0</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot><tr class="table-light"><td colspan="4" class="text-end fw-bold">Total:</td><td class="fw-bold" id="grandTotal">Rp 0</td><td></td></tr></tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-secondary"><i class="bi bi-save me-1"></i>Simpan sbg Draft</button>
                    <button type="submit" name="submit_approval" value="1" class="btn btn-primary"><i class="bi bi-send me-1"></i>Ajukan Approval</button>
                    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        let itemIndex = {{ count($purchaseOrder->items) }};
        const items = @json($items);
        function formatRupiah(num) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(num); }
        function calculateTotals() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(r => {
                const sub = (parseFloat(r.querySelector('.qty-input').value) || 0) * (parseFloat(r.querySelector('.price-input').value) || 0);
                r.querySelector('.subtotal-cell').textContent = formatRupiah(sub);
                total += sub;
            });
            document.getElementById('grandTotal').textContent = formatRupiah(total);
        }
        function buildItemOptions() {
            let h = '<option value="">Pilih Item</option>';
            items.forEach(i => h += `<option value="${i.id}" data-unit="${i.unit}">${i.code} - ${i.name}</option>`);
            return h;
        }
        document.getElementById('addItem').addEventListener('click', function() {
            const row = document.createElement('tr');
            row.className = 'item-row';
            row.innerHTML = `<td><select class="form-select form-select-sm item-select" name="items[${itemIndex}][item_id]" required>${buildItemOptions()}</select></td><td><input type="number" class="form-control form-control-sm qty-input" name="items[${itemIndex}][quantity]" min="0.01" step="0.01" value="1" required></td><td><input type="text" class="form-control form-control-sm unit-input" name="items[${itemIndex}][unit]" required></td><td><input type="number" class="form-control form-control-sm price-input" name="items[${itemIndex}][selling_price]" min="0" step="1" value="0" required></td><td class="subtotal-cell fw-semibold">Rp 0</td><td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x"></i></button></td>`;
            document.getElementById('itemsBody').appendChild(row);
            attachRowEvents(row);
            itemIndex++;
        });
        function attachRowEvents(row) {
            row.querySelector('.item-select').addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                row.querySelector('.unit-input').value = opt.dataset.unit || '';

                // Fetch latest price
                const clientId = document.getElementById('clientSelect').value;
                if (clientId && this.value) {
                    fetch(`{{ route('api.item-price') }}?client_id=${clientId}&item_id=${this.value}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.selling_price !== undefined && data.selling_price !== null) {
                                row.querySelector('.price-input').value = data.selling_price;
                            } else {
                                row.querySelector('.price-input').value = 0;
                            }
                            calculateTotals();
                        });
                }
            });
            row.querySelector('.qty-input').addEventListener('input', calculateTotals);
            row.querySelector('.price-input').addEventListener('input', calculateTotals);
            row.querySelector('.remove-item').addEventListener('click', function() {
                if (document.querySelectorAll('.item-row').length > 1) { row.remove(); calculateTotals(); }
            });
        }
        document.querySelectorAll('.item-row').forEach(attachRowEvents);
        calculateTotals();
    </script>
    @endpush
</x-app-layout>
