<x-app-layout>
    <x-slot name="title">Detail Retur - {{ $returnNote->return_number }}</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>{{ $returnNote->return_number }}</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('return-notes.index') }}">Retur</a></li><li class="breadcrumb-item active">Detail</li></ol></nav>
        </div>
        <div class="d-flex gap-2">
            @if($returnNote->status === 'draft')
            <form action="{{ route('return-notes.destroy', $returnNote) }}" method="POST" id="del-rn-{{ $returnNote->id }}">
                @csrf @method('DELETE')
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete('del-rn-{{ $returnNote->id }}')"><i class="bi bi-trash me-1"></i>Hapus</button>
            </form>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 text-center">{!! $returnNote->status_badge !!}</div>
                    <table class="table table-borderless mb-0" style="font-size: 13px;">
                        <tr><td class="text-muted">No. Retur</td><td class="fw-semibold">{{ $returnNote->return_number }}</td></tr>
                        <tr><td class="text-muted">Surat Jalan</td><td><a href="{{ route('delivery-notes.show', $returnNote->deliveryNote) }}">{{ $returnNote->deliveryNote->dn_number }}</a></td></tr>
                        <tr><td class="text-muted">PO</td><td><a href="{{ route('purchase-orders.show', $returnNote->purchaseOrder) }}">{{ $returnNote->purchaseOrder->po_number }}</a></td></tr>
                        <tr><td class="text-muted">Client</td><td>{{ $returnNote->client->name }}</td></tr>
                        <tr><td class="text-muted">Tanggal Retur</td><td>{{ $returnNote->return_date->format('d/m/Y') }}</td></tr>
                        <tr><td class="text-muted">Dibuat Oleh</td><td>{{ $returnNote->creator->name ?? '-' }}</td></tr>
                        @if($returnNote->reason)
                        <tr><td class="text-muted">Alasan</td><td>{{ $returnNote->reason }}</td></tr>
                        @endif
                        @if($returnNote->notes)
                        <tr><td class="text-muted">Catatan</td><td>{{ $returnNote->notes }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            @if($returnNote->status !== 'processed')
            <div class="card mt-3">
                <div class="card-header">Update Status</div>
                <div class="card-body">
                    <form action="{{ route('return-notes.update-status', $returnNote) }}" method="POST">
                        @csrf @method('PATCH')
                        <select class="form-select mb-2" name="status">
                            @if($returnNote->status === 'draft')<option value="confirmed">Konfirmasi Retur</option>@endif
                            @if($returnNote->status === 'confirmed')<option value="processed">Tandai Diproses</option>@endif
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm w-100">Update Status</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-arrow-return-left me-2"></i>Item yang Diretur</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>#</th><th>Item</th><th>Qty Terkirim</th><th>Qty Retur</th><th>Satuan</th><th>Alasan</th></tr></thead>
                        <tbody>
                            @foreach($returnNote->items as $i => $rnItem)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $rnItem->item->name ?? '-' }}</td>
                                    <td class="text-muted">{{ $rnItem->deliveryNoteItem ? number_format($rnItem->deliveryNoteItem->quantity_delivered, 2) : '-' }}</td>
                                    <td class="fw-semibold text-danger">{{ number_format($rnItem->quantity_returned, 2) }}</td>
                                    <td>{{ $rnItem->unit }}</td>
                                    <td>{{ $rnItem->reason ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
