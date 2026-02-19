<x-app-layout>
    <x-slot name="title">Edit Purchase Order</x-slot>
    <div class="page-header">
        <h1>Edit PO: {{ $purchaseOrder->po_number }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">PO</a></li><li class="breadcrumb-item active">Edit</li></ol></nav>
    </div>

    @if(!$canEditItems && ($hasDeliveryNotes || $hasInvoice))
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i><strong>Perhatian:</strong> PO ini sudah memiliki 
        @if($hasDeliveryNotes) Surat Jalan @endif
        @if($hasDeliveryNotes && $hasInvoice) dan @endif
        @if($hasInvoice) Invoice @endif
        terkait. Anda hanya bisa mengubah <strong>harga</strong> dan <strong>informasi umum</strong> (tanggal kirim, catatan). Item tidak bisa ditambah/dihapus.
    </div>
    @endif

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
                            <label class="form-label">Status</label>
                            <div>{!! $purchaseOrder->status_badge !!}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="client_id" id="clientSelect" required {{ !$canEditItems ? 'disabled' : '' }}>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ $purchaseOrder->client_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @if(!$canEditItems)<input type="hidden" name="client_id" value="{{ $purchaseOrder->client_id }}">@endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal PO <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="po_date" value="{{ $purchaseOrder->po_date->format('Y-m-d') }}" required {{ !$canEditItems ? 'readonly' : '' }}>
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
                        <div class="d-flex gap-2">
                            @if($canEditItems)
                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#quickProductModal"><i class="bi bi-box-seam me-1"></i>Produk Baru</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addItem"><i class="bi bi-plus me-1"></i>Tambah Item</button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table mb-0" id="itemsTable">
                                <thead class="sticky-top bg-white" style="z-index:2;"><tr><th width="40">#</th><th>Item</th><th width="100">Qty</th><th width="80">Satuan</th><th width="180">Harga Beli</th><th width="180">Harga Jual</th><th width="180">Subtotal</th><th width="50"></th></tr></thead>
                                <tbody id="itemsBody">
                                    @foreach($purchaseOrder->items as $i => $poItem)
                                    <tr class="item-row" data-index="{{ $i }}">
                                        <td class="row-num text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <select class="form-select form-select-sm item-select select2" name="items[{{ $i }}][item_id]" required {{ !$canEditItems ? 'disabled' : '' }}>
                                                <option value="">Pilih Item</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->id }}" data-unit="{{ $item->unit }}" {{ $poItem->item_id == $item->id ? 'selected' : '' }}>{{ $item->code }} - {{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                            @if(!$canEditItems)<input type="hidden" name="items[{{ $i }}][item_id]" value="{{ $poItem->item_id }}">@endif
                                        </td>
                                        <td><input type="number" class="form-control form-control-sm qty-input" name="items[{{ $i }}][quantity]" value="{{ $poItem->quantity }}" min="0.01" step="0.01" required {{ !$canEditItems ? 'readonly' : '' }}></td>
                                        <td><input type="text" class="form-control form-control-sm unit-input" name="items[{{ $i }}][unit]" value="{{ $poItem->unit }}" required {{ !$canEditItems ? 'readonly' : '' }}></td>
                                        <td><input type="number" class="form-control form-control-sm purchase-price-input" name="items[{{ $i }}][purchase_price]" value="{{ $poItem->purchase_price }}" min="0" step="1"></td>
                                        <td><input type="number" class="form-control form-control-sm price-input" name="items[{{ $i }}][selling_price]" value="{{ $poItem->selling_price }}" min="0" step="1" required></td>
                                        <td class="subtotal-cell fw-semibold">Rp 0</td>
                                        <td>
                                            @if($canEditItems)
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light"><td colspan="6" class="text-end fw-bold">Total (Beli):</td><td class="fw-bold text-muted" id="totalPurchase">Rp 0</td><td></td></tr>
                                    <tr class="table-light"><td colspan="6" class="text-end fw-bold">Total (Jual):</td><td class="fw-bold" id="grandTotal">Rp 0</td><td></td></tr>
                                    <tr class="table-success"><td colspan="6" class="text-end fw-bold">Estimasi Profit:</td><td class="fw-bold" id="profitEstimate">Rp 0</td><td></td></tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    @if($canEditItems)
                    <button type="submit" class="btn btn-secondary"><i class="bi bi-save me-1"></i>Simpan sbg Draft</button>
                    <button type="submit" name="submit_approval" value="1" class="btn btn-primary"><i class="bi bi-send me-1"></i>Ajukan Approval</button>
                    @else
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                    @endif
                    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>

    {{-- Quick Product Add Modal --}}
    <div class="modal fade" id="quickProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Produk Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="quickProductAlert"></div>
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="qp_category_id" required>
                            <option value="">Pilih Kategori</option>
                            @php $categories = \App\Models\Category::active()->orderBy('name')->get(); @endphp
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Produk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="qp_code" placeholder="Contoh: PRD001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="qp_name" placeholder="Nama produk">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="qp_unit" placeholder="kg, pcs, dus, dll">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="saveQuickProduct"><i class="bi bi-check-lg me-1"></i>Simpan & Tambah</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let itemIndex = {{ count($purchaseOrder->items) }};
        let items = @json($items);
        const canEditItems = {{ $canEditItems ? 'true' : 'false' }};

        function formatRupiah(num) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(num); }
        function calculateTotals() {
            let totalJual = 0;
            let totalBeli = 0;
            document.querySelectorAll('.item-row').forEach(r => {
                const qty = parseFloat(r.querySelector('.qty-input').value) || 0;
                const priceJual = parseFloat(r.querySelector('.price-input').value) || 0;
                const priceBeli = parseFloat(r.querySelector('.purchase-price-input').value) || 0;
                
                const subJual = qty * priceJual;
                const subBeli = qty * priceBeli;
                
                r.querySelector('.subtotal-cell').textContent = formatRupiah(subJual);
                totalJual += subJual;
                totalBeli += subBeli;
            });
            document.getElementById('totalPurchase').textContent = formatRupiah(totalBeli);
            document.getElementById('grandTotal').textContent = formatRupiah(totalJual);
            document.getElementById('profitEstimate').textContent = formatRupiah(totalJual - totalBeli);
        }
        function buildItemOptions() {
            let h = '<option value="">Pilih Item</option>';
            items.forEach(i => h += `<option value="${i.id}" data-unit="${i.unit}">${i.code} - ${i.name}</option>`);
            return h;
        }

        document.addEventListener("DOMContentLoaded", function() {
            if (typeof $ !== 'undefined') {
                $(document).ready(function() {
                    $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
                    $('.item-select.select2').select2({ theme: 'bootstrap-5', width: '100%' });
                    
                    document.querySelectorAll('.item-row').forEach(attachRowEvents);
                    calculateTotals();
                });
            } else {
                document.querySelectorAll('.item-row').forEach(attachRowEvents);
                calculateTotals();
            }

            if (canEditItems && document.getElementById('addItem')) {
                document.getElementById('addItem').addEventListener('click', function() {
                    const row = document.createElement('tr');
                    row.className = 'item-row';
                    row.innerHTML = `<td class="row-num text-muted">${document.querySelectorAll('.item-row').length + 1}</td><td><select class="form-select form-select-sm item-select select2" name="items[${itemIndex}][item_id]" required>${buildItemOptions()}</select></td><td><input type="number" class="form-control form-control-sm qty-input" name="items[${itemIndex}][quantity]" min="0.01" step="0.01" value="1" required></td><td><input type="text" class="form-control form-control-sm unit-input" name="items[${itemIndex}][unit]" required></td><td><input type="number" class="form-control form-control-sm purchase-price-input" name="items[${itemIndex}][purchase_price]" min="0" step="1" value="0"></td><td><input type="number" class="form-control form-control-sm price-input" name="items[${itemIndex}][selling_price]" min="0" step="1" value="0" required></td><td class="subtotal-cell fw-semibold">Rp 0</td><td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x"></i></button></td>`;
                    document.getElementById('itemsBody').appendChild(row);
                    
                    if (typeof $ !== 'undefined') {
                        $(row.querySelector('.item-select')).select2({ theme: 'bootstrap-5', width: '100%' });
                    }
                    
                    attachRowEvents(row);
                    itemIndex++;
                });
            }

            // Quick Product Save
            document.getElementById('saveQuickProduct')?.addEventListener('click', function() {
                const alertDiv = document.getElementById('quickProductAlert');
                alertDiv.innerHTML = '';

                const data = {
                    category_id: document.getElementById('qp_category_id').value,
                    code: document.getElementById('qp_code').value,
                    name: document.getElementById('qp_name').value,
                    unit: document.getElementById('qp_unit').value,
                };

                if (!data.category_id || !data.code || !data.name || !data.unit) {
                    alertDiv.innerHTML = '<div class="alert alert-danger py-2 small">Semua field wajib diisi.</div>';
                    return;
                }

                fetch('{{ route("api.items.quick-store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(r => r.json())
                .then(result => {
                    if (result.success) {
                        // Add to items list
                        items.push({id: result.item.id, code: result.item.code, name: result.item.name, unit: result.item.unit});
                        
                        // Update all select options
                        document.querySelectorAll('.item-select').forEach(sel => {
                            const newOption = document.createElement('option');
                            newOption.value = result.item.id;
                            newOption.dataset.unit = result.item.unit;
                            newOption.textContent = `${result.item.code} - ${result.item.name}`;
                            sel.appendChild(newOption);
                        });

                        // Close modal and clear
                        bootstrap.Modal.getInstance(document.getElementById('quickProductModal')).hide();
                        document.getElementById('qp_category_id').value = '';
                        document.getElementById('qp_code').value = '';
                        document.getElementById('qp_name').value = '';
                        document.getElementById('qp_unit').value = '';

                        Swal.fire({icon: 'success', title: 'Berhasil!', text: result.message, timer: 2000, showConfirmButton: false});
                    } else if (result.errors) {
                        let errHtml = '<div class="alert alert-danger py-2 small">';
                        Object.values(result.errors).forEach(msgs => msgs.forEach(m => errHtml += m + '<br>'));
                        errHtml += '</div>';
                        alertDiv.innerHTML = errHtml;
                    }
                })
                .catch(err => {
                    alertDiv.innerHTML = '<div class="alert alert-danger py-2 small">Terjadi error. Silakan coba lagi.</div>';
                });
            });

            function attachRowEvents(row) {
                const selectEl = row.querySelector('.item-select');
                
                const handleSelection = function() {
                    const selectedOption = selectEl.querySelector(`option[value="${selectEl.value}"]`);
                    const unit = selectedOption ? selectedOption.dataset.unit : '';
                    row.querySelector('.unit-input').value = unit || '';

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
                
                const removeBtn = row.querySelector('.remove-item');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        if (document.querySelectorAll('.item-row').length > 1) { 
                            if (typeof $ !== 'undefined') {
                                $(row.querySelector('.item-select')).select2('destroy');
                            }
                            row.remove(); 
                            calculateTotals();
                            renumberRows();
                        }
                    });
                }
            }

            function renumberRows() {
                document.querySelectorAll('.item-row').forEach((r, i) => {
                    const num = r.querySelector('.row-num');
                    if (num) num.textContent = i + 1;
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
