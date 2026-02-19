<x-app-layout>
    <x-slot name="title">Client</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>Client</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Client</li></ol></nav>
        </div>
        <a href="{{ route('clients.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Client</a>
    </div>

    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari nama / kode client...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Cari</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>PIC</th>
                            <th>Termin</th>
                            <th>Credit Limit</th>
                            <th class="text-center">Total PO</th>
                            <th class="text-center">PO Belum Selesai</th>
                            <th class="text-end">Tagihan Belum Lunas</th>
                            <th>Status</th>
                            <th width="140">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            @php
                                $nominalUnpaid = $client->invoices->sum('remaining_amount');
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration + ($clients->currentPage() - 1) * $clients->perPage() }}</td>
                                <td><code>{{ $client->code }}</code></td>
                                <td class="fw-semibold">{{ $client->name }}</td>
                                <td>{{ $client->pic_name ?? '-' }}</td>
                                <td>{{ $client->payment_terms_label }}</td>
                                <td>{{ $client->credit_limit ? \App\Helpers\FormatHelper::rupiah($client->credit_limit) : '-' }}</td>
                                <td class="text-center"><span class="badge bg-secondary">{{ $client->total_po }}</span></td>
                                <td class="text-center">
                                    @if($client->total_po_unpaid > 0)
                                        <span class="badge bg-warning text-dark">{{ $client->total_po_unpaid }}</span>
                                    @else
                                        <span class="badge bg-light text-dark">0</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold {{ $nominalUnpaid > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ $nominalUnpaid > 0 ? \App\Helpers\FormatHelper::rupiah($nominalUnpaid) : '-' }}
                                </td>
                                <td>{!! $client->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Non-aktif</span>' !!}</td>
                                <td>
                                    <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-outline-info btn-action" title="Detail"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-primary btn-action" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline" id="del-client-{{ $client->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" onclick="confirmDelete('del-client-{{ $client->id }}')" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center py-4 text-muted">Belum ada client.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($clients->hasPages())
            <div class="card-footer d-flex justify-content-center">{{ $clients->withQueryString()->links() }}</div>
        @endif
    </div>
</x-app-layout>
