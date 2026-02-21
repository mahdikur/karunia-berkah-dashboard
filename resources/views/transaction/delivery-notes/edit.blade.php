<x-app-layout>
    <x-slot name="title">Edit Surat Jalan - {{ $deliveryNote->dn_number }}</x-slot>
    <div class="page-header">
        <h1>Edit Surat Jalan: {{ $deliveryNote->dn_number }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('delivery-notes.index') }}">Surat Jalan</a></li><li class="breadcrumb-item active">Edit</li></ol></nav>
    </div>

    <form action="{{ route('delivery-notes.update', $deliveryNote) }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Informasi</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">No. Surat Jalan</label>
                            <input type="text" class="form-control" value="{{ $deliveryNote->dn_number }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purchase Order</label>
                            <input type="text" class="form-control" value="{{ $deliveryNote->purchaseOrder->po_number }} - {{ $deliveryNote->client->name }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="dn_date" value="{{ $deliveryNote->dn_date->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Pengiriman <span class="text-danger">*</span></label>
                            <select class="form-select" name="delivery_type" required>
                                <option value="full" {{ $deliveryNote->delivery_type === 'full' ? 'selected' : '' }}>Full</option>
                                <option value="partial" {{ $deliveryNote->delivery_type === 'partial' ? 'selected' : '' }}>Partial</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div>{!! $deliveryNote->status_badge !!}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="notes" rows="2">{{ $deliveryNote->notes }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">Item yang Dikirim</div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead><tr><th width="40">#</th><th>Item</th><th>Qty PO</th><th>Qty Kirim</th><th>Satuan</th><th width="150">Unavailable</th></tr></thead>
                            <tbody>
                                @foreach($deliveryNote->purchaseOrder->items as $i => $poItem)
                                @php
                                    $dnItem = $deliveryNote->items->firstWhere('po_item_id', $poItem->id);
                                @endphp
                                <tr class="dn-item-row">
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        {{ $poItem->item->name }}
                                        <input type="hidden" name="items[{{ $i }}][po_item_id]" value="{{ $poItem->id }}">
                                    </td>
                                    <td>{{ number_format($poItem->quantity, 2) }}</td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm qty-deliver"
                                            name="items[{{ $i }}][quantity_delivered]"
                                            value="{{ $dnItem ? $dnItem->quantity_delivered : 0 }}"
                                            min="0" step="0.01" required
                                            {{ $dnItem && $dnItem->is_unavailable ? 'readonly' : '' }}>
                                    </td>
                                    <td>{{ $poItem->unit }}</td>
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input unavailable-check" 
                                                name="items[{{ $i }}][is_unavailable]" value="1"
                                                {{ $dnItem && $dnItem->is_unavailable ? 'checked' : '' }}>
                                            <label class="form-check-label small">N/A</label>
                                        </div>
                                        <input type="text" class="form-control form-control-sm mt-1 unavailable-reason {{ !($dnItem && $dnItem->is_unavailable) ? 'd-none' : '' }}" 
                                            name="items[{{ $i }}][unavailable_reason]" placeholder="Alasan..."
                                            value="{{ $dnItem->unavailable_reason ?? '' }}">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                    <a href="{{ route('delivery-notes.show', $deliveryNote) }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function initUnavailable() {
            document.querySelectorAll('.unavailable-check').forEach(cb => {
                cb.addEventListener('change', function() {
                    const row = this.closest('tr');
                    const qtyInput = row.querySelector('.qty-deliver');
                    const reasonInput = row.querySelector('.unavailable-reason');
                    if (this.checked) {
                        qtyInput.value = 0;
                        qtyInput.readOnly = true;
                        reasonInput.classList.remove('d-none');
                    } else {
                        qtyInput.readOnly = false;
                        reasonInput.classList.add('d-none');
                        reasonInput.value = '';
                    }
                });
            });
        }
        document.addEventListener('DOMContentLoaded', initUnavailable);
    </script>
    @endpush
</x-app-layout>
