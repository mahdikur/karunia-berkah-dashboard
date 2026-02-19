<x-app-layout>
    <x-slot name="title">Modal</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div><h1>Modal</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Modal</li></ol></nav></div>
        <a href="{{ route('modals.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Modal</a>
    </div>
    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="Cari no. modal...">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from', $dateFrom) }}">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to', $dateTo) }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>No. Modal</th><th>Tanggal</th><th>Total</th><th>Teralokasi</th><th>Sisa</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($modals as $m)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><a href="{{ route('modals.show', $m) }}" class="fw-semibold">{{ $m->modal_number }}</a></td>
                        <td>{{ $m->modal_date->format('d/m/Y') }}</td>
                        <td>{{ \App\Helpers\FormatHelper::rupiah($m->total_amount) }}</td>
                        <td>{{ \App\Helpers\FormatHelper::rupiah($m->allocated_amount) }}</td>
                        <td class="{{ $m->remaining_amount > 0 ? 'text-success' : '' }}">{{ \App\Helpers\FormatHelper::rupiah($m->remaining_amount) }}</td>
                        <td><a href="{{ route('modals.show', $m) }}" class="btn btn-sm btn-outline-info btn-action"><i class="bi bi-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada modal.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
    @if($modals->hasPages())<div class="card-footer d-flex justify-content-center">{{ $modals->links() }}</div>@endif
    </div>
</x-app-layout>
