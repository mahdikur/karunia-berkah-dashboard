<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="page-header">
        <h1>Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">Overview</li>
            </ol>
        </nav>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 fade-in-up">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
                <div class="stat-value">{{ \App\Helpers\FormatHelper::rupiah($totalSalesMonth) }}</div>
                <div class="stat-label">Penjualan Bulan Ini</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 fade-in-up">
            <div class="stat-card stat-card-warning">
                <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                <div class="stat-value">{{ \App\Helpers\FormatHelper::rupiah($totalUnpaid) }}</div>
                <div class="stat-label">Invoice Belum Lunas ({{ $unpaidCount }})</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 fade-in-up">
            <div class="stat-card stat-card-danger">
                <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                <div class="stat-value">{{ $overdueInvoiceCount }}</div>
                <div class="stat-label">Invoice Overdue</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 fade-in-up">
            <div class="stat-card stat-card-info">
                <div class="stat-icon"><i class="bi bi-cart-check"></i></div>
                <div class="stat-value">{{ $pendingPoCount }}</div>
                <div class="stat-label">PO Pending Approval</div>
            </div>
        </div>
    </div>

    @if(auth()->user()->isSuperadmin())
    <div class="row g-3 mb-4">
        <!-- Sales Chart -->
        <div class="col-lg-8 fade-in-up">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-graph-up me-2"></i>Grafik Penjualan 6 Bulan Terakhir</span>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="280"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Clients -->
        <div class="col-lg-4 fade-in-up">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-trophy me-2"></i>Top 5 Client
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($topClients as $index => $client)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-{{ $index < 3 ? 'primary' : 'secondary' }}" style="font-size: 12px; min-width: 28px;">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <div style="font-size: 13px; font-weight: 600;">{{ $client->name }}</div>
                                        <div style="font-size: 11px; color: #9ca3af;">{{ $client->invoices_count }} invoice</div>
                                    </div>
                                </div>
                                <span style="font-size: 13px; font-weight: 600; color: #059669;">
                                    {{ \App\Helpers\FormatHelper::rupiah($client->total_sales) }}
                                </span>
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">
                                Belum ada data
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Activities -->
    <div class="row g-3">
        <div class="col-lg-6 fade-in-up">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-cart me-2"></i>PO Terbaru
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>No. PO</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPOs as $po)
                                    <tr>
                                        <td>
                                            <a href="{{ route('purchase-orders.show', $po) }}" class="text-decoration-none fw-semibold">
                                                {{ $po->po_number }}
                                            </a>
                                        </td>
                                        <td>{{ $po->client->name }}</td>
                                        <td>{!! $po->status_badge !!}</td>
                                        <td>{{ $po->po_date->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Belum ada PO</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 fade-in-up">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-receipt me-2"></i>Invoice Terbaru
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>No. Invoice</th>
                                    <th>Client</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentInvoices as $inv)
                                    <tr>
                                        <td>
                                            <a href="{{ route('invoices.show', $inv) }}" class="text-decoration-none fw-semibold">
                                                {{ $inv->invoice_number }}
                                            </a>
                                        </td>
                                        <td>{{ $inv->client->name }}</td>
                                        <td>{{ \App\Helpers\FormatHelper::rupiah($inv->total_amount) }}</td>
                                        <td>{!! $inv->status_badge !!}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Belum ada invoice</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Sales Chart
        const ctx = document.getElementById('salesChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabels ?? []) !!},
                    datasets: [{
                        label: 'Penjualan',
                        data: {!! json_encode($chartData ?? []) !!},
                        backgroundColor: 'rgba(54, 116, 171, 0.8)',
                        borderColor: '#3674ab',
                        borderWidth: 1,
                        borderRadius: 6,
                        barPercentage: 0.6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(0) + 'jt';
                                    if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                                    return 'Rp ' + value;
                                }
                            },
                            grid: { color: 'rgba(0,0,0,0.04)' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
