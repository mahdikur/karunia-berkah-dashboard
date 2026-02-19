<x-app-layout>
    <x-slot name="title">Pengeluaran</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div><h1>Pengeluaran</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Pengeluaran</li></ol></nav></div>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Catat Pengeluaran</a>
    </div>
    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi / no...">
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="category">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
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

    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>No.</th><th>Kategori</th><th>Deskripsi</th><th>Jumlah</th><th>Tanggal</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($expenses as $exp)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><code>{{ $exp->expense_number }}</code></td>
                        <td><span class="badge bg-light text-dark">{{ $exp->category }}</span></td>
                        <td>{{ Str::limit($exp->description, 60) }}</td>
                        <td class="fw-semibold text-danger">{{ \App\Helpers\FormatHelper::rupiah($exp->amount) }}</td>
                        <td>{{ $exp->expense_date->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('expenses.show', $exp) }}" class="btn btn-sm btn-outline-info btn-action"><i class="bi bi-eye"></i></a>
                            <form action="{{ route('expenses.destroy', $exp) }}" method="POST" class="d-inline" id="del-exp-{{ $exp->id }}">@csrf @method('DELETE')<button type="button" class="btn btn-sm btn-outline-danger btn-action" onclick="confirmDelete('del-exp-{{ $exp->id }}')"><i class="bi bi-trash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada pengeluaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
    @if($expenses->hasPages())<div class="card-footer d-flex justify-content-center">{{ $expenses->links() }}</div>@endif
    </div>
</x-app-layout>
