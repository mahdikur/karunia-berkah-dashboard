<x-app-layout>
    <x-slot name="title">Detail SJ - {{ $deliveryNote->dn_number }}</x-slot>
    <div class="page-header">
        <h1>{{ $deliveryNote->dn_number }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('delivery-notes.index') }}">Surat Jalan</a></li><li class="breadcrumb-item active">Detail</li></ol></nav>
    </div>
    <div class="d-flex justify-content-end mb-3">
         <a href="{{ route('delivery-notes.print', $deliveryNote) }}" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>Print</a>
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
                    </table>
                </div>
            </div>

            @if($deliveryNote->status !== 'received')
            <div class="card mt-3">
                <div class="card-header">Update Status</div>
                <div class="card-body">
                    <form action="{{ route('delivery-notes.update-status', $deliveryNote) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="d-flex gap-2 mb-2">
                        </div>
                        <select class="form-select mb-2" name="status">
                            @if($deliveryNote->status === 'draft')<option value="sent">Dikirim</option>@endif
                            @if($deliveryNote->status === 'sent')<option value="received">Diterima</option>@endif
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm w-100">Update</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Item Terkirim</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>#</th><th>Item</th><th>Qty Kirim</th><th>Satuan</th></tr></thead>
                        <tbody>
                            @foreach($deliveryNote->items as $i => $dnItem)
                                <tr><td>{{ $i+1 }}</td><td>{{ $dnItem->item->name ?? '-' }}</td><td>{{ number_format($dnItem->quantity_delivered, 2) }}</td><td>{{ $dnItem->unit }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
