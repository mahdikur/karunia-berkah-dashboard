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
                            <select class="form-select select2 @error('client_id') is-invalid @enderror" name="client_id" id="clientSelect" required>
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
                                        <th width="180">Harga Beli</th>
                                        <th width="180">Harga Jual</th>
                                        <th width="180">Subtotal</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr class="item-row" data-index="0">
                                        <td>
                                            <select class="form-select form-select-sm item-select select2" name="items[0][item_id]" required>
                                                <option value="">Pilih Item</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->id }}" data-unit="{{ $item->unit }}">{{ $item->code }} - {{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" class="form-control form-control-sm qty-input" name="items[0][quantity]" min="0.01" step="0.01" value="1" required></td>
                                        <td><input type="text" class="form-control form-control-sm unit-input" name="items[0][unit]" required></td>
                                        <td><input type="number" class="form-control form-control-sm purchase-price-input" name="items[0][purchase_price]" min="0" step="1" value="0"></td>
                                        <td><input type="number" class="form-control form-control-sm price-input" name="items[0][selling_price]" min="0" step="1" value="0" required></td>
                                        <td class="subtotal-cell fw-semibold">Rp 0</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x"></i></button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="5" class="text-end fw-bold">Total (Beli):</td>
                                        <td class="fw-bold text-muted" id="totalPurchase">Rp 0</td>
                                        <td></td>
                                    </tr>
                                    <tr class="table-light">
                                        <td colspan="5" class="text-end fw-bold">Total (Jual):</td>
                                        <td class="fw-bold" id="grandTotal">Rp 0</td>
                                        <td></td>
                                    </tr>
                                    <tr class="table-success">
                                        <td colspan="5" class="text-end fw-bold">Estimasi Profit:</td>
                                        <td class="fw-bold" id="profitEstimate">Rp 0</td>
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
            let totalJual = 0;
            let totalBeli = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const priceJual = parseFloat(row.querySelector('.price-input').value) || 0;
                const priceBeli = parseFloat(row.querySelector('.purchase-price-input').value) || 0;
                
                const subJual = qty * priceJual;
                const subBeli = qty * priceBeli;
                
                row.querySelector('.subtotal-cell').textContent = formatRupiah(subJual);
                totalJual += subJual;
                totalBeli += subBeli;
            });
            document.getElementById('totalPurchase').textContent = formatRupiah(totalBeli);
            document.getElementById('grandTotal').textContent = formatRupiah(totalJual);
            document.getElementById('profitEstimate').textContent = formatRupiah(totalJual - totalBeli);
        }

        function buildItemOptions() {
            let html = '<option value="">Pilih Item</option>';
            items.forEach(i => {
                html += `<option value="${i.id}" data-unit="${i.unit}">${i.code} - ${i.name}</option>`;
            });
            return html;
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Check for jQuery
            if (typeof $ !== 'undefined') {
                $(document).ready(function() {
                    $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
                    $('.item-select.select2').select2({ theme: 'bootstrap-5', width: '100%' });
                    
                    document.querySelectorAll('.item-row').forEach(attachRowEvents);
                    calculateTotals();
                });
            } else {
                console.error("jQuery is not loaded");
                // Fallback for non-jQuery Select2 initialization if needed, or just proceed without it
                document.querySelectorAll('.item-row').forEach(attachRowEvents);
                calculateTotals();
            }

            document.getElementById('addItem').addEventListener('click', function() {
                const row = document.createElement('tr');
                row.className = 'item-row';
                row.dataset.index = itemIndex;
                row.innerHTML = `
                    <td><select class="form-select form-select-sm item-select select2" name="items[${itemIndex}][item_id]" required>${buildItemOptions()}</select></td>
                    <td><input type="number" class="form-control form-control-sm qty-input" name="items[${itemIndex}][quantity]" min="0.01" step="0.01" value="1" required></td>
                    <td><input type="text" class="form-control form-control-sm unit-input" name="items[${itemIndex}][unit]" required></td>
                    <td><input type="number" class="form-control form-control-sm purchase-price-input" name="items[${itemIndex}][purchase_price]" min="0" step="1" value="0"></td>
                    <td><input type="number" class="form-control form-control-sm price-input" name="items[${itemIndex}][selling_price]" min="0" step="1" value="0" required></td>
                    <td class="subtotal-cell fw-semibold">Rp 0</td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x"></i></button></td>
                `;
                document.getElementById('itemsBody').appendChild(row);
                
                if (typeof $ !== 'undefined') {
                    $(row.querySelector('.item-select')).select2({ theme: 'bootstrap-5', width: '100%' });
                }

                attachRowEvents(row);
                itemIndex++;
            });

            function attachRowEvents(row) {
                const selectEl = row.querySelector('.item-select');
                
                const handleSelection = function() {
                    const selectedOption = selectEl.querySelector(`option[value="${selectEl.value}"]`);
                    const unit = selectedOption ? selectedOption.dataset.unit : '';
                    row.querySelector('.unit-input').value = unit || '';

                    // Fetch latest price
                    const clientId = document.getElementById('clientSelect').value;
                    if (clientId && selectEl.value) {
                        fetch(`{{ route('api.item-price') }}?client_id=${clientId}&item_id=${selectEl.value}`)
                            .then(r => r.json())
                            .then(data => {
                                if (data.selling_price !== undefined && data.selling_price !== null) {
                                    row.querySelector('.price-input').value = data.selling_price;
                                } else {
                                    row.querySelector('.price-input').value = 0;
                                }
                                if (data.purchase_price !== undefined && data.purchase_price !== null) {
                                    row.querySelector('.purchase-price-input').value = data.purchase_price;
                                } else {
                                    row.querySelector('.purchase-price-input').value = 0;
                                }
                                calculateTotals();
                            });
                    }
                };

                if (typeof $ !== 'undefined') {
                    $(selectEl).on('select2:select change', handleSelection);
                } else {
                    selectEl.addEventListener('change', handleSelection);
                }

                row.querySelector('.qty-input').addEventListener('input', calculateTotals);
                row.querySelector('.purchase-price-input').addEventListener('input', calculateTotals);
                row.querySelector('.price-input').addEventListener('input', calculateTotals);
                row.querySelector('.remove-item').addEventListener('click', function() {
                    if (document.querySelectorAll('.item-row').length > 1) {
                        if (typeof $ !== 'undefined') {
                            $(row.querySelector('.item-select')).select2('destroy');
                        }
                        row.remove();
                        calculateTotals();
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
