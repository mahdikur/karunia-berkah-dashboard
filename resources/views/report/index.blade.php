<x-app-layout>
    <x-slot name="title">Laporan</x-slot>
    <div class="page-header"><h1>Laporan</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Laporan</li></ol></nav></div>
    <div class="row g-3">
        <div class="col-md-4">
            <a href="{{ route('reports.monthly') }}" class="card text-decoration-none border-0" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
                <div class="card-body text-center py-4">
                    <div style="width:60px;height:60px;border-radius:14px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;"><i class="bi bi-calendar3 text-white" style="font-size:28px;"></i></div>
                    <h5 class="mb-1">Laporan Bulanan</h5>
                    <p class="text-muted mb-0" style="font-size:13px;">Ringkasan transaksi per bulan</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('reports.profit-loss') }}" class="card text-decoration-none border-0" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
                <div class="card-body text-center py-4">
                    <div style="width:60px;height:60px;border-radius:14px;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;"><i class="bi bi-graph-up text-white" style="font-size:28px;"></i></div>
                    <h5 class="mb-1">Laba / Rugi</h5>
                    <p class="text-muted mb-0" style="font-size:13px;">Profit & loss tahunan</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('reports.client') }}" class="card text-decoration-none border-0" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
                <div class="card-body text-center py-4">
                    <div style="width:60px;height:60px;border-radius:14px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;"><i class="bi bi-person-badge text-white" style="font-size:28px;"></i></div>
                    <h5 class="mb-1">Laporan Client</h5>
                    <p class="text-muted mb-0" style="font-size:13px;">Detail transaksi per client</p>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
