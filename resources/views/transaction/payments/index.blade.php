<x-app-layout>
    <x-slot name="title">Payments</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>Pembayaran</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Pembayaran</li></ol></nav>
        </div>
        <a href="{{ route('payments.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Catat Pembayaran</a>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>No. Invoice</th><th>Client</th><th>Tanggal</th><th>Jumlah</th><th>Metode</th><th>Referensi</th></tr></thead>
                    <tbody>
                        @forelse($payments as $pay)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('invoices.show', $pay->invoice) }}">{{ $pay->invoice->invoice_number }}</a></td>
                                <td>{{ $pay->invoice->client->name }}</td>
                                <td>{{ $pay->payment_date->format('d/m/Y') }}</td>
                                <td class="fw-semibold text-success">{{ \App\Helpers\FormatHelper::rupiah($pay->amount) }}</td>
                                <td>{!! $pay->payment_method_label !!}</td>
                                <td>{{ $pay->reference_number ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())<div class="card-footer d-flex justify-content-center">{{ $payments->links() }}</div>@endif
    </div>
</x-app-layout>
