<x-app-layout>
    <x-slot name="title">Laporan Client</x-slot>
    <div class="page-header"><h1>Laporan Client</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Laporan</a></li><li class="breadcrumb-item active">Client</li></ol></nav></div>

    <div class="card mb-3"><div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <select class="form-select" name="client_id" required>
                    <option value="">Pilih Client</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="date" class="form-control" name="date_from" value="{{ request('date_from', now()->startOfYear()->format('Y-m-d')) }}"></div>
            <div class="col-md-2"><input type="date" class="form-control" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Lihat</button></div>
        </form>
    </div></div>

    @if($reportData)
    <div class="row g-3 mb-3">
        <div class="col-md-2"><div class="stat-card stat-card-primary"><div class="stat-value">{{ $reportData['total_po'] }}</div><div class="stat-label">Total PO</div></div></div>
        <div class="col-md-2"><div class="stat-card stat-card-info"><div class="stat-value">{{ $reportData['total_invoice'] }}</div><div class="stat-label">Invoice</div></div></div>
        <div class="col-md-2"><div class="stat-card stat-card-primary"><div class="stat-value" style="font-size:14px;">{{ \App\Helpers\FormatHelper::rupiah($reportData['total_sales']) }}</div><div class="stat-label">Total Penjualan</div></div></div>
        <div class="col-md-2"><div class="stat-card stat-card-primary"><div class="stat-value" style="font-size:14px;">{{ \App\Helpers\FormatHelper::rupiah($reportData['total_paid']) }}</div><div class="stat-label">Terbayar</div></div></div>
        <div class="col-md-2"><div class="stat-card stat-card-warning"><div class="stat-value" style="font-size:14px;">{{ \App\Helpers\FormatHelper::rupiah($reportData['total_outstanding']) }}</div><div class="stat-label">Outstanding</div></div></div>
        <div class="col-md-2"><div class="stat-card stat-card-danger"><div class="stat-value" style="font-size:14px;">{{ \App\Helpers\FormatHelper::rupiah($reportData['overdue_amount']) }}</div><div class="stat-label">Overdue</div></div></div>
    </div>

    <div class="card">
        <div class="card-header"><i class="bi bi-receipt me-2"></i>Invoice {{ $selectedClient->name }}</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead><tr><th>No. Invoice</th><th>Tanggal</th><th>Jatuh Tempo</th><th>Total</th><th>Terbayar</th><th>Sisa</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($reportData['invoices'] as $inv)
                        <tr>
                            <td><a href="{{ route('invoices.show', $inv) }}">{{ $inv->invoice_number }}</a></td>
                            <td>{{ $inv->invoice_date->format('d/m/Y') }}</td>
                            <td>{{ $inv->due_date->format('d/m/Y') }}</td>
                            <td>{{ \App\Helpers\FormatHelper::rupiah($inv->total_amount) }}</td>
                            <td class="text-success">{{ \App\Helpers\FormatHelper::rupiah($inv->paid_amount) }}</td>
                            <td class="{{ $inv->remaining_amount > 0 ? 'text-danger fw-bold' : '' }}">{{ \App\Helpers\FormatHelper::rupiah($inv->remaining_amount) }}</td>
                            <td>{!! $inv->status_badge !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-app-layout>
