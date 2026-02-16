<x-app-layout>
    <x-slot name="title">Surat Jalan</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>Surat Jalan</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Surat Jalan</li></ol></nav>
        </div>
        <a href="{{ route('delivery-notes.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Buat Surat Jalan</a>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>No. SJ</th><th>No. PO</th><th>Client</th><th>Tanggal</th><th>Tipe</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse($deliveryNotes as $dn)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('delivery-notes.show', $dn) }}" class="fw-semibold">{{ $dn->dn_number }}</a></td>
                                <td><a href="{{ route('purchase-orders.show', $dn->purchaseOrder) }}">{{ $dn->purchaseOrder->po_number }}</a></td>
                                <td>{{ $dn->client->name }}</td>
                                <td>{{ $dn->dn_date->format('d/m/Y') }}</td>
                                <td><span class="badge bg-light text-dark">{{ ucfirst($dn->delivery_type) }}</span></td>
                                <td>{!! $dn->status_badge !!}</td>
                                <td><a href="{{ route('delivery-notes.show', $dn) }}" class="btn btn-sm btn-outline-info btn-action"><i class="bi bi-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada surat jalan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($deliveryNotes->hasPages())<div class="card-footer d-flex justify-content-center">{{ $deliveryNotes->links() }}</div>@endif
    </div>
</x-app-layout>
