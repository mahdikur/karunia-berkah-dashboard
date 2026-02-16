<x-app-layout>
    <x-slot name="title">Detail Item - {{ $item->name }}</x-slot>
    <div class="page-header">
        <h1>{{ $item->name }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('items.index') }}">Items</a></li><li class="breadcrumb-item active">Detail</li></ol></nav>
    </div>
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    @if($item->photo)
                        <img src="{{ Storage::url($item->photo) }}" class="rounded mb-3" style="max-width: 200px;">
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height: 150px;">
                            <i class="bi bi-image text-muted" style="font-size: 48px;"></i>
                        </div>
                    @endif
                    <h5 class="mb-1">{{ $item->name }}</h5>
                    <p class="text-muted mb-0"><code>{{ $item->code }}</code></p>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Kategori</span><span class="fw-semibold">{{ $item->category->name }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Satuan</span><span>{{ $item->unit }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Status</span>{!! $item->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Non-aktif</span>' !!}</li>
                </ul>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-clock-history me-2"></i>Riwayat Harga</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Client</th><th>Harga Beli</th><th>Harga Jual</th><th>Tanggal</th><th>Diubah oleh</th></tr></thead>
                            <tbody>
                                @forelse($item->priceHistories->sortByDesc('changed_at')->take(20) as $ph)
                                    <tr>
                                        <td>{{ $ph->client->name ?? '-' }}</td>
                                        <td>{{ $ph->purchase_price ? \App\Helpers\FormatHelper::rupiah($ph->purchase_price) : '-' }}</td>
                                        <td class="fw-semibold">{{ \App\Helpers\FormatHelper::rupiah($ph->selling_price) }}</td>
                                        <td>{{ $ph->changed_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $ph->changedByUser->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-3 text-muted">Belum ada riwayat harga.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
