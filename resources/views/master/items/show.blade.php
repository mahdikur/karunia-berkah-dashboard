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
            @php
                // Group histories by client, sorted desc
                $allHistories = $item->priceHistories->sortByDesc('changed_at');
                // Group by client_id
                $byClient = $allHistories->groupBy(fn($ph) => $ph->client_id ?? 'none');
            @endphp

            @forelse($byClient as $clientId => $histories)
                @php
                    $clientName = $histories->first()->client->name ?? 'Tanpa Client';
                    // Filter only entries where price changed (compare to previous)
                    $filtered = [];
                    $prevPrice = null;
                    foreach ($histories as $ph) {
                        if ($prevPrice === null || (float)$ph->selling_price !== $prevPrice) {
                            $filtered[] = $ph;
                            $prevPrice = (float)$ph->selling_price;
                        }
                    }
                @endphp

                @if(count($filtered) > 0)
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-person me-2"></i>Riwayat Harga — <strong>{{ $clientName }}</strong></span>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#chart-{{ $clientId }}">
                            <i class="bi bi-bar-chart-line me-1"></i>Chart
                        </button>
                    </div>
                    <div class="collapse" id="chart-{{ $clientId }}">
                        <div class="card-body border-bottom">
                            <canvas id="priceChart-{{ $clientId }}" height="100"></canvas>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:13px;">
                                <thead><tr><th>Harga Beli</th><th>Harga Jual</th><th>Trend</th><th>Tanggal</th><th>Oleh</th></tr></thead>
                                <tbody>
                                    @foreach($filtered as $idx => $ph)
                                        @php
                                            $nextPh = $filtered[$idx + 1] ?? null;
                                            $prevPh = $filtered[$idx - 1] ?? null;
                                            if ($prevPh) {
                                                $diff = (float)$ph->selling_price - (float)$prevPh->selling_price;
                                                $trend = $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'same');
                                            } else {
                                                $trend = 'new';
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $ph->purchase_price ? \App\Helpers\FormatHelper::rupiah($ph->purchase_price) : '-' }}</td>
                                            <td class="fw-semibold">{{ \App\Helpers\FormatHelper::rupiah($ph->selling_price) }}</td>
                                            <td>
                                                @if($trend === 'up')
                                                    <span class="text-success fw-bold" title="Naik dari sebelumnya"><i class="bi bi-arrow-up-circle-fill"></i></span>
                                                @elseif($trend === 'down')
                                                    <span class="text-danger fw-bold" title="Turun dari sebelumnya"><i class="bi bi-arrow-down-circle-fill"></i></span>
                                                @else
                                                    <span class="text-muted" title="Harga baru"><i class="bi bi-circle-fill" style="font-size:8px;"></i></span>
                                                @endif
                                            </td>
                                            <td>{{ $ph->changed_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $ph->changedByUser->name ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx{{ $clientId }} = document.getElementById('priceChart-{{ $clientId }}');
                    if (!ctx{{ $clientId }}) return;
                    new Chart(ctx{{ $clientId }}, {
                        type: 'line',
                        data: {
                            labels: {!! json_encode(array_reverse(array_map(fn($ph) => $ph->changed_at->format('d/m/Y'), $filtered))) !!},
                            datasets: [{
                                label: 'Harga Jual — {{ $clientName }}',
                                data: {!! json_encode(array_reverse(array_map(fn($ph) => (float)$ph->selling_price, $filtered))) !!},
                                borderColor: '#0d6efd',
                                backgroundColor: 'rgba(13,110,253,0.08)',
                                fill: true,
                                tension: 0.3,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: true } },
                            scales: {
                                y: {
                                    ticks: {
                                        callback: function(v) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(v); }
                                    }
                                }
                            }
                        }
                    });
                });
                </script>
                @endif
            @empty
                <div class="card">
                    <div class="card-body text-center text-muted py-4">Belum ada riwayat harga.</div>
                </div>
            @endforelse
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endpush
</x-app-layout>
