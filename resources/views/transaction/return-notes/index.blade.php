<x-app-layout>
    <x-slot name="title">Retur Barang</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>Retur Barang</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Retur</li></ol></nav>
        </div>
        <a href="{{ route('return-notes.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Buat Retur</a>
    </div>

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="Cari no. retur...">
                </div>
                <div class="col-md-3">
                    <select class="form-select form-select-sm select2-filter" name="client_id" id="filterClient">
                        <option value="">Semua Client</option>
                        @foreach($clients as $c)<option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="status">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                        <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Diproses</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from', $dateFrom) }}">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to', $dateTo) }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search"></i></button>
                </div>
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

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>No. Retur</th>
                            <th>No. SJ</th>
                            <th>No. PO</th>
                            <th>Client</th>
                            <th>Tanggal</th>
                            <th>Alasan</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returnNotes as $rn)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('return-notes.show', $rn) }}" class="fw-semibold">{{ $rn->return_number }}</a></td>
                                <td><a href="{{ route('delivery-notes.show', $rn->deliveryNote) }}">{{ $rn->deliveryNote->dn_number }}</a></td>
                                <td><a href="{{ route('purchase-orders.show', $rn->purchaseOrder) }}">{{ $rn->purchaseOrder->po_number }}</a></td>
                                <td>{{ $rn->client->name }}</td>
                                <td>{{ $rn->return_date->format('d/m/Y') }}</td>
                                <td>{{ Str::limit($rn->reason, 30) ?? '-' }}</td>
                                <td>{!! $rn->status_badge !!}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('return-notes.show', $rn) }}" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                        @if($rn->status === 'draft')
                                        <form action="{{ route('return-notes.destroy', $rn) }}" method="POST" id="del-rn-{{ $rn->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('del-rn-{{ $rn->id }}')" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-4 text-muted">Belum ada data retur.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($returnNotes->hasPages())<div class="card-footer d-flex justify-content-center">{{ $returnNotes->links() }}</div>@endif
    </div>
</x-app-layout>
