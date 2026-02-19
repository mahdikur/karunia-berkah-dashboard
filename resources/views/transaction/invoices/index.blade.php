<x-app-layout>
    <x-slot name="title">Invoices</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>Invoice</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Invoice</li></ol></nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('invoices.batch') }}" class="btn btn-outline-secondary"><i class="bi bi-collection me-1"></i>Batch Invoice</a>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Buat Invoice</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3"><input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="No. Invoice..."></div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        @foreach(['unpaid','partial','paid','overdue'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="client_id">
                        <option value="">Semua Client</option>
                        @foreach($clients as $c)<option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>No. Invoice</th><th>Client</th><th>Tgl Invoice</th><th>Jatuh Tempo</th><th>Total</th><th>Sisa</th><th>Status</th><th width="120">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('invoices.show', $inv) }}" class="fw-semibold">{{ $inv->invoice_number }}</a></td>
                                <td>{{ $inv->client->name }}</td>
                                <td>{{ $inv->invoice_date->format('d/m/Y') }}</td>
                                <td>{{ $inv->due_date->format('d/m/Y') }}</td>
                                <td>{{ \App\Helpers\FormatHelper::rupiah($inv->total_amount) }}</td>
                                <td class="{{ $inv->remaining_amount > 0 ? 'text-danger' : '' }}">{{ \App\Helpers\FormatHelper::rupiah($inv->remaining_amount) }}</td>
                                <td>{!! $inv->status_badge !!}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                        @if($inv->status !== 'paid')
                                        <a href="{{ route('invoices.edit', $inv) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                        @endif
                                        <a href="{{ route('invoices.print', $inv) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print"><i class="bi bi-printer"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-4 text-muted">Belum ada invoice.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($invoices->hasPages())<div class="card-footer d-flex justify-content-center">{{ $invoices->withQueryString()->links() }}</div>@endif
    </div>
</x-app-layout>
