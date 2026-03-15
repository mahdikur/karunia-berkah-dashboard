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

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>No. PO</th>
                            <th>Client</th>
                            <th>Tgl PO</th>
                            <th>Status</th>
                            <th class="text-center" width="90">Invoice</th>
                            <th class="text-center" width="90">Surat Jalan</th>
                            <th class="text-center" width="70">Retur</th>
                            <th>Dibuat</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                            <tr>
                                <td>{{ $loop->iteration + ($purchaseOrders->currentPage() - 1) * $purchaseOrders->perPage() }}</td>
                                <td><a href="{{ route('purchase-orders.show', $po) }}" class="fw-semibold text-decoration-none">{{ $po->po_number }}</a></td>
                                <td>{{ $po->client->name }}</td>
                                <td>{{ $po->po_date->format('d/m/Y') }}</td>
                                <td>{!! $po->status_badge !!}</td>

                                {{-- Invoice --}}
                                <td class="text-center">
                                    @if($po->invoice_count > 0)
                                        <a href="#" class="badge bg-success text-decoration-none"
                                           data-bs-toggle="modal" data-bs-target="#modalInv{{ $po->id }}">
                                            {{ $po->invoice_count }}x
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                {{-- Surat Jalan --}}
                                <td class="text-center">
                                    @if($po->delivery_notes_count > 0)
                                        <a href="#" class="badge bg-info text-decoration-none"
                                           data-bs-toggle="modal" data-bs-target="#modalSJ{{ $po->id }}">
                                            {{ $po->delivery_notes_count }}x
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                {{-- Retur --}}
                                <td class="text-center">
                                    @if($po->return_notes_count > 0)
                                        <a href="#" class="badge bg-danger text-decoration-none"
                                           data-bs-toggle="modal" data-bs-target="#modalRtn{{ $po->id }}">
                                            Yes
                                        </a>
                                    @else
                                        <span class="badge bg-light text-secondary border" style="font-size:10px;">No</span>
                                    @endif
                                </td>

                                <td>{{ $po->creator->name ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('purchase-orders.edit', $po) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center py-4 text-muted">Belum ada PO.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($purchaseOrders->hasPages())
            <div class="card-footer d-flex justify-content-center">{{ $purchaseOrders->withQueryString()->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>

    {{-- ===== MODALS (di luar tabel) ===== --}}
    @foreach($purchaseOrders as $po)

        {{-- Modal Invoice --}}
        @if($po->invoice_count > 0)
        <div class="modal fade" id="modalInv{{ $po->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Invoice – {{ $po->po_number }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>No. Invoice</th><th>Tgl Invoice</th><th class="text-end">Total</th><th>Status</th><th></th></tr>
                            </thead>
                            <tbody>
                                @if($po->invoice)
                                <tr>
                                    <td class="fw-semibold">{{ $po->invoice->invoice_number }}</td>
                                    <td>{{ $po->invoice->invoice_date->format('d/m/Y') }}</td>
                                    <td class="text-end">Rp {{ number_format($po->invoice->total_amount, 0, ',', '.') }}</td>
                                    <td>{!! $po->invoice->status_badge !!}</td>
                                    <td><a href="{{ route('invoices.show', $po->invoice) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a></td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Modal Surat Jalan --}}
        @if($po->delivery_notes_count > 0)
        <div class="modal fade" id="modalSJ{{ $po->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="bi bi-truck me-2"></i>Surat Jalan – {{ $po->po_number }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>#</th><th>No. SJ</th><th>Tgl SJ</th><th>Tipe</th><th>Status</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($po->deliveryNotes as $i => $dn)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="fw-semibold">{{ $dn->dn_number }}</td>
                                    <td>{{ $dn->dn_date->format('d/m/Y') }}</td>
                                    <td><span class="badge bg-secondary">{{ ucfirst($dn->delivery_type) }}</span></td>
                                    <td>{!! $dn->status_badge !!}</td>
                                    <td><a href="{{ route('delivery-notes.show', $dn) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Modal Retur --}}
        @if($po->return_notes_count > 0)
        <div class="modal fade" id="modalRtn{{ $po->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-arrow-return-left me-2"></i>Retur – {{ $po->po_number }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>#</th><th>No. Retur</th><th>Tgl Retur</th><th>Alasan</th><th>Status</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($po->returnNotes as $i => $retur)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="fw-semibold">{{ $retur->return_number }}</td>
                                    <td>{{ $retur->return_date->format('d/m/Y') }}</td>
                                    <td>{{ Str::limit($retur->reason, 40) }}</td>
                                    <td>{!! $retur->status_badge !!}</td>
                                    <td><a href="{{ route('return-notes.show', $retur) }}" class="btn btn-sm btn-outline-danger"><i class="bi bi-eye"></i></a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

    @endforeach

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined') {
            $('#filterClient, #filterStatus').select2({ theme: 'bootstrap-5', width: '100%' });
        }
    });
    </script>
    @endpush
</x-app-layout>
