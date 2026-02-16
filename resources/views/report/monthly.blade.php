<x-app-layout>
    <x-slot name="title">Laporan Bulanan</x-slot>
    <div class="page-header"><h1>Laporan Bulanan</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Laporan</a></li><li class="breadcrumb-item active">Bulanan</li></ol></nav></div>

    <div class="card mb-3"><div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <select class="form-select" name="month">
                    @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $m)
                        <option value="{{ $i+1 }}" {{ $month == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="number" class="form-control" name="year" value="{{ $year }}" min="2020" max="2030"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Lihat</button></div>
        </form>
    </div></div>

    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="stat-card stat-card-primary"><div class="stat-value">{{ \App\Helpers\FormatHelper::rupiah($revenue) }}</div><div class="stat-label">Pendapatan</div></div></div>
        <div class="col-md-3"><div class="stat-card stat-card-warning"><div class="stat-value">{{ \App\Helpers\FormatHelper::rupiah($cogs) }}</div><div class="stat-label">HPP</div></div></div>
        <div class="col-md-3"><div class="stat-card stat-card-info"><div class="stat-value">{{ \App\Helpers\FormatHelper::rupiah($grossProfit) }}</div><div class="stat-label">Laba Kotor</div></div></div>
        <div class="col-md-3"><div class="stat-card {{ $netProfit >= 0 ? 'stat-card-primary' : 'stat-card-danger' }}"><div class="stat-value">{{ \App\Helpers\FormatHelper::rupiah($netProfit) }}</div><div class="stat-label">Laba Bersih ({{ number_format($profitMargin, 1) }}%)</div></div></div>
    </div>

    <div class="card">
        <div class="card-header">Breakdown per Client</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0"><thead><tr><th>#</th><th>Client</th><th>Total Penjualan</th></tr></thead>
            <tbody>
                @forelse($clientBreakdown as $i => $c)
                    <tr><td>{{ $i+1 }}</td><td class="fw-semibold">{{ $c->name }}</td><td>{{ \App\Helpers\FormatHelper::rupiah($c->total_revenue) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center py-3 text-muted">Tidak ada data.</td></tr>
                @endforelse
            </tbody></table>
        </div>
    </div>
</x-app-layout>
