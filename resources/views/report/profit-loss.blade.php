<x-app-layout>
    <x-slot name="title">Laba / Rugi</x-slot>
    <div class="page-header"><h1>Laporan Laba / Rugi</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Laporan</a></li><li class="breadcrumb-item active">Laba Rugi</li></ol></nav></div>

    <div class="card mb-3"><div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2"><input type="number" class="form-control" name="year" value="{{ $year }}" min="2020" max="2030"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Lihat</button></div>
        </form>
    </div></div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark"><tr><th class="text-white">Bulan</th><th class="text-white">Pendapatan</th><th class="text-white">HPP</th><th class="text-white">Laba Kotor</th><th class="text-white">Pengeluaran</th><th class="text-white">Laba Bersih</th></tr></thead>
                    <tbody>
                        @php $totals = ['revenue'=>0,'cogs'=>0,'gross_profit'=>0,'expenses'=>0,'net_profit'=>0]; @endphp
                        @foreach($monthlyData as $d)
                            @php foreach($totals as $k => &$v) $v += $d[$k]; @endphp
                            <tr>
                                <td class="fw-semibold">{{ $d['month'] }}</td>
                                <td>{{ \App\Helpers\FormatHelper::rupiah($d['revenue']) }}</td>
                                <td>{{ \App\Helpers\FormatHelper::rupiah($d['cogs']) }}</td>
                                <td>{{ \App\Helpers\FormatHelper::rupiah($d['gross_profit']) }}</td>
                                <td class="text-danger">{{ \App\Helpers\FormatHelper::rupiah($d['expenses']) }}</td>
                                <td class="{{ $d['net_profit'] >= 0 ? 'text-success' : 'text-danger' }} fw-bold">{{ \App\Helpers\FormatHelper::rupiah($d['net_profit']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td>TOTAL</td>
                            <td>{{ \App\Helpers\FormatHelper::rupiah($totals['revenue']) }}</td>
                            <td>{{ \App\Helpers\FormatHelper::rupiah($totals['cogs']) }}</td>
                            <td>{{ \App\Helpers\FormatHelper::rupiah($totals['gross_profit']) }}</td>
                            <td class="text-danger">{{ \App\Helpers\FormatHelper::rupiah($totals['expenses']) }}</td>
                            <td class="{{ $totals['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">{{ \App\Helpers\FormatHelper::rupiah($totals['net_profit']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3"><div class="card-body"><canvas id="plChart" height="200"></canvas></div></div>

    @push('scripts')
    <script>
        const data = @json($monthlyData);
        new Chart(document.getElementById('plChart'), {
            type: 'line', data: {
                labels: data.map(d => d.month),
                datasets: [
                    { label: 'Pendapatan', data: data.map(d => d.revenue), borderColor: '#3b82f6', tension: 0.3 },
                    { label: 'Laba Bersih', data: data.map(d => d.net_profit), borderColor: '#10b981', tension: 0.3 },
                    { label: 'Pengeluaran', data: data.map(d => d.expenses), borderColor: '#ef4444', tension: 0.3 }
                ]
            }, options: { responsive:true, plugins:{ tooltip:{ callbacks:{ label: ctx => ctx.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw) }}}}
        });
    </script>
    @endpush
</x-app-layout>
