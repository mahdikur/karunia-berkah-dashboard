<x-app-layout>
    <x-slot name="title">Buat Surat Jalan</x-slot>
    <div class="page-header">
        <h1>Buat Surat Jalan</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('delivery-notes.index') }}">Surat Jalan</a></li><li class="breadcrumb-item active">Buat</li></ol></nav>
    </div>

    <form action="{{ route('delivery-notes.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Informasi</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Purchase Order <span class="text-danger">*</span></label>
                            <select class="form-select" name="purchase_order_id" id="poSelect" required>
                                <option value="">Pilih PO</option>
                                @foreach($purchaseOrders as $po)
                                    <option value="{{ $po->id }}" {{ ($selectedPo?->id ?? old('purchase_order_id')) == $po->id ? 'selected' : '' }}
                                        data-items="{{ json_encode($po->items->map(fn($i) => ['id'=>$i->id,'name'=>$i->item->name,'qty'=>$i->quantity,'unit'=>$i->unit])) }}">
                                        {{ $po->po_number }} - {{ $po->client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="dn_date" value="{{ old('dn_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Pengiriman <span class="text-danger">*</span></label>
                            <select class="form-select" name="delivery_type" required>
                                <option value="full">Full</option>
                                <option value="partial">Partial</option>
                            </select>
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
                    <div class="card-header">Item yang Dikirim</div>
                    <div class="card-body p-0">
                        <table class="table mb-0" id="dnItems">
                            <thead><tr><th>Item</th><th>Qty PO</th><th>Qty Kirim</th><th>Satuan</th></tr></thead>
                            <tbody id="dnItemsBody">
                                @if($selectedPo)
                                    @foreach($selectedPo->items as $poItem)
                                    <tr>
                                        <td>{{ $poItem->item->name }}<input type="hidden" name="items[{{ $loop->index }}][po_item_id]" value="{{ $poItem->id }}"></td>
                                        <td>{{ number_format($poItem->quantity, 2) }}</td>
                                        <td><input type="number" class="form-control form-control-sm" name="items[{{ $loop->index }}][quantity_delivered]" value="{{ $poItem->quantity }}" min="0.01" step="0.01" required></td>
                                        <td>{{ $poItem->unit }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="4" class="text-center py-3 text-muted">Pilih PO terlebih dahulu</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                    <a href="{{ route('delivery-notes.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        document.getElementById('poSelect').addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const body = document.getElementById('dnItemsBody');
            if (!this.value) {
                body.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-muted">Pilih PO terlebih dahulu</td></tr>';
                return;
            }
            const items = JSON.parse(opt.dataset.items || '[]');
            body.innerHTML = items.map((it, i) => `
                <tr>
                    <td>${it.name}<input type="hidden" name="items[${i}][po_item_id]" value="${it.id}"></td>
                    <td>${parseFloat(it.qty).toFixed(2)}</td>
                    <td><input type="number" class="form-control form-control-sm" name="items[${i}][quantity_delivered]" value="${it.qty}" min="0.01" step="0.01" required></td>
                    <td>${it.unit}</td>
                </tr>
            `).join('');
        });
    </script>
    @endpush
</x-app-layout>
