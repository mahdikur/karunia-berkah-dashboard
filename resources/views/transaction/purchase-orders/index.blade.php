<x-app-layout>
    <x-slot name="title">Purchase Orders</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>Purchase Orders</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">PO</li></ol></nav>
        </div>
        <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Buat PO</a>
    </div>

    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari no. PO...">
                </div>
                <div class="col-md-3">
                    <select class="form-select select2-filter" name="client_id" id="filterClient">
                        <option value="">Semua Client</option>
                        @foreach($clients as $c)<option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select select2-filter" name="status" id="filterStatus">
                        <option value="">Semua Status</option>
                        @foreach(['draft','pending_approval','approved','rejected','in_delivery','completed','cancelled'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from', $dateFrom) }}" placeholder="Dari">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to', $dateTo) }}" placeholder="Sampai">
                </div>
                <div class="col-md-1"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined') {
            $('#filterClient, #filterStatus').select2({ theme: 'bootstrap-5', width: '100%' });
        }
    });
    </script>
    @endpush

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th width="50">#</th><th>No. PO</th><th>Client</th><th>Tgl PO</th><th>Status</th><th>Dibuat</th><th width="160">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                            <tr>
                                <td>{{ $loop->iteration + ($purchaseOrders->currentPage() - 1) * $purchaseOrders->perPage() }}</td>
                                <td><a href="{{ route('purchase-orders.show', $po) }}" class="fw-semibold text-decoration-none">{{ $po->po_number }}</a></td>
                                <td>{{ $po->client->name }}</td>
                                <td>{{ $po->po_date->format('d/m/Y') }}</td>
                                <td>{!! $po->status_badge !!}</td>
                                <td>{{ $po->creator->name ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('purchase-orders.edit', $po) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="{{ route('delivery-notes.index', ['search' => $po->po_number]) }}" class="btn btn-sm btn-outline-secondary" title="List Surat Jalan"><i class="bi bi-truck"></i></a>
                                        <a href="{{ route('invoices.index', ['search' => $po->po_number]) }}" class="btn btn-sm btn-outline-success" title="List Invoice"><i class="bi bi-receipt"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada PO.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($purchaseOrders->hasPages())
            <div class="card-footer d-flex justify-content-center">{{ $purchaseOrders->withQueryString()->links() }}</div>
        @endif
    </div>
</x-app-layout>
