<x-app-layout>
    <x-slot name="title">Payments</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>Pembayaran</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Pembayaran</li></ol></nav>
        </div>
        <a href="{{ route('payments.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Catat Pembayaran</a>
    </div>
    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="No. Invoice...">
                </div>
                <div class="col-md-3">
                    <select class="form-select select2-filter" name="client_id" id="filterClient">
                        <option value="">Semua Client</option>
                        @foreach($clients as $c)<option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="payment_method">
                        <option value="">Semua Metode</option>
                        <option value="transfer" {{ request('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="giro" {{ request('payment_method') === 'giro' ? 'selected' : '' }}>Giro</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from', $dateFrom) }}">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to', $dateTo) }}">
                </div>
                <div class="col-md-1"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined') {
            $('#filterClient').select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'Semua Client', allowClear: true });
        }
    });
    </script>
    @endpush
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
        @if($payments->hasPages())<div class="card-footer d-flex justify-content-center">{{ $payments->links('pagination::bootstrap-5') }}</div>@endif
    </div>
</x-app-layout>
