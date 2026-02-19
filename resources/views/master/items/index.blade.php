<x-app-layout>
    <x-slot name="title">Item / Produk</x-slot>

    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>Item / Produk</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Items</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('items.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Tambah Item
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari nama / kode item...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category_id">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
                </div>
                @if(request()->hasAny(['search', 'category_id']))
                <div class="col-md-2">
                    <a href="{{ route('items.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
                @endif
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
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Harga Terakhir</th>
                            <th>Diperbarui</th>
                            <th>Status</th>
                            <th width="140">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php $lastPrice = $item->priceHistories->first(); @endphp
                            <tr>
                                <td>{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                                <td><code>{{ $item->code }}</code></td>
                                <td class="fw-semibold">{{ $item->name }}</td>
                                <td><span class="badge bg-light text-dark">{{ $item->category->name }}</span></td>
                                <td>{{ $item->unit }}</td>
                                <td class="fw-semibold">{{ $lastPrice ? \App\Helpers\FormatHelper::rupiah($lastPrice->selling_price) : '-' }}</td>
                                <td class="text-muted" style="font-size:12px;">{{ $lastPrice ? $lastPrice->changed_at->diffForHumans() : '-' }}</td>
                                <td>
                                    @if($item->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Non-aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('items.show', $item) }}" class="btn btn-sm btn-outline-info btn-action" title="Detail"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-outline-primary btn-action" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline" id="del-item-{{ $item->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" onclick="confirmDelete('del-item-{{ $item->id }}')" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-4 text-muted">Belum ada item.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($items->hasPages())
            <div class="card-footer d-flex justify-content-center">{{ $items->withQueryString()->links() }}</div>
        @endif
    </div>
</x-app-layout>
