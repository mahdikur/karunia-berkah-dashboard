<x-app-layout>
    <x-slot name="title">Detail Modal {{ $modal->modal_number }}</x-slot>
    <div class="page-header"><h1>{{ $modal->modal_number }}</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('modals.index') }}">Modal</a></li><li class="breadcrumb-item active">Detail</li></ol></nav></div>
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card"><div class="card-body">
                <table class="table table-borderless mb-0" style="font-size: 13px;">
                    <tr><td class="text-muted">Total</td><td class="fw-bold">{{ \App\Helpers\FormatHelper::rupiah($modal->total_amount) }}</td></tr>
                    <tr><td class="text-muted">Teralokasi</td><td>{{ \App\Helpers\FormatHelper::rupiah($modal->allocated_amount) }}</td></tr>
                    <tr><td class="text-muted">Sisa</td><td class="text-success fw-semibold">{{ \App\Helpers\FormatHelper::rupiah($modal->remaining_amount) }}</td></tr>
                    <tr><td class="text-muted">Tanggal</td><td>{{ $modal->modal_date->format('d/m/Y') }}</td></tr>
                    <tr><td class="text-muted">Dibuat</td><td>{{ $modal->creator->name ?? '-' }}</td></tr>
                </table>
            </div></div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Alokasi ke PO</div>
                <div class="card-body p-0">
                    <table class="table mb-0"><thead><tr><th>No. PO</th><th>Client</th><th>Alokasi</th></tr></thead><tbody>
                        @forelse($modal->allocations as $a)
                        <tr><td>{{ $a->purchaseOrder->po_number }}</td><td>{{ $a->purchaseOrder->client->name ?? '-' }}</td><td>{{ \App\Helpers\FormatHelper::rupiah($a->allocated_amount) }}</td></tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-3 text-muted">Belum ada alokasi.</td></tr>
                        @endforelse
                    </tbody></table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
