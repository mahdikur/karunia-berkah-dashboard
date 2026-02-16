<x-app-layout>
    <x-slot name="title">Detail Client - {{ $client->name }}</x-slot>
    <div class="page-header">
        <h1>{{ $client->name }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Client</a></li><li class="breadcrumb-item active">Detail</li></ol></nav>
    </div>
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    @if($client->logo)
                        <img src="{{ Storage::url($client->logo) }}" class="rounded mb-3" style="max-width: 120px;">
                    @endif
                    <h5>{{ $client->name }}</h5>
                    <p class="text-muted"><code>{{ $client->code }}</code></p>
                </div>
                <ul class="list-group list-group-flush" style="font-size: 13px;">
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">PIC</span><span>{{ $client->pic_name ?? '-' }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Telepon</span><span>{{ $client->phone ?? '-' }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Email</span><span>{{ $client->email ?? '-' }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Termin</span><span>{{ $client->payment_terms_label }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Credit Limit</span><span>{{ $client->credit_limit ? \App\Helpers\FormatHelper::rupiah($client->credit_limit) : '-' }}</span></li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Outstanding</span>
                        <span class="fw-bold {{ $outstandingAmount > 0 ? 'text-danger' : 'text-success' }}">{{ \App\Helpers\FormatHelper::rupiah($outstandingAmount) }}</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-cart me-2"></i>PO Terbaru</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0"><thead><tr><th>No. PO</th><th>Tanggal</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($client->purchaseOrders as $po)
                                    <tr>
                                        <td><a href="{{ route('purchase-orders.show', $po) }}">{{ $po->po_number }}</a></td>
                                        <td>{{ $po->po_date->format('d/m/Y') }}</td>
                                        <td>{!! $po->status_badge !!}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-3 text-muted">Belum ada PO</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><i class="bi bi-receipt me-2"></i>Invoice Terbaru</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0"><thead><tr><th>No. Invoice</th><th>Total</th><th>Sisa</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($client->invoices as $inv)
                                    <tr>
                                        <td><a href="{{ route('invoices.show', $inv) }}">{{ $inv->invoice_number }}</a></td>
                                        <td>{{ \App\Helpers\FormatHelper::rupiah($inv->total_amount) }}</td>
                                        <td>{{ \App\Helpers\FormatHelper::rupiah($inv->remaining_amount) }}</td>
                                        <td>{!! $inv->status_badge !!}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada invoice</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
