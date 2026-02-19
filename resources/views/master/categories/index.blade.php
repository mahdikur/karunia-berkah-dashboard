<x-app-layout>
    <x-slot name="title">Kategori</x-slot>

    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>Kategori</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kategori</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Tambah Kategori
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                                <td class="fw-semibold">{{ $category->name }}</td>
                                <td>{{ $category->description ?? '-' }}</td>
                                <td><span class="badge bg-secondary">{{ $category->items_count }}</span></td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Non-aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('items.index', ['category_id' => $category->id]) }}" class="btn btn-sm btn-outline-info btn-action" title="Lihat Item">
                                        <i class="bi bi-box-seam"></i>
                                    </a>
                                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-primary btn-action" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" id="delete-form-{{ $category->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" onclick="confirmDelete('delete-form-{{ $category->id }}')" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data kategori.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($categories->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
