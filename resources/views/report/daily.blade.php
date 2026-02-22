<x-app-layout>
    <x-slot name="title">Laporan Harian</x-slot>
    <div class="page-header"><h1>Laporan Harian</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Laporan</a></li><li class="breadcrumb-item active">Harian</li></ol></nav></div>

    <div class="card mb-3"><div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Tanggal</label>
                <input type="date" class="form-control" name="date" value="{{ $date }}">
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Tampilkan</button></div>
        </form>
    </div></div>

    <div class="row g-3">
        <!-- Summary Cards -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <strong>Ringkasan: {{ $displayDate }}</strong>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0" style="font-size: 14px;">
                        <tr>
                            <td class="text-muted"><strong>Modal Dialokasikan</strong></td>
                            <td class="text-end text-primary fw-bold">{{ \App\Helpers\FormatHelper::rupiah($stats['modal']) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><strong>Kembalian Modal</strong></td>
                            <td class="text-end text-success fw-bold">{{ \App\Helpers\FormatHelper::rupiah($stats['kembalian_modal']) }}</td>
                        </tr>
                        <tr style="border-top: 2px solid #dee2e6;">
                            <td class="text-muted"><strong>Pendapatan (Revenue)</strong></td>
                            <td class="text-end text-info fw-bold">{{ \App\Helpers\FormatHelper::rupiah($stats['revenue']) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">HPP (Harga Pokok Penjualan)</td>
                            <td class="text-end">{{ \App\Helpers\FormatHelper::rupiah($stats['hpp']) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><strong>Laba Kotor</strong></td>
                            <td class="text-end text-success fw-bold">{{ \App\Helpers\FormatHelper::rupiah($stats['laba_kotor']) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Pengeluaran Operasional</td>
                            <td class="text-end text-danger">- {{ \App\Helpers\FormatHelper::rupiah($stats['pengeluaran']) }}</td>
                        </tr>
                        <tr style="border-top: 2px solid #dee2e6; background: #f8f9fa;">
                            <td class="text-muted"><strong>Laba Bersih</strong></td>
                            <td class="text-end {{ $stats['laba_bersih'] >= 0 ? 'text-success' : 'text-danger' }} fw-bold" style="font-size: 16px;">
                                {{ \App\Helpers\FormatHelper::rupiah($stats['laba_bersih']) }}
                            </td>
                        </tr>
                        <tr style="background: #f8f9fa;">
                            <td class="text-muted"><strong>Margin Keuntungan</strong></td>
                            <td class="text-end fw-bold">{{ number_format($stats['margin'], 2) }}%</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-md-6">
            <div class="row g-2">
                <div class="col-12">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Modal</h6>
                            <h4 class="text-primary mb-0">{{ \App\Helpers\FormatHelper::rupiah($stats['modal']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Kembalian Modal</h6>
                            <h4 class="text-success mb-0">{{ \App\Helpers\FormatHelper::rupiah($stats['kembalian_modal']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Pengeluaran</h6>
                            <h4 class="text-danger mb-0">{{ \App\Helpers\FormatHelper::rupiah($stats['pengeluaran']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($itemBreakdown->count() > 0)
    <div class="card mt-3">
        <div class="card-header">
            <strong>Breakdown Per Item</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">HPP/Unit</th>
                            <th class="text-end">Total HPP</th>
                            <th class="text-end">Harga Jual/Unit</th>
                            <th class="text-end">Total Revenue</th>
                            <th class="text-end">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itemBreakdown as $item)
                        <tr>
                            <td>{{ $item['item_name'] }}</td>
                            <td class="text-center">{{ number_format($item['qty'], 2) }}</td>
                            <td class="text-end">{{ \App\Helpers\FormatHelper::rupiah($item['hpp_per_unit']) }}</td>
                            <td class="text-end">{{ \App\Helpers\FormatHelper::rupiah($item['total_hpp']) }}</td>
                            <td class="text-end">{{ \App\Helpers\FormatHelper::rupiah($item['selling_price']) }}</td>
                            <td class="text-end fw-semibold">{{ \App\Helpers\FormatHelper::rupiah($item['total_revenue']) }}</td>
                            <td class="text-end text-success fw-bold">{{ \App\Helpers\FormatHelper::rupiah($item['profit']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="card mt-3">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
            Tidak ada transaksi pada tanggal {{ $displayDate }}
        </div>
    </div>
    @endif

</x-app-layout>
