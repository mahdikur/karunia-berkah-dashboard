<x-app-layout>
    <x-slot name="title">Buat Retur</x-slot>
    <div class="page-header">
        <h1>Buat Retur Barang</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('return-notes.index') }}">Retur</a></li><li class="breadcrumb-item active">Buat</li></ol></nav>
    </div>

    <form action="{{ route('return-notes.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Informasi Retur</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Surat Jalan <span class="text-danger">*</span></label>
                            <select class="form-select" name="delivery_note_id" id="dnSelect" required>
                                <option value="">Pilih Surat Jalan</option>
                                @foreach($deliveryNotes as $dn)
                                    <option value="{{ $dn->id }}" {{ ($selectedDn?->id ?? old('delivery_note_id')) == $dn->id ? 'selected' : '' }}
                                        data-client="{{ $dn->client->name }}"
                                        data-po="{{ $dn->purchaseOrder->po_number }}"
                                        data-items="{{ json_encode($dn->items->map(fn($i) => [
                                            'id' => $i->id,
                                            'name' => $i->item->name,
                                            'qty' => $i->quantity_delivered,
                                            'unit' => $i->unit,
                                            'is_unavailable' => $i->is_unavailable
                                        ])) }}">
                                        {{ $dn->dn_number }} - {{ $dn->client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client</label>
                            <input type="text" class="form-control" id="clientName" value="{{ $selectedDn?->client->name ?? '' }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. PO</label>
                            <input type="text" class="form-control" id="poNumber" value="{{ $selectedDn?->purchaseOrder->po_number ?? '' }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Retur <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alasan Retur</label>
                            <select class="form-select" name="reason">
                                <option value="">Pilih Alasan</option>
                                <option value="Barang rusak">Barang rusak</option>
                                <option value="Barang tidak sesuai">Barang tidak sesuai</option>
                                <option value="Kualitas kurang">Kualitas kurang</option>
                                <option value="Kelebihan kirim">Kelebihan kirim</option>
                                <option value="Lainnya">Lainnya</option>
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
                    <div class="card-header">Item yang Diretur</div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                        <table class="table mb-0">
                            <thead class="sticky-top bg-white" style="z-index:2;"><tr><th width="40">#</th><th>Item</th><th>Qty Terkirim</th><th>Qty Retur</th><th>Satuan</th><th>Alasan</th></tr></thead>
                            <tbody id="returnItemsBody">
                                @if($selectedDn)
                                    @foreach($selectedDn->items as $i => $dnItem)
                                        @if(!$dnItem->is_unavailable)
                                        <tr>
                                            <td class="text-muted">{{ $i + 1 }}</td>
                                            <td>
                                                {{ $dnItem->item->name }}
                                                <input type="hidden" name="items[{{ $i }}][delivery_note_item_id]" value="{{ $dnItem->id }}">
                                            </td>
                                            <td>{{ number_format($dnItem->quantity_delivered, 2) }}</td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm" name="items[{{ $i }}][quantity_returned]" value="0" min="0" max="{{ $dnItem->quantity_delivered }}" step="0.01">
                                            </td>
                                            <td>{{ $dnItem->unit }}</td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" name="items[{{ $i }}][reason]" placeholder="Alasan retur item...">
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                @else
                                    <tr><td colspan="6" class="text-center py-3 text-muted">Pilih Surat Jalan terlebih dahulu</td></tr>
                                @endif
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Retur</button>
                    <a href="{{ route('return-notes.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function updateReturnTable() {
            const select = document.getElementById('dnSelect');
            const opt = select.options[select.selectedIndex];
            const body = document.getElementById('returnItemsBody');
            
            if (!opt || !select.value) {
                document.getElementById('clientName').value = '';
                document.getElementById('poNumber').value = '';
                body.innerHTML = '<tr><td colspan="6" class="text-center py-3 text-muted">Pilih Surat Jalan terlebih dahulu</td></tr>';
                return;
            }

            document.getElementById('clientName').value = opt.dataset.client || '';
            document.getElementById('poNumber').value = opt.dataset.po || '';

            const items = JSON.parse(opt.dataset.items || '[]').filter(it => !it.is_unavailable);
            if (items.length === 0) {
                body.innerHTML = '<tr><td colspan="6" class="text-center py-3 text-muted">Tidak ada item yang bisa diretur dari SJ ini.</td></tr>';
                return;
            }

            body.innerHTML = items.map((it, i) => `
                <tr>
                    <td class="text-muted">${i + 1}</td>
                    <td>${it.name}<input type="hidden" name="items[${i}][delivery_note_item_id]" value="${it.id}"></td>
                    <td>${parseFloat(it.qty).toFixed(2)}</td>
                    <td><input type="number" class="form-control form-control-sm" name="items[${i}][quantity_returned]" value="0" min="0" max="${it.qty}" step="0.01"></td>
                    <td>${it.unit}</td>
                    <td><input type="text" class="form-control form-control-sm" name="items[${i}][reason]" placeholder="Alasan retur item..."></td>
                </tr>
            `).join('');
        }

        document.getElementById('dnSelect').addEventListener('change', updateReturnTable);
        
        // Jalankan saat pertama kali dimuat jika sudah ada dn_id terpilih
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('dnSelect').value) {
                updateReturnTable();
            }
        });
    </script>
    @endpush
</x-app-layout>
