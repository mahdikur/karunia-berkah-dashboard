<x-app-layout>
    <x-slot name="title">Surat Jalan</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>Surat Jalan</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Surat Jalan</li></ol></nav>
        </div>
        <a href="{{ route('delivery-notes.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Buat Surat Jalan</a>
    </div>
    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="No. SJ / No. PO...">
                </div>
                <div class="col-md-3">
                    <select class="form-select select2-filter" name="client_id" id="filterClient">
                        <option value="">Semua Client</option>
                        @foreach($clients as $c)<option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="delivery_type">
                        <option value="">Semua Tipe</option>
                        <option value="full" {{ request('delivery_type') === 'full' ? 'selected' : '' }}>Full</option>
                        <option value="partial" {{ request('delivery_type') === 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Dikirim</option>
                        <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Diterima</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from', $dateFrom) }}">
                </div>
                <div class="col-md-1">
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to', $dateTo) }}">
                </div>
                <div class="col-md-1"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined') {
            $('#filterClient').select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'Semua Client', allowClear: true });
        }
    });
    </script>
    @endpush
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>No. SJ</th><th>No. PO</th><th>Client</th><th>Tanggal</th><th>Tipe</th><th>Status</th><th width="140">Aksi</th></tr></thead>
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
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('delivery-notes.show', $dn) }}" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                        @if($dn->status !== 'received')
                                        <a href="{{ route('delivery-notes.edit', $dn) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                        @endif
                                        <a href="{{ route('delivery-notes.print', $dn) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print"><i class="bi bi-printer"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada surat jalan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($deliveryNotes->hasPages())<div class="card-footer d-flex justify-content-center">{{ $deliveryNotes->links('pagination::bootstrap-5') }}</div>@endif
    </div>
</x-app-layout>
