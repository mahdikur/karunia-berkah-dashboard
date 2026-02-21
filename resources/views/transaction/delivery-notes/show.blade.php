<x-app-layout>
    <x-slot name="title">Detail SJ - {{ $deliveryNote->dn_number }}</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>{{ $deliveryNote->dn_number }}</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('delivery-notes.index') }}">Surat Jalan</a></li><li class="breadcrumb-item active">Detail</li></ol></nav>
        </div>
        <div class="d-flex gap-2">
            @if($deliveryNote->status !== 'received')
                <a href="{{ route('delivery-notes.edit', $deliveryNote) }}" class="btn btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
                <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#regenerateModal"><i class="bi bi-arrow-repeat me-1"></i>Regenerate</button>
            @endif
            <a href="{{ route('delivery-notes.print', $deliveryNote) }}" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>Print</a>
            <a href="{{ route('return-notes.create', ['dn_id' => $deliveryNote->id]) }}" class="btn btn-outline-danger"><i class="bi bi-arrow-return-left me-1"></i>Buat Retur</a>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 text-center">{!! $deliveryNote->status_badge !!}</div>
                    <table class="table table-borderless mb-0" style="font-size: 13px;">
                        <tr><td class="text-muted">No. PO</td><td><a href="{{ route('purchase-orders.show', $deliveryNote->purchaseOrder) }}">{{ $deliveryNote->purchaseOrder->po_number }}</a></td></tr>
                        <tr><td class="text-muted">Client</td><td>{{ $deliveryNote->client->name }}</td></tr>
                        <tr><td class="text-muted">Tanggal</td><td>{{ $deliveryNote->dn_date->format('d/m/Y') }}</td></tr>
                        <tr><td class="text-muted">Tipe</td><td>{{ ucfirst($deliveryNote->delivery_type) }}</td></tr>
                        <tr><td class="text-muted">Dibuat</td><td>{{ $deliveryNote->creator->name ?? '-' }}</td></tr>
                        @if($deliveryNote->notes)
                        <tr><td class="text-muted">Catatan</td><td>{{ $deliveryNote->notes }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            @if($deliveryNote->status !== 'received')
            <div class="card mt-3">
                <div class="card-header">Update Status</div>
                <div class="card-body">
                    <form action="{{ route('delivery-notes.update-status', $deliveryNote) }}" method="POST">
                        @csrf @method('PATCH')
                        <select class="form-select mb-2" name="status">
                            @if($deliveryNote->status === 'draft')<option value="sent">Dikirim</option>@endif
                            @if($deliveryNote->status === 'sent')<option value="received">Diterima</option>@endif
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm w-100">Update</button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Return Notes --}}
            @if($deliveryNote->returnNotes && $deliveryNote->returnNotes->count())
            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-arrow-return-left me-2"></i>Retur Terkait</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>No. Retur</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($deliveryNote->returnNotes as $rn)
                            <tr>
                                <td><a href="{{ route('return-notes.show', $rn) }}">{{ $rn->return_number }}</a></td>
                                <td>{!! $rn->status_badge !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Item Terkirim</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>#</th><th>Item</th><th>Qty Kirim</th><th>Satuan</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($deliveryNote->items as $i => $dnItem)
                                <tr class="{{ $dnItem->is_unavailable ? 'table-warning' : '' }}">
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $dnItem->item->name ?? '-' }}</td>
                                    <td>
                                        @if($dnItem->is_unavailable)
                                            <span class="text-danger fw-semibold">0</span>
                                        @else
                                            {{ number_format($dnItem->quantity_delivered, 2) }}
                                        @endif
                                    </td>
                                    <td>{{ $dnItem->unit }}</td>
                                    <td>
                                        @if($dnItem->is_unavailable)
                                            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Tidak Tersedia</span>
                                            @if($dnItem->unavailable_reason)
                                                <br><small class="text-muted">{{ $dnItem->unavailable_reason }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-success">Terkirim</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Regenerate Modal --}}
    <div class="modal fade" id="regenerateModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-arrow-repeat me-1"></i>Regenerate Surat Jalan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('delivery-notes.regenerate', $deliveryNote) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted small">Regenerate akan menghapus item yang ada sekarang dan menggantinya dengan item dari PO ({{ $deliveryNote->purchaseOrder->po_number }}).</p>
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Semua perubahan manual pada item akan hilang.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Regenerate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
