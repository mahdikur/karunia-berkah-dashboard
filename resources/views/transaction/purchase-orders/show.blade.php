<x-app-layout>
    <x-slot name="title">Detail PO - {{ $purchaseOrder->po_number }}</x-slot>
    <div class="page-header d-flex justify-content-between align-items-start">
        <div>
            <h1>{{ $purchaseOrder->po_number }}</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">PO</a></li><li class="breadcrumb-item active">Detail</li></ol></nav>
        </div>
        <div class="d-flex gap-2">
            @if(in_array($purchaseOrder->status, ['draft', 'rejected']))
                <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
            @endif
            @if($purchaseOrder->status === 'approved')
                <a href="{{ route('delivery-notes.create', ['po_id' => $purchaseOrder->id]) }}" class="btn btn-outline-success"><i class="bi bi-truck me-1"></i>Buat Surat Jalan</a>
                <a href="{{ route('invoices.create', ['po_id' => $purchaseOrder->id]) }}" class="btn btn-outline-info"><i class="bi bi-receipt me-1"></i>Buat Invoice</a>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 text-center">{!! $purchaseOrder->status_badge !!}</div>
                    <table class="table table-borderless mb-0" style="font-size: 13px;">
                        <tr><td class="text-muted">Client</td><td class="fw-semibold">{{ $purchaseOrder->client->name }}</td></tr>
                        <tr><td class="text-muted">Tgl PO</td><td>{{ $purchaseOrder->po_date->format('d/m/Y') }}</td></tr>
                        <tr><td class="text-muted">Tgl Kirim</td><td>{{ $purchaseOrder->delivery_date?->format('d/m/Y') ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Dibuat oleh</td><td>{{ $purchaseOrder->creator->name ?? '-' }}</td></tr>
                        @if($purchaseOrder->approver)
                            <tr><td class="text-muted">Approved oleh</td><td>{{ $purchaseOrder->approver->name }}</td></tr>
                            <tr><td class="text-muted">Tgl Approve</td><td>{{ $purchaseOrder->approved_at?->format('d/m/Y H:i') }}</td></tr>
                        @endif
                        @if($purchaseOrder->rejected_reason)
                            <tr><td class="text-muted">Alasan Tolak</td><td class="text-danger">{{ $purchaseOrder->rejected_reason }}</td></tr>
                        @endif
                        @if($purchaseOrder->notes)
                            <tr><td class="text-muted">Catatan</td><td>{{ $purchaseOrder->notes }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Approval Actions --}}
            @if(auth()->user()->isSuperadmin() && $purchaseOrder->status === 'pending_approval')
            <div class="card mt-3">
                <div class="card-header bg-warning text-dark"><i class="bi bi-exclamation-circle me-2"></i>Approval</div>
                <div class="card-body">
                    <form action="{{ route('purchase-orders.approve', $purchaseOrder) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve PO ini?')"><i class="bi bi-check-lg me-1"></i>Approve</button>
                    </form>
                    <button class="btn btn-outline-danger w-100" data-bs-toggle="collapse" data-bs-target="#rejectForm"><i class="bi bi-x-lg me-1"></i>Reject</button>
                    <div class="collapse mt-2" id="rejectForm">
                        <form action="{{ route('purchase-orders.reject', $purchaseOrder) }}" method="POST">
                            @csrf
                            <textarea class="form-control mb-2" name="rejected_reason" placeholder="Alasan penolakan..." required></textarea>
                            <button type="submit" class="btn btn-danger btn-sm w-100">Konfirmasi Reject</button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Daftar Item</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Satuan</th>
                                    <th>Harga Jual</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->items as $i => $poItem)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $poItem->item->name ?? '-' }}</td>
                                        <td>{{ number_format($poItem->quantity, 2) }}</td>
                                        <td>{{ $poItem->unit }}</td>
                                        <td>{{ \App\Helpers\FormatHelper::rupiah($poItem->selling_price) }}</td>
                                        <td class="fw-semibold">{{ \App\Helpers\FormatHelper::rupiah($poItem->subtotal) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="5" class="text-end fw-bold">Total</td>
                                    <td class="fw-bold">{{ \App\Helpers\FormatHelper::rupiah($purchaseOrder->total_amount) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Delivery Notes --}}
            @if($purchaseOrder->deliveryNotes->count())
            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-truck me-2"></i>Surat Jalan</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0"><thead><tr><th>No. SJ</th><th>Tanggal</th><th>Tipe</th><th>Status</th></tr></thead><tbody>
                        @foreach($purchaseOrder->deliveryNotes as $dn)
                            <tr><td><a href="{{ route('delivery-notes.show', $dn) }}">{{ $dn->dn_number }}</a></td><td>{{ $dn->dn_date->format('d/m/Y') }}</td><td>{{ ucfirst($dn->delivery_type) }}</td><td>{!! $dn->status_badge !!}</td></tr>
                        @endforeach
                    </tbody></table>
                </div>
            </div>
            @endif

            {{-- Invoice --}}
            @if($purchaseOrder->invoice)
            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-receipt me-2"></i>Invoice</div>
                <div class="card-body">
                    <a href="{{ route('invoices.show', $purchaseOrder->invoice) }}" class="fw-semibold">{{ $purchaseOrder->invoice->invoice_number }}</a>
                    <span class="ms-2">{!! $purchaseOrder->invoice->status_badge !!}</span>
                    <span class="ms-2">{{ \App\Helpers\FormatHelper::rupiah($purchaseOrder->invoice->total_amount) }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
