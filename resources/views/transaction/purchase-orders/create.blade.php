<x-app-layout>
    <x-slot name="title">Buat Purchase Order</x-slot>
    <div class="page-header">
        <h1>Buat Purchase Order</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">PO</a></li><li class="breadcrumb-item active">Buat</li></ol></nav>
    </div>

    <form action="{{ route('purchase-orders.store') }}" method="POST" id="poForm">
        @csrf
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Informasi PO</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <select class="form-select @error('client_id') is-invalid @enderror" name="client_id" id="clientSelect" required>
                                <option value="">Pilih Client</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal PO <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="po_date" value="{{ old('po_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Pengiriman</label>
                            <input type="date" class="form-control" name="delivery_date" value="{{ old('delivery_date') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Daftar Item</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItem">
                            <i class="bi bi-plus me-1"></i>Tambah Item
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th width="100">Qty</th>
                                        <th width="80">Satuan</th>
                                        <th width="180">Harga Jual</th>
                                        <th width="180">Subtotal</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr class="item-row" data-index="0">
                                        <td>
                                            <select class="form-select form-select-sm item-select" name="items[0][item_id]" required>
                                                <option value="">Pilih Item</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->id }}" data-unit="{{ $item->unit }}">{{ $item->code }} - {{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" class="form-control form-control-sm qty-input" name="items[0][quantity]" min="0.01" step="0.01" value="1" required></td>
                                        <td><input type="text" class="form-control form-control-sm unit-input" name="items[0][unit]" required></td>
                                        <td><input type="number" class="form-control form-control-sm price-input" name="items[0][selling_price]" min="0" step="1" value="0" required></td>
                                        <td class="subtotal-cell fw-semibold">Rp 0</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x"></i></button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="4" class="text-end fw-bold">Total:</td>
                                        <td class="fw-bold" id="grandTotal">Rp 0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-secondary"><i class="bi bi-save me-1"></i>Simpan sebagai Draft</button>
                    <button type="submit" name="submit_approval" value="1" class="btn btn-primary"><i class="bi bi-send me-1"></i>Ajukan Approval</button>
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        let itemIndex = 1;
        const items = @json($items);

        function formatRupiah(num) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
        }

        function calculateTotals() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                const sub = qty * price;
                row.querySelector('.subtotal-cell').textContent = formatRupiah(sub);
                total += sub;
            });
            document.getElementById('grandTotal').textContent = formatRupiah(total);
        }

        function buildItemOptions() {
            let html = '<option value="">Pilih Item</option>';
            items.forEach(i => {
                html += `<option value="${i.id}" data-unit="${i.unit}">${i.code} - ${i.name}</option>`;
            });
            return html;
        }

        document.getElementById('addItem').addEventListener('click', function() {
            const row = document.createElement('tr');
            row.className = 'item-row';
            row.dataset.index = itemIndex;
            row.innerHTML = `
                <td><select class="form-select form-select-sm item-select" name="items[${itemIndex}][item_id]" required>${buildItemOptions()}</select></td>
                <td><input type="number" class="form-control form-control-sm qty-input" name="items[${itemIndex}][quantity]" min="0.01" step="0.01" value="1" required></td>
                <td><input type="text" class="form-control form-control-sm unit-input" name="items[${itemIndex}][unit]" required></td>
                <td><input type="number" class="form-control form-control-sm price-input" name="items[${itemIndex}][selling_price]" min="0" step="1" value="0" required></td>
                <td class="subtotal-cell fw-semibold">Rp 0</td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x"></i></button></td>
            `;
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
                if (document.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                    calculateTotals();
                }
            });
        }

        document.querySelectorAll('.item-row').forEach(attachRowEvents);
        calculateTotals();
    </script>
    @endpush
</x-app-layout>
